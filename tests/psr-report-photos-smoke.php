<?php
define('ABSPATH', __DIR__);
class WP_Error { public function __construct($code, $message) {} }
function is_wp_error($v) { return $v instanceof WP_Error; }
function add_action($hook, $cb) {}
function apply_filters($hook, $value, $path) { return $GLOBALS['mock_pdf_scan'] ?? $value; }
function get_post($id) { return (object)array('post_type'=>$id === 7 ? 'segnala_disservizio' : 'post'); }
function is_user_logged_in() { return $GLOBALS['logged']; }
function current_user_can($cap, $id) { return $GLOBALS['caps'][$cap] ?? false; }
require dirname(__DIR__) . '/inc/psr-report-photos.php';
function verify($condition, $name) { if (!$condition) { fwrite(STDERR, "FAIL: $name\n"); exit(1); } echo "PASS: $name\n"; }
verify(psr_report_photos_available(), 'GD available');
$tmp = tempnam(sys_get_temp_dir(), 'psr');
try {
    $im = imagecreatetruecolor(1800, 1000);
    imagepng($im, $tmp); imagedestroy($im);
    $encoded = psr_report_photo_encode($tmp);
    verify(is_string($encoded), 'valid PNG converted');
    $jpeg = base64_decode($encoded, true);
    $info = getimagesizefromstring($jpeg);
    verify($info[2] === IMAGETYPE_JPEG && $info[0] === 1600, 'JPEG resize');
    verify(strlen($jpeg) <= 480*1024, 'bounded storage');
    file_put_contents($tmp, '<?php echo 1; ?>');
    clearstatcache(true, $tmp);
    verify(is_wp_error(psr_report_photo_encode($tmp)), 'non-image rejected');
    // Documento PDF tradizionale con tabella xref e offset reali.
    $pdf = "%PDF-1.4\n";
    $offsets = array(0);
    foreach (array('<< /Type /Catalog /Pages 2 0 R >>','<< /Type /Pages /Kids [3 0 R] /Count 1 >>','<< /Type /Page /Parent 2 0 R /MediaBox [0 0 200 200] >>') as $i=>$obj) {
        $offsets[] = strlen($pdf);
        $pdf .= ($i+1) . " 0 obj\n" . $obj . "\nendobj\n";
    }
    $xref = strlen($pdf);
    $pdf .= "xref\n0 4\n0000000000 65535 f \n";
    for ($i=1; $i<=3; $i++) { $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]); }
    $pdf .= "trailer\n<< /Size 4 /Root 1 0 R >>\nstartxref\n" . $xref . "\n%%EOF\n";
    file_put_contents($tmp, $pdf); clearstatcache(true,$tmp);
    $doc = psr_report_pdf_encode($tmp);
    verify(is_string($doc) && strpos($doc,'pdf:')===0 && base64_decode(substr($doc,4),true)===$pdf, 'simple PDF accepted');
    $GLOBALS['mock_pdf_scan']=false;
    verify(is_wp_error(psr_report_pdf_encode($tmp)), 'negative scanner result rejected');
    unset($GLOBALS['mock_pdf_scan']);
    // Keep xref offset intact by inserting after the xref header.
    foreach (array('/JavaScript','/J#53','/ObjStm','/Encrypt','/EmbeddedFile','/Launch','/AcroForm') as $marker) {
        $blocked = str_replace("trailer\n", "% " . $marker . "\ntrailer\n", $pdf);
        file_put_contents($tmp,$blocked); clearstatcache(true,$tmp);
        verify(is_wp_error(psr_report_pdf_encode($tmp)), 'reject active/opaque PDF marker '.$marker);
    }
    file_put_contents($tmp, $pdf . '<?php ?>'); clearstatcache(true,$tmp);
    verify(is_wp_error(psr_report_pdf_encode($tmp)), 'PDF with trailing code rejected');
    file_put_contents($tmp, "%PDF-1.4\nfake\n%%EOF\n"); clearstatcache(true,$tmp);
    verify(is_wp_error(psr_report_pdf_encode($tmp)), 'fake PDF rejected');
    $f = array('tmp_name'=>$tmp, 'error'=>UPLOAD_ERR_OK,'size'=>12);
    $files = array('report_photo_0'=>$f, 'report_photo_1'=>$f, 'report_photo_2'=>$f);
    verify(psr_report_photos_shape_valid($files, '3'), 'three images');
    verify(!psr_report_photos_shape_valid($files, '4'), 'four rejected');
    verify(!psr_report_photos_shape_valid($files, '2'), 'count mismatch rejected');
    $bad = array('report_photo_0'=>array_merge($f,array('size'=>2097153)));
    verify(!psr_report_photos_shape_valid($bad, '1'), 'oversize rejected');
    verify(is_wp_error(psr_report_photos_prepare(array('report_photo_0'=>$f), '1')), 'non-HTTP temporary file rejected');
    $GLOBALS['logged'] = false; $GLOBALS['caps'] = array('read_post'=>true, 'edit_post'=>true);
    verify(!psr_report_photo_authorized(7), 'anonymous denied');
    $GLOBALS['logged'] = true; $GLOBALS['caps']['edit_post'] = false;
    verify(!psr_report_photo_authorized(7), 'unprivileged denied');
    $GLOBALS['caps']['edit_post'] = true;
    verify(!psr_report_photo_authorized(8), 'other post type denied');
    verify(psr_report_photo_authorized(7), 'authorized report access');
} finally { @unlink($tmp); }
