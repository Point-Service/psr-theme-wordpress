<?php
/**
 * PSR/CMB2 smoke test: run via WP-CLI in an isolated, clean WordPress instance.
 * No real Comune data, no remote service, no migrations.
 */
if (!defined('ABSPATH')) { fwrite(STDERR, "Not running inside WordPress\n"); exit(1); }
function psr_cmb2_ci_assert($passed, $message) {
    if (!$passed) { fwrite(STDERR, "FAILED: " . $message . "\n"); exit(1); }
    fwrite(STDOUT, "OK: " . $message . "\n");
}
psr_cmb2_ci_assert(defined('CMB2_VERSION') && CMB2_VERSION === '2.13.5', 'official CMB2 2.13.5 bootstrapped');
psr_cmb2_ci_assert(class_exists('CMB2') && class_exists('CMB2_Field'), 'CMB2 classes load');
psr_cmb2_ci_assert(function_exists('cmb2_get_metabox') && function_exists('new_cmb2_box'), 'CMB2 public APIs exist');
$box = cmb2_get_metabox('psr_cmb2_ci');
psr_cmb2_ci_assert($box instanceof CMB2, 'CMB2 test metabox registered');
psr_cmb2_ci_assert($box->get_field('psr_cmb2_ci_text') instanceof CMB2_Field, 'CMB2 text field works');
psr_cmb2_ci_assert($box->get_field('psr_cmb2_ci_files') instanceof CMB2_Field, 'CMB2 file-list field works');
$post_id = wp_insert_post(array('post_type'=>'post','post_title'=>'PSR CMB2 test','post_status'=>'draft'), true);
psr_cmb2_ci_assert(!is_wp_error($post_id) && (int)$post_id > 0, 'WordPress draft created');
$box->save_fields($post_id, 'post', array('psr_cmb2_ci_text'=>'Campo di prova'));
psr_cmb2_ci_assert(get_post_meta($post_id,'psr_cmb2_ci_text',true)==='Campo di prova', 'CMB2 saves text metadata correctly');
psr_cmb2_ci_assert(class_exists('CMB2_Type_File_List'), 'File-list implementation available');
psr_cmb2_ci_assert(class_exists('CMB2_REST_Controller_Fields'), 'CMB2 REST controller available');
wp_delete_post($post_id, true);
fwrite(STDOUT, "ALL CMB2 WORDPRESS SMOKE CHECKS PASSED\n");
