<?php
require dirname(__DIR__) . '/inc/psr-otp-smtp-crypto.php';
function check($ok,$label) { if (!$ok) { fwrite(STDERR,"FAIL $label\n"); exit(1); } echo "PASS $label\n"; }
check(psr_otp_crypto_available(),'sodium available');
$settings = array('host'=>'smtp.example.test','port'=>465,'user'=>'otp@example.test');
$key = random_bytes(32);
$secret = 'fake-test-password-123';
$c = psr_otp_smtp_encrypt($settings,$secret,$key);
check(is_array($c) && psr_otp_smtp_decrypt($c,$key)===$secret,'round trip');
check(strpos(json_encode($c),$secret)===false,'no plaintext');
check(psr_otp_smtp_encrypt($settings,$secret,$key)['nonce']!==$c['nonce'],'random nonce');
check(psr_otp_smtp_decrypt($c,random_bytes(32))===false,'wrong key');
foreach (array('host'=>'other.example.test','port'=>587,'user'=>'other@example.test') as $k=>$v) {
    $bad=$c; $bad[$k]=$v;
    check(psr_otp_smtp_decrypt($bad,$key)===false,'authenticated metadata '.$k);
}
$bad=$c; $raw=base64_decode($bad['ciphertext']); $raw[0]=chr(ord($raw[0])^1); $bad['ciphertext']=base64_encode($raw);
check(psr_otp_smtp_decrypt($bad,$key)===false,'tampering rejected');
check(!psr_otp_smtp_settings_valid(array_merge($settings,array('port'=>25))),'no unencrypted port');
check(!psr_otp_smtp_settings_valid(array_merge($settings,array('host'=>'smtp.example.test;other.example.test'))),'no alternate host list');

define('AUTH_KEY',str_repeat('aX',32));
define('SECURE_AUTH_KEY',str_repeat('bY',32));
define('LOGGED_IN_KEY',str_repeat('cZ',32));
define('NONCE_KEY',str_repeat('dW',32));
function get_current_blog_id() { return $GLOBALS['mock_blog'] ?? 1; }
$siteKey=psr_otp_smtp_site_key();
check(is_string($siteKey) && strlen($siteKey)===32,'derived site key');
$ct=psr_otp_smtp_encrypt($settings,$secret,$siteKey);
$GLOBALS['mock_blog']=2;
check(psr_otp_smtp_decrypt($ct,psr_otp_smtp_site_key())===false,'other site denied');
$GLOBALS['mock_blog']=1;
define('ABSPATH',__DIR__);
class WP_Error { public $code; public function __construct($c,$m) {$this->code=$c;} }
function is_wp_error($x){return $x instanceof WP_Error;}
function get_option($k,$default){return $GLOBALS['mock_option'] ?? $default;}
function add_filter(...$args){}
function add_action(...$args){}
function is_ssl(){return $GLOBALS['ssl'] ?? true;}
function get_current_user_id(){return $GLOBALS['uid'] ?? 1;}
function current_user_can($cap){return $GLOBALS['cap'] ?? true;}
function psr_login_privilege_authorized(){return $GLOBALS['verified'] ?? true;}
function update_option($key,$record,$autoload){$GLOBALS['mock_option']=psr_otp_smtp_protect_option($record,$GLOBALS['mock_option']??array());}
require dirname(__DIR__).'/inc/psr-otp-smtp.php';
check(!psr_otp_smtp_enabled(),'default disabled');
check(!psr_otp_smtp_for_user(1) && !psr_otp_smtp_for_user(2),'both use original transport by default');
$record=array('enabled'=>false,'encrypted'=>psr_otp_smtp_encrypt_password($secret));
check(psr_otp_smtp_store($record),'owner stores encrypted');
check(!psr_otp_smtp_enabled(),'saved disabled');
check(psr_otp_smtp_load()['password']===$secret,'load saved password');
$record['enabled']=true;
$GLOBALS['uid']=2;
check(!psr_otp_smtp_store($record),'other admin rejected');
check(!psr_otp_smtp_enabled(),'other admin cannot activate');
$GLOBALS['uid']=1; $GLOBALS['verified']=false;
check(!psr_otp_smtp_owner(),'unverified session rejected');
$GLOBALS['verified']=true; $GLOBALS['ssl']=false;
check(!psr_otp_smtp_owner(),'HTTP rejected');
$GLOBALS['ssl']=true;
check(psr_otp_smtp_protect_option($record,psr_otp_smtp_record())['enabled']===false,'generic settings write rejected');
check(psr_otp_smtp_store($record) && psr_otp_smtp_enabled(),'owner enables');
check(!psr_otp_smtp_for_user(1),'ID 1 always uses original transport');
check(psr_otp_smtp_for_user(2) && psr_otp_smtp_for_user(42),'other accounts use dedicated SMTP');
check(!psr_otp_smtp_for_user(0),'invalid account never routed');
$broken=$record; $broken['encrypted']['ciphertext']='broken';
check(psr_otp_smtp_store($broken) && !psr_otp_smtp_for_user(1) && psr_otp_smtp_for_user(2),'broken password cannot affect owner route');
check(is_wp_error(psr_otp_smtp_load()),'broken password fails closed for other users');
check(psr_otp_smtp_store($record),'restore valid fixture');
$record['encrypted']['host']='other.example.test';
check(is_wp_error(psr_otp_smtp_load($record)),'unexpected SMTP host denied');
define('PSR_OTP_SMTP_ENABLED',false);
check(!psr_otp_smtp_enabled(),'emergency override works');
check(!psr_otp_smtp_for_user(1) && !psr_otp_smtp_for_user(2),'override returns all users to original transport');
