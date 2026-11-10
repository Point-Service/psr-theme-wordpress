<?php
/**
 * PSR Security Suite - disattivazione selettiva dei commenti WordPress.
 *
 * Interessa esclusivamente commenti/pingback/trackback nativi su:
 * - articoli (post)
 * - pagine (page)
 * - allegati (attachment)
 *
 * NON modifica post type personalizzati, form di segnalazione disservizi,
 * assistenza, appuntamenti, richieste civiche, AJAX, REST generiche o OTP.
 * Nessuna scansione, migrazione, chiamata HTTP o scrittura DB.
 */
if (!defined('ABSPATH')) { exit; }

/** I post type non elencati (es. segnala_disservizio) restano invariati. */
function psr_native_comments_target($post_id) {
    $post_id = (int)$post_id;
    if ($post_id <= 0) { return false; }
    $type = get_post_type($post_id);
    return in_array($type, array('post', 'page', 'attachment'), true);
}

/** Il core WordPress usa comments_open() anche per respingere wp-comments-post.php. */
function psr_native_comments_close($open, $post_id) {
    return psr_native_comments_target($post_id) ? false : $open;
}
add_filter('comments_open', 'psr_native_comments_close', PHP_INT_MAX, 2);
add_filter('pings_open', 'psr_native_comments_close', PHP_INT_MAX, 2);

/** Non mostrare i commenti vecchi sulle sole pagine/articoli pubblici. */
function psr_native_comments_hide_public($comments, $post_id) {
    if (!is_admin() && psr_native_comments_target($post_id)) { return array(); }
    return $comments;
}
add_filter('comments_array', 'psr_native_comments_hide_public', PHP_INT_MAX, 2);

/**
 * Difesa aggiuntiva per wp_new_comment() e commenti inseriti dal core/plugin.
 * WP_Error dal filtro pre_comment_approved interrompe l'inserimento (WP 4.9+).
 * Non bloccare tipi di commento personalizzati usati eventualmente da altri servizi.
 */
function psr_native_comments_reject($approved, $commentdata) {
    if (is_wp_error($approved) || !is_array($commentdata)) { return $approved; }
    $type = isset($commentdata['comment_type']) && is_string($commentdata['comment_type'])
        ? $commentdata['comment_type'] : '';
    if (!in_array($type, array('', 'comment', 'pingback', 'trackback'), true)) { return $approved; }
    if (!psr_native_comments_target($commentdata['comment_post_ID'] ?? 0)) { return $approved; }
    return new WP_Error('psr_native_comments_closed',
        'I commenti agli articoli e alle pagine del sito sono disabilitati.',
        array('status' => 403));
}
add_filter('pre_comment_approved', 'psr_native_comments_reject', PHP_INT_MAX, 2);

/** Esplicita protezione REST sui soli commenti nativi degli articoli/pagine. */
function psr_native_comments_rest_reject($prepared_comment, $request) {
    if (is_wp_error($prepared_comment) || !is_array($prepared_comment)) { return $prepared_comment; }
    // Non impedire a un amministratore di moderare o modificare commenti storici.
    if (is_object($request) && method_exists($request, 'get_param')
        && (int)$request->get_param('id') > 0) { return $prepared_comment; }
    return psr_native_comments_reject($prepared_comment, $prepared_comment);
}
add_filter('rest_pre_insert_comment', 'psr_native_comments_rest_reject', PHP_INT_MAX, 2);

/** I nuovi articoli e pagine nascono con commenti e ping chiusi. */
function psr_native_comments_default_closed($status, $post_type, $comment_type) {
    if (in_array($post_type, array('post', 'page', 'attachment'), true)
        && in_array($comment_type, array('comment', 'pingback', 'trackback'), true)) {
        return 'closed';
    }
    return $status;
}
add_filter('get_default_comment_status', 'psr_native_comments_default_closed', PHP_INT_MAX, 3);

/** Nasconde soltanto i metabox commenti dalle schermate editoriali standard. */
function psr_native_comments_hide_editor_support() {
    if (!is_admin()) { return; }
    foreach (array('post', 'page', 'attachment') as $type) {
        if (post_type_exists($type)) {
            remove_post_type_support($type, 'comments');
            remove_post_type_support($type, 'trackbacks');
        }
    }
}
add_action('init', 'psr_native_comments_hide_editor_support', 120);

/**
 * Le raccolte REST dei commenti non usano comments_array: escludere i
 * commenti dei soli tipi nativi dalla query pubblica, compresi i risultati
 * senza filtro ?post=. Non tocca gli altri post type o la moderazione.
 */
function psr_native_comments_rest_query($args, $request) {
    if (!is_array($args) || current_user_can('moderate_comments')) { return $args; }
    $post_types = get_post_types(array(), 'names');
    $permitted = array_values(array_diff((array) $post_types, array('post', 'page', 'attachment')));
    // Conserva le eventuali restrizioni post_type gia' imposte da plugin terzi.
    if (isset($args['post_type']) && $args['post_type'] !== '' && $args['post_type'] !== 'any') {
        $permitted = array_values(array_intersect($permitted, (array) $args['post_type']));
    }
    if ($permitted) {
        $args['post_type'] = $permitted;
    } else {
        // Valore impossibile: mantiene vuota la raccolta se non esistono CPT.
        $args['post__in'] = array(0);
    }
    return $args;
}
add_filter('rest_comment_query', 'psr_native_comments_rest_query', PHP_INT_MAX, 2);

/**
 * La rotta REST GET/HEAD /wp/v2/comments/{id} usa un lookup diretto,
 * percio' non passa per rest_comment_query.
 * Per i non moderatori nascondi i commenti nativi con 404 (senza cancellarli).
 */
function psr_native_comments_rest_single($result, $handler, $request) {
    if (is_wp_error($result) || current_user_can('moderate_comments')
        || !($request instanceof WP_REST_Request)
        || !in_array($request->get_method(), array('GET', 'HEAD'), true)
        || !preg_match('~^/wp/v2/comments/([0-9]+)$~D', $request->get_route(), $matches)) {
        return $result;
    }
    $comment = get_comment((int) $matches[1]);
    if ($comment instanceof WP_Comment && psr_native_comments_target($comment->comment_post_ID)) {
        return new WP_Error('psr_native_comment_not_found', 'Commento non disponibile.', array('status' => 404));
    }
    return $result;
}
add_filter('rest_request_before_callbacks', 'psr_native_comments_rest_single', PHP_INT_MAX, 3);
