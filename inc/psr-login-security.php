<?php
/**
 * PSR Login Security - TOTP obbligatorio per WordPress ID 1 e lockout IP progressivo.
 * Modulo autonomo caricato dal tema, non esegue scansioni dei file.
 * Richiede OpenSSL e PHP 7.4+; i dati OTP restano nel database del singolo sito.
 */
if (!defined('ABSPATH')) { exit; }

function psr_login_main_admin($user) {
    return $user instanceof WP_User && (int) $user->ID === 1;
}
function psr_login_ip() {
    // Non fidarsi mai di X-Forwarded-For/CF-Connecting-IP senza un proxy configurato.
    $ip = isset($_SERVER['REMOTE_ADDR']) ? trim((string) $_SERVER['REMOTE_ADDR']) : '';
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ? $ip : '';
}
function psr_login_ip_key($ip) {
    return 'psr_login_ip_' . hash_hmac('sha256', $ip, wp_salt('auth'));
}
function psr_login_rate_state($ip) {
    if ($ip === '') { return array('n'=>0, 'until'=>0, 'start'=>0); }
    $state = get_transient(psr_login_ip_key($ip));
    return is_array($state) ? $state : array('n'=>0, 'until'=>0, 'start'=>0);
}
function psr_login_rate_blocked($ip) {
    if (!$ip) { return 0; }
    $state = psr_login_rate_state($ip);
    return max(0, (int) ($state['until'] ?? 0) - time());
}
function psr_login_rate_failure($ip) {
    if ($ip === '') { return; }
    $state = psr_login_rate_state($ip);
    $now = time();
    if (empty($state['start']) || $now - (int) $state['start'] > 7 * DAY_IN_SECONDS) {
        $state = array('n'=>0,'until'=>0,'start'=>$now);
    }
    $state['n'] = (int) ($state['n'] ?? 0) + 1;
    // 5 errori: 10 minuti; 10: 1 ora; 15: 1 giorno; 25: ban 7 giorni.
    $count = $state['n'];
    if ($count >= 25) { $state['until'] = max((int)$state['until'], $now + 7 * DAY_IN_SECONDS); }
    elseif ($count >= 15) { $state['until'] = max((int)$state['until'], $now + DAY_IN_SECONDS); }
    elseif ($count >= 10) { $state['until'] = max((int)$state['until'], $now + HOUR_IN_SECONDS); }
    elseif ($count >= 5) { $state['until'] = max((int)$state['until'], $now + 10 * MINUTE_IN_SECONDS); }
    set_transient(psr_login_ip_key($ip), $state, 8 * DAY_IN_SECONDS);
}
function psr_login_auth_precheck($user, $username, $password) {
    if (defined('WP_CLI') && WP_CLI) { return $user; }
    $remaining = psr_login_rate_blocked(psr_login_ip());
    if ($remaining) {
        return new WP_Error('psr_login_blocked', 'Troppi tentativi di accesso. Riprova piu\' tardi.');
    }
    return $user;
}
add_filter('authenticate', 'psr_login_auth_precheck', 1, 3);

function psr_login_failed_password($username, $error = null) {
    if ($error instanceof WP_Error) {
        $codes = $error->get_error_codes();
        // Non contare OTP richiesto, login non interattivo o IP gia' bloccato.
        foreach ($codes as $code) { if (strpos((string) $code, 'psr_') === 0) { return; } }
        // Account inesistente o password errata sono i soli errori conteggiati.
        if (!array_intersect($codes, array('incorrect_password','invalid_username','invalid_email','authentication_failed','invalid_password','invalid_credentials'))) { return; }
    }
    psr_login_rate_failure(psr_login_ip());
}
add_action('wp_login_failed', 'psr_login_failed_password', 10, 2);

function psr_login_b32_encode($bytes) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $bits = 0; $value = 0; $out = '';
    for ($i = 0; $i < strlen($bytes); $i++) {
        $value = ($value << 8) | ord($bytes[$i]); $bits += 8;
        while ($bits >= 5) { $out .= $alphabet[($value >> ($bits - 5)) & 31]; $bits -= 5; }
        $value &= (1 << $bits) - 1;
    }
    if ($bits) { $out .= $alphabet[($value << (5 - $bits)) & 31]; }
    return $out;
}
function psr_login_b32_decode($encoded) {
    $encoded = strtoupper(preg_replace('/[\s\-]/', '', (string)$encoded));
    if ($encoded === '' || preg_match('/[^A-Z2-7]/', $encoded)) { return false; }
    $alpha = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567'; $bits = 0; $value = 0; $out = '';
    for ($i = 0; $i < strlen($encoded); $i++) {
        $n = strpos($alpha, $encoded[$i]); $value = ($value << 5) | $n; $bits += 5;
        if ($bits >= 8) { $out .= chr(($value >> ($bits - 8)) & 255); $bits -= 8; $value &= (1 << $bits) - 1; }
    }
    return $out;
}
function psr_login_totp_at($b32, $step) {
    $secret = psr_login_b32_decode($b32);
    if ($secret === false || strlen($secret) < 16 || $step < 0) { return false; }
    $counter = pack('N2', ($step >> 32) & 0xffffffff, $step & 0xffffffff);
    $hash = hash_hmac('sha1', $counter, $secret, true);
    $offset = ord(substr($hash, -1)) & 0x0f;
    $n = ((ord($hash[$offset]) & 0x7f) << 24) | (ord($hash[$offset+1]) << 16) | (ord($hash[$offset+2]) << 8) | ord($hash[$offset+3]);
    return str_pad((string) ($n % 1000000), 6, '0', STR_PAD_LEFT);
}
function psr_login_totp_step($b32, $code, $user_id = 0) {
    if (!preg_match('/^[0-9]{6}$/D', (string)$code)) { return false; }
    $now = (int) floor(time() / 30);
    $last = $user_id ? (int) get_user_meta($user_id, '_psr_login_last_step', true) : -1;
    foreach (array($now-1, $now, $now+1) as $step) {
        $expected = psr_login_totp_at($b32, $step);
        if ($expected !== false && $step > $last && hash_equals($expected, (string)$code)) { return $step; }
    }
    return false;
}
function psr_login_secret_encrypt($secret) {
    if (!function_exists('openssl_encrypt') || !function_exists('random_bytes')) { return false; }
    $iv = random_bytes(12); $tag = '';
    $key = hash('sha256', 'psr-login-otp-v1|' . wp_salt('secure_auth'), true);
    $data = openssl_encrypt($secret, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    return is_string($data) ? base64_encode($iv . $tag . $data) : false;
}
function psr_login_secret_decrypt($encrypted) {
    $raw = base64_decode((string)$encrypted, true);
    if (!is_string($raw) || strlen($raw) < 29 || !function_exists('openssl_decrypt')) { return false; }
    $key = hash('sha256', 'psr-login-otp-v1|' . wp_salt('secure_auth'), true);
    $secret = openssl_decrypt(substr($raw, 28), 'aes-256-gcm', $key, OPENSSL_RAW_DATA, substr($raw, 0, 12), substr($raw, 12, 16));
    return is_string($secret) && preg_match('/^[A-Z2-7]{32}$/D', $secret) ? $secret : false;
}
function psr_login_recovery_digest($code) {
    return hash_hmac('sha256', strtoupper(preg_replace('/[^A-Z0-9]/i', '', $code)), 'psr-recovery-v1|' . wp_salt('secure_auth'));
}
function psr_login_recovery_codes($user_id) {
    $plain = array(); $hashes = array();
    for ($i = 0; $i < 10; $i++) {
        $bytes = psr_login_b32_encode(random_bytes(8));
        $code = 'PSR-' . substr($bytes, 0, 4) . '-' . substr($bytes, 4, 4) . '-' . substr($bytes, 8, 4);
        $plain[] = $code; $hashes[] = psr_login_recovery_digest($code);
    }
    update_user_meta($user_id, '_psr_login_recovery', $hashes);
    return $plain;
}
function psr_login_validate_recovery($user_id, $candidate) {
    if (!preg_match('/^PSR-[A-Z2-7]{4}-[A-Z2-7]{4}-[A-Z2-7]{4}$/Di', trim($candidate))) { return false; }
    $hashes = get_user_meta($user_id, '_psr_login_recovery', true);
    if (!is_array($hashes)) { return false; }
    $test = psr_login_recovery_digest($candidate);
    foreach ($hashes as $k => $hash) {
        if (is_string($hash) && hash_equals($hash, $test)) {
            unset($hashes[$k]); update_user_meta($user_id, '_psr_login_recovery', array_values($hashes)); return true;
        }
    }
    return false;
}
function psr_login_session_key($token) {
    return hash_hmac('sha256', $token, wp_salt('logged_in'));
}
function psr_login_mark_auth_cookie($auth_cookie, $expire, $expiration, $user_id, $scheme, $token) {
    if (empty($GLOBALS['psr_login_granting']) || (int) $user_id !== 1 || !is_string($token)) { return; }
    $sessions = get_user_meta(1, '_psr_login_sessions', true);
    if (!is_array($sessions)) { $sessions = array(); }
    $sessions = array_filter($sessions, static function ($expiry) { return (int)$expiry > time(); });
    $sessions[psr_login_session_key($token)] = (int)$expiration;
    // Evitare accumuli a tempo indefinito di sessioni vecchie.
    if (count($sessions) > 25) { asort($sessions, SORT_NUMERIC); $sessions = array_slice($sessions, -25, null, true); }
    update_user_meta(1, '_psr_login_sessions', $sessions);
}
add_action('set_auth_cookie', 'psr_login_mark_auth_cookie', 10, 6);

function psr_login_force_proved_session() {
    if (defined('WP_CLI') && WP_CLI) { return; }
    if (defined('DOING_CRON') && DOING_CRON) { return; }
    if ((int) get_current_user_id() !== 1) { return; }
    $token = function_exists('wp_get_session_token') ? wp_get_session_token() : '';
    $sessions = get_user_meta(1, '_psr_login_sessions', true);
    $valid = is_string($token) && $token !== '' && is_array($sessions)
        && (int)($sessions[psr_login_session_key($token)] ?? 0) > time();
    if ($valid) { return; }
    // Revochiamo le vecchie sessioni (incluse quelle precedenti all'attivazione OTP).
    if (function_exists('wp_destroy_current_session')) { wp_destroy_current_session(); }
    wp_clear_auth_cookie();
    wp_set_current_user(0);
}
add_action('init', 'psr_login_force_proved_session', 1);

function psr_login_disable_app_passwords_for_id1($available, $user) {
    return psr_login_main_admin($user) ? false : $available;
}
add_filter('wp_is_application_passwords_available_for_user', 'psr_login_disable_app_passwords_for_id1', 10, 2);

function psr_login_interactive() {
    $script = basename((string)parse_url((string)($_SERVER['SCRIPT_NAME'] ?? ''), PHP_URL_PATH));
    return $script === 'wp-login.php' && strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) === 'POST';
}
function psr_login_cookie_name() { return 'psr_login_pending'; }
function psr_login_pending_key($ticket) { return 'psr_login_pending_' . hash('sha256', $ticket); }
function psr_login_set_pending_cookie($ticket, $expiry) {
    $path = defined('COOKIEPATH') ? COOKIEPATH : '/';
    $path = $path ?: '/';
    setcookie(psr_login_cookie_name(), $ticket, array('expires'=>$expiry, 'path'=>$path, 'secure'=>is_ssl(), 'httponly'=>true, 'samesite'=>'Strict'));
}
function psr_login_cancel_ticket($ticket) {
    if ($ticket !== '') { delete_transient(psr_login_pending_key($ticket)); }
    psr_login_set_pending_cookie('', time()-3600);
}
function psr_login_start_otp($user, $username, $password) {
    if (!psr_login_main_admin($user) || (defined('WP_CLI') && WP_CLI)) { return $user; }
    // Nessuna sessione autenticata puo' nascere da XML-RPC, API, AJAX o login alternativi.
    if (!psr_login_interactive()) {
        return new WP_Error('psr_login_interactive_only', 'Per questo account e\' richiesto l\'accesso interattivo con OTP.');
    }
    if (!function_exists('random_bytes') || !function_exists('openssl_encrypt')) {
        return new WP_Error('psr_login_crypto_missing', 'OTP non disponibile: contattare l\'amministratore del server (OpenSSL).');
    }
    $ticket = bin2hex(random_bytes(32));
    $encrypted = get_user_meta($user->ID, '_psr_login_secret', true);
    $enroll = $encrypted === '' || $encrypted === false;
    if (!$enroll && psr_login_secret_decrypt($encrypted) === false) {
        return new WP_Error('psr_login_otp_key_problem', 'Configurazione OTP non decifrabile: intervenire da Plesk per il recupero.');
    }
    $pending = array(
        'uid'=>(int)$user->ID, 'created'=>time(), 'ua'=>hash('sha256', (string)($_SERVER['HTTP_USER_AGENT'] ?? '')),
        'ip'=>psr_login_ip(), 'tries'=>0, 'remember'=>!empty($_POST['rememberme']), 'redirect'=>
            wp_validate_redirect(isset($_REQUEST['redirect_to']) ? (string)wp_unslash($_REQUEST['redirect_to']) : '', admin_url()),
        'enroll'=>$enroll, 'secret'=>$enroll ? psr_login_b32_encode(random_bytes(20)) : '',
    );
    set_transient(psr_login_pending_key($ticket), $pending, 5 * MINUTE_IN_SECONDS);
    psr_login_set_pending_cookie($ticket, time()+5*MINUTE_IN_SECONDS);
    wp_safe_redirect(add_query_arg('action', 'psr_mfa', wp_login_url()));
    exit;
}
add_filter('authenticate', 'psr_login_start_otp', 99, 3);

function psr_login_pending() {
    $ticket = isset($_COOKIE[psr_login_cookie_name()]) ? (string)$_COOKIE[psr_login_cookie_name()] : '';
    if (!preg_match('/^[a-f0-9]{64}$/D', $ticket)) { return array('', false); }
    $p = get_transient(psr_login_pending_key($ticket));
    if (!is_array($p) || time() - (int)($p['created'] ?? 0) > 300 ||
        !hash_equals((string)($p['ua'] ?? ''), hash('sha256', (string)($_SERVER['HTTP_USER_AGENT'] ?? ''))) ||
        (string)($p['ip'] ?? '') !== psr_login_ip() || (int)($p['uid'] ?? 0) !== 1) {
        return array($ticket, false);
    }
    return array($ticket, $p);
}
function psr_login_grant_session($user, $pending) {
    $GLOBALS['psr_login_granting'] = true;
    wp_set_auth_cookie($user->ID, !empty($pending['remember']));
    unset($GLOBALS['psr_login_granting']);
    wp_set_current_user($user->ID);
    do_action('wp_login', $user->user_login, $user);
}
function psr_login_otp_page() {
    list($ticket, $pending) = psr_login_pending();
    if (psr_login_rate_blocked(psr_login_ip())) { psr_login_cancel_ticket($ticket); $pending = false; }
    if (!$pending) {
        wp_die('Verifica scaduta o non valida. <a href="' . esc_url(wp_login_url()) . '">Ricomincia dal login</a>.', 'PSR - OTP', array('response'=>403));
    }
    $user = get_userdata((int)$pending['uid']);
    if (!psr_login_main_admin($user)) { psr_login_cancel_ticket($ticket); wp_die('Account non valido.'); }
    $message = '';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!isset($_POST['psr_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['psr_nonce'])), 'psr_mfa_' . $ticket)) {
            wp_die('Richiesta non valida.', 'PSR - OTP', array('response'=>403));
        }
        $input = isset($_POST['psr_otp']) && is_string($_POST['psr_otp']) ? trim(wp_unslash($_POST['psr_otp'])) : '';
        if (strlen($input) > 64) { $input = ''; }
        $secret = !empty($pending['enroll']) ? $pending['secret'] : psr_login_secret_decrypt(get_user_meta(1, '_psr_login_secret', true));
        $step = is_string($secret) ? psr_login_totp_step($secret, $input, !empty($pending['enroll']) ? 0 : 1) : false;
        $recovered = false;
        if (!$pending['enroll'] && $step === false) { $recovered = psr_login_validate_recovery(1, $input); }
        if ($step !== false || $recovered) {
            if (!empty($pending['enroll'])) {
                $cipher = psr_login_secret_encrypt($secret);
                if ($cipher === false) { wp_die('Crittografia OTP non disponibile.'); }
                update_user_meta(1, '_psr_login_secret', $cipher);
                update_user_meta(1, '_psr_login_last_step', (int)$step);
                // Elimina tutte le sessioni admin precedenti al nuovo OTP.
                if (class_exists('WP_Session_Tokens')) { WP_Session_Tokens::get_instance(1)->destroy_all(); }
                update_user_meta(1, '_psr_login_sessions', array());
                $backup_codes = psr_login_recovery_codes(1);
            } else {
                if ($step !== false) { update_user_meta(1, '_psr_login_last_step', (int)$step); }
                $backup_codes = array();
            }
            psr_login_cancel_ticket($ticket);
            if (psr_login_ip() !== '') { delete_transient(psr_login_ip_key(psr_login_ip())); }
            psr_login_grant_session($user, $pending);
            if (!empty($backup_codes)) {
                nocache_headers();
                login_header('PSR - Salva i codici di recupero');
                echo '<p><strong>OTP attivato per l\'amministratore principale.</strong></p>';
                echo '<p>Salva questi 10 codici monouso in luogo sicuro, fuori dal sito. Non saranno mostrati nuovamente.</p>';
                echo '<pre style="white-space:pre-wrap;user-select:all">' . esc_html(implode("\n", $backup_codes)) . '</pre>';
                echo '<p><a class="button button-primary" href="' . esc_url($pending['redirect']) . '">Ho salvato i codici: entra nel pannello</a></p>';
                login_footer(); exit;
            }
            wp_safe_redirect($pending['redirect']); exit;
        }
        $pending['tries'] = (int)$pending['tries'] + 1;
        psr_login_rate_failure(psr_login_ip());
        if ($pending['tries'] >= 5) {
            psr_login_cancel_ticket($ticket);
            wp_die('Troppi codici errati. Ricomincia dal login dopo il periodo di blocco.', 'PSR - OTP', array('response'=>403));
        }
        set_transient(psr_login_pending_key($ticket), $pending, 5 * MINUTE_IN_SECONDS);
        $message = 'Codice non valido. Verifica l\'ora sul telefono e riprova.';
    }
    nocache_headers();
    login_header('PSR - Verifica obbligatoria');
    if ($message) { echo '<div class="message" role="alert">' . esc_html($message) . '</div>'; }
    if (!empty($pending['enroll'])) {
        $label = wp_specialchars_decode(get_bloginfo('name'), ENT_QUOTES);
        $uri = 'otpauth://totp/' . rawurlencode($label . ':admin-ID1') . '?secret=' . rawurlencode($pending['secret']) . '&issuer=' . rawurlencode($label) . '&algorithm=SHA1&digits=6&period=30';
        echo '<p><strong>Primo accesso: registra obbligatoriamente OTP.</strong></p>';
        echo '<p>Apri Google Authenticator, Microsoft Authenticator o altra app TOTP e aggiungi una chiave manuale:</p>';
        echo '<p style="font-family:monospace;word-break:break-all"><strong>' . esc_html($pending['secret']) . '</strong></p>';
        echo '<p>Tipo: basato sull\'ora (TOTP), 6 cifre, ogni 30 secondi. Nessun codice viene inviato via email.</p>';
        // URI non caricato da servizi esterni (evita esporre il segreto).
        echo '<details><summary>URI di configurazione per app compatibili</summary><code style="overflow-wrap:anywhere">' . esc_html($uri) . '</code></details>';
    } else {
        echo '<p>Inserisci il codice a 6 cifre dell\'app Authenticator oppure uno dei codici di recupero monouso.</p>';
    }
    echo '<form method="post" autocomplete="off">';
    wp_nonce_field('psr_mfa_' . $ticket, 'psr_nonce');
    echo '<p><label for="psr_otp">Codice OTP o recupero</label><input class="input" type="text" id="psr_otp" name="psr_otp" required maxlength="64" autocomplete="one-time-code" inputmode="text"></p>';
    echo '<p class="submit"><input type="submit" class="button button-primary button-large" value="Verifica e accedi"></p>';
    echo '<p><a href="' . esc_url(wp_login_url()) . '">Annulla e torna al login</a></p></form>';
    login_footer(); exit;
}
add_action('login_form_psr_mfa', 'psr_login_otp_page');

function psr_login_admin_menu() {
    if ((int)get_current_user_id() !== 1 || !current_user_can('manage_options')) { return; }
    add_management_page('PSR Sicurezza accessi', 'PSR Sicurezza accessi', 'manage_options', 'psr-login-security', 'psr_login_admin_page');
}
add_action('admin_menu', 'psr_login_admin_menu');
function psr_login_admin_page() {
    if ((int)get_current_user_id() !== 1 || !current_user_can('manage_options')) { wp_die('Non autorizzato', '', array('response'=>403)); }
    $status = get_user_meta(1, '_psr_login_secret', true);
    $codes = get_user_meta(1, '_psr_login_recovery', true);
    echo '<div class="wrap"><h1>PSR Sicurezza accessi (solo ID 1)</h1>';
    echo '<p>OTP: <strong>' . ($status ? 'Configurato e obbligatorio' : 'Da configurare al prossimo login') . '</strong>. Codici di recupero rimasti: <strong>' . (is_array($codes) ? count($codes) : 0) . '</strong>.</p>';
    echo '<p>Blocchi IP progressivi: 5 errori = 10 minuti; 10 = 1 ora; 15 = 24 ore; 25 = ban per 7 giorni. Utilizza esclusivamente REMOTE_ADDR.</p>';
    if (isset($_POST['psr_unban_ip'])) {
        check_admin_referer('psr_unban_ip');
        $ip = sanitize_text_field(wp_unslash($_POST['psr_unban_ip']));
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            delete_transient(psr_login_ip_key($ip));
            echo '<div class="notice notice-success"><p>Blocco IP revocato per ' . esc_html($ip) . '.</p></div>';
        } else { echo '<div class="notice notice-error"><p>Indirizzo IP non valido.</p></div>'; }
    }
    echo '<form method="post">'; wp_nonce_field('psr_unban_ip');
    echo '<label for="psr_unban_ip">Sblocca indirizzo IP (solo se autorizzato):</label> <input id="psr_unban_ip" name="psr_unban_ip" type="text" placeholder="203.0.113.5" required> ';
    submit_button('Rimuovi blocco IP', 'secondary', 'submit', false);
    echo '</form><p>Se perdi telefono e codici: recupero solo via accesso amministrativo sicuro a Plesk/database. Non esiste un bypass pubblico dell\'OTP.</p></div>';
}
