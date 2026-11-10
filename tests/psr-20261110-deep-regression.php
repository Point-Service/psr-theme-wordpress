<?php
/**
 * PSR deep regression test for 2026-11-10 fixes.
 * Run via WP-CLI with isolated WordPress, dummy posts and no Comune data.
 */
if (!defined('ABSPATH') || !(defined('WP_CLI') && WP_CLI)) { exit(1); }
function psr_audit_assert($value, $label) {
    if (!$value) { fwrite(STDERR, "FAIL: {$label}\n"); exit(1); }
    echo "PASS: {$label}\n";
}
psr_audit_assert(defined('CMB2_VERSION') && CMB2_VERSION === '2.13.5', 'CMB2 version');
psr_audit_assert(function_exists('psr_native_comments_target'), 'Native comment guard loaded');
psr_audit_assert(function_exists('psr_booking_slot_available'), 'Booking guard loaded');
psr_audit_assert(function_exists('psr_public_guard_rate_allow'), 'Public form guard loaded');
psr_audit_assert(post_type_exists('segnala_disservizio'), 'Civic report type available');

$post = wp_insert_post(array('post_type'=>'post','post_title'=>'CI Article','post_status'=>'publish','comment_status'=>'open'),true);
$custom = wp_insert_post(array('post_type'=>'segnala_disservizio','post_title'=>'CI Civic','post_status'=>'publish','comment_status'=>'open'),true);
psr_audit_assert(!is_wp_error($post) && !is_wp_error($custom) && $post>0 && $custom>0, 'Test posts created');
$comment = 0;
$attachment = 0;
try {
    psr_audit_assert(!comments_open($post), 'Native post discussion is closed');
    psr_audit_assert(comments_open($custom), 'Civic report discussion settings unaffected');
    $comment = wp_insert_comment(array(
       'comment_post_ID'=>$post,'comment_content'=>'Old comment that should not be publicly indexed',
       'comment_author'=>'Historical','comment_author_email'=>'history@example.test',
       'comment_approved'=>1,
    ));
    psr_audit_assert($comment>0, 'Historical comment inserted for test');
    psr_audit_assert(psr_native_comments_hide_public(array('old'),$post)===array(),'Legacy comment hidden in theme template');

    $req = new WP_REST_Request('GET','/wp/v2/comments');
    $req->set_param('post',$post);
    $response = rest_do_request($req);
    $data = $response->get_data();
    echo 'OBSERVED: public REST comments listing count=' . (is_array($data) ? count($data) : -1) . PHP_EOL;
    psr_audit_assert(!is_wp_error($response) && $response->get_status()===200
        && is_array($data) && count($data)===0, 'Historical native comments are hidden from public REST listing');

    $single = rest_do_request(new WP_REST_Request('GET','/wp/v2/comments/'.$comment));
    echo 'OBSERVED: public REST single old comment HTTP=' . $single->get_status() . PHP_EOL;
    psr_audit_assert($single->get_status()>=400, 'Historical native comment not reachable by REST ID');

    $create = new WP_REST_Request('POST','/wp/v2/comments');
    $create->set_param('post',$post);
    $create->set_param('content','A new spam comment');
    $create->set_param('author_name','Bot');
    $create->set_param('author_email','bot@example.test');
    $rejected = rest_do_request($create);
    psr_audit_assert($rejected->get_status()>=400, 'New native comment blocked via REST');
    psr_audit_assert(psr_native_comments_default_closed('open','post','comment')==='closed', 'New native posts default closed');

    $uploaded = wp_upload_bits('psr-cmb2-regression.pdf',null,"%PDF-1.4\n1 0 obj <</Type/Catalog>> endobj\n%%EOF");
    psr_audit_assert(empty($uploaded['error']) && !empty($uploaded['file']), 'Dummy PDF fixture uploaded');
    $attachment = wp_insert_attachment(array(
      'post_title'=>'PSR CMB2 document','post_status'=>'inherit',
      'post_mime_type'=>'application/pdf',
    ),$uploaded['file'],$post,true);
    psr_audit_assert(!is_wp_error($attachment) && $attachment>0,'Native WordPress attachment registered');
    $attachmentUrl=wp_get_attachment_url($attachment);
    psr_audit_assert(is_string($attachmentUrl) && $attachmentUrl!=='', 'Attachment URL available');
    $box=new_cmb2_box(array('id'=>'psr_ci_file_list','title'=>'File-list','object_types'=>array('post')));
    $box->add_field(array('id'=>'psr_ci_documenti','name'=>'Documenti','type'=>'file_list'));
    $box->save_fields($post,'post',array('psr_ci_documenti'=>array($attachmentUrl=>$attachment)));
    $saved=get_post_meta($post,'psr_ci_documenti',true);
    echo 'OBSERVED: file-list meta saved=' . wp_json_encode($saved) . PHP_EOL;
    psr_audit_assert(is_array($saved) && count($saved)===1
      && (int)($saved[$attachmentUrl]??0)===(int)$attachment, 'CMB2 file_list attachment preserved with WordPress attachment ID');
} finally {
    if($comment)wp_delete_comment($comment,true);
    if($attachment)wp_delete_attachment($attachment,true);
    wp_delete_post($post,true);
    wp_delete_post($custom,true);
}
echo "ALL PSR DEEP REGRESSION CHECKS PASSED\n";
