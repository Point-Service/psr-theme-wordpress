<?php
/** Dedicated transport only for PSR email OTP. No impact on other wp_mail calls. */
if (!defined('ABSPATH')) { exit; }
require_once __DIR__ . '/psr-otp-smtp-crypto.php';

function psr_otp_smtp_error($code) {
    return new WP_Error($code, 'Invio OTP non riuscito. Contattare il gestore del portale.');
}

function psr_otp_smtp_defaults() {
    return array('host'=>'out.postassl.it','port'=>465,'user'=>'otp@servizipa.cloud');
}
function psr_otp_smtp_option() { return '_psr_otp_smtp_settings_v1'; }
function psr_otp_smtp_record() {
    $value = get_option(psr_otp_smtp_option(), array());
    return is_array($value) ? $value : array();
}
function psr_otp_smtp_enabled() {
    // Emergency override via Plesk File Manager; no change to OTP requirement.
    if (defined('PSR_OTP_SMTP_ENABLED') && PSR_OTP_SMTP_ENABLED === false) { return false; }
    $r = psr_otp_smtp_record();
    return ($r['enabled'] ?? false) === true;
}
/** Authentication routing: ID 1 can NEVER depend on the dedicated SMTP password. */
function psr_otp_smtp_for_user($user_id) {
    return (int)$user_id > 1 && psr_otp_smtp_enabled();
}
function psr_otp_smtp_load($record = null) {
    $record = $record === null ? psr_otp_smtp_record() : $record;
    $encrypted = $record['encrypted'] ?? null;
    $defaults = psr_otp_smtp_defaults();
    if (!is_array($encrypted)) { return psr_otp_smtp_error('psr_otp_smtp_config_missing'); }
    foreach ($defaults as $k=>$v) {
        if (($encrypted[$k] ?? null) !== $v) { return psr_otp_smtp_error('psr_otp_smtp_config_invalid'); }
    }
    $key = psr_otp_smtp_site_key();
    if ($key === false) { return psr_otp_smtp_error('psr_otp_smtp_key_unavailable'); }
    try { $password = psr_otp_smtp_decrypt($encrypted,$key); }
    finally { sodium_memzero($key); }
    if ($password === false) { return psr_otp_smtp_error('psr_otp_smtp_decrypt_failed'); }
    $defaults['password'] = $password;
    return $defaults;
}
function psr_otp_smtp_encrypt_password($password) {
    $key = psr_otp_smtp_site_key();
    if ($key === false) { return false; }
    try { return psr_otp_smtp_encrypt(psr_otp_smtp_defaults(), $password, $key); }
    finally { sodium_memzero($key); }
}
function psr_otp_smtp_owner() {
    return is_ssl() && (int)get_current_user_id() === 1 && current_user_can('manage_options')
        && function_exists('psr_login_privilege_authorized') && psr_login_privilege_authorized();
}
/** Only allowlisted SMTP status codes: no raw replies, addresses or message body in logs. */
function psr_otp_smtp_failure_code($mailer) {
    try {
        $reply = $mailer->getSMTPInstance()->getError();
        $code = (string)($reply['smtp_code'] ?? '');
        $extended = (string)($reply['smtp_code_ex'] ?? '');
        if (in_array($extended, array('5.7.26','5.7.1','5.7.0','4.7.0','4.7.1'), true)) {
            return 'psr_otp_smtp_' . str_replace('.', '_', $extended);
        }
        if (in_array($code, array('421','450','451','452','454','530','534','535','550','551','552','553','554'), true)) {
            return 'psr_otp_smtp_' . $code;
        }
    } catch (Throwable $ignored) {}
    return 'psr_otp_smtp_transport_failed';
}

function psr_otp_smtp_send($recipient, $subject, $body, $record = null) {
    if (!is_string($recipient) || !is_email($recipient)) { return psr_otp_smtp_error('psr_otp_smtp_invalid_recipient'); }
    $settings = psr_otp_smtp_load($record);
    if (is_wp_error($settings)) { return $settings; }
    $mailer = null;
    try {
        require_once ABSPATH . WPINC . '/PHPMailer/Exception.php';
        require_once ABSPATH . WPINC . '/PHPMailer/PHPMailer.php';
        require_once ABSPATH . WPINC . '/PHPMailer/SMTP.php';
        $mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
        // Fresh object, no global WordPress PHPMailer or plugin mail filters.
        $mailer->isSMTP();
        $mailer->Host = $settings['host'];
        $mailer->Port = $settings['port'];
        $mailer->SMTPAuth = true;
        $mailer->Username = $settings['user'];
        $mailer->Password = $settings['password'];
        $mailer->SMTPSecure = $settings['port'] === 465 ? 'ssl' : 'tls';
        $mailer->SMTPAutoTLS = true;
        $mailer->SMTPOptions = array('ssl'=>array('verify_peer'=>true,'verify_peer_name'=>true,'allow_self_signed'=>false));
        $mailer->SMTPDebug = 0;
        $mailer->Timeout = 15;
        $mailer->SMTPKeepAlive = false;
        $mailer->CharSet = 'UTF-8';
        $mailer->setFrom($settings['user'], 'PSR - Sicurezza accessi');
        $mailer->Sender = $settings['user'];
        $mailer->addAddress($recipient);
        $mailer->isHTML(false);
        $mailer->Subject = $subject;
        $mailer->Body = $body;
        return $mailer->send() ? true : psr_otp_smtp_error(psr_otp_smtp_failure_code($mailer));
    } catch (Throwable $e) {
        return psr_otp_smtp_error($mailer ? psr_otp_smtp_failure_code($mailer) : 'psr_otp_smtp_transport_failed');
    } finally {
        if (is_string($settings['password'])) { sodium_memzero($settings['password']); }
        if ($mailer) {
            if (is_string($mailer->Password)) { sodium_memzero($mailer->Password); }
            $mailer->Password = '';
            $mailer->Body = '';
            try { $mailer->smtpClose(); } catch (Throwable $ignored) {}
            $mailer->clearAllRecipients();
        }
    }
}

/** Block changes through generic settings endpoints; only the checked form may update this option. */
function psr_otp_smtp_protect_option($value, $old) {
    return !empty($GLOBALS['psr_otp_smtp_saving']) && psr_otp_smtp_owner() ? $value : $old;
}
add_filter('pre_update_option_' . psr_otp_smtp_option(), 'psr_otp_smtp_protect_option', PHP_INT_MAX, 2);

function psr_otp_smtp_store($record) {
    if (!psr_otp_smtp_owner()) { return false; }
    $GLOBALS['psr_otp_smtp_saving'] = true;
    try {
        update_option(psr_otp_smtp_option(), $record, false);
        return psr_otp_smtp_record() === $record;
    } finally { unset($GLOBALS['psr_otp_smtp_saving']); }
}

/** Test delivery only to the pinned administrator address, never arbitrary recipients. */
function psr_otp_smtp_test($record) {
    $email = function_exists('psr_auth_admin_email') ? psr_auth_admin_email() : '';
    if (!$email || !is_email($email)) { return psr_otp_smtp_error('psr_otp_smtp_admin_email_missing'); }
    $lock = function_exists('psr_auth_lock') ? psr_auth_lock('otp_smtp_test|' . (int)get_current_blog_id()) : false;
    if (!$lock) { return psr_otp_smtp_error('psr_otp_smtp_test_busy'); }
    try {
        if (get_transient('psr_otp_smtp_test_limit')) { return psr_otp_smtp_error('psr_otp_smtp_test_limit'); }
        if (!set_transient('psr_otp_smtp_test_limit',1,30)) { return psr_otp_smtp_error('psr_otp_smtp_test_busy'); }
    } finally { psr_auth_unlock($lock); }
    return psr_otp_smtp_send($email, '[PSR] Test SMTP - ' . wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES),
        "Messaggio di prova del servizio SMTP OTP.\nSito: " . home_url('/') . "\nNon contiene un codice di accesso.", $record);
}
function psr_otp_smtp_result_message($code) {
    $messages = array(
        'saved'=>'Impostazioni salvate.',
        'disabled'=>'SMTP dedicato disattivato. Gli OTP usano il sistema precedente.',
        'tested'=>'Server SMTP ha accettato la prova. Verifica di averla ricevuta, anche nello spam, prima di attivare.',
        'enabled'=>'SMTP dedicato attivo per gli altri utenti. Verifica un login con un account diverso da ID 1. Il tuo OTP resta sul sistema precedente.',
        'missing'=>'Inserisci la password SMTP prima di proseguire.',
        'crypto'=>'Cifratura non disponibile: verificare sodium e le quattro chiavi di wp-config.php.',
        'storage'=>'Impossibile salvare la configurazione.',
        'psr_otp_smtp_test_limit'=>'Attendi almeno 30 secondi tra le prove.',
        'psr_otp_smtp_test_busy'=>'Prova non disponibile, riprova.',
        'psr_otp_smtp_decrypt_failed'=>'Password non decifrabile: inseriscila nuovamente.',
        'psr_otp_smtp_key_unavailable'=>'Chiavi di sicurezza non disponibili.',
        'psr_otp_smtp_535'=>'Autenticazione SMTP rifiutata (535). Verifica la password.',
        'psr_otp_smtp_5_7_26'=>'Messaggio rifiutato per autenticazione del dominio (5.7.26).',
        'psr_otp_smtp_transport_failed'=>'Connessione o invio SMTP non riuscito. Verifica il servizio Aruba.'
    );
    return $messages[$code] ?? 'Operazione non riuscita. Codice diagnostico: ' . $code;
}
function psr_otp_smtp_redirect($result) {
    $result = preg_match('/\A(?:saved|disabled|tested|enabled|missing|crypto|storage|psr_otp_smtp_[a-z0-9_]+)\z/D', $result) ? $result : 'storage';
    if (function_exists('psr_center_log_event')) {
        psr_center_log_event('security_setting_changed', in_array($result,array('saved','disabled','tested','enabled'),true)?'info':'warning',
            function_exists('psr_login_ip')?psr_login_ip():'', '', '', 'otp_smtp', $result);
    }
    wp_safe_redirect(add_query_arg('smtp_result',$result,psr_center_url('otp'))); exit;
}
function psr_otp_smtp_admin_save() {
    if (!psr_otp_smtp_owner() || ($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') { wp_die('Non autorizzato','',array('response'=>403)); }
    check_admin_referer('psr_otp_smtp_save');
    $action = $_POST['smtp_operation'] ?? '';
    if (!is_string($action) || !in_array($action,array('save','test'),true)) { wp_die('Richiesta non valida','',array('response'=>400)); }
    $input = $_POST['psr_smtp_password'] ?? '';
    if (!is_string($input)) { wp_die('Richiesta non valida','',array('response'=>400)); }
    $password = wp_unslash($input);
    unset($_POST['psr_smtp_password'], $_REQUEST['psr_smtp_password'], $input);
    $old = psr_otp_smtp_record();
    $record = $old;
    $changed = $password !== '';
    if ($changed) {
        $encrypted = psr_otp_smtp_encrypt_password($password);
        if (function_exists('sodium_memzero')) { sodium_memzero($password); } else { unset($password); }
        if (!$encrypted) { psr_otp_smtp_redirect('crypto'); }
        $record = array('enabled'=>false,'encrypted'=>$encrypted);
    }
    $enabled = ($_POST['psr_smtp_enabled'] ?? '') === '1';
    if ($action === 'test' || $enabled) {
        if (empty($record['encrypted'])) { psr_otp_smtp_redirect('missing'); }
        // A checked save with newly entered credentials or activation requires a real SMTP test.
        if ($action === 'test' || $changed || empty($old['enabled'])) {
            $sent = psr_otp_smtp_test($record);
            if (is_wp_error($sent)) { psr_otp_smtp_redirect($sent->get_error_code()); }
            if ($action === 'test') { psr_otp_smtp_redirect('tested'); }
        }
    }
    $record['enabled'] = $enabled;
    if (!psr_otp_smtp_store($record)) { psr_otp_smtp_redirect('storage'); }
    psr_otp_smtp_redirect($enabled ? 'enabled' : ($changed ? 'saved' : 'disabled'));
}
add_action('admin_post_psr_otp_smtp_save','psr_otp_smtp_admin_save');

function psr_otp_smtp_admin_panel() {
    if (!psr_otp_smtp_owner()) { return; }
    $record = psr_otp_smtp_record();
    $stored = !empty($record['encrypted']);
    $active = psr_otp_smtp_enabled();
    $override = defined('PSR_OTP_SMTP_ENABLED') && PSR_OTP_SMTP_ENABLED === false;
    $key = psr_otp_smtp_site_key();
    $ready = $key !== false;
    if ($ready) { sodium_memzero($key); }
    echo '<div class="psrc-card" style="margin-top:18px"><h2>SMTP dedicato agli OTP</h2><p>Riservato all’amministratore ID 1. Il tuo account ID 1 usa sempre il sistema precedente WordPress/Plesk, anche se SMTP dedicato è attivo o non funziona. Le altre email del Comune non vengono modificate.</p>';
    $result = $_GET['smtp_result'] ?? '';
    if (is_string($result) && preg_match('/\A(?:saved|disabled|tested|enabled|missing|crypto|storage|psr_otp_smtp_[a-z0-9_]{1,64})\z/D',$result)) {
        echo '<div role="status" class="notice notice-' . (in_array($result,array('saved','disabled','tested','enabled'),true)?'success':'error') . ' inline"><p>' . esc_html(psr_otp_smtp_result_message($result)) . '</p></div>';
    }
    echo '<p><strong>Trasporto altri utenti:</strong> ' . ($active?'SMTP dedicato Aruba':'Sistema precedente WordPress') . ' · <strong>Password:</strong> ' . ($stored?'Cifrata nel database':'Non configurata') . '</p>';
    if ($override) { echo '<div class="notice notice-warning inline"><p>Disattivazione di emergenza imposta da wp-config.php.</p></div>'; }
    if (!$ready) { echo '<div class="notice notice-error inline"><p>Chiavi di sicurezza o estensione sodium non disponibili. Non attivare SMTP.</p></div>'; }
    echo '<form method="post" action="' . esc_url(admin_url('admin-post.php')) . '" autocomplete="off">';
    wp_nonce_field('psr_otp_smtp_save');
    echo '<input type="hidden" name="action" value="psr_otp_smtp_save"><div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin:16px 0">';
    foreach (array('Mittente / Username'=>'otp@servizipa.cloud','Server SMTP'=>'out.postassl.it','Porta / Sicurezza'=>'465 · SSL/TLS') as $label=>$value) {
        echo '<div><label>' . esc_html($label) . '</label><p><strong>' . esc_html($value) . '</strong></p></div>';
    }
    echo '</div><p><label for="psr-smtp-password"><strong>Password SMTP</strong></label><br><input id="psr-smtp-password" type="password" name="psr_smtp_password" maxlength="1024" autocomplete="new-password" style="width:min(100%,360px)" placeholder="' . ($stored?'Lascia vuoto per mantenere la password':'Inserisci la password della casella') . '"></p>';
    echo '<p><label><input type="checkbox" name="psr_smtp_enabled" value="1" ' . checked(!empty($record['enabled']),true,false) . '> Utilizza SMTP dedicato per gli OTP degli altri utenti (ID 1 sempre escluso)</label></p>';
    echo '<p class="psrc-muted">Prima salva la password senza attivare, invia una prova e controlla la posta. L’attivazione esegue un ulteriore test SMTP. Il test non contiene codici OTP e arriva soltanto all’indirizzo protetto ID 1; i veri OTP di ID 1 non usano mai questo SMTP. Attendi 30 secondi tra i test.</p>';
    echo '<div style="display:flex;gap:8px;flex-wrap:wrap"><button type="submit" class="psrc-btn" name="smtp_operation" value="save">Salva impostazioni</button><button type="submit" class="psrc-btn secondary" name="smtp_operation" value="test">Invia email di prova</button></div></form>';
    echo '<p class="psrc-muted">In caso di blocco puoi ripristinare il vecchio trasporto da Plesk File Manager inserendo <code>define(&#39;PSR_OTP_SMTP_ENABLED&#39;, false);</code> in wp-config.php. Non disabilita gli OTP.</p></div>';
}
