<?php
/**
 * PSR Login Security - OTP via email obbligatorio per WordPress ID 1 e lockout IP progressivo.
 * Modulo autonomo caricato dal tema, non esegue scansioni dei file.
 * Richiede PHP 7.4+ e posta WordPress configurata; gli OTP non sono mai salvati in chiaro.
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

/**
 * Destinatario bloccato dopo la prima verifica: evitare cambi email silenziosi.
 * Prima della conferma usa la mail dell'utente ID 1 (deve gia' essere controllata).
 */
function psr_login_email_target($user) {
    $pinned = get_option('_psr_login_email_to', '');
    $email = is_string($pinned) && $pinned !== '' ? $pinned : ($user instanceof WP_User ? $user->user_email : '');
    return is_string($email) && is_email($email) ? $email : '';
}
function psr_login_email_digest($ticket, $code) {
    return hash_hmac('sha256', 'psr-login-email-v1|' . $ticket . '|' . $code, wp_salt('secure_auth'));
}
function psr_login_email_matches($pending, $ticket, $code) {
    if (!is_array($pending) || !preg_match('/^[0-9]{8}$/D', (string)$code)) { return false; }
    if ((int)($pending['code_expires'] ?? 0) < time() || (int)($pending['deadline'] ?? 0) < time()) { return false; }
    $expected = $pending['digest'] ?? '';
    return is_string($expected) && strlen($expected) === 64
        && hash_equals($expected, psr_login_email_digest($ticket, (string)$code));
}
/** Limite condiviso tra nuove richieste e reinvii dell'ID 1 per sito. */
function psr_login_email_can_send() {
    $now = time();
    $state = get_transient('_psr_login_email_send_limit');
    if (!is_array($state) || $now - (int)($state['start'] ?? 0) >= HOUR_IN_SECONDS) {
        $state = array('start'=>$now, 'last'=>0, 'n'=>0);
    }
    if ((int)($state['last'] ?? 0) > $now - 60 || (int)($state['n'] ?? 0) >= 10) { return false; }
    $state['last'] = $now;
    $state['n'] = (int)$state['n'] + 1;
    set_transient('_psr_login_email_send_limit', $state, HOUR_IN_SECONDS);
    return true;
}
/** Emette OTP: salva soltanto HMAC e metadati, non il codice in chiaro. */
function psr_login_email_issue($user, $ticket, &$pending) {
    $email = psr_login_email_target($user);
    if ($email === '') { return new WP_Error('psr_login_email_missing', 'Email OTP non configurata. Contattare il responsabile del server.'); }
    if (!function_exists('wp_mail') || !function_exists('random_int')) {
        return new WP_Error('psr_login_email_unavailable', 'Servizio OTP via email non disponibile. Accesso negato.');
    }
    if (!psr_login_email_can_send()) {
        return new WP_Error('psr_login_email_throttled', 'Troppi invii OTP oppure richiesta troppo ravvicinata. Riprovare piu\' tardi.');
    }
    $now = time();
    $code = str_pad((string)random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
    $pending['digest'] = psr_login_email_digest($ticket, $code);
    $pending['code_expires'] = min($now + 300, (int)($pending['deadline'] ?? ($now + 300)));
    $pending['sent_at'] = $now;
    $subject = '[PSR] Codice di accesso WordPress';
    $body = "E' stato richiesto l'accesso all'amministrazione WordPress.\n\n";
    $body .= 'Sito: ' . home_url('/') . "\n";
    $body .= 'Codice OTP: ' . $code . "\n\n";
    $body .= "Scade entro 5 minuti ed e' utilizzabile una sola volta.\n";
    $body .= "Se non sei stato tu, cambia la password e segnala l'evento al responsabile.\n";
    try { $sent = wp_mail($email, $subject, $body, array('Content-Type: text/plain; charset=UTF-8')); }
    catch (Throwable $e) { $sent = false; }
    // Restituzione true da wp_mail significa accettazione dal trasporto, NON consegna garantita.
    if (!$sent) {
        unset($pending['digest'], $pending['code_expires']);
        return new WP_Error('psr_login_email_send_failed', 'Invio OTP non riuscito. Verificare SMTP da Plesk: accesso negato.');
    }
    return true;
}
function psr_login_session_key($token) {
    return hash_hmac('sha256', $token, wp_salt('logged_in'));
}
function psr_login_mark_auth_cookie($auth_cookie, $expire, $expiration, $user_id, $scheme, $token) {
    if (empty($GLOBALS['psr_login_granting']) || (int) $user_id !== 1 || !is_string($token)) { return; }
    $sessions = get_user_meta(1, '_psr_login_email_sessions', true);
    if (!is_array($sessions)) { $sessions = array(); }
    $sessions = array_filter($sessions, static function ($expiry) { return (int)$expiry > time(); });
    $sessions[psr_login_session_key($token)] = (int)$expiration;
    // Evitare accumuli a tempo indefinito di sessioni vecchie.
    if (count($sessions) > 25) { asort($sessions, SORT_NUMERIC); $sessions = array_slice($sessions, -25, null, true); }
    update_user_meta(1, '_psr_login_email_sessions', $sessions);
}
add_action('set_auth_cookie', 'psr_login_mark_auth_cookie', 10, 6);

function psr_login_force_proved_session() {
    if (defined('WP_CLI') && WP_CLI) { return; }
    if (defined('DOING_CRON') && DOING_CRON) { return; }
    if ((int) get_current_user_id() !== 1) { return; }
    $token = function_exists('wp_get_session_token') ? wp_get_session_token() : '';
    $sessions = get_user_meta(1, '_psr_login_email_sessions', true);
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
    // Niente bypass via XML-RPC, REST, application passwords, login alternativi.
    if (!psr_login_interactive()) {
        return new WP_Error('psr_login_interactive_only', 'Accesso amministrativo disponibile solo tramite login con OTP via email.');
    }
    if (!function_exists('random_bytes')) {
        return new WP_Error('psr_login_crypto_missing', 'Generatore crittografico OTP non disponibile. Accesso negato.');
    }
    $ticket = bin2hex(random_bytes(32));
    $now = time();
    $pending = array(
        'uid'=>1, 'created'=>$now, 'deadline'=>$now + 900,
        'ua'=>hash('sha256', (string)($_SERVER['HTTP_USER_AGENT'] ?? '')),
        'ip'=>psr_login_ip(), 'tries'=>0, 'resends'=>0,
        'remember'=>!empty($_POST['rememberme']),
        'redirect'=>wp_validate_redirect(isset($_REQUEST['redirect_to']) ? (string)wp_unslash($_REQUEST['redirect_to']) : '', admin_url()),
    );
    $sent = psr_login_email_issue($user, $ticket, $pending);
    if (is_wp_error($sent)) { return $sent; } // Mai creare sessioni senza OTP inviato.
    set_transient(psr_login_pending_key($ticket), $pending, 5 * MINUTE_IN_SECONDS);
    psr_login_set_pending_cookie($ticket, $pending['code_expires']);
    wp_safe_redirect(add_query_arg('action', 'psr_mfa', wp_login_url()));
    exit;
}
add_filter('authenticate', 'psr_login_start_otp', 99, 3);

function psr_login_pending() {
    $ticket = isset($_COOKIE[psr_login_cookie_name()]) ? (string)$_COOKIE[psr_login_cookie_name()] : '';
    if (!preg_match('/^[a-f0-9]{64}$/D', $ticket)) { return array('', false); }
    $p = get_transient(psr_login_pending_key($ticket));
    if (!is_array($p) || (int)($p['code_expires'] ?? 0) < time() || (int)($p['deadline'] ?? 0) < time() ||
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
        wp_die('Verifica scaduta o non valida. <a href="' . esc_url(wp_login_url()) . '">Ricomincia dal login</a>.', 'PSR - OTP email', array('response'=>403));
    }
    $user = get_userdata((int)$pending['uid']);
    if (!psr_login_main_admin($user)) { psr_login_cancel_ticket($ticket); wp_die('Account non valido.'); }
    $message = '';
    if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
        $nonce = (isset($_POST['psr_nonce']) && is_string($_POST['psr_nonce'])) ? sanitize_text_field(wp_unslash($_POST['psr_nonce'])) : '';
        if (!wp_verify_nonce($nonce, 'psr_mfa_' . $ticket)) {
            wp_die('Richiesta non valida.', 'PSR - OTP email', array('response'=>403));
        }
        if (isset($_POST['psr_resend'])) {
            if ((int)($pending['resends'] ?? 0) >= 3) {
                $message = 'Limite di reinvii raggiunto: ricomincia dal login.';
            } elseif ((int)($pending['sent_at'] ?? 0) > time() - 60) {
                $message = 'Attendi almeno 60 secondi prima del reinvio.';
            } else {
                $pending['resends'] = (int)($pending['resends'] ?? 0) + 1;
                $sent = psr_login_email_issue($user, $ticket, $pending);
                if (!is_wp_error($sent)) {
                    set_transient(psr_login_pending_key($ticket), $pending, max(1, $pending['code_expires'] - time()));
                    psr_login_set_pending_cookie($ticket, $pending['code_expires']);
                    $message = 'Nuovo codice richiesto: controlla la posta. Il codice precedente non e\' piu\' valido.';
                } else { $message = $sent->get_error_message(); }
            }
        } else {
            $input = (isset($_POST['psr_otp']) && is_string($_POST['psr_otp'])) ? trim(wp_unslash($_POST['psr_otp'])) : '';
            if (psr_login_email_matches($pending, $ticket, $input)) {
                // Usa un token monouso: invalida il ticket PRIMA di creare la sessione.
                psr_login_cancel_ticket($ticket);
                if (!get_option('_psr_login_email_to', '')) {
                    update_option('_psr_login_email_to', psr_login_email_target($user), false);
                }
                if (psr_login_ip() !== '') { delete_transient(psr_login_ip_key(psr_login_ip())); }
                psr_login_grant_session($user, $pending);
                wp_safe_redirect($pending['redirect']); exit;
            }
            $pending['tries'] = (int)$pending['tries'] + 1;
            psr_login_rate_failure(psr_login_ip());
            if ($pending['tries'] >= 5 || psr_login_rate_blocked(psr_login_ip())) {
                psr_login_cancel_ticket($ticket);
                wp_die('Troppi codici errati. Ricomincia dal login dopo il blocco.', 'PSR - OTP email', array('response'=>403));
            }
            set_transient(psr_login_pending_key($ticket), $pending, max(1, (int)$pending['code_expires'] - time()));
            $message = 'Codice non valido o scaduto.';
        }
    }
    nocache_headers();
    login_header('PSR - Verifica email obbligatoria');
    echo '<p><strong>Verifica obbligatoria per amministratore principale.</strong></p>';
    echo '<p>Abbiamo richiesto un codice OTP di 8 cifre via email. Scade dopo 5 minuti ed e\' valido una sola volta.</p>';
    if ($message) { echo '<div class="message" role="alert">' . esc_html($message) . '</div>'; }
    echo '<form method="post" autocomplete="off">';
    wp_nonce_field('psr_mfa_' . $ticket, 'psr_nonce');
    echo '<p><label for="psr_otp">Codice ricevuto via email</label>';
    echo '<input class="input" type="text" id="psr_otp" name="psr_otp" required maxlength="8" pattern="[0-9]{8}" autocomplete="one-time-code" inputmode="numeric"></p>';
    echo '<p class="submit"><input type="submit" class="button button-primary button-large" value="Verifica e accedi"></p>';
    echo '</form>';
    echo '<form method="post" style="margin-top:12px">';
    wp_nonce_field('psr_mfa_' . $ticket, 'psr_nonce');
    echo '<input type="hidden" name="psr_resend" value="1">';
    echo '<p><button type="submit" class="button">Invia nuovamente il codice (max 3)</button></p></form>';
    echo '<p>Se la posta non arriva, verificare SMTP dal pannello Plesk; non esiste bypass pubblico.</p>';
    echo '<p><a href="' . esc_url(wp_login_url()) . '">Annulla e torna al login</a></p>';
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
    $user = get_userdata(1);
    $target = psr_login_email_target($user);
    $pinned = get_option('_psr_login_email_to', '');
    echo '<div class="wrap"><h1>PSR Sicurezza accessi (solo ID 1)</h1>';
    echo '<p><strong>OTP obbligatorio via email</strong> (8 cifre, 5 minuti, una sola volta).</p>';
    echo '<p>Destinatario: <strong>' . esc_html($target ? $target : 'NON CONFIGURATO') . '</strong>'; 
    echo $pinned ? ' (bloccato dopo prima verifica)</p>' : ' (sara\' bloccato alla prima verifica riuscita)</p>';
    echo '<p>Verifica che la consegna email WordPress/SMTP funzioni prima di distribuire questo modulo.</p>';
    echo '<p>Blocco IP: 5 tentativi = 10 min; 10 = 1 ora; 15 = 24 ore; 25 = 7 giorni.</p>';
    if (isset($_POST['psr_unban_ip'])) {
        check_admin_referer('psr_unban_ip');
        $ip = sanitize_text_field(wp_unslash($_POST['psr_unban_ip']));
        if (filter_var($ip, FILTER_VALIDATE_IP)) {
            delete_transient(psr_login_ip_key($ip));
            echo '<div class="notice notice-success"><p>Blocco IP revocato per ' . esc_html($ip) . '.</p></div>';
        } else { echo '<div class="notice notice-error"><p>Indirizzo IP non valido.</p></div>'; }
    }
    echo '<form method="post">'; wp_nonce_field('psr_unban_ip');
    echo '<label for="psr_unban_ip">Sblocca IP autorizzato:</label> <input id="psr_unban_ip" name="psr_unban_ip" type="text" placeholder="203.0.113.5" required> ';
    submit_button('Rimuovi blocco IP', 'secondary', 'submit', false);
    echo '</form><p>Se cambia la casella email o SMTP si interrompe, il recupero richiede accesso amministrativo a Plesk/WP-CLI, non dal login pubblico.</p></div>';
}
