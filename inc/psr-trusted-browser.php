<?php
/**
 * PSR - Browser attendibile: skip OTP solo dopo un precedente OTP verificato,
 * esclusivamente per ID 1 dalla rete aziendale identificata con un singolo IP pubblico.
 * Non cambia il controllo sessione (max 12 ore e inattivita' 1 ora).
 * Le chiavi per la verifica del cookie sono solo lato WordPress, mai nel codice.
 */
if (!defined('ABSPATH')) { exit; }

function psr_trust_enabled() {
    return !defined('PSR_TRUSTED_BROWSER_ENABLED') || PSR_TRUSTED_BROWSER_ENABLED === true;
}

/** Configurazione modificabile da wp-config.php, non dall'utente via HTTP. */
function psr_trust_office_ip() {
    $ip = defined('PSR_TRUSTED_BROWSER_OFFICE_IP')
        ? PSR_TRUSTED_BROWSER_OFFICE_IP : '185.136.124.56';
    return is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
        ? $ip : '';
}

function psr_trust_office_status() {
    if (!psr_trust_enabled()) { return 'disabled'; }
    if (!is_ssl()) { return 'https_missing'; }
    if (!function_exists('psr_login_ip') || !function_exists('psr_geo_decision')) { return 'module_missing'; }
    $office = psr_trust_office_ip();
    $actual = psr_login_ip();
    if ($office === '') { return 'office_invalid'; }
    if ($actual === '' || !hash_equals($office, $actual)) { return 'ip_mismatch'; }
    $geo = psr_geo_decision();
    if (!is_array($geo) || !empty($geo['blocked']) || ($geo['country'] ?? '') !== 'IT'
        || !hash_equals($actual, (string)($geo['ip'] ?? ''))) { return 'geo_denied'; }
    return 'ok';
}
function psr_trust_office_request() { return psr_trust_office_status() === 'ok'; }

function psr_trust_cookie_name() {
    // Cookie host-only: non si trasferisce tra diversi Comuni o sottodomini.
    return '__Host-psr_trusted_' . substr(hash('sha256', site_url('/')), 0, 16);
}
function psr_trust_meta_key() { return '_psr_trusted_browsers_v1'; }
/** ID 1 only. Old records of any other user are permanently ineligible. */
function psr_trust_owner($user) {
    return $user instanceof WP_User && (int)$user->ID === 1;
}
function psr_trust_ttl() { return 30 * DAY_IN_SECONDS; }

function psr_trust_user_agent_hash() {
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (!is_string($ua) || $ua === '' || strlen($ua) > 512) { return ''; }
    return hash_hmac('sha256', 'psr-trust-ua-v1|' . $ua, wp_salt('auth'));
}
function psr_trust_password_stamp($user) {
    if (!psr_trust_owner($user) ||
        !is_string($user->user_pass) || $user->user_pass === '') { return ''; }
    return hash_hmac('sha256', 'psr-trust-pw-v1|' . $user->user_pass, wp_salt('secure_auth'));
}
function psr_trust_token_digest($user_id, $token) {
    return hash_hmac('sha256', 'psr-trust-token-v1|' . (int)$user_id . '|'
        . site_url('/') . '|' . $token, wp_salt('auth'));
}
function psr_trust_office_stamp() {
    return hash_hmac('sha256', 'psr-trust-office-v1|' . psr_trust_office_ip(), wp_salt('auth'));
}

/** Non accetta login da REST, XML-RPC, PHP CLI o da IP non aziendale. */
/** Inspect the exact same predicates for login and the owner-only diagnostic panel. */
function psr_trust_browser_status($user) {
    if (!psr_trust_owner($user)) { return 'other_user'; }
    $office = psr_trust_office_status();
    if ($office !== 'ok') { return $office; }
    $ua_hash = psr_trust_user_agent_hash();
    $pw_stamp = psr_trust_password_stamp($user);
    if ($ua_hash === '' || $pw_stamp === '') { return 'identity_missing'; }
    $token = $_COOKIE[psr_trust_cookie_name()] ?? '';
    if ($token === '') { return 'cookie_missing'; }
    if (!is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)) { return 'cookie_invalid'; }
    $records = get_user_meta(1, psr_trust_meta_key(), true);
    $entry = is_array($records) ? ($records[psr_trust_token_digest(1,$token)] ?? null) : null;
    if (!is_array($entry)) { return 'record_missing'; }
    $now = time();
    if ((int)($entry['expires'] ?? 0) <= $now
        || (int)($entry['created'] ?? 0) <= 0
        || (int)($entry['created'] ?? 0) > $now
        || (int)($entry['expires'] ?? 0) - (int)($entry['created'] ?? 0) > psr_trust_ttl()) { return 'expired'; }
    if (!is_string($entry['ua'] ?? null) || !hash_equals($ua_hash,$entry['ua'])) { return 'browser_changed'; }
    if (!is_string($entry['password'] ?? null) || !hash_equals($pw_stamp,$entry['password'])) { return 'password_changed'; }
    if (!is_string($entry['office'] ?? null) || !hash_equals(psr_trust_office_stamp(),$entry['office'])) { return 'office_changed'; }
    return 'ok';
}
function psr_trust_can_login($user) {
    return function_exists('psr_login_interactive') && psr_login_interactive()
        && psr_trust_browser_status($user) === 'ok';
}

/** Salva SOLO l'HMAC del token, mai il suo valore in chiaro nel database. */
function psr_trust_store_token($user, $token) {
    if (!psr_trust_owner($user)
        || !is_string($token) || !preg_match('/^[a-f0-9]{64}$/D', $token)
        || !psr_trust_office_request()) { return false; }

    $ua_hash = psr_trust_user_agent_hash();
    $pw_stamp = psr_trust_password_stamp($user);
    if ($ua_hash === '' || $pw_stamp === '') { return false; }
    $lock = function_exists('psr_auth_lock') ? psr_auth_lock('trusted-browser|' . (int)$user->ID) : false;
    if (!$lock) { return false; }
    try {
        $records = get_user_meta((int)$user->ID, psr_trust_meta_key(), true);
        if (!is_array($records)) { $records = array(); }
        $now = time();
        foreach ($records as $key => $item) {
            if (!is_array($item) || (int)($item['expires'] ?? 0) <= $now
                || !is_string($item['password'] ?? null) || !hash_equals($pw_stamp, $item['password'])
                || !is_string($item['office'] ?? null) || !hash_equals(psr_trust_office_stamp(), $item['office'])) {
                unset($records[$key]);
            }
        }
        $digest = psr_trust_token_digest($user->ID, $token);
        $records[$digest] = array(
            'created' => $now,
            'expires' => $now + psr_trust_ttl(),
            'ua' => $ua_hash,
            'password' => $pw_stamp,
            'office' => psr_trust_office_stamp(),
        );
        // Massimo 25 browser per utente, con rimozione dei piu' vecchi.
        if (count($records) > 25) {
            uasort($records, static function ($a, $b) {
                return (int)($a['created'] ?? 0) <=> (int)($b['created'] ?? 0);
            });
            $records = array_slice($records, -25, null, true);
        }
        return update_user_meta((int)$user->ID, psr_trust_meta_key(), $records) !== false;
    } finally {
        psr_auth_unlock($lock);
    }
}

function psr_trust_toast_key($user_id, $wp_session_token) {
    return '_psr_trust_welcome_' . substr(hash_hmac('sha256',
        (int)$user_id . '|' . $wp_session_token, wp_salt('auth')), 0, 40);
}

/** Invocata solamente dopo OTP valido, sessione creata e cookie di sessione emesso. */
function psr_trust_after_otp($user) {
    if (!psr_trust_owner($user)) { return false; }
    $status = psr_trust_office_status();
    if ($status !== 'ok') { psr_trust_enrollment_note($status); return false; }
    if (headers_sent()) { psr_trust_enrollment_note('headers_sent'); return false; }
    try {
        $token = bin2hex(random_bytes(32));
    } catch (Throwable $e) {
        psr_trust_enrollment_note('random_failed');
        return false; // Nessun fallback a token prevedibili.
    }
    if (!psr_trust_store_token($user, $token)) { psr_trust_enrollment_note('store_failed'); return false; }
    $ok = setcookie(psr_trust_cookie_name(), $token, array(
        'expires' => time() + psr_trust_ttl(),
        'path' => '/', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax'
    ));
    if (!$ok) {
        // La credenziale non consegnata non e' utilizzabile.
        psr_trust_enrollment_note('cookie_emit_failed'); return false;
    }
    psr_trust_enrollment_note('issued');
    // La notifica non contiene token, IP, email o identificativi personali.
    $session = $GLOBALS['psr_device_new_token'] ?? '';
    if (is_string($session) && $session !== '') {
        set_transient(psr_trust_toast_key($user->ID, $session), 1, 5 * MINUTE_IN_SECONDS);
    }
    return true;
}

/** Invalida i browser dopo un cambio di password, anche da reset/account admin. */
function psr_trust_password_reset($user) {
    if ($user instanceof WP_User) { delete_user_meta((int)$user->ID, psr_trust_meta_key()); }
}
add_action('password_reset', 'psr_trust_password_reset', 10, 1);
function psr_trust_profile_update($user_id, $old_user_data) {
    $updated = get_userdata((int)$user_id);
    if ($updated instanceof WP_User && $old_user_data instanceof WP_User
        && !hash_equals((string)$old_user_data->user_pass, (string)$updated->user_pass)) {
        delete_user_meta((int)$user_id, psr_trust_meta_key());
    }
}
add_action('profile_update', 'psr_trust_profile_update', 10, 2);

/** Toast mostrato una sola volta dopo prima associazione OTP dalla rete aziendale. */
function psr_trust_welcome_toast() {
    if (!function_exists('psr_login_session_verified') || !is_user_logged_in()) { return; }
    $uid = (int)get_current_user_id();
    if ($uid !== 1 || !psr_login_session_verified($uid)) { return; }
    $session = wp_get_session_token();
    if (!is_string($session) || $session === '') { return; }
    $key = psr_trust_toast_key($uid, $session);
    if (!get_transient($key)) { return; }
    delete_transient($key);
    echo '<div id="psr-trust-toast" role="status" aria-live="polite" class="psr-trust-toast">';
    echo '<strong>Browser aziendale riconosciuto</strong>';
    echo '<p>Solo il tuo account ID 1, su questo Comune e da questo browser, per 30 giorni può accedere dalla rete dell’ufficio con la sola password, senza nuovo OTP. Da altre reti o browser servirà ancora l’OTP. Le sessioni restano limitate a 12 ore (1 ora di inattività).</p>';
    echo '<button type="button" aria-label="Chiudi avviso" onclick="document.getElementById(\'psr-trust-toast\').remove()">×</button></div>';
    echo '<style>.psr-trust-toast{position:fixed;right:18px;bottom:20px;z-index:2147483000;max-width:430px;background:#173f33;color:#fff;padding:16px 44px 16px 18px;border-radius:12px;box-shadow:0 7px 28px #0005;font:14px/1.45 system-ui,-apple-system,Segoe UI,sans-serif}.psr-trust-toast strong{display:block;font-size:16px;margin-bottom:6px}.psr-trust-toast p{margin:0}.psr-trust-toast button{position:absolute;top:8px;right:10px;border:0;background:none;color:#fff;font-size:25px;cursor:pointer}@media(max-width:600px){.psr-trust-toast{left:10px;right:10px;bottom:12px;max-width:none}}</style>';
    echo '<script>setTimeout(function(){var t=document.getElementById("psr-trust-toast");if(t)t.remove();},14000);</script>';
}
add_action('admin_footer', 'psr_trust_welcome_toast', PHP_INT_MAX);
add_action('wp_footer', 'psr_trust_welcome_toast', PHP_INT_MAX);

/** Last enrollment result is informational only; never used to authorize. */
function psr_trust_enrollment_note($status) {
    update_user_meta(1, '_psr_trusted_browser_last_result', array('status'=>$status,'time'=>time()));
}
function psr_trust_status_label($status) {
    $labels = array('ok'=>'Riconosciuto e valido','disabled'=>'Funzione disattivata','https_missing'=>'HTTPS non riconosciuto',
        'module_missing'=>'Modulo non disponibile','office_invalid'=>'IP aziendale non configurato correttamente',
        'ip_mismatch'=>'IP rilevato diverso da quello autorizzato','geo_denied'=>'Verifica geografica o blocco IP non superata',
        'identity_missing'=>'Dati del browser o account non disponibili','cookie_missing'=>'Cookie assente nel browser',
        'cookie_invalid'=>'Cookie non valido','record_missing'=>'Associazione non trovata nel database',
        'expired'=>'Associazione scaduta','browser_changed'=>'Browser o User-Agent cambiato',
        'password_changed'=>'Password WordPress cambiata','office_changed'=>'IP autorizzato cambiato',
        'headers_sent'=>'Intestazioni già inviate: cookie non emesso','random_failed'=>'Generazione token non riuscita',
        'store_failed'=>'Salvataggio associazione non riuscito','cookie_emit_failed'=>'Emissione cookie non riuscita',
        'issued'=>'Cookie emesso dopo OTP valido (da verificare nel browser)','other_user'=>'Account non autorizzato');
    return is_string($status) ? ($labels[$status] ?? 'Non disponibile') : 'Non disponibile';
}
function psr_trust_diagnostics_panel() {
    if (!function_exists('psr_center_is_owner') || !psr_center_is_owner() || !psr_login_session_verified(1) || !is_ssl()) { return; }
    $user = get_userdata(1);
    $status = psr_trust_browser_status($user);
    $last = get_user_meta(1,'_psr_trusted_browser_last_result',true);
    echo '<div class="psrc-card" style="margin-top:18px"><h2>Browser aziendale · 30 giorni</h2><p>Esclusivamente per ID 1 e per il singolo IP autorizzato. Le sessioni WordPress conservano le normali scadenze.</p>';
    echo '<p><strong>IP pubblico rilevato:</strong> <code>' . esc_html(psr_login_ip() ?: 'Non rilevato') . '</code><br><strong>IP autorizzato:</strong> <code>' . esc_html(psr_trust_office_ip() ?: 'Non configurato') . '</code></p>';
    echo '<p><strong>Stato rete:</strong> ' . esc_html(psr_trust_status_label(psr_trust_office_status())) . '<br><strong>Stato browser attuale:</strong> ' . esc_html(psr_trust_status_label($status)) . '</p>';
    if (is_array($last)) { echo '<p><strong>Ultimo tentativo di associazione:</strong> ' . esc_html(psr_trust_status_label($last['status']??'')) . ' · ' . esc_html(wp_date('d/m/Y H:i:s',(int)($last['time']??0))) . '</p>'; }
    if ($status === 'ok') {
        $token = $_COOKIE[psr_trust_cookie_name()];
        $records = get_user_meta(1,psr_trust_meta_key(),true);
        $entry = $records[psr_trust_token_digest(1,$token)]??array();
        if (!empty($entry['expires'])) { echo '<p><strong>Scadenza:</strong> ' . esc_html(wp_date('d/m/Y H:i:s',(int)$entry['expires'])) . '</p>'; }
    }
    echo '<p class="psrc-muted">Se il cookie è assente, prova un nuovo login con OTP nello stesso browser normale e con lo stesso indirizzo del sito (www o senza www). Non usare una finestra privata. Nessun IP viene autorizzato automaticamente.</p></div>';
}
