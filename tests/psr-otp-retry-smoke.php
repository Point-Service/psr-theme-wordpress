<?php
define('ABSPATH', __DIR__);
define('HOUR_IN_SECONDS',3600);
function add_action(...$a) {}
function add_filter(...$a) {}
function wp_salt($which) { return 'test-'.$which; }
function wp_using_ext_object_cache(){return true;}
function wp_cache_get($k,$g,$force=false){return $GLOBALS['transients'][$k]??false;}
function set_transient($k,$v,$ttl){$GLOBALS['transients'][$k]=$v;return true;}
function psr_geo_public_ip(){return $GLOBALS['ip'];}
function wp_check_password($p,$h,$uid){return $p==='correct' && $h==='new-hash';}
class WP_User {public $ID;public $user_pass;function __construct($id,$hash){$this->ID=$id;$this->user_pass=$hash;}}
class WP_Error {public $code;public $message;function __construct($c,$m){$this->code=$c;$this->message=$m;}}
class FakeDB {
    public $options='wp_options';public $users='wp_users';public $rows=[];public $hash='new-hash';
    function prepare($sql,...$args){return [$sql,$args];}
    function query($q){[$sql,$a]=$q;if(strpos($sql,'INSERT IGNORE')===0){if(isset($this->rows[$a[0]]))return 0;$this->rows[$a[0]]=$a[1];return 1;}if(strpos($sql,'DELETE FROM')===0){if(($this->rows[$a[0]]??null)!==$a[1])return 0;unset($this->rows[$a[0]]);return 1;}throw new Exception('query');}
    function get_var($q){return strpos($q[0],'SELECT user_pass')===0?$this->hash:($this->rows[$q[1][0]]??null);}
}
$wpdb=new FakeDB();$GLOBALS['transients']=[];$GLOBALS['ip']='192.0.2.10';
require __DIR__.'/../inc/psr-login-security.php';
function check($ok,$label){if(!$ok){fwrite(STDERR,"FAIL ".$label.PHP_EOL);exit(1);}echo "OK ".$label.PHP_EOL;}
$u=new WP_User(2,'old-hash');$other=new WP_User(3,'new-hash');
$stamp=psr_login_password_stamp_after_auth($u,'correct');
check($stamp===hash_hmac('sha256','new-hash',wp_salt('auth')),'rehash uses current verified hash');
check(psr_login_password_stamp_after_auth($u,'wrong')==='','changed password rejects old credential');
check(psr_login_password_stamp_after_auth($other,'correct')===$stamp,'unchanged hash');
$wpdb->hash='';check(psr_login_password_stamp_after_auth($u,'correct')==='','missing account denied');$wpdb->hash='new-hash';
$ticket=str_repeat('a',64);$_COOKIE[psr_login_cookie_name()]=$ticket;$_SERVER['HTTP_USER_AGENT']='browser';
$p=['uid'=>2,'code_expires'=>time()+300,'deadline'=>time()+900,'ua'=>hash('sha256','browser'),'ip'=>$GLOBALS['ip'],'password_stamp'=>$stamp,'resends'=>2,'tries'=>1];
set_transient(psr_login_pending_key($ticket),$p,300);
check(psr_login_reusable_pending($u,$stamp)===$p,'resume same browser and user without resetting counters');
check(psr_login_reusable_pending($other,$stamp)===false,'other user cannot resume');
check(psr_login_reusable_pending($u,'changed')===false,'changed credential cannot resume');
$GLOBALS['ip']='192.0.2.11';check(psr_login_reusable_pending($u,$stamp)===false,'changed IP cannot resume');$GLOBALS['ip']='192.0.2.10';
$_SERVER['HTTP_USER_AGENT']='other';check(psr_login_reusable_pending($u,$stamp)===false,'changed browser cannot resume');$_SERVER['HTTP_USER_AGENT']='browser';
$p['code_expires']=time()-1;set_transient(psr_login_pending_key($ticket),$p,300);check(psr_login_reusable_pending($u,$stamp)===false,'expired ticket cannot resume');
$r=null;$e=null;check(psr_login_email_can_send($u,$r,$e),'initial send');$first=$r;
check(!psr_login_email_can_send($u,$r,$e)&&$e->code==='psr_login_email_throttled','successful send cooldown');
check(psr_login_email_can_send($other,$r,$e),'other user independent');
$k='_psr_login_email_limit_2';$before=$GLOBALS['transients'][$k];
psr_login_email_mark_failed($u,'stale');check($GLOBALS['transients'][$k]===$before,'stale reservation cannot modify newer send');
psr_login_email_mark_failed($u,$first);$after=$GLOBALS['transients'][$k];
check($after['n']===1&&$after['retry_at']<=time()+15,'failed transport short pause without refund');
check(!psr_login_email_can_send($u,$r,$e),'no immediate repeat on failure');
$GLOBALS['transients'][$k]['retry_at']=time()-1;
check(psr_login_email_can_send($u,$r,$e),'retry after cooldown');
check($GLOBALS['transients'][$k]['n']===2,'attempts remain counted');
$GLOBALS['transients'][$k]['n']=10;check(!psr_login_email_can_send($u,$r,$e)&&$e->code==='psr_login_email_user_limit','hourly quota preserved');
$ipkey='_psr_login_mail_ip_'.substr(hash_hmac('sha256',$GLOBALS['ip'],wp_salt('auth')),0,32);
$GLOBALS['transients'][$ipkey]['n']=180;
check(!psr_login_email_can_send(new WP_User(4,'new-hash'),$r,$e)&&$e->code==='psr_login_email_ip_limit','IP quota preserved');
