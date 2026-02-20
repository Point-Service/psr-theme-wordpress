<?php
/*
Plugin Name: DCI Push Scheduler
Description: Pianificazione notifiche push WebApp
Version: 1.0
Author: Luca Giardina
*/

if (!defined('ABSPATH')) exit;

/* ================= CONFIG ================= */

define('DCI_WEBAPP_CAP', 'dci_manage_webapp_mobile');
define('DCI_WEBAPP_OPT', 'dci_webapp_mobile_url');
define('DCI_WEBAPP_SECRET', 'tonyluca');

/* ================= CAP ================= */

register_activation_hook(__FILE__, function(){

  $role = get_role('administrator');
  if ($role) $role->add_cap(DCI_WEBAPP_CAP);

  dci_create_push_table();
});

/* ================= DB ================= */

function dci_create_push_table(){

  global $wpdb;

  $table = $wpdb->prefix.'dci_push_queue';
  $charset = $wpdb->get_charset_collate();

  $sql = "CREATE TABLE IF NOT EXISTS $table (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    msg TEXT NOT NULL,
    send_at DATETIME NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  ) $charset;";

  require_once ABSPATH.'wp-admin/includes/upgrade.php';
  dbDelta($sql);
}

/* ================= MENU ================= */

add_action('admin_menu', function(){

  add_menu_page(
    'Push Scheduler',
    'Push Scheduler',
    DCI_WEBAPP_CAP,
    'dci_push',
    'dci_admin_page',
    'dashicons-megaphone',
    58
  );
});

/* ================= SETTINGS ================= */

add_action('admin_init', function(){

  register_setting('dci_push_group', DCI_WEBAPP_OPT);
});

/* ================= ADMIN PAGE ================= */

function dci_admin_page(){

  if (!current_user_can(DCI_WEBAPP_CAP)) return;

  global $wpdb;
  $table = $wpdb->prefix.'dci_push_queue';

  $url = get_option(DCI_WEBAPP_OPT);
  ?>

<div class="wrap">

<h1>DCI Push Scheduler</h1>

<!-- URL -->

<form method="post" action="options.php">
<?php settings_fields('dci_push_group'); ?>

<h2>URL WebApp</h2>

<input type="text" name="<?=DCI_WEBAPP_OPT?>" value="<?=esc_attr($url)?>" class="regular-text">

<?php submit_button('Salva URL'); ?>
</form>

<hr>

<!-- FORM -->

<h2>Pianifica Notifica</h2>

<form method="post">

<?php wp_nonce_field('dci_push_save','dci_push_nonce'); ?>

<table class="form-table">

<tr>
<th>Titolo</th>
<td><input name="title" required class="regular-text"></td>
</tr>

<tr>
<th>Messaggio</th>
<td><textarea name="msg" required class="large-text"></textarea></td>
</tr>

<tr>
<th>Data / Ora</th>
<td><input type="datetime-local" name="time" required></td>
</tr>

</table>

<?php submit_button('Pianifica'); ?>

</form>

<hr>

<!-- LISTA -->

<h2>Storico</h2>

<?php
$rows = $wpdb->get_results("SELECT * FROM $table ORDER BY send_at DESC");
?>

<table class="widefat striped">

<tr>
<th>ID</th>
<th>Titolo</th>
<th>Messaggio</th>
<th>Invio</th>
<th>Stato</th>
</tr>

<?php foreach($rows as $r): ?>

<tr>
<td><?=$r->id?></td>
<td><?=esc_html($r->title)?></td>
<td><?=esc_html($r->msg)?></td>
<td><?=$r->send_at?></td>
<td><?=$r->status?></td>
</tr>

<?php endforeach; ?>

</table>

</div>

<?php
}

/* ================= SAVE ================= */

add_action('admin_init', function(){

  if(!isset($_POST['dci_push_nonce'])) return;

  if(!wp_verify_nonce($_POST['dci_push_nonce'],'dci_push_save')) return;

  if(!current_user_can(DCI_WEBAPP_CAP)) return;

  global $wpdb;

  $table = $wpdb->prefix.'dci_push_queue';

  $title = sanitize_text_field($_POST['title']);
  $msg   = sanitize_textarea_field($_POST['msg']);
  $time  = sanitize_text_field($_POST['time']);

  $ts = strtotime($time);

  if($ts <= time()) return;

  $wpdb->insert($table,[
    'title'=>$title,
    'msg'=>$msg,
    'send_at'=>date('Y-m-d H:i:s',$ts),
    'status'=>'pending'
  ]);

  $id = $wpdb->insert_id;

  wp_schedule_single_event($ts,'dci_send_push',[$id]);

});

/* ================= CRON ================= */

add_action('dci_send_push','dci_send_push_callback');

function dci_send_push_callback($id){

  global $wpdb;

  $table = $wpdb->prefix.'dci_push_queue';

  $row = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM $table WHERE id=%d",$id
  ));

  if(!$row || $row->status!='pending') return;

  $url = get_option(DCI_WEBAPP_OPT);

  if(!$url) return;

  /* TOKEN */

  $ts = time();
  $n  = wp_generate_password(16,false,false);

  parse_str(parse_url($url,PHP_URL_QUERY),$q);

  $ente = $q['Ente'] ?? '';

  $sum=0; foreach(str_split($n) as $c) $sum+=ord($c);

  $sec = DCI_WEBAPP_SECRET;

  $sig = ord($sec[0]).strlen($sec).($ts%997).strlen($n).($sum%997).strlen($ente);

  $sep = strpos($url,'?')===false?'?':'&';

  $final = $url.$sep.
    "ts=$ts&n=$n&sig=$sig".
    "&notificapush=ok".
    "&Titolo=".rawurlencode($row->title).
    "&msg=".rawurlencode($row->msg);

  /* SEND */

  $res = wp_remote_get($final,['timeout'=>20]);

  if(is_wp_error($res)){

    $wpdb->update($table,['status'=>'error'],['id'=>$id]);

  }else{

    $wpdb->update($table,['status'=>'sent'],['id'=>$id]);

  }
}

