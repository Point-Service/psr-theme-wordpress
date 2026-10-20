<?php
/**
 * PSR Geo Login — blocca il login e il pannello WordPress quando il server
 * riconosce con sufficiente certezza che l'IP pubblico e' estero (tutti i ruoli).
 * Caricato dal tema. Frontend: solo ban IP già confermati, mai geoblocking dei visitatori.
 *
 * Requisiti: WordPress con API HTTP e transients; PHP 7.4+.
 * IP: soltanto REMOTE_ADDR (mai intestazioni proxy non autenticate).
 * Servizio di geolocalizzazione: https://api.country.is/{ip}.
 * In caso di GeoIP non disponibile/non conclusivo non effettua blocchi:
 * preferiamo segnalare il problema piuttosto che rendere impossibile il login.
 */
if (!defined('ABSPATH')) { exit; }

function psr_geo_enabled() {
    return !defined('PSR_GEO_LOGIN_ENABLED') || (bool) PSR_GEO_LOGIN_ENABLED;
}
/** Blacklist globale cumulativa: applicata a login e amministrazione di ogni Comune.
 * L'IP confermato qui non puo' essere sbloccato dall'interfaccia.
 * E' possibile aggiungere altri indirizzi via PSR_GEO_EXTRA_DENY_IPS in wp-config.php.
 */
function psr_geo_global_deny_ips() {
    $list = array('45.148.232.225');
    if (defined('PSR_GEO_EXTRA_DENY_IPS')) {
        $extra = PSR_GEO_EXTRA_DENY_IPS;
        if (is_string($extra)) { $extra = explode(',', $extra); }
        if (is_array($extra)) { $list = array_merge($list, $extra); }
    }
    $valid = array();
    foreach ($list as $candidate) {
        $candidate = trim((string) $candidate);
        if (filter_var($candidate, FILTER_VALIDATE_IP)) { $valid[$candidate] = true; }
    }
    return array_keys($valid);
}
function psr_geo_ip_blacklisted($ip) {
    if (!filter_var($ip, FILTER_VALIDATE_IP)) { return false; }
    $packed = inet_pton($ip);
    foreach (psr_geo_global_deny_ips() as $candidate) {
        if ($packed === inet_pton($candidate)) { return true; }
    }
    return false;
}
function psr_geo_public_ip() {
    $ip = isset($_SERVER['REMOTE_ADDR']) ? trim((string) $_SERVER['REMOTE_ADDR']) : '';
    // Compatibilita' con IPv4 mappati in IPv6, senza fidarsi di header HTTP.
    if (stripos($ip, '::ffff:') === 0) {
        $v4 = substr($ip, 7);
        if (filter_var($v4, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) { $ip = $v4; }
    }
    return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) ? $ip : '';
}
/** Hash irreversibile dell'IP fiduciario: non si salva l'indirizzo in chiaro.
 * La rotazione delle chiavi WordPress invalida le impronte (fail closed).
 */
function psr_geo_trusted_digest($ip) {
    if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) { return ''; }
    $packed = @inet_pton($ip);
    return $packed === false ? '' : hash_hmac('sha256', 'psr-trusted-v1|' . bin2hex($packed), wp_salt('auth'));
}
function psr_geo_trusted_entries() {
    $entries = get_option('_psr_geo_trusted_ip_hashes', array());
    return is_array($entries) ? $entries : array();
}
function psr_geo_trusted_match($ip) {
    $digest = psr_geo_trusted_digest($ip);
    if ($digest === '' || psr_geo_ip_blacklisted($ip)) { return false; }
    $entries = psr_geo_trusted_entries();
    foreach ($entries as $candidate => $item) {
        if (is_string($candidate) && strlen($candidate) === 64 && hash_equals($candidate, $digest)) { return true; }
    }
    return false;
}
function psr_geo_ip_allowlisted($ip) {
    if ($ip === '') { return false; }
    if (psr_geo_trusted_match($ip)) { return true; }
    if (!defined('PSR_GEO_ALLOW_IPS')) { return false; }
    $list = PSR_GEO_ALLOW_IPS;
    if (is_string($list)) { $list = explode(',', $list); }
    if (!is_array($list)) { return false; }
    foreach ($list as $item) {
        $item = trim((string) $item);
        if ($item !== '' && filter_var($item, FILTER_VALIDATE_IP) && inet_pton($item) === inet_pton($ip)) {
            return true;
        }
    }
    return false;
}
function psr_geo_cache_key($ip) {
    // Il database non contiene l'IP in chiaro per questo lookup.
    return 'psr_geo_v1_' . substr(hash_hmac('sha256', $ip, wp_salt('auth')), 0, 40);
}
function psr_geo_country($ip) {
    if ($ip === '') { return array('country'=>'', 'source'=>'no_public_ip'); }
    if (psr_geo_ip_allowlisted($ip)) { return array('country'=>'IT', 'source'=>'allowlist'); }
    $key = psr_geo_cache_key($ip);
    $cached = get_transient($key);
    if (is_array($cached) && isset($cached['country'], $cached['source'])) { return $cached; }

    $country = '';
    $source = 'geoip_unavailable';
    // API pubblica; interrogata solo per i tentativi di accesso al backend.
    // Timeout limitato; nessuna richiesta dal frontend pubblico.
    if (function_exists('wp_remote_get')) {
        try {
            $response = wp_remote_get('https://api.country.is/' . rawurlencode($ip), array(
                'timeout'=>2, 'redirection'=>0, 'sslverify'=>true,
                'reject_unsafe_urls'=>true, 'headers'=>array('Accept'=>'application/json'),
                'user-agent'=>'PSR-GeoLogin/1.0',
            ));
        } catch (Throwable $e) {
            // Errore di plugin/HTTP: lasciare disponibile il portale, avviso all'ID 1.
            $response = new WP_Error('psr_geo_http_failed', 'GeoIP non disponibile');
        }
        if (!is_wp_error($response) && (int) wp_remote_retrieve_response_code($response) === 200) {
            $body = wp_remote_retrieve_body($response);
            if (is_string($body) && strlen($body) < 1024) {
                $data = json_decode($body, true);
                if (is_array($data) && isset($data['country'], $data['ip']) && is_string($data['country']) && is_string($data['ip'])
                    && filter_var($data['ip'], FILTER_VALIDATE_IP) && inet_pton($data['ip']) === inet_pton($ip)) {
                    $value = strtoupper($data['country']);
                    if (preg_match('/^[A-Z]{2}$/D', $value) && !in_array($value, array('XX','ZZ'), true)) {
                        $country = $value;
                        $source = 'country.is';
                    }
                }
            }
        }
    }
    $result = array('country'=>$country, 'source'=>$source);
    // 24h per classificazioni confermate, 5 minuti se verifica non possibile.
    set_transient($key, $result, $country !== '' ? DAY_IN_SECONDS : 5 * MINUTE_IN_SECONDS);
    return $result;
}
function psr_geo_decision() {
    if (!empty($GLOBALS['psr_geo_request_decision'])) { return $GLOBALS['psr_geo_request_decision']; }
    $out = array('blocked'=>false, 'reason'=>'disabled', 'country'=>'', 'ip'=>'');
    if ((defined('WP_CLI') && WP_CLI) || (defined('DOING_CRON') && DOING_CRON)) {
        return $GLOBALS['psr_geo_request_decision'] = $out;
    }
    $ip = psr_geo_public_ip();
    $out['ip'] = $ip;
    // Questo blocco ha priorita' su allowlist, GeoIP, cache e disattivazione del filtro paese.
    if (psr_geo_ip_blacklisted($ip)) {
        $out['blocked'] = true;
        $out['reason'] = 'global_blacklist';
        return $GLOBALS['psr_geo_request_decision'] = $out;
    }
    if (!psr_geo_enabled()) {
        return $GLOBALS['psr_geo_request_decision'] = $out;
    }
    if ($ip === '') {
        $out['reason'] = 'ip_not_public_or_proxy';
        return $GLOBALS['psr_geo_request_decision'] = $out;
    }
    if (psr_geo_ip_allowlisted($ip)) {
        $out['country'] = 'IT'; $out['reason'] = 'explicit_allowlist';
        return $GLOBALS['psr_geo_request_decision'] = $out;
    }
    $geo = psr_geo_country($ip);
    $out['country'] = $geo['country'];
    $out['reason'] = $geo['source'];
    $out['blocked'] = $geo['country'] !== '' && $geo['country'] !== 'IT';
    return $GLOBALS['psr_geo_request_decision'] = $out;
}
function psr_geo_deny_event($decision) {
    return isset($decision['reason']) && $decision['reason'] === 'global_blacklist' ? 'ip_blacklist_denied' : 'geo_denied';
}
function psr_geo_deny() {
    if (function_exists('psr_center_log_limited')) {
        $d = psr_geo_decision();
        psr_center_log_limited(psr_geo_deny_event($d),'critical',(string)$d['ip'],'',(string)$d['country'],'geo','Pannello o login respinto',60);
    }
    nocache_headers();
    if (function_exists('status_header')) { status_header(403); }
    wp_die('Accesso amministrativo non consentito da questo indirizzo IP. Contatta il gestore del portale.',
        'PSR - Accesso riservato', array('response'=>403));
}
/** Ban di sito pubblico solo per IP esplicitamente bloccati, MAI per semplici IP esteri.
 * Una lettura transient per richiesta WordPress, nessun servizio GeoIP/scan/crawl.
 * Nginx/Apache deve coprire file statici e PHP indipendenti da WordPress.
 * Recupero via wp-config.php: define('PSR_SITE_BAN_ENABLED', false);
 */
function psr_geo_site_ban_enabled() {
    if (defined('PSR_SITE_BAN_ENABLED')) { return (bool) PSR_SITE_BAN_ENABLED; }
    return get_option('_psr_site_ban_enabled', '1') === '1';
}
function psr_geo_site_ban_reason($ip) {
    if ($ip === '') { return ''; }
    if (psr_geo_ip_blacklisted($ip)) { return 'global_blacklist'; }
    if (function_exists('psr_login_rate_state')) {
        $state = psr_login_rate_state($ip);
        // 5/10 errori: lockout solo login. 15+ errori oppure ban manuale: sito WordPress.
        if ((int)($state['until'] ?? 0) > time() &&
            ((int)($state['n'] ?? 0) >= 15 || !empty($state['site_ban']))) { return 'rate_ban'; }
    }
    return '';
}
function psr_geo_site_ban_gate() {
    if (!psr_geo_site_ban_enabled() || (defined('WP_CLI') && WP_CLI)
        || (defined('DOING_CRON') && DOING_CRON)) { return; }
    $ip = psr_geo_public_ip();
    $reason = psr_geo_site_ban_reason($ip);
    if ($reason === '') { return; }
    if (function_exists('psr_center_log_limited')) {
        psr_center_log_limited('site_ban_denied','critical',$ip,'','','frontend',
            $reason === 'global_blacklist' ? 'Richiesta WordPress: blacklist permanente' : 'Richiesta WordPress: ban temporaneo', 300);
    }
    nocache_headers();
    if (function_exists('status_header')) { status_header(403); }
    if (!defined('DONOTCACHEPAGE')) { define('DONOTCACHEPAGE', true); }
    wp_die('Accesso non disponibile da questo indirizzo IP.', 'Accesso non consentito', array('response'=>403));
}
add_action('init', 'psr_geo_site_ban_gate', 0);

// Login standard e schermata OTP: il blocco avviene prima di chiedere credenziali.
function psr_geo_login_init() {
    if (psr_geo_decision()['blocked']) { psr_geo_deny(); }
}
add_action('login_init', 'psr_geo_login_init', 0);

// Blocca anche i percorsi che invocano wp_authenticate senza usare wp-login.php.
function psr_geo_authenticate($user, $username, $password) {
    if (psr_geo_decision()['blocked']) {
        if (function_exists('psr_center_log_limited')) {
            $d = psr_geo_decision();
            psr_center_log_limited(psr_geo_deny_event($d),'critical',(string)$d['ip'],(string)$username,(string)$d['country'],'authenticate','Tentativo login respinto',60);
        }
        return new WP_Error('psr_geo_foreign_ip', 'Accesso amministrativo non consentito da questo indirizzo IP.');
    }
    return $user;
}
add_filter('authenticate', 'psr_geo_authenticate', 0, 3);

// admin_init include anche admin-ajax.php e admin-post.php. Lascia intatti i form
// pubblici (nopriv), ma impedisce richieste backend di qualsiasi utente gia'
// autenticato, comprese quelle AJAX.
function psr_geo_admin_init() {
    // Vale per QUALSIASI utente autenticato, anche subscriber e contributor.
    // admin-ajax/admin-post pubblici (nopriv) rimangono operativi.
    if (!is_user_logged_in()) { return; }
    if (psr_geo_decision()['blocked']) { psr_geo_deny(); }
}
add_action('admin_init', 'psr_geo_admin_init', 0);

// I client REST gia' autenticati di qualsiasi ruolo non aggirano il gate.
function psr_geo_rest_authentication($result) {
    if (is_wp_error($result)) { return $result; }
    if (is_user_logged_in() && psr_geo_decision()['blocked']) {
        if (function_exists('psr_center_log_limited')) {
            $d = psr_geo_decision();
            psr_center_log_limited(psr_geo_deny_event($d),'critical',(string)$d['ip'],'',(string)$d['country'],'rest','API REST autenticata respinta',60);
        }
        return new WP_Error('psr_geo_foreign_ip', 'Accesso amministrativo non consentito da questo indirizzo IP.', array('status'=>403));
    }
    return $result;
}
add_filter('rest_authentication_errors', 'psr_geo_rest_authentication', 100);

// XML-RPC puo' offrire un altro percorso di autenticazione non-browser.
// Non disabilitare XML-RPC per il resto dei visitatori/servizi italiani.
function psr_geo_xmlrpc_enabled($enabled) {
    if (psr_geo_decision()['blocked']) {
        if (function_exists('psr_center_log_limited')) {
            $d=psr_geo_decision();
            psr_center_log_limited(psr_geo_deny_event($d),'critical',(string)$d['ip'],'',(string)$d['country'],'xmlrpc','XML-RPC estero respinto',60);
        }
        return false;
    }
    return $enabled;
}
add_filter('xmlrpc_enabled', 'psr_geo_xmlrpc_enabled', 0);

// Avviso privato, solo ID 1, se IP non affidabile o GeoIP non disponibile.
function psr_geo_admin_notice() {
    if (!psr_geo_enabled() || (int) get_current_user_id() !== 1 || !current_user_can('manage_options')) { return; }
    $d = psr_geo_decision();
    if ($d['reason'] !== 'geoip_unavailable' && $d['reason'] !== 'ip_not_public_or_proxy') { return; }
    echo '<div class="notice notice-warning"><p><strong>PSR Geo Login:</strong> impossibile determinare in modo affidabile il Paese dell\'IP (' . esc_html($d['reason']) . '). Il blocco geografico non si applica a questa connessione. Controlla GeoIP e REMOTE_ADDR in Plesk.</p></div>';
}
add_action('admin_notices', 'psr_geo_admin_notice', 2);

function psr_geo_tools_menu() {
    if ((int) get_current_user_id() !== 1 || !current_user_can('manage_options')) { return; }
    add_management_page('PSR Geo Login', 'PSR Geo Login', 'manage_options', 'psr-geo-login', 'psr_geo_tools_page');
}
add_action('admin_menu', 'psr_geo_tools_menu');
function psr_geo_tools_page() {
    if ((int) get_current_user_id() !== 1 || !current_user_can('manage_options')) {
        wp_die('Non autorizzato', '', array('response'=>403));
    }
    if (function_exists('psr_center_page')) { psr_center_page('geo'); return; }
    $d = psr_geo_decision();
    echo '<div class="wrap"><h1>PSR Geo Login — riservato ID 1</h1>';
    echo '<p>Stato filtro: <strong>' . (psr_geo_enabled() ? 'Attivo' : 'Disattivato da wp-config.php') . '</strong></p>';
    echo '<p>IP riconosciuto dal server: <code>' . esc_html($d['ip'] ?: '(non pubblico)') . '</code></p>';
    echo '<p>Paese rilevato: <strong>' . esc_html($d['country'] ?: 'non determinabile') . '</strong>. Origine: <code>' . esc_html($d['reason']) . '</code></p>';
    echo '<p>Il blocco e\' applicato soltanto quando viene riconosciuto un Paese diverso da IT. Se GeoIP non risponde o REMOTE_ADDR e\' un proxy privato, l\'accesso resta disponibile e viene mostrato un avviso.</p>';
    echo '<p>Per un filtro rigoroso, indipendente da WordPress, configura GeoIP/WAF sul server. Non usare X-Forwarded-For senza un proxy esplicitamente attendibile.</p></div>';
}
