<?php
/** Standalone smoke test for the actual PSR trusted-browser module. */
define('ABSPATH', __DIR__);
define('DAY_IN_SECONDS', 86400);
define('MINUTE_IN_SECONDS', 60);
class WP_User { public $ID; public $user_pass; public function __construct($id=1,$pw='hash-one') {$this->ID=$id;$this->user_pass=$pw;} }
$GLOBALS['mock_meta'] = array();
$GLOBALS['mock_transients'] = array();
$GLOBALS['mock_geo'] = array('ip'=>'185.136.124.56','country'=>'IT','blocked'=>false);
$GLOBALS['mock_https'] = true;
$GLOBALS['mock_interactive'] = true;
function site_url($p='/') { return 'https://comune.example.test' . $p; }
function wp_salt($which) { return 'server-only-test-salt-' . $which; }
function is_ssl() { return $GLOBALS['mock_https']; }
function psr_login_ip() { return $_SERVER['REMOTE_ADDR'] ?? ''; }
function psr_geo_decision() { return $GLOBALS['mock_geo']; }
function psr_login_interactive() { return $GLOBALS['mock_interactive']; }
function psr_auth_lock($resource) { return array($resource, 'lock'); }
function psr_auth_unlock($lock) { return true; }
function get_user_meta($id, $key, $single) { return $GLOBALS['mock_meta'][$id][$key] ?? ''; }
function update_user_meta($id,$key,$value) { $GLOBALS['mock_meta'][$id][$key] = $value; return true; }
function delete_user_meta($id, $key) { unset($GLOBALS['mock_meta'][$id][$key]); return true; }
function get_userdata($id) { return $GLOBALS['mock_user'] ?? null; }
function add_action($name,$handler,$prio=10,$args=1) { return true; }
function set_transient($key,$value,$ttl) { $GLOBALS['mock_transients'][$key]=$value; return true; }
function get_transient($key) { return $GLOBALS['mock_transients'][$key] ?? false; }
function delete_transient($key) { unset($GLOBALS['mock_transients'][$key]); return true; }
function is_user_logged_in() { return true; }
function get_current_user_id() { return $GLOBALS['mock_current_uid'] ?? 1; }
function psr_login_session_verified($id) { return true; }
function wp_get_session_token() { return 'session-token'; }
function assert_case($name, $expected, $actual) { if ($expected !== $actual) { fwrite(STDERR, "FAIL $name ".var_export($actual,true)."\n"); exit(1); } }
$_SERVER['REMOTE_ADDR'] = '185.136.124.56';
$_SERVER['HTTP_USER_AGENT'] = 'Test browser 120';
require dirname(__DIR__) . '/inc/psr-trusted-browser.php';
$user = new WP_User();
$GLOBALS['mock_user'] = $user;
$token = str_repeat('a',64);
assert_case('without trust always OTP', false, psr_trust_can_login($user));
assert_case('create trust after OTP proof', true, psr_trust_store_token($user,$token));
$records = get_user_meta(1,psr_trust_meta_key(),true);
assert_case('one record', 1, count($records));
assert_case('never store token in plain', false, strpos(serialize($records), $token)!==false);
$_COOKIE[psr_trust_cookie_name()] = $token;
assert_case('from exact office IP', true, psr_trust_can_login($user));
$other = new WP_User(22);
// Simulate a valid pre-update trusted record for an ordinary administrator.
$other_digest = psr_trust_token_digest(22,$token);
$GLOBALS['mock_meta'][22][psr_trust_meta_key()] = array($other_digest=>$records[psr_trust_token_digest(1,$token)]);
assert_case('legacy other-user record rejected at office',false,psr_trust_can_login($other));
assert_case('other user cannot store token',false,psr_trust_store_token($other,$token));
assert_case('other user cannot enroll after OTP',false,psr_trust_after_otp($other));
assert_case('invalid user cannot trust',false,psr_trust_can_login(new WP_User(0)));
assert_case('owner trust unaffected by other user',true,psr_trust_can_login($user));
$GLOBALS['mock_current_uid']=22;
$GLOBALS['mock_transients'][psr_trust_toast_key(22,'session-token')]=1;
ob_start(); psr_trust_welcome_toast(); $output=ob_get_clean();
assert_case('other user never gets trust toast','',$output);
$GLOBALS['mock_current_uid']=1;
$_SERVER['REMOTE_ADDR']='185.136.124.55';
assert_case('adjacent IP rejected', false, psr_trust_can_login($user));
$_SERVER['HTTP_X_FORWARDED_FOR']=psr_trust_office_ip();
$_SERVER['HTTP_CF_CONNECTING_IP']=psr_trust_office_ip();
assert_case('forged proxy headers rejected',false,psr_trust_can_login($user));
assert_case('cannot enroll outside office',false,psr_trust_store_token($user,$token));
unset($_SERVER['HTTP_X_FORWARDED_FOR'],$_SERVER['HTTP_CF_CONNECTING_IP']);
$_SERVER['REMOTE_ADDR']='185.136.124.56';
$GLOBALS['mock_geo']['blocked']=true;
assert_case('blacklist rejected', false, psr_trust_can_login($user));
$GLOBALS['mock_geo']['blocked']=false;
$GLOBALS['mock_geo']['country']='FR';
assert_case('geo outside IT rejected', false, psr_trust_can_login($user));
$GLOBALS['mock_geo']['country']='IT';
$GLOBALS['mock_interactive']=false;
assert_case('REST or external auth not accepted', false, psr_trust_can_login($user));
$GLOBALS['mock_interactive']=true;
$GLOBALS['mock_https']=false;
assert_case('non-TLS rejected', false, psr_trust_can_login($user));
$GLOBALS['mock_https']=true;
$_SERVER['HTTP_USER_AGENT']='Another browser';
assert_case('other browser rejected', false, psr_trust_can_login($user));
$_SERVER['HTTP_USER_AGENT']='Test browser 120';
$user->user_pass='newhash';
assert_case('password change revokes', false, psr_trust_can_login($user));
$user->user_pass='hash-one';
$key = psr_trust_token_digest($user->ID,$token);
$GLOBALS['mock_meta'][1][psr_trust_meta_key()][$key]['expires']=time()-1;
assert_case('expired token rejected',false,psr_trust_can_login($user));
assert_case('bad token rejected',false,psr_trust_store_token($user,'short'));
assert_case('new token after OTP',true,psr_trust_store_token($user,str_repeat('b',64)));
$_COOKIE[psr_trust_cookie_name()] = str_repeat('b',64);
assert_case('new token accepted',true,psr_trust_can_login($user));
psr_trust_password_reset($user);
assert_case('password reset deletes all browsers',false,psr_trust_can_login($user));
echo "PSR trusted-browser smoke: all checks passed\n";
