<?php
/**
 * PSR WebApp - v3.1 ticket monouso, verifica automatica su WordPress ufficiale.
 * Nessuna chiave condivisa con ASP e nessun segreto pubblicato nel tema.
 * La verifica si basa sull'origine WordPress gia' registrata nel database AppComuni.
 */
if (!defined('ABSPATH')) { exit; }
if (!defined('DCI_WEBAPP_CAP')) { define('DCI_WEBAPP_CAP', 'dci_manage_webapp_mobile'); }
if (!defined('DCI_WEBAPP_OPT')) { define('DCI_WEBAPP_OPT', 'dci_webapp_mobile_url'); }

add_action('init', static function () {
    $role = get_role('administrator');
    if ($role && !$role->has_cap(DCI_WEBAPP_CAP)) { $role->add_cap(DCI_WEBAPP_CAP); }
}, 20);
add_action('admin_menu', static function () {
    add_menu_page('Gestione WebApp mobile', 'Gestione WebApp mobile', DCI_WEBAPP_CAP,
        'dci_webapp_mobile', 'dci_webapp_mobile_page', 'dashicons-smartphone', 58);
});

/** La destinazione e' un pannello ASP HTTPS con un unico parametro Ente. */
function psr_webapp_v3_url($url) {
    $url = trim((string)$url);
    if ($url === '' || strlen($url) > 2048 || preg_match('/[\x00-\x20\x7f]/', $url)) { return false; }
    $parts = wp_parse_url($url);
    if (!is_array($parts) || strtolower((string)($parts['scheme'] ?? '')) !== 'https'
        || empty($parts['host']) || isset($parts['user']) || isset($parts['pass'])
        || isset($parts['fragment']) || (isset($parts['port']) && (int)$parts['port'] !== 443)
        || !preg_match('~/appcomuni/pannello_admin\.asp$~iD', (string)($parts['path'] ?? ''))
        || !isset($parts['query'])) { return false; }
    $host=(string)$parts['host'];
    if (!preg_match('/\A(?=.{4,253}\z)[a-z0-9][a-z0-9.-]*\.[a-z]{2,24}\z/iD', $host)) { return false; }
    $query = explode('&', $parts['query']);
    if (count($query) !== 1) { return false; }
    $pair = explode('=', $query[0], 2);
    if (count($pair) !== 2 || strcasecmp(rawurldecode($pair[0]), 'Ente') !== 0) { return false; }
    $ente = rawurldecode(str_replace('+', ' ', $pair[1]));
    if (!preg_match('/\A[A-Za-z0-9_-]{1,64}\z/D', $ente)) { return false; }
    return array('url'=>$url, 'ente'=>$ente);
}

add_action('admin_init', static function () {
    register_setting('dci_webapp_mobile_group', DCI_WEBAPP_OPT, array(
        'type'=>'string',
        'sanitize_callback'=>static function ($url) {
            if ((int)get_current_user_id() !== 1 || !current_user_can('manage_options')) {
                return (string)get_option(DCI_WEBAPP_OPT, '');
            }
            $url=trim((string)$url);
            if ($url==='') { return ''; }
            $parsed=psr_webapp_v3_url($url);
            if (!$parsed) {
                add_settings_error(DCI_WEBAPP_OPT, 'invalid_url',
                    'URL WebApp non valido. Esempio: https://www.webapp.servizipa.cloud/appcomuni/pannello_admin.asp?Ente=codice', 'error');
                return (string)get_option(DCI_WEBAPP_OPT, '');
            }
            return esc_url_raw($parsed['url'], array('https'));
        },
        'default'=>'',
    ));
});

/**
 * Crea ticket a elevata entropia e ne salva SOLO l'hash sul WordPress dell'ente.
 * La scadenza e' controllata sul server emittente; un ticket non puo' essere riutilizzato.
 */
function psr_webapp_v3_issue_ticket($tenant) {
    for ($i=0; $i<3; $i++) {
        $ticket=bin2hex(random_bytes(32));
        $key='_psr_webapp_v3_' . hash('sha256', $ticket);
        $value=(string)(time()+90) . '|' . $tenant . '|' . (int)get_current_user_id();
        if (add_option($key, $value, '', false)) { return $ticket; }
    }
    return false;
}

/**
 * La richiesta viene dal server AppComuni verso il WordPress ufficiale nel DB Access.
 * Non accetta alcun URL del chiamante. L'eliminazione condizionata e' atomica su MySQL.
 */
function psr_webapp_v3_verify_ticket() {
    nocache_headers();
    header('Content-Type: text/plain; charset=utf-8');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: no-referrer');
    // Diagnostic code only: never include ticket, tenant, PIN or user identifiers.
    $deny=static function ($code='INVALID') {
        status_header(403);
        if (!headers_sent()) { header('X-PSR-Webapp-Deny: ' . $code); }
        echo 'DENIED';
        exit;
    };
    if (($_SERVER['REQUEST_METHOD']??'')!=='POST') { $deny('METHOD'); }
    $ticket=isset($_POST['ticket']) && is_string($_POST['ticket']) ? wp_unslash($_POST['ticket']) : '';
    $tenant=isset($_POST['ente']) && is_string($_POST['ente']) ? wp_unslash($_POST['ente']) : '';
    if (!preg_match('/\A[a-f0-9]{64}\z/D', $ticket)
        || !preg_match('/\A[A-Za-z0-9_-]{1,64}\z/D', $tenant)) { $deny('FORMAT'); }
    global $wpdb;
    $option='_psr_webapp_v3_' . hash('sha256', $ticket);
    // Bypass non-authoritative object caches: claim uses DB conditional DELETE.
    $stored=$wpdb->get_var($wpdb->prepare(
        "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1", $option));
    if (!is_string($stored)) { $deny('NOT_FOUND'); }
    // A ticket cannot outlive or change the administrator-configured tenant.
    $current_destination=psr_webapp_v3_url((string)get_option(DCI_WEBAPP_OPT, ''));
    if (!$current_destination || !hash_equals((string)$current_destination['ente'], $tenant)) { $deny('DESTINATION'); }
    $components=explode('|', $stored);
    if (count($components)!==3 || !ctype_digit($components[0])
        || (int)$components[0] < time() || (int)$components[0] > time()+90
        || !hash_equals((string)$components[1], $tenant) || (int)$components[2]<1
        || !user_can((int)$components[2], DCI_WEBAPP_CAP)) { $deny('EXPIRED_OR_RIGHTS'); }
    $deleted=$wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name = %s AND option_value = %s LIMIT 1",
        $option, $stored));
    if ($deleted!==1) { $deny('ALREADY_USED'); }
    wp_cache_delete($option, 'options');
    // Owner identity is derived server-side from the consumed ticket, never from URL/POST.
    // Restrict automatic PIN bypass to the site's original ID 1 administrator.
    $is_owner = ((int)$components[2] === 1) && user_can(1, 'manage_options');
    status_header(200);
    echo 'PSR-WEBAPP-V3-OK:' . $tenant . ($is_owner ? ':OWNER' : ':USER');
    exit;
}
add_action('wp_ajax_psr_webapp_v3_verify', 'psr_webapp_v3_verify_ticket');
add_action('wp_ajax_nopriv_psr_webapp_v3_verify', 'psr_webapp_v3_verify_ticket');

/** The REST endpoint is another transport for the SAME one-time ticket claim.
 * It never accepts a browser session as evidence or skips the SQL DELETE gate.
 * A missing/invalid ticket still returns HTTP 403; no anonymous authorization.
 */
add_action('rest_api_init', static function () {
    register_rest_route('psr-webapp/v3', '/verify', array(
        'methods' => 'POST',
        'permission_callback' => '__return_true',
        'callback' => static function () { psr_webapp_v3_verify_ticket(); },
    ));
});

/**
 * Percorso pubblico alternativo per la verifica server-to-server.
 * Alcuni hosting limitano POST anonimi a wp-admin/admin-ajax.php.
 * Non e' un bypass: usa LO STESSO verificatore con DELETE atomica del ticket.
 * Non concede accesso in assenza di un ticket valido e monouso.
 */
add_action('init', static function () {
    if (!isset($_GET['psr_webapp_verify']) || !is_string($_GET['psr_webapp_verify'])
        || $_GET['psr_webapp_verify'] !== '1') { return; }
    psr_webapp_v3_verify_ticket();
}, 20);


/** Eventual tickets never consumed are removed during the normal WP-Cron schedule. */
function psr_webapp_v3_cleanup() {
    global $wpdb;
    $like=$wpdb->esc_like('_psr_webapp_v3_') . '%';
    $wpdb->query($wpdb->prepare(
        "DELETE FROM {$wpdb->options} WHERE option_name LIKE %s AND "
       . "CAST(SUBSTRING_INDEX(option_value, '|', 1) AS UNSIGNED) < %d LIMIT 250",
        $like, time()));
}
add_action('psr_webapp_v3_cleanup', 'psr_webapp_v3_cleanup');
add_action('admin_init', static function () {
    if (current_user_can(DCI_WEBAPP_CAP) && !wp_next_scheduled('psr_webapp_v3_cleanup')) {
        wp_schedule_event(time()+HOUR_IN_SECONDS, 'daily', 'psr_webapp_v3_cleanup');
    }
});

add_action('admin_post_dci_webapp_open', static function () {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        wp_die('Metodo non consentito.', 'Gestione WebApp', array('response'=>405));
    }
    if (!current_user_can(DCI_WEBAPP_CAP)) {
        wp_die('Non autorizzato.', 'Gestione WebApp', array('response'=>403));
    }
    check_admin_referer('dci_webapp_open_v3', 'dci_webapp_nonce');
    $url=psr_webapp_v3_url((string)get_option(DCI_WEBAPP_OPT, ''));
    if (!$url) {
        wp_die('Indirizzo WebApp non configurato o non sicuro.', 'Gestione WebApp', array('response'=>400));
    }
    $ticket=psr_webapp_v3_issue_ticket($url['ente']);
    if (!$ticket) { wp_die('Impossibile creare il ticket di accesso.', 'Gestione WebApp', array('response'=>503)); }
    $target=add_query_arg(array('v'=>'3','ticket'=>$ticket), $url['url']);
    nocache_headers();
    header('Referrer-Policy: no-referrer');
    wp_redirect($target, 303, 'PSR WebApp');
    exit;
});

function dci_webapp_mobile_page() {
    if (!current_user_can(DCI_WEBAPP_CAP)) {
        wp_die('Non hai i permessi per accedere a questa pagina.', 'Gestione WebApp', array('response'=>403));
    }
    $url=(string)get_option(DCI_WEBAPP_OPT, '');
    $valid=psr_webapp_v3_url($url);
    $can_edit=(int)get_current_user_id()===1 && current_user_can('manage_options');
    ?>
    <div class="wrap">
      <h1>Gestione WebApp mobile</h1>
      <p>Collegamento sicuro automatico: nessuna chiave da configurare e ticket monouso valido 90 secondi.
         AppComuni verifica il ticket direttamente con il WordPress ufficiale dell'ente.</p>
      <?php if ($can_edit): ?>
        <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
          <?php settings_fields('dci_webapp_mobile_group'); ?>
          <table class="form-table" role="presentation"><tr>
            <th><label for="psr_webapp_url">URL WebApp (HTTPS)</label></th>
            <td>
              <input type="url" class="regular-text" id="psr_webapp_url"
                     name="<?php echo esc_attr(DCI_WEBAPP_OPT); ?>" value="<?php echo esc_attr($url); ?>"
                     placeholder="https://www.webapp.servizipa.cloud/appcomuni/pannello_admin.asp?Ente=codice">
              <p class="description">Solo amministratore ID 1. L'indirizzo del WordPress ufficiale deve essere gia' presente nel database AppComuni (URL API Notizie).</p>
              <?php submit_button('Salva indirizzo'); ?>
            </td>
          </tr></table>
        </form>
      <?php elseif ($url!==''): ?>
        <p>Destinazione configurata dall'amministratore: <code><?php echo esc_html($url); ?></code></p>
      <?php endif; ?>
      <?php if ($valid): ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
              target="_blank" rel="noopener noreferrer" style="display:inline-block;margin-top:12px">
          <?php wp_nonce_field('dci_webapp_open_v3','dci_webapp_nonce'); ?>
          <input type="hidden" name="action" value="dci_webapp_open">
          <button type="submit" class="button button-primary button-hero">Apri pannello WebApp protetto</button>
        </form>
      <?php else: ?>
        <div class="notice notice-warning"><p>Inserisci un indirizzo WebApp HTTPS valido per abilitare l'accesso.</p></div>
      <?php endif; ?>
    </div>
    <?php
}
