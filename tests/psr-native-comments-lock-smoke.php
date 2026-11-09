<?php
/** Mocked unit-smoke test: no WordPress installation, database or mail required. */
define('ABSPATH', '/mock/');
$GLOBALS['psr_posts'] = array(101=>'post',102=>'page',103=>'attachment',201=>'segnala_disservizio',202=>'richiesta_assistenza',203=>'appuntamento',204=>'rating',205=>'custom_portal');
$GLOBALS['psr_mock_admin'] = false;
$GLOBALS['psr_hooks'] = array();
function add_filter($tag, $callback, $priority = 10, $accepted = 1) { $GLOBALS['psr_hooks'][$tag][] = $callback; }
function add_action($tag, $callback, $priority = 10, $accepted = 1) { $GLOBALS['psr_hooks'][$tag][] = $callback; }
function get_post_type($id) { return $GLOBALS['psr_posts'][$id] ?? false; }
function is_admin() { return $GLOBALS['psr_mock_admin']; }
function is_wp_error($value) { return $value instanceof WP_Error; }
function post_type_exists($type) { return in_array($type, $GLOBALS['psr_posts'], true); }
function remove_post_type_support($type, $feature) { $GLOBALS['psr_support_removed'][] = $type.':'.$feature; }
class WP_Error {
    private $code;
    public function __construct($code, $message = '', $data = null) { $this->code = $code; }
    public function get_error_code() { return $this->code; }
}
class MockRequest {
    private $id;
    public function __construct($id = 0) { $this->id = $id; }
    public function get_param($name) { return $name === 'id' ? $this->id : null; }
}
require __DIR__.'/../inc/psr-native-comments-lock.php';
function check($condition, $name) {
    if (!$condition) { fwrite(STDERR, "FAIL: ".$name."\n"); exit(1); }
    echo "OK: ".$name."\n";
}
check(psr_native_comments_target(101) && psr_native_comments_target(102) && psr_native_comments_target(103), 'native post types covered');
check(!psr_native_comments_target(0) && !psr_native_comments_target(201) && !psr_native_comments_target(202)
    && !psr_native_comments_target(203) && !psr_native_comments_target(204) && !psr_native_comments_target(205), 'civic and custom post types unaffected');
check(psr_native_comments_close(true,101) === false && psr_native_comments_close(true,102) === false, 'new comments closed');
check(psr_native_comments_close(true,201) === true && psr_native_comments_close(false,201) === false, 'segnalazioni comment-status left unchanged');
check(psr_native_comments_close(true,202) === true, 'assistance left unchanged');
check(psr_native_comments_default_closed('open','post','comment') === 'closed'
    && psr_native_comments_default_closed('open','post','pingback') === 'closed', 'future post comments/pings closed');
check(psr_native_comments_default_closed('open','segnala_disservizio','comment') === 'open', 'civic defaults unchanged');
check(psr_native_comments_reject(1,array('comment_post_ID'=>101,'comment_type'=>'comment')) instanceof WP_Error, 'new native comment rejected');
check(psr_native_comments_reject(1,array('comment_post_ID'=>101,'comment_type'=>'pingback')) instanceof WP_Error, 'native pingback rejected');
check(psr_native_comments_reject(1,array('comment_post_ID'=>102)) instanceof WP_Error, 'native page comment rejected');
check(psr_native_comments_reject(1,array('comment_post_ID'=>201,'comment_type'=>'comment')) === 1, 'segnalazioni unaffected');
check(psr_native_comments_reject(1,array('comment_post_ID'=>202,'comment_type'=>'comment')) === 1, 'assistance unaffected');
check(psr_native_comments_reject(1,array('comment_post_ID'=>101,'comment_type'=>'special_civic_event')) === 1, 'custom comment type unaffected');
check(psr_native_comments_rest_reject(array('comment_post_ID'=>101,'comment_type'=>'comment'),new MockRequest()) instanceof WP_Error, 'native REST comment rejected');
check(psr_native_comments_rest_reject(array('comment_post_ID'=>201,'comment_type'=>'comment'),new MockRequest()) === array('comment_post_ID'=>201,'comment_type'=>'comment'), 'civic REST content unaffected');
check(psr_native_comments_rest_reject(array('comment_post_ID'=>101,'comment_type'=>'comment'),new MockRequest(12)) === array('comment_post_ID'=>101,'comment_type'=>'comment'), 'existing comments can still be moderated');
check(psr_native_comments_hide_public(array('visible'),101) === array(), 'public old comments hidden on articles');
check(psr_native_comments_hide_public(array('visible'),201) === array('visible'), 'civic data display left unchanged');
$GLOBALS['psr_mock_admin']=true;
check(psr_native_comments_hide_public(array('visible'),101) === array('visible'), 'admin historical comments preserved');
psr_native_comments_hide_editor_support();
check(in_array('post:comments',$GLOBALS['psr_support_removed'],true)
    && !in_array('segnala_disservizio:comments',$GLOBALS['psr_support_removed'],true), 'editor changes apply only to native types');
echo "ALL PSR NATIVE COMMENTS SMOKE CHECKS PASSED\n";
