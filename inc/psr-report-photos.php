<?php
/**
 * Immagini riservate delle segnalazioni, mai nella libreria Media pubblica.
 * Immagini raster ricodificate JPEG; un PDF semplice opzionale, solo download riservato.
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
/**
 * PDF: controlli conservativi, NON un antivirus o un parser PDF completo.
 * Non renderizzare, eseguire, decomprimere o pubblicare il documento originale.
 * I PDF cifrati, con object stream o elementi attivi sono rifiutati.
 */
function psr_report_pdf_available() {
    return class_exists('finfo') && (!defined('PSR_REPORT_PDF_ENABLED') || PSR_REPORT_PDF_ENABLED !== false);
}
function psr_report_pdf_encode($path) {
    if (!psr_report_pdf_available()) {
        return psr_report_photos_error('Caricamento PDF non disponibile. Puoi inviare immagini.');
    }
    $size = @filesize($path);
    if (!$size || $size > 2 * 1024 * 1024) {
        return psr_report_photos_error('Il PDF deve avere dimensione massima di 2 MB.');
    }
    $bytes = @file_get_contents($path, false, null, 0, 2 * 1024 * 1024 + 1);
    if (!is_string($bytes) || strlen($bytes) !== $size
        || !preg_match('/\A%PDF-(?:1\.[0-7]|2\.0)[\r\n]/', $bytes)
        || !preg_match('/startxref\s+([0-9]{1,10})\s+%%EOF[ \t\r\n]*\z/D', $bytes, $xref)) {
        return psr_report_photos_error('PDF non valido o danneggiato.');
    }
    $offset = (int)$xref[1];
    if ($offset <= 0 || $offset >= $size
        || !preg_match('/\A(?:xref\b|[0-9]+\s+[0-9]+\s+obj\b)/', substr($bytes, $offset, 80))) {
        return psr_report_photos_error('Struttura PDF non valida.');
    }
    try {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        if ($finfo->buffer($bytes) !== 'application/pdf') {
            return psr_report_photos_error('Formato PDF non riconosciuto.');
        }
    } catch (Throwable $e) { return psr_report_photos_error('Verifica PDF non disponibile.'); }
    // Normalizza gli escape esadecimali dei nomi PDF prima del controllo.
    $normalized = preg_replace_callback('/#([0-9a-fA-F]{2})/', static function ($m) {
        return chr(hexdec($m[1]));
    }, $bytes);
    if (!is_string($normalized)
        || preg_match('~/(?:Encrypt|ObjStm|JavaScript|JS|AA|OpenAction|Launch|EmbeddedFile|EmbeddedFiles|Filespec|EF|RichMedia|XFA|AcroForm|SubmitForm|ImportData|GoToR|GoToE|URI|Movie|Sound|Rendition|3D|Collection|FileAttachment|Screen|JBIG2Decode)(?=[\x00-\x20()<>\[\]{}/%]|$)~i', $normalized)) {
        return psr_report_photos_error('PDF protetto, interattivo o con contenuto non ammesso. Esporta una copia PDF semplice o allega immagini.');
    }
    // Consente di collegare un antivirus locale tramite integrazione server.
    // Nessun invio del documento a servizi esterni. In modalita' obbligatoria
    // un esito mancante impedisce il caricamento, non la segnalazione testuale.
    $scan = apply_filters('psr_report_pdf_scan_result', null, $path);
    if ($scan === false || (defined('PSR_REPORT_PDF_REQUIRE_SCAN') && PSR_REPORT_PDF_REQUIRE_SCAN && $scan !== true)) {
        return psr_report_photos_error('PDF non accettato dal controllo di sicurezza.');
    }
    return 'pdf:' . base64_encode($bytes);
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
    $pdf_count = 0;
    for ($i = 0; $i < (int)$count; $i++) {
        $path = $files['report_photo_' . $i]['tmp_name'];
        if (!is_uploaded_file($path)) { return psr_report_photos_error(); }
        $prefix = @file_get_contents($path, false, null, 0, 5);
        if ($prefix === '%PDF-') {
            if (++$pdf_count > 1) { return psr_report_photos_error('Puoi allegare al massimo un PDF.'); }
            $encoded = psr_report_pdf_encode($path);
        } else {
            $encoded = psr_report_photo_encode($path);
        }
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
        $stored = get_post_meta($post->ID, '_psr_report_photo_' . $i, true);
        if (!is_string($stored) || $stored === '') { continue; }
        $found = true;
        $url = psr_report_photo_url($post->ID, $i);
        if (strpos($stored, 'pdf:') === 0) {
            echo '<p><strong>Documento PDF</strong> — scaricare solo se necessario e verificare con un antivirus aggiornato.</p>';
            echo '<p><a class="button" href="' . esc_url(add_query_arg('download', '1', $url)) . '">Scarica documento PDF</a></p>';
            continue;
        }
        echo '<p><img loading="lazy" src="' . esc_url($url) . '" alt="Foto del disservizio ' . ($i + 1) . '" style="max-width:100%;max-height:420px;object-fit:contain"></p>';
        echo '<p><a class="button" href="' . esc_url(add_query_arg('download', '1', $url)) . '">Scarica immagine ' . ($i + 1) . '</a></p>';
    }
    if (!$found) { echo '<p>Nessun allegato.</p>'; }
}
add_action('add_meta_boxes_segnala_disservizio', static function () {
    add_meta_box('psr-report-photos', 'Allegati del disservizio', 'psr_report_photos_metabox', 'segnala_disservizio', 'normal', 'default');
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
    if (is_string($encoded) && strpos($encoded, 'pdf:') === 0) {
        $pdf = strlen($encoded) <= 2796210 ? base64_decode(substr($encoded, 4), true) : false;
        if (!$pdf || strlen($pdf) > 2 * 1024 * 1024 || strpos($pdf, '%PDF-') !== 0) {
            wp_die('Documento non disponibile.', '', array('response'=>404));
        }
        nocache_headers();
        header('Cache-Control: private, no-store, max-age=0', true);
        header('X-Content-Type-Options: nosniff');
        header("Content-Security-Policy: sandbox; default-src 'none'");
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="documento-disservizio-' . $id . '-' . ((int)$index + 1) . '.pdf"');
        header('Content-Length: ' . strlen($pdf));
        echo $pdf;
        exit;
    }
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
