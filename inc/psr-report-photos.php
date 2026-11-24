<?php
/**
 * Immagini riservate delle segnalazioni, mai nella libreria Media pubblica.
 * Solo immagini raster ricodificate JPEG, senza nome originale o EXIF.
 */
if (!defined('ABSPATH')) { exit; }

function psr_report_photos_available() {
    return function_exists('imagecreatefromstring') && function_exists('imagecreatetruecolor')
        && function_exists('imagejpeg') && function_exists('getimagesize');
}
function psr_report_photos_error($message = 'Immagine non valida. Scegli una foto JPEG, PNG o WebP.') {
    return new WP_Error('psr_report_photo_invalid', $message);
}
function psr_report_photo_encode($path) {
    if (!psr_report_photos_available()) {
        return psr_report_photos_error('Caricamento immagini non disponibile. Puoi inviare senza allegati.');
    }
    $size = @filesize($path);
    if (!$size || $size > 2 * 1024 * 1024) { return psr_report_photos_error('Immagine troppo grande (massimo 2 MB dopo la conversione).'); }
    $info = @getimagesize($path);
    if (!$info || !in_array($info[2], array(IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP), true)) { return psr_report_photos_error(); }
    $w0 = (int)$info[0]; $h0 = (int)$info[1];
    if ($w0 < 1 || $h0 < 1 || $w0 > 6000 || $h0 > 6000 || $w0 * $h0 > 4000000) {
        return psr_report_photos_error('Dimensioni immagine troppo grandi.');
    }
    $bytes = @file_get_contents($path);
    $image = is_string($bytes) ? @imagecreatefromstring($bytes) : false;
    unset($bytes);
    if (!$image) { return psr_report_photos_error(); }
    $scale = min(1, 1600 / max($w0, $h0));
    $w = max(1, (int)round($w0 * $scale)); $h = max(1, (int)round($h0 * $scale));
    $out = imagecreatetruecolor($w, $h);
    if (!$out) { imagedestroy($image); return psr_report_photos_error(); }
    imagefill($out, 0, 0, imagecolorallocate($out, 255, 255, 255));
    $copied = imagecopyresampled($out, $image, 0, 0, 0, 0, $w, $h, $w0, $h0);
    imagedestroy($image);
    if (!$copied) { imagedestroy($out); return psr_report_photos_error(); }
    $jpeg = '';
    foreach (array(82, 70, 55, 40) as $quality) {
        ob_start();
        $ok = imagejpeg($out, null, $quality);
        $candidate = ob_get_clean();
        if ($ok && is_string($candidate) && strlen($candidate) > 0 && strlen($candidate) <= 480 * 1024) {
            $jpeg = $candidate; break;
        }
    }
    imagedestroy($out);
    return $jpeg !== '' ? base64_encode($jpeg) : psr_report_photos_error('Immagine troppo dettagliata. Prova una foto più piccola.');
}
/** Strict file count, shape and upload origin checks before decoding. */
function psr_report_photos_shape_valid($files, $count) {
    if (!is_string($count) || !in_array($count, array('1','2','3'), true)
        || !is_array($files) || count($files) !== (int)$count) { return false; }
    for ($i = 0; $i < (int)$count; $i++) {
        $file = $files['report_photo_' . $i] ?? null;
        if (!is_array($file) || ($file['error'] ?? null) !== UPLOAD_ERR_OK
            || !is_string($file['tmp_name'] ?? null) || !is_int($file['size'] ?? null)
            || $file['size'] < 1 || $file['size'] > 2 * 1024 * 1024) { return false; }
    }
    return true;
}
function psr_report_photos_prepare($files, $count) {
    if (!psr_report_photos_shape_valid($files, $count)) {
        return psr_report_photos_error('Caricamento incompleto o limite immagini superato. Riprova.');
    }
    $photos = array();
    for ($i = 0; $i < (int)$count; $i++) {
        $path = $files['report_photo_' . $i]['tmp_name'];
        if (!is_uploaded_file($path)) { return psr_report_photos_error(); }
        $encoded = psr_report_photo_encode($path);
        if (is_wp_error($encoded)) { return $encoded; }
        $photos[] = $encoded;
    }
    return $photos;
}
function psr_report_photo_authorized($post_id) {
    $post = get_post($post_id);
    return $post && $post->post_type === 'segnala_disservizio'
        && is_user_logged_in() && current_user_can('read_post', $post_id)
        && current_user_can('edit_post', $post_id);
}
function psr_report_photo_url($post_id, $index) {
    return add_query_arg(array('action'=>'psr_report_photo','post_id'=>$post_id,'photo'=>$index,
        '_wpnonce'=>wp_create_nonce('psr_report_photo_' . $post_id . '_' . $index)), admin_url('admin-post.php'));
}
function psr_report_photos_metabox($post) {
    if (!psr_report_photo_authorized($post->ID)) { return; }
    $found = false;
    for ($i = 0; $i < 3; $i++) {
        if (!get_post_meta($post->ID, '_psr_report_photo_' . $i, true)) { continue; }
        $found = true;
        $url = psr_report_photo_url($post->ID, $i);
        echo '<p><img loading="lazy" src="' . esc_url($url) . '" alt="Foto del disservizio ' . ($i + 1) . '" style="max-width:100%;max-height:420px;object-fit:contain"></p>';
        echo '<p><a class="button" href="' . esc_url(add_query_arg('download', '1', $url)) . '">Scarica immagine ' . ($i + 1) . '</a></p>';
    }
    if (!$found) { echo '<p>Nessuna immagine allegata.</p>'; }
}
add_action('add_meta_boxes_segnala_disservizio', static function () {
    add_meta_box('psr-report-photos', 'Foto e immagini del disservizio', 'psr_report_photos_metabox', 'segnala_disservizio', 'normal', 'default');
});
function psr_report_photo_download() {
    $raw = $_GET['post_id'] ?? '';
    $index = $_GET['photo'] ?? '';
    $id = is_string($raw) && ctype_digit($raw) ? (int)$raw : 0;
    if (!$id || !is_string($index) || !in_array($index, array('0','1','2'), true) || !psr_report_photo_authorized($id)) {
        wp_die('Accesso non consentito.', '', array('response'=>403));
    }
    check_admin_referer('psr_report_photo_' . $id . '_' . $index);
    $encoded = get_post_meta($id, '_psr_report_photo_' . $index, true);
    $jpeg = is_string($encoded) && strlen($encoded) <= 655360 ? base64_decode($encoded, true) : false;
    if (!$jpeg || substr($jpeg, 0, 3) !== "\xFF\xD8\xFF") { wp_die('Immagine non disponibile.', '', array('response'=>404)); }
    nocache_headers();
    header('Cache-Control: private, no-store, max-age=0', true);
    header('X-Content-Type-Options: nosniff');
    header('Content-Type: image/jpeg');
    header('Content-Disposition: ' . (($_GET['download'] ?? '') === '1' ? 'attachment' : 'inline') . '; filename="foto-disservizio-' . $id . '-' . ((int)$index + 1) . '.jpg"');
    header('Content-Length: ' . strlen($jpeg));
    echo $jpeg;
    exit;
}
add_action('admin_post_psr_report_photo', 'psr_report_photo_download');
