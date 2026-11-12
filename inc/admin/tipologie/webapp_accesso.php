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
    // Exact transport-level proof from the same HTTPS origin, after one-time
    // atomic claim. ASP validates the entire value, never substrings.
    $grant='PSR-WEBAPP-V3-OK:' . $tenant . ($is_owner ? ':OWNER' : ':USER');
    status_header(200);
    if (!headers_sent()) { header('X-PSR-Webapp-Grant: ' . $grant); }
    echo $grant;
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
    $site_name=(string)get_bloginfo('name');
    ?>
    <div class="wrap psr-wa">
      <style>
      .psr-wa{--wa-deep:#142f4f;--wa-ink:#193551;--wa-muted:#5d7086;--wa-border:#dce6f0;--wa-blue:#285cc7;--wa-bg:#f1f6fc;color:var(--wa-ink);max-width:1160px;margin:24px 24px 40px 0;font-family:inherit}
      .psr-wa *{box-sizing:border-box}
      .psr-wa .wa-hero{position:relative;overflow:hidden;border-radius:20px;padding:32px 36px;background:linear-gradient(116deg,#193858 0%,#284d76 66%,#305b87 100%);color:#fff;box-shadow:0 9px 28px rgba(28,62,103,.13)}
      .psr-wa .wa-hero:after{content:"";position:absolute;right:-45px;top:-85px;width:285px;height:285px;border:1px solid rgba(255,255,255,.14);border-radius:50%;box-shadow:0 0 0 45px rgba(255,255,255,.035),0 0 0 92px rgba(255,255,255,.025);pointer-events:none}
      .psr-wa .wa-eyebrow{display:flex;align-items:center;gap:9px;font-size:12px;font-weight:750;letter-spacing:1.25px;text-transform:uppercase;color:#c9e6ff}
      .psr-wa .wa-hero h1{color:#fff;font-size:30px;font-weight:760;line-height:1.24;margin:14px 0 10px;padding:0}
      .psr-wa .wa-hero p{font-size:14px;line-height:1.65;color:#dbebf7;max-width:620px;margin:0}
      .psr-wa .wa-stamps{display:flex;align-items:center;gap:8px;flex-wrap:wrap;margin-top:20px}
      .psr-wa .wa-stamp{border:1px solid rgba(255,255,255,.2);color:#e4f0fc;padding:7px 12px;border-radius:99px;background:rgba(255,255,255,.075);font-size:12px;font-weight:600}
      .psr-wa .wa-grid{display:grid;grid-template-columns:minmax(0,1.4fr) minmax(275px,.8fr);gap:18px;margin-top:20px}
      .psr-wa .wa-card{border:1px solid var(--wa-border);border-radius:18px;background:#fff;padding:26px 28px;box-shadow:0 5px 18px rgba(26,60,92,.045);min-width:0}
      .psr-wa .wa-card-head{display:flex;gap:13px;align-items:center;margin-bottom:15px}
      .psr-wa .wa-icon{flex:0 0 44px;width:44px;height:44px;border-radius:13px;display:flex;align-items:center;justify-content:center;background:#e9f1ff;color:#285cc7}
      .psr-wa .wa-icon .dashicons{font-size:23px;width:23px;height:23px}
      .psr-wa h2{font-size:18px;font-weight:760;margin:0;line-height:1.35;color:#163653}
      .psr-wa .wa-card p{margin:7px 0 0;line-height:1.65;font-size:13px;color:var(--wa-muted)}
      .psr-wa .wa-access-status{padding:11px 13px;border-radius:10px;display:flex;gap:9px;align-items:flex-start;color:#216148;background:#e7f6ed;border:1px solid #c5e8d4;font-weight:650;font-size:12px;margin:21px 0}
      .psr-wa .wa-access-status .dashicons{flex:0 0 19px}
      .psr-wa .wa-open{display:inline-flex;align-items:center;justify-content:center;gap:10px;min-height:48px;padding:0 24px!important;font-size:14px!important;font-weight:700!important;line-height:1.2!important;border-radius:11px!important;background:#285cc7!important;border:1px solid #285cc7!important;color:#fff!important;box-shadow:0 5px 12px rgba(40,92,199,.17);transition:transform .15s,background .15s;text-decoration:none}
      .psr-wa .wa-open:hover{background:#1e4ea9!important;transform:translateY(-1px)}
      .psr-wa .wa-open:focus{outline:2px solid #204a9d;outline-offset:3px}
      .psr-wa .wa-open .dashicons{margin:0;font-size:19px;height:19px;width:19px}
      .psr-wa .wa-hint{font-size:12px!important;margin-top:11px!important;color:#708398!important}
      .psr-wa .wa-row{display:flex;gap:10px;align-items:center;font-size:13px;line-height:1.5;padding:11px 0;border-bottom:1px solid #eaf0f5;color:#3a5068}
      .psr-wa .wa-row:last-child{border-bottom:0}
      .psr-wa .wa-row .dashicons{color:#5087a8}
      .psr-wa .wa-setting{margin-top:20px}
      .psr-wa .wa-setting label{display:block;font-size:12px;font-weight:750;color:#26435f;margin:0 0 8px}
      .psr-wa .wa-setting input[type=url]{display:block;width:100%;max-width:100%;border:1px solid #c9d6e3;border-radius:10px;padding:12px;min-height:44px;font-size:13px;box-shadow:none}
      .psr-wa .wa-setting input[type=url]:focus{border-color:#5285d6;box-shadow:0 0 0 3px rgba(80,133,214,.12)}
      .psr-wa .wa-setting .button{border-radius:9px;padding:6px 16px;min-height:36px;margin:14px 0 0;font-weight:650}
      .psr-wa .wa-footnote{margin-top:13px!important;font-size:12px!important}
      .psr-wa .wa-url-read{overflow-wrap:anywhere;background:#f2f6fa;border:1px solid #e1eaf1;border-radius:10px;padding:12px;font-size:12px;color:#4a6076;margin-top:16px}
      .psr-wa .wa-note{margin-top:18px;padding:14px 17px;background:#edf4fd;border-radius:11px;border-left:3px solid #73a0d8;color:#425c78;font-size:12px;line-height:1.55}
      .psr-wa .wa-warning{margin:17px 0 0;border:1px solid #f0d9b5;background:#fff8ed;color:#785520;border-radius:10px;padding:12px 14px;font-size:13px}
      .psr-wa .wa-meta{margin-top:16px;color:#5d758d;font-size:12px;display:flex;gap:7px;align-items:center}
      @media(max-width:960px){.psr-wa .wa-grid{grid-template-columns:1fr}}
      @media(max-width:600px){.psr-wa{margin:12px 12px 25px 0}.psr-wa .wa-hero{padding:25px 22px;border-radius:14px}.psr-wa .wa-hero h1{font-size:24px}.psr-wa .wa-card{padding:22px 19px;border-radius:14px}.psr-wa .wa-open{width:100%}}
      @media(prefers-reduced-motion:reduce){.psr-wa .wa-open{transition:none}}
      </style>
      <section class="wa-hero" aria-label="Gestione WebApp comunale">
        <div class="wa-eyebrow"><span class="dashicons dashicons-shield-alt" aria-hidden="true"></span> Servizi digitali comunali</div>
        <h1>Gestione WebApp mobile</h1>
        <p>Accedi al pannello dedicato alla WebApp del Comune, in modo protetto e senza credenziali aggiuntive.</p>
        <div class="wa-stamps"><span class="wa-stamp">Accesso WordPress verificato</span><span class="wa-stamp">Ticket temporaneo monouso</span><span class="wa-stamp"><?php echo $can_edit ? 'Amministratore' : 'Consultazione'; ?></span></div>
      </section>
      <div class="wa-grid">
        <section class="wa-card" aria-labelledby="wa-access-title">
          <div class="wa-card-head"><div class="wa-icon"><span class="dashicons dashicons-external" aria-hidden="true"></span></div><div><h2 id="wa-access-title">Accesso al pannello</h2><p><?php echo esc_html($site_name); ?></p></div></div>
          <p><?php echo $can_edit ? 'Puoi consultare e amministrare le impostazioni della WebApp.' : 'Puoi consultare tutte le sezioni. Le modifiche sono riservate all’amministratore principale.'; ?></p>
          <div class="wa-access-status"><span class="dashicons dashicons-yes-alt" aria-hidden="true"></span><span>Abilitazione WordPress presente. Nessun PIN aggiuntivo richiesto.</span></div>
          <?php if ($valid): ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" target="_blank" rel="noopener noreferrer">
              <?php wp_nonce_field('dci_webapp_open_v3','dci_webapp_nonce'); ?>
              <input type="hidden" name="action" value="dci_webapp_open">
              <button type="submit" class="button button-primary wa-open"><span class="dashicons dashicons-external" aria-hidden="true"></span>Apri pannello WebApp</button>
            </form>
            <p class="wa-hint">Il pannello si apre in una nuova scheda. Il collegamento è valido una sola volta.</p>
          <?php else: ?>
            <div class="wa-warning">Indirizzo WebApp non configurato o non valido. L’amministratore ID 1 deve impostare un indirizzo HTTPS corretto.</div>
          <?php endif; ?>
          <div class="wa-meta"><span class="dashicons dashicons-lock" aria-hidden="true"></span>Sessione WebApp separata e con durata limitata.</div>
        </section>
        <section class="wa-card" aria-labelledby="wa-right-title">
          <div class="wa-card-head"><div class="wa-icon"><span class="dashicons dashicons-admin-generic" aria-hidden="true"></span></div>
            <div><h2 id="wa-right-title"><?php echo $can_edit ? 'Configurazione collegamento' : 'Permessi di accesso'; ?></h2><p><?php echo $can_edit ? 'Riservata all’amministratore WordPress ID 1' : 'Il tuo profilo è autorizzato alla consultazione'; ?></p></div>
          </div>
          <?php if ($can_edit): ?>
            <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>" class="wa-setting">
              <?php settings_fields('dci_webapp_mobile_group'); ?>
              <label for="psr_webapp_url">URL sicuro del pannello (HTTPS)</label>
              <input type="url" id="psr_webapp_url" name="<?php echo esc_attr(DCI_WEBAPP_OPT); ?>" value="<?php echo esc_attr($url); ?>" required placeholder="https://www.webapp.servizipa.cloud/appcomuni/pannello_admin.asp?Ente=codice">
              <?php submit_button('Salva collegamento', 'secondary', 'submit', false); ?>
            </form>
            <p class="wa-footnote">L’indirizzo è modificabile soltanto dall’utente ID 1. Non cambiare URL se l’integrazione è già funzionante.</p>
          <?php else: ?>
            <div class="wa-row"><span class="dashicons dashicons-yes" aria-hidden="true"></span>Permesso di accesso attivo</div>
            <div class="wa-row"><span class="dashicons dashicons-visibility" aria-hidden="true"></span>Consultazione delle informazioni</div>
            <div class="wa-row"><span class="dashicons dashicons-lock" aria-hidden="true"></span>Salvataggi e modifiche non consentiti</div>
            <?php if ($valid): ?><div class="wa-url-read"><?php echo esc_html($url); ?></div><?php endif; ?>
          <?php endif; ?>
        </section>
      </div>
      <p class="wa-note">La verifica dell’identità avviene su WordPress. AppComuni accetta soltanto ticket temporanei, associati all’ente e utilizzabili una sola volta. Non è necessario configurare un PIN.</p>
    </div>
    <?php
}
