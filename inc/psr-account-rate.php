<?php
/**
 * PSR account-aware rate limit v4.
 * Protegge ogni account dalla rete che genera gli errori, senza punire i colleghi
 * dietro lo stesso IP. La blacklist e i ban IP manuali restano separati.
 */
if (!defined('ABSPATH')) { exit; }

function psr_account_rate_identity($username) {
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
        return array('subject'=>'user:' . (int)$user->ID, 'name'=>(string)$user->user_login, 'exists'=>true);
    }
    return array('subject'=>'invalid:' . hash('sha256', strtolower($input)),
        'name'=>substr($input, 0, 80), 'exists'=>false);
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
            $state['blocked'][hash('sha256', $identity['subject'])] = $now;
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
        // Finestra mobile approssimata da blocchi di un'ora, senza cron né nuove tabelle.
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
