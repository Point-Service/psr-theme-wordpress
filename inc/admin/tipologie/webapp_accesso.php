<?php
/**
 * PSR Gestione WebApp - authentication v2 (WordPress issuer).
 * This file replaces inc/admin/tipologie/webapp_accesso.php.
 * IMPORTANT: activate with the matching AppComuni ASP package and PSR_WEBAPP_HMAC_KEY in wp-config.php.
 */
if (!defined('ABSPATH')) { exit; }
if (!defined('DCI_WEBAPP_CAP')) { define('DCI_WEBAPP_CAP', 'dci_manage_webapp_mobile'); }
if (!defined('DCI_WEBAPP_OPT')) { define('DCI_WEBAPP_OPT', 'dci_webapp_mobile_url'); }

/** The administrator capability is preserved, without database writes on the frontend. */
add_action('init', static function () {
    $role = get_role('administrator');
    if ($role && !$role->has_cap(DCI_WEBAPP_CAP)) {
        $role->add_cap(DCI_WEBAPP_CAP);
    }
}, 20);

add_action('admin_menu', static function () {
    add_menu_page('Gestione WebApp mobile', 'Gestione WebApp mobile', DCI_WEBAPP_CAP,
        'dci_webapp_mobile', 'dci_webapp_mobile_page', 'dashicons-smartphone', 58);
});

/** Validates both old saved URLs and new settings, never contacts the destination. */
function psr_webapp_url_parts($raw) {
    $raw = trim((string) $raw);
    if ($raw === '' || strlen($raw) > 2048 || preg_match('/[\x00-\x20\x7F]/', $raw)) { return false; }
    $url = wp_parse_url($raw);
    if (!is_array($url) || strtolower((string) ($url['scheme'] ?? '')) !== 'https'
        || empty($url['host']) || isset($url['user']) || isset($url['pass'])
        || isset($url['fragment']) || (isset($url['port']) && (int) $url['port'] !== 443)
        || !preg_match('~/pannello_admin\.asp$~i', (string) ($url['path'] ?? ''))
        || empty($url['query'])) { return false; }
    // Do not let pre-existing or duplicated query keys change the signed parameters.
    $pairs = explode('&', $url['query']);
    if (count($pairs) !== 1) { return false; }
    $pieces = explode('=', $pairs[0], 2);
    if (count($pieces) !== 2 || strcasecmp(rawurldecode($pieces[0]), 'Ente') !== 0) { return false; }
    $ente = rawurldecode(str_replace('+', ' ', $pieces[1]));
    if (!preg_match('/\A[A-Za-z0-9_-]{1,64}\z/D', $ente)) { return false; }
    return array('url' => $raw, 'ente' => $ente);
}

add_action('admin_init', static function () {
    register_setting('dci_webapp_mobile_group', DCI_WEBAPP_OPT, array(
        'type' => 'string',
        'sanitize_callback' => static function ($value) {
            // Preserve the original user ID 1 rule; check server-side, not only in UI.
            if ((int) get_current_user_id() !== 1 || !current_user_can('manage_options')) {
                return (string) get_option(DCI_WEBAPP_OPT, '');
            }
            $value = trim((string) $value);
            if ($value === '') { return ''; }
            $parts = psr_webapp_url_parts($value);
            if (!$parts) {
                add_settings_error(DCI_WEBAPP_OPT, 'psr_webapp_bad_url',
                    'URL non valido: usa HTTPS e un link a pannello_admin.asp?Ente=codice.', 'error');
                return (string) get_option(DCI_WEBAPP_OPT, '');
            }
            return esc_url_raw($parts['url'], array('https'));
        },
        'default' => '',
    ));
});

/** Signed handoff: v2 + Unix timestamp + 128-bit random nonce + tenant. */
add_action('admin_post_dci_webapp_open', static function () {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        wp_die('Metodo non consentito.', 'Gestione WebApp', array('response' => 405));
    }
    if (!current_user_can(DCI_WEBAPP_CAP)) {
        wp_die('Non autorizzato.', 'Gestione WebApp', array('response' => 403));
    }
    check_admin_referer('dci_webapp_open_v2', 'dci_webapp_nonce');
    if (!defined('PSR_WEBAPP_HMAC_KEY') || !is_string(PSR_WEBAPP_HMAC_KEY)
        || !preg_match('/\A[a-f0-9]{64}\z/D', PSR_WEBAPP_HMAC_KEY)) {
        wp_die('Configurazione sicurezza WebApp mancante. Contattare l’amministratore.',
            'Gestione WebApp', array('response' => 503));
    }
    $parts = psr_webapp_url_parts((string) get_option(DCI_WEBAPP_OPT, ''));
    if (!$parts) {
        wp_die('Indirizzo WebApp non configurato o non sicuro.',
            'Gestione WebApp', array('response' => 400));
    }
    $ts = (string) time();
    $n = bin2hex(random_bytes(16));
    $ente = $parts['ente'];
    $payload = "v2\n" . $ts . "\n" . $n . "\n" . $ente;
    $sig = hash_hmac('sha256', $payload, PSR_WEBAPP_HMAC_KEY);
    // Escape preserved query; do not use wp_safe_redirect (destination is another domain).
    $target = add_query_arg(array('v' => '2', 'ts' => $ts, 'n' => $n, 'sig' => $sig), $parts['url']);
    nocache_headers();
    if (!headers_sent()) { header('Referrer-Policy: no-referrer'); }
    wp_redirect($target, 303, 'PSR WebApp');
    exit;
});

function dci_webapp_mobile_page() {
    if (!current_user_can(DCI_WEBAPP_CAP)) {
        wp_die('Non hai i permessi per accedere a questa pagina.', 'Gestione WebApp', array('response' => 403));
    }
    $url = (string) get_option(DCI_WEBAPP_OPT, '');
    $can_edit = (int) get_current_user_id() === 1 && current_user_can('manage_options');
    $ready = defined('PSR_WEBAPP_HMAC_KEY') && is_string(PSR_WEBAPP_HMAC_KEY)
        && (bool) preg_match('/\A[a-f0-9]{64}\z/D', PSR_WEBAPP_HMAC_KEY);
    $valid_url = psr_webapp_url_parts($url);
    ?>
    <div class="wrap">
        <h1>Gestione WebApp mobile</h1>
        <p>Accesso protetto al pannello WebApp dell'ente. Il collegamento utilizza un token firmato,
            temporaneo e valido una sola volta.</p>
        <?php if (!$ready) : ?>
            <div class="notice notice-error"><p><strong>Accesso non disponibile:</strong>
                configurare PSR_WEBAPP_HMAC_KEY in wp-config.php e aggiornare AppComuni.</p></div>
        <?php endif; ?>
        <?php if ($can_edit) : ?>
            <form method="post" action="<?php echo esc_url(admin_url('options.php')); ?>">
                <?php settings_fields('dci_webapp_mobile_group'); ?>
                <table class="form-table" role="presentation"><tr>
                    <th scope="row"><label for="psr_webapp_url">URL WebApp (HTTPS)</label></th>
                    <td><input type="url" id="psr_webapp_url" name="<?php echo esc_attr(DCI_WEBAPP_OPT); ?>"
                        class="regular-text" value="<?php echo esc_attr($url); ?>"
                        placeholder="https://assistenza.example.it/appcomuni/pannello_admin.asp?Ente=codice">
                        <p class="description">Modificabile solo dall'amministratore ID 1. Il codice ente deve essere composto da lettere, numeri, trattini o underscore.</p>
                        <?php submit_button('Salva indirizzo'); ?>
                    </td></tr></table>
            </form>
        <?php else : ?>
            <p>La destinazione è gestita dall'amministratore principale.</p>
            <?php if ($url !== '') : ?><p><code><?php echo esc_html($url); ?></code></p><?php endif; ?>
        <?php endif; ?>
        <?php if ($ready && $valid_url) : ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>"
                  target="_blank" rel="noopener noreferrer" style="display:inline-block;margin-top:12px">
                <?php wp_nonce_field('dci_webapp_open_v2', 'dci_webapp_nonce'); ?>
                <input type="hidden" name="action" value="dci_webapp_open">
                <button type="submit" class="button button-primary button-hero">Apri pannello WebApp protetto</button>
            </form>
        <?php endif; ?>
    </div>
    <?php
}
