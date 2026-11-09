<?php
/**
 * PSR account-aware rate limit v4.
 * Protegge ogni account dalla rete che genera gli errori, senza punire i colleghi
 * dietro lo stesso IP. La blacklist e i ban IP manuali restano separati.
 */
if (!defined('ABSPATH')) { exit; }

function psr_account_rate_identity($username) {
    static $cache = array();
    if (is_string($username) && isset($cache[$username])) { return $cache[$username]; }
    if (!is_string($username) || trim($username) === '') {
        return array('subject'=>'', 'name'=>'', 'exists'=>false);
    }
    $input = trim($username);
    if (strlen($input) > 320) { $input = substr($input, 0, 320); }
    $user = get_user_by('login', $input);
    if (!($user instanceof WP_User) && strpos($input, '@') !== false) {
        $user = get_user_by('email', $input);
    }
    if ($user instanceof WP_User) {
        $identity = array('subject'=>'user:' . (int)$user->ID, 'name'=>(string)$user->user_login, 'exists'=>true);
    } else {
        $identity = array('subject'=>'invalid:' . hash('sha256', strtolower($input)),
            'name'=>substr($input, 0, 80), 'exists'=>false);
    }
    if (count($cache) < 64 && is_string($username)) { $cache[$username] = $identity; }
    return $identity;
}

function psr_account_rate_key($ip, $subject) {
    return 'psr_account_rate_' . hash_hmac('sha256', $ip . '|' . $subject, wp_salt('auth'));
}
function psr_account_rate_state($ip, $username) {
    if ($ip === '') { return array(); }
    $id = psr_account_rate_identity($username);
    if ($id['subject'] === '') { return array(); }
    $state = psr_auth_fresh_transient(psr_account_rate_key($ip, $id['subject']));
    return is_array($state) ? $state : array();
}
function psr_account_rate_blocked($ip, $username) {
    $state = psr_account_rate_state($ip, $username);
    return max(0, (int)($state['until'] ?? 0) - time());
}
function psr_account_rate_clear($ip, $username) {
    if ($ip === '') { return false; }
    $id = psr_account_rate_identity($username);
    if ($id['subject'] === '') { return false; }
    $key = psr_account_rate_key($ip, $id['subject']);
    $lock = psr_auth_lock('account|' . $key);
    if (!$lock) { return false; }
    try { return delete_transient($key) || get_transient($key) === false; }
    finally { psr_auth_unlock($lock); }
}

function psr_account_rate_risk_key($ip) {
    return 'psr_account_risk_' . hash_hmac('sha256', $ip, wp_salt('auth'));
}
function psr_account_rate_risk_state($ip) {
    if ($ip === '') { return array(); }
    $state = psr_auth_fresh_transient(psr_account_rate_risk_key($ip));
    return is_array($state) && (int)($state['start'] ?? 0) > time() - HOUR_IN_SECONDS
        ? $state : array();
}
function psr_account_rate_unknown_limited($ip, $username) {
    if ($ip === '') { return false; }
    $state = psr_account_rate_risk_state($ip);
    if (empty($state['suspicious'])) { return false; }
    $id = psr_account_rate_identity($username);
    // Un attacco a molti nomi non blocca gli account legittimi dell'ufficio.
    return $id['subject'] !== '' && !$id['exists'];
}

/** Conta account realmente bloccati in un'ora; nessun ban globale da soli errori. */
function psr_account_rate_note_ip($ip, $identity, $newly_blocked) {
    if ($ip === '') { return; }
    $key = psr_account_rate_risk_key($ip);
    $lock = psr_auth_lock('risk|' . $key, 250);
    if (!$lock) { return; }
    $alert = false;
    $count = 0;
    try {
        $now = time();
        $state = psr_auth_fresh_transient($key);
        if (!is_array($state) || (int)($state['start'] ?? 0) <= $now - HOUR_IN_SECONDS) {
            $state = array('start'=>$now, 'errors'=>0, 'blocked'=>array(), 'invalid'=>array(), 'alerted'=>false, 'suspicious'=>false);
        }
        $state['errors'] = min(10000, (int)($state['errors'] ?? 0) + 1);
        if ($newly_blocked && !empty($identity['exists'])) {
            if (count($state['blocked']) < 64) { $state['blocked'][hash('sha256', $identity['subject'])] = $now; }
        } elseif (empty($identity['exists'])) {
            // Mantiene memoria limitata contro username casuali generati dai bot.
            if (count($state['invalid']) < 64) {
                $state['invalid'][hash('sha256', $identity['subject'])] = true;
            }
        }
        $count = count($state['blocked']);
        // Sei account coinvolti = allarme. Mai bloccare tutta la sede su questa sola prova.
        if ($count >= 6 || (count($state['invalid']) >= 30 && $state['errors'] >= 60)) {
            $state['suspicious'] = true;
            if (empty($state['alerted'])) { $state['alerted'] = true; $alert = true; }
        }
        // Finestra di 60 minuti senza nuove tabelle; nessuna query sulle pagine pubbliche.
        set_transient($key, $state, HOUR_IN_SECONDS);
    } finally { psr_auth_unlock($lock); }
    if ($alert && function_exists('psr_center_log_event')) {
        psr_center_log_event('ip_risk', 'critical', $ip, '', '', 'login',
            'Anomalia: ' . $count . ' account distinti limitati; IP monitorato, accessi legittimi consentiti');
    }
}

/** Limiti individuali, con contatori non condivisi fra colleghi. */
function psr_account_rate_failure($ip, $username, $reason = 'password') {
    if ($ip === '' || !is_string($username) || trim($username) === '') { return false; }
    $identity = psr_account_rate_identity($username);
    if ($identity['subject'] === '') { return false; }
    $key = psr_account_rate_key($ip, $identity['subject']);
    $lock = psr_auth_lock('account|' . $key);
    if (!$lock) { return false; }
    $count = 0;
    $newly_blocked = false;
    $until = 0;
    try {
        $now = time();
        $state = psr_auth_fresh_transient($key);
        if (!is_array($state) || (int)($state['start'] ?? 0) <= $now - HOUR_IN_SECONDS) {
            $state = array('start'=>$now, 'n'=>0, 'until'=>0);
        }
        $was_blocked = (int)($state['until'] ?? 0) > $now;
        // Gli errori durante un blocco gia' attivo non lo prolungano.
        if (!$was_blocked) {
            $state['n'] = min(1000, (int)$state['n'] + 1);
            $count = (int)$state['n'];
            $duration = $count >= 15 ? HOUR_IN_SECONDS : ($count >= 10 ? 30 * MINUTE_IN_SECONDS : ($count >= 5 ? 10 * MINUTE_IN_SECONDS : 0));
            if ($duration) {
                $state['until'] = $now + $duration;
                $newly_blocked = true;
            }
        } else { $count = (int)($state['n'] ?? 0); }
        $until = (int)($state['until'] ?? 0);
        set_transient($key, $state, 2 * HOUR_IN_SECONDS);
    } finally { psr_auth_unlock($lock); }
    if (function_exists('psr_center_log_event')) {
        psr_center_log_event($reason === 'otp' ? 'otp_failed' : 'login_failed',
            $newly_blocked ? 'critical' : 'warning', $ip, $identity['name'], '',
            $reason === 'otp' ? 'otp' : 'login',
            'Errore account/IP n. ' . $count . ($newly_blocked ? '; limitato fino a ' . gmdate('Y-m-d H:i:s', $until) . ' UTC' : ''));
        if ($newly_blocked && !empty($identity['exists'])) {
            psr_center_log_event('account_blocked', 'warning', $ip, $identity['name'],
                '', 'login', 'Limite per account da questo IP; scadenza ' . gmdate('Y-m-d H:i:s', $until) . ' UTC');
        }
    }
    psr_account_rate_note_ip($ip, $identity, $newly_blocked);
    return true;
}

/**
 * Migrazione non bloccante: elimina solo i vecchi ban IP automatici,
 * verificati italiani e non riconducibili a protezioni manuali/GeoIP.
 * Quattro record per esecuzione WP-Cron, mai in una normale visita.
 */
function psr_account_rate_migrate_schedule() {
    if (get_option('_psr_account_rate_migration_v4', '') === 'done') { return; }
    if (!wp_next_scheduled('psr_account_rate_migration_batch')) {
        wp_schedule_single_event(time() + MINUTE_IN_SECONDS, 'psr_account_rate_migration_batch');
    }
}
add_action('init', 'psr_account_rate_migrate_schedule', 40);
function psr_account_rate_migrate_batch() {
    if (!function_exists('psr_center_install') || !psr_center_install()
        || !function_exists('psr_geo_country')) { return; }
    global $wpdb;
    list(, $table) = psr_center_tables();
    $cursor = get_option('_psr_account_rate_migration_cursor_v4', '');
    if (!is_string($cursor)) { $cursor = ''; }
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT ip FROM $table WHERE origin = 'auto' AND ip > %s
         AND blocked_until > %s ORDER BY ip ASC LIMIT 4",
        $cursor, gmdate('Y-m-d H:i:s')
    ));
    if (!$rows) {
        update_option('_psr_account_rate_migration_v4', 'done', false);
        delete_option('_psr_account_rate_migration_cursor_v4');
        return;
    }
    foreach ($rows as $row) {
        $ip = (string)$row->ip;
        update_option('_psr_account_rate_migration_cursor_v4', $ip, false);
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)
            || (function_exists('psr_geo_ip_blacklisted') && psr_geo_ip_blacklisted($ip))) { continue; }
        $geo = psr_geo_country($ip);
        if (($geo['country'] ?? '') !== 'IT') { continue; }
        $key = psr_login_ip_key($ip);
        $lock = psr_auth_lock('rate|' . $key);
        if (!$lock) { continue; }
        try {
            $state = psr_login_rate_state($ip);
            if (!empty($state['site_ban']) || ($state['mode'] ?? '') === 'geo') { continue; }
            // Verifica ancora l'origine: un ban manuale concorrente non va rimosso.
            $origin = $wpdb->get_var($wpdb->prepare("SELECT origin FROM $table WHERE ip = %s", $ip));
            if ($origin !== 'auto') { continue; }
            delete_transient($key);
            psr_center_remove_ip($ip);
            if (function_exists('psr_center_log_event')) {
                psr_center_log_event('legacy_auto_unban', 'info', $ip, '', 'IT', 'migration',
                    'Vecchio blocco automatico IP rimosso; protezioni account ora separate');
            }
        } finally { psr_auth_unlock($lock); }
    }
    // Job a piccoli lotti: non rallenta le pagine dei cittadini.
    if (!wp_next_scheduled('psr_account_rate_migration_batch')) {
        wp_schedule_single_event(time() + 2 * MINUTE_IN_SECONDS, 'psr_account_rate_migration_batch');
    }
}
add_action('psr_account_rate_migration_batch', 'psr_account_rate_migrate_batch');
