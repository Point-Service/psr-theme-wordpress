<?php
/** Isolated tests of actual capacity/lock functions with an atomic options-table model. */
define('ABSPATH', __DIR__);
if (isset($argv[1])) { define('PSR_AUTH_MAX_PARALLEL', (int)$argv[1]); }
function add_action(...$args) {}
function add_filter(...$args) {}
class PSR_Capacity_DB {
    public $options='wp_options';
    public $rows=array();
    public $attempts=0;
    public function prepare($sql,...$args) { return array($sql,$args); }
    public function query($q) {
        list($sql,$args)=$q;
        if (strpos($sql,'INSERT IGNORE')===0) {
            $this->attempts++;
            if (isset($this->rows[$args[0]])) { return 0; }
            $this->rows[$args[0]]=$args[1]; return 1;
        }
        if (strpos($sql,'DELETE FROM')===0) {
            if (($this->rows[$args[0]]??null)!==$args[1]) { return 0; }
            unset($this->rows[$args[0]]); return 1;
        }
        throw new RuntimeException('Unexpected query');
    }
    public function get_var($q) { return $this->rows[$q[1][0]]??null; }
}
$wpdb=new PSR_Capacity_DB();
require __DIR__.'/../inc/psr-auth-hardening.php';
function check($condition,$message) {
    if (!$condition) { fwrite(STDERR,"FAIL: ".$message.PHP_EOL); exit(1); }
    echo "OK: ".$message.PHP_EOL;
}
$max=defined('PSR_AUTH_MAX_PARALLEL')?min(64,max(1,PSR_AUTH_MAX_PARALLEL)):24;
$locks=array();
for ($i=0;$i<$max;$i++) {
    $lock=psr_auth_capacity();
    check(is_array($lock),'occupy distinct slot '.($i+1));
    $locks[]=$lock;
}
check(count($wpdb->rows)===$max,'exact capacity limit');
$wpdb->attempts=0;
check(psr_auth_capacity()===false && $wpdb->attempts===$max,'saturation bounded to one scan');
for ($i=0;$i<100;$i++) {
    $index=$i%$max;
    psr_auth_unlock($locks[$index]);
    $wpdb->attempts=0;
    $locks[$index]=psr_auth_capacity();
    if (!is_array($locks[$index]) || $wpdb->attempts>$max) { check(false,'single remaining slot'); }
}
check(true,'100 single-free-slot scenarios, no false rejection');
foreach ($locks as $lock) { psr_auth_unlock($lock); }
check(count($wpdb->rows)===0,'all slots released');
$account=psr_auth_lock('password|same-account-and-ip',0);
check(is_array($account) && psr_auth_lock('password|same-account-and-ip',0)===false,'same-account lock unchanged');
psr_auth_unlock($account);
$locks=array();
for ($i=0;$i<min(7,$max);$i++) {
    $locks[]=psr_auth_capacity();
}
check(count(array_filter($locks,'is_array'))===min(7,$max),'seven distinct in-flight requests (subject to explicit cap)');
foreach ($locks as $lock) { psr_auth_unlock($lock); }
check(count($wpdb->rows)===0,'no lingering locks');
