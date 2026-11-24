<?php
define('ABSPATH', __DIR__);
class WP_Error { public function __construct($code, $message) {} }
function is_wp_error($v) { return $v instanceof WP_Error; }
function add_action($hook, $cb) {}
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
