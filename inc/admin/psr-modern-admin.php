<?php
/**
 * PSR – menu e barra amministrativa opzionali.
 * Nessuna modifica alle capability, ai link o ai menu registrati da WordPress.
 */
if (!defined('ABSPATH')) { exit; }

function psr_modern_admin_is_owner() {
    return (int) get_current_user_id() === 1 && current_user_can('manage_options');
}
function psr_modern_admin_enabled() {
    return !(defined('PSR_ADMIN_CLASSIC_UI') && PSR_ADMIN_CLASSIC_UI)
        && get_option('psr_modern_admin_ui', '1') === '1';
}
function psr_modern_admin_content_enabled() {
    return psr_modern_admin_enabled() && get_option('psr_modern_admin_content_ui', '1') === '1';
}
function psr_modern_admin_config_screen() {
    return is_admin() && !is_network_admin() && !is_user_admin() && !wp_doing_ajax()
        && isset($_GET['page']) && $_GET['page'] === 'dci_options';
}
function psr_modern_admin_forced_flags() {
    return array(
        defined('PSR_ADMIN_CLASSIC_UI') && PSR_ADMIN_CLASSIC_UI,
        defined('PSR_HOME_TOOLS_DISABLED') && PSR_HOME_TOOLS_DISABLED,
    );
}
function psr_modern_admin_save() {
    if (!psr_modern_admin_config_screen() || !psr_modern_admin_is_owner()
        || !isset($_POST['psr_modern_admin_save'])) { return; }
    check_admin_referer('psr_modern_admin_save', 'psr_modern_admin_nonce');
    // Ignora anche i vecchi form a tendina rimasti aperti durante un aggiornamento.
    $fields = array('psr_modern_admin_value', 'psr_home_tools_enabled', 'psr_modern_admin_content_value');
    $valid = isset($_POST['psr_modern_admin_checks']) && $_POST['psr_modern_admin_checks'] === '2';
    foreach ($fields as $field) {
        if (isset($_POST[$field]) && $_POST[$field] !== '1') { $valid = false; }
    }
    if ($valid) {
        $forced = psr_modern_admin_forced_flags();
        if (!$forced[0]) {
            update_option('psr_modern_admin_ui', isset($_POST[$fields[0]]) ? '1' : '0', false);
            update_option('psr_modern_admin_content_ui', isset($_POST[$fields[2]]) ? '1' : '0', false);
        }
        if (!$forced[1]) {
            update_option('psr_home_contextual_tools', isset($_POST[$fields[1]]) ? '1' : '0', false);
        }
    }
    wp_safe_redirect(admin_url('admin.php?page=dci_options'));
    exit;
}
add_action('admin_init', 'psr_modern_admin_save', 90);

function psr_modern_admin_setting() {
    if (!psr_modern_admin_config_screen() || !psr_modern_admin_is_owner()) { return; }
    $forced = psr_modern_admin_forced_flags();
    $modern = psr_modern_admin_enabled();
    $contextual = !$forced[1] && get_option('psr_home_contextual_tools', '1') === '1';
    echo '<div class="notice notice-info" style="padding:12px 16px;margin-top:16px">';
    echo '<p><strong>Interfaccia amministrativa PSR avanzata</strong></p>';
    echo '<form method="post" action="' . esc_url(admin_url('admin.php?page=dci_options')) . '">';
    wp_nonce_field('psr_modern_admin_save', 'psr_modern_admin_nonce');
    echo '<input type="hidden" name="psr_modern_admin_save" value="1">';
    echo '<input type="hidden" name="psr_modern_admin_checks" value="2">';
    echo '<p><label><input type="checkbox" name="psr_modern_admin_value" value="1"'
        . checked($modern, true, false) . disabled($forced[0], true, false)
        . '> Abilita menu amministrativo moderno</label></p>';
    echo '<p class="description" style="margin-left:24px">Rinnova il menu laterale e la barra superiore di WordPress, organizzando le voci in sezioni richiudibili e aggiungendo la ricerca rapida.</p>';
    echo '<p><label><input type="checkbox" name="psr_home_tools_enabled" value="1"'
        . checked($contextual, true, false) . disabled($forced[1], true, false)
        . '> Abilita gestione contestuale dei contenuti</label></p>';
    echo '<p class="description" style="margin-left:24px">Mostra nella home e nelle sezioni interne del sito i comandi rapidi per aggiungere, modificare e configurare i contenuti, aprendo le relative schermate di WordPress in base ai permessi dell’operatore.</p>';
    echo '<p><label><input type="checkbox" name="psr_modern_admin_content_value" value="1"'
        . checked(!$forced[0] && get_option('psr_modern_admin_content_ui', '1') === '1', true, false) . disabled($forced[0], true, false)
        . '> Abilita grafica moderna delle schermate interne</label></p>';
    echo '<p class="description" style="margin-left:24px">Rinnova tabelle, filtri, pulsanti e pannelli di configurazione senza cambiare la disposizione delle schermate. Richiede il menu amministrativo moderno.</p>';
    if ($forced[0]) { echo '<p>Stile classico imposto dalla configurazione di emergenza.</p>'; }
    if ($forced[1]) { echo '<p>Gestione contestuale disabilitata dalla configurazione di emergenza.</p>'; }
    if (!$forced[0] || !$forced[1]) { submit_button('Salva impostazioni', 'secondary', 'psr_modern_admin_submit', false); }
    echo '</form></div>';
}
add_action('admin_notices', 'psr_modern_admin_setting');

function psr_modern_admin_body_class($classes) {
    return $classes . ' psr-modern-admin ';
}

function psr_modern_admin_enqueue() {
    if (!psr_modern_admin_enabled() || is_network_admin() || is_user_admin()
        || (defined('IFRAME_REQUEST') && IFRAME_REQUEST)) { return; }
    // Classe presente nell'HTML iniziale: larghezza, colori e toolbar dal primo rendering.
    add_filter('admin_body_class', 'psr_modern_admin_body_class');
    wp_register_style('psr-modern-admin', false, array(), '1.0.16');
    wp_enqueue_style('psr-modern-admin');
    wp_add_inline_style('psr-modern-admin', <<<'CSS'
body.psr-modern-admin {
    --psr-nav: #10243c;
    --psr-deep: #0c1e33;
    --psr-hover: #233d59;
    --psr-active: #245c9f;
    --psr-muted: #b7c9dc;
    --psr-width: 220px;
}
body.psr-modern-admin #adminmenuback,
body.psr-modern-admin #adminmenuwrap,
body.psr-modern-admin #adminmenu { background: var(--psr-nav); }
body.psr-modern-admin #adminmenu a { color: #f1f5fa; }
body.psr-modern-admin #adminmenu div.wp-menu-image:before { color: #b7d1ed; }
body.psr-modern-admin #adminmenu li.menu-top:hover,
body.psr-modern-admin #adminmenu li.opensub > a.menu-top,
body.psr-modern-admin #adminmenu li > a.menu-top:focus { background: var(--psr-hover); color: #fff; }
body.psr-modern-admin #adminmenu li.current > a.menu-top,
body.psr-modern-admin #adminmenu li.wp-has-current-submenu > a.wp-has-current-submenu { background: var(--psr-active); color: #fff; }
body.psr-modern-admin #adminmenu .wp-has-current-submenu div.wp-menu-image:before,
body.psr-modern-admin #adminmenu li.current div.wp-menu-image:before { color: #fff; }
body.psr-modern-admin #adminmenu .wp-submenu,
body.psr-modern-admin #adminmenu .wp-has-current-submenu .wp-submenu { background: var(--psr-deep); }
body.psr-modern-admin #adminmenu .wp-submenu a { color: #d7e4f2; }
body.psr-modern-admin #adminmenu .wp-submenu a:hover,
body.psr-modern-admin #adminmenu .wp-submenu a:focus,
body.psr-modern-admin #adminmenu .wp-submenu li.current a { color: #fff; }
body.psr-modern-admin #adminmenu #collapse-button { color: var(--psr-muted); }
body.psr-modern-admin #adminmenu a:focus-visible,
body.psr-modern-admin #adminmenu button:focus-visible,
body.psr-modern-admin #adminmenu input:focus-visible,
body.psr-modern-admin #wpadminbar a:focus-visible { outline: 2px solid #e0b860; outline-offset: -2px; }
body.psr-modern-admin #wpadminbar { background: var(--psr-deep); box-shadow: 0 2px 8px rgba(8,24,44,.20); }
body.psr-modern-admin #wpadminbar .ab-item,
body.psr-modern-admin #wpadminbar a.ab-item,
body.psr-modern-admin #wpadminbar > #wp-toolbar span.ab-label,
body.psr-modern-admin #wpadminbar .ab-icon:before,
body.psr-modern-admin #wpadminbar .ab-item:before { color: #e0eaf5; }
body.psr-modern-admin #wpadminbar .ab-top-menu > li.hover > .ab-item,
body.psr-modern-admin #wpadminbar .ab-top-menu > li:hover > .ab-item,
body.psr-modern-admin #wpadminbar .ab-top-menu > li > .ab-item:focus { background: var(--psr-hover); color: #fff; }
body.psr-modern-admin #wpadminbar .ab-sub-wrapper,
body.psr-modern-admin #wpadminbar .ab-submenu { background: var(--psr-nav); }
body.psr-modern-admin #wpadminbar .ab-submenu .ab-item:hover,
body.psr-modern-admin #wpadminbar .ab-submenu .ab-item:focus { color: #fff; background: var(--psr-hover); }
body.psr-modern-admin #wpadminbar #wp-admin-bar-my-account .avatar { border-radius: 50%; border-color: #9db8d3; }
body.psr-modern-admin #wpadminbar #wp-admin-bar-design-comuni > .ab-item { font-weight: 600; }
#adminmenu .psr-nav-tools, #adminmenu .psr-nav-heading { display: none; }
@media (min-width: 961px) {
    /* Solo mentre il DOM del menu viene preparato; mai il contenuto o la toolbar. */
    html.psr-nav-pending body.psr-modern-admin:not(.folded) #adminmenu {
        visibility: hidden;
    }
    body.psr-modern-admin:not(.folded) #adminmenuback,
    body.psr-modern-admin:not(.folded) #adminmenuwrap,
    body.psr-modern-admin:not(.folded) #adminmenu { width: var(--psr-width); }
    body.psr-modern-admin:not(.folded) #wpcontent,
    body.psr-modern-admin:not(.folded) #wpfooter { margin-left: var(--psr-width); }
    body.psr-modern-admin:not(.folded) #adminmenu .wp-submenu { left: var(--psr-width); }
    body.psr-modern-admin:not(.folded) #adminmenu .wp-has-current-submenu .wp-submenu {
        left: auto; width: auto; min-width: 0;
    }
    body.psr-modern-admin:not(.folded) #adminmenu > li.menu-top > a.menu-top { min-height: 36px; border-radius: 6px; margin: 2px 6px; }
    body.psr-modern-admin:not(.folded) #adminmenu > li.menu-top > a.menu-top .wp-menu-name {
        padding-top: 10px;
        padding-bottom: 10px;
        font-size: 14px;
        font-weight: 600;
        line-height: 1.35;
        overflow-wrap: anywhere;
    }
    body.psr-modern-admin:not(.folded) #adminmenu .wp-submenu a {
        font-size: 13px;
        font-weight: 500;
        line-height: 1.4;
    }
    body.psr-modern-admin:not(.folded) #adminmenu .wp-submenu li.current a {
        font-weight: 600;
    }
    body.psr-modern-admin:not(.folded) #adminmenu .wp-menu-image { padding-top: 2px; }
    body.psr-modern-admin:not(.folded) #adminmenu .wp-has-current-submenu > a:after,
    body.psr-modern-admin:not(.folded) #adminmenu li.current > a:after { display: none; }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-tools,
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-heading { display: block; }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-tools { padding: 8px 12px 10px; }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-brand { display: block; color: #fff; font-size: 15px; font-weight: 650; line-height: 1.4; overflow-wrap: anywhere; }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-caption { display: block; color: var(--psr-muted); font-size: 11px; margin: 2px 0 12px; }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-search { box-sizing: border-box; width: 100%; min-height: 34px; background: #203851; color: #fff; border: 1px solid #607c99; border-radius: 6px; padding: 0 8px; font-size: 12px; }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-search::placeholder { color: #d1dfed; }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-heading { padding: 9px 10px 2px; }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-group { display: flex; align-items: center; justify-content: space-between; width: 100%; border: 0; border-radius: 4px; background: transparent; color: #e0eaf5; padding: 7px 3px; gap: 6px; cursor: pointer; font: 700 13px/1.4 system-ui,sans-serif; text-transform: uppercase; letter-spacing: .025em; text-align: left; overflow-wrap: anywhere; }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-group:hover { color: #fff; background: var(--psr-hover); }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-group:after { content: '\203A'; font-size: 19px; line-height: 1; font-weight: 700; flex-shrink: 0; }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-group[aria-expanded="true"]:after { transform: rotate(90deg); }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-hidden { display: none; }
    body.psr-modern-admin:not(.folded) #wpadminbar .ab-top-menu > li > .ab-item { padding-left: 9px; padding-right: 9px; }
}
CSS
    );
    if (psr_modern_admin_content_enabled()) {
        // Etichette di fallback CMB2 in italiano; non sovrascrive le traduzioni
        // esistenti ne' le etichette personalizzate dei singoli campi.
        add_filter('gettext_cmb2', static function ($translated, $text) {
            if ($translated !== $text || !in_array($text, array('Add or Upload File', 'Add or Upload Files'), true)) {
                return $translated;
            }
            $locale = get_user_locale();
            if ($locale !== 'it' && strpos($locale, 'it_') !== 0) {
                return $translated;
            }
            return $text === 'Add or Upload File' ? 'Carica o seleziona file' : 'Carica o seleziona piu\' file';
        }, 10, 2);
        wp_add_inline_style('psr-modern-admin', <<<'CSS'
/* PSR: rifinitura schermate interne, solo skin moderna, senza cambiare layout. */
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap > h1.wp-heading-inline,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap > h1 { color:#17324d;font-weight:650;letter-spacing:-.025em; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap > .page-title-action { border-radius:7px;border-color:#a6bdd5;color:#245c9f;background:#fff;font-weight:600; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap > .page-title-action:hover { background:#e8f1fa;border-color:#245c9f;color:#163d67; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap > .page-title-action:focus-visible { outline:2px solid #245c9f;outline-offset:2px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .subsubsub a:not(.current) { color:#245c9f; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .subsubsub a.current { color:#17324d;font-weight:650; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table { border:1px solid #d5dee8;border-radius:9px;box-shadow:0 2px 6px rgba(16,36,60,.045);border-collapse:separate;border-spacing:0; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table thead th,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table thead td,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table tfoot th,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table tfoot td { background:#eaf0f6;color:#233f5c; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table thead tr > :first-child { border-top-left-radius:8px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table thead tr > :last-child { border-top-right-radius:8px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table tfoot tr > :first-child { border-bottom-left-radius:8px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table tfoot tr > :last-child { border-bottom-right-radius:8px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table thead th a,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table tfoot th a { color:#234d76;font-weight:600; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table tbody tr:not(.plugin-update-tr) > th,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table tbody tr:not(.plugin-update-tr) > td { border-bottom:1px solid #e6ebf0; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table tbody tr:last-child > th,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table tbody tr:last-child > td { border-bottom:0; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table.striped > tbody > :nth-child(odd):not(.plugin-update-tr) { background-color:#f6f8fb; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table.striped > tbody > tr:not(.plugin-update-tr):hover { background-color:#edf3fa; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table .row-title { color:#245c9f;font-weight:600; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .wp-list-table .row-title:hover { color:#173f6e;text-decoration:underline; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .tablenav .button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .search-box .button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .tablenav .tablenav-pages .button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap #posts-filter .button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .submit-box .button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap #submitdiv .button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .form-table + .submit .button { border-radius:6px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .tablenav .button:not(.button-primary):not(.disabled):not(:disabled),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .search-box .button:not(.button-primary):not(:disabled) { color:#245c9f;background:#fff;border-color:#acbfd3; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .tablenav .button:not(.button-primary):not(.disabled):not(:disabled):hover,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .search-box .button:not(.button-primary):not(:disabled):hover { color:#173f6e;background:#e8f0fa;border-color:#245c9f; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .submit-box .button-primary,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap #submitdiv .button-primary,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .form-table + .submit .button-primary { background:#245c9f;border-color:#245c9f;color:#fff; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .submit-box .button-primary:hover,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap #submitdiv .button-primary:hover,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .form-table + .submit .button-primary:hover { background:#194879;border-color:#194879; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .search-box input[type=search],
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .tablenav select,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .tablenav .current-page,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .form-table input.regular-text,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .form-table textarea.large-text,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb-row input.regular-text,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb-row textarea.cmb2_textarea,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap #titlediv #title { border-radius:6px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .search-box input[type=search]:not(:disabled),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .tablenav select:not(:disabled),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .tablenav .current-page:not(:disabled) { border-color:#a9bacb;background-color:#fff; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .postbox,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .stuffbox { border-radius:8px;border-color:#d5dee8;box-shadow:0 2px 6px rgba(16,36,60,.045); }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .postbox .postbox-header { border-bottom-color:#dce4ec; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .postbox .postbox-header h2,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .postbox .hndle { color:#233f5c; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box { border-radius:8px;border-color:#d5dee8;box-shadow:0 2px 6px rgba(16,36,60,.045); }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .nav-tab-wrapper .nav-tab { box-sizing:border-box;background:#eef3f8;color:#28445d;border-color:#dce4ec;white-space:normal;overflow-wrap:anywhere; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .nav-tab-wrapper .nav-tab:hover { background:#e2ebf5;color:#173f6e; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .nav-tab-wrapper .nav-tab-active,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .nav-tab-wrapper .nav-tab-active:hover { background:#dce9f7;color:#173f6e;box-shadow:inset 3px 0 #245c9f;font-weight:600; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap > .notice,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content > .notice,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap > .updated,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content > .updated,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap > .error,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content > .error { border-radius:5px;box-shadow:0 1px 3px rgba(16,36,60,.04); }

/* PSR: rifinitura CMB2, editor classico e pannelli nativi. Nessuna modifica a dimensioni delle colonne, controlli o visibilita. */
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page > h2 { color:#17324d;font-weight:650;letter-spacing:-.02em; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box > .nav-tab-wrapper { background:#f5f8fc;border-bottom:0;border-radius:8px 0 0 0; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box > .nav-tab-wrapper > a.nav-tab { padding:9px 9px;border:0;border-bottom:1px solid #e0e7ef;background:#f5f8fc;color:#344e68;font-weight:500;line-height:1.45; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box > .nav-tab-wrapper > a.nav-tab:hover { background:#e8f0f9;color:#194879; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box > .nav-tab-wrapper > a.nav-tab-active,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box > .nav-tab-wrapper > a.nav-tab-active:hover { background:#dce9f7;color:#173f6e;font-weight:650;box-shadow:inset 3px 0 #245c9f; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box > .nav-tab-wrapper > a.nav-tab:focus-visible { outline:2px solid #245c9f;outline-offset:-3px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-type-title { background:#eef3f8;border-bottom-color:#dbe4ed; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-type-title .cmb2-metabox-title { color:#17324d;font-weight:650;font-size:14px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-th > label,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb-th > label { color:#233f5c;font-weight:600; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-metabox-description,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-metabox-description { color:#52657a;line-height:1.5; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-row { border-bottom-color:#e4eaf0; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-repeatable-grouping,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb-repeatable-grouping { border-radius:8px;border-color:#d5dee8;box-shadow:0 1px 3px rgba(16,36,60,.045); }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-group-title,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb-group-title { background:#eaf0f6;color:#233f5c;border-radius:7px 7px 0 0;font-weight:600; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-td :is(input[type=text],input[type=url],input[type=email],input[type=tel],input[type=number],input[type=password],textarea,select):not(.wp-color-picker),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb-td :is(input[type=text],input[type=url],input[type=email],input[type=tel],input[type=number],textarea,select):not(.wp-color-picker),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .form-table :is(input.regular-text,textarea.large-text,select) { border-radius:6px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-td :is(input[type=text],input[type=url],input[type=email],input[type=tel],input[type=number],input[type=password],textarea,select):not(.wp-color-picker):not(:disabled):not([readonly]):not([aria-invalid=true]):not(.error):focus-visible,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb-td :is(input[type=text],input[type=url],input[type=email],input[type=tel],input[type=number],textarea,select):not(.wp-color-picker):not(:disabled):not([readonly]):not([aria-invalid=true]):not(.error):focus-visible { outline:2px solid #245c9f;outline-offset:1px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box :is(.cmb2-upload-button,.cmb-add-group-row,.cmb-add-row-button),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox :is(.cmb2-upload-button,.cmb-add-group-row,.cmb-add-row-button) { border-radius:6px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box :is(.cmb2-upload-button,.cmb-add-group-row,.cmb-add-row-button):not(:disabled):not(.disabled),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox :is(.cmb2-upload-button,.cmb-add-group-row,.cmb-add-row-button):not(:disabled):not(.disabled) { background:#fff;color:#245c9f;border-color:#a6bdd5;font-weight:600; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box :is(.cmb2-upload-button,.cmb-add-group-row,.cmb-add-row-button):not(:disabled):not(.disabled):hover,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox :is(.cmb2-upload-button,.cmb-add-group-row,.cmb-add-row-button):not(:disabled):not(.disabled):hover { background:#e8f0fa;color:#173f6e;border-color:#245c9f; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box :is(.cmb-remove-group-row,.cmb-remove-row-button,.move-up,.move-down),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox :is(.cmb-remove-group-row,.cmb-remove-row-button,.move-up,.move-down) { border-radius:5px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .submit-box { background:#f6f8fb;border-top:1px solid #e4eaf0;border-left-color:#e4eaf0;border-bottom-right-radius:8px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) > .nav-tab-wrapper .nav-tab { border-radius:6px 6px 0 0; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .form-table th > label { color:#233f5c; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .form-table td > .description { color:#52657a; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) > table.widefat:not(.wp-list-table) { border-radius:8px;border-color:#d5dee8;box-shadow:0 2px 6px rgba(16,36,60,.045); }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) > table.widefat:not(.wp-list-table) > thead > tr > :is(th,td) { background:#eaf0f6;color:#233f5c; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) > form > .button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) > form > .submit > .button { border-radius:6px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #dashboard-widgets .postbox { border-radius:8px;border-color:#d5dee8;box-shadow:0 2px 6px rgba(16,36,60,.045); }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #dashboard-widgets .postbox .postbox-header { border-bottom-color:#dce4ec; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #dashboard-widgets .postbox .postbox-header h2 { color:#233f5c; }

/* Rifinitura campi file CMB2: stessi controlli nativi, nessun cambio di eventi. */
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-type-file .cmb-td input.cmb2-upload-file[type=text] ~ input.cmb2-upload-button {
    margin:8px 0 6px !important;
    float:none;
    vertical-align:middle;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box input.cmb2-upload-button[type=button],
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox input.cmb2-upload-button[type=button],
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap input.cmb2-upload-button[type=button] {
    border-radius:7px;box-shadow:0 1px 3px rgba(16,36,60,.08);
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled) {
    background:#eaf1f9;color:#245c9f;border-color:#acc1d8;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled):hover,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled):hover,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled):hover {
    background:#dce9f7;color:#173f6e;border-color:#245c9f;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-media-status .img-status > img,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-media-status .img-status > img,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap .cmb2-media-status .img-status > img {
    border-radius:8px !important;
    outline:1px solid #d5dee8;
    box-shadow:0 2px 6px rgba(16,36,60,.08);
    background-color:#f8fafc !important;
    background-image:linear-gradient(45deg,#e9edf2 25%,transparent 25%,transparent 75%,#e9edf2 75%,#e9edf2),linear-gradient(45deg,#e9edf2 25%,transparent 25%,transparent 75%,#e9edf2 75%,#e9edf2) !important;
    background-size:16px 16px;
    background-position:0 0,8px 8px;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-media-status .img-status .cmb2-remove-file-button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-media-status .img-status .cmb2-remove-file-button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap .cmb2-media-status .img-status .cmb2-remove-file-button {
    box-sizing:border-box;
    background:#fff;
    border:1px solid #dba3a3;
    border-radius:50%;
    color:#a4262c;
    width:22px;height:22px;
    left:auto;right:-4px;top:7px;
    box-shadow:0 1px 3px rgba(16,36,60,.1);
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-media-status .img-status .cmb2-remove-file-button::after,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-media-status .img-status .cmb2-remove-file-button::after,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap .cmb2-media-status .img-status .cmb2-remove-file-button::after {
    content:"\00D7";
    position:absolute;inset:0;
    text-indent:0;text-align:center;
    font:600 19px/19px system-ui,sans-serif;
    pointer-events:none;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-media-status .img-status .cmb2-remove-file-button:hover,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-media-status .img-status .cmb2-remove-file-button:hover,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap .cmb2-media-status .img-status .cmb2-remove-file-button:hover {
    color:#fff;background:#a4262c;border-color:#a4262c;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-media-status .file-status,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-media-status .file-status,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap .cmb2-media-status .file-status {border-radius:6px;}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-media-status .cmb2-remove-file-button:focus-visible,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-media-status .cmb2-remove-file-button:focus-visible,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap .cmb2-media-status .cmb2-remove-file-button:focus-visible {outline:2px solid #245c9f;outline-offset:3px;}

/* Configurazione PSR: superficie leggera e campi piu' definiti.
   Conserva colonne, dimensioni dei campi, widget e stati di validazione. */
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box {
    background:#f4f7fb;
    border-color:#d5e0eb;
    border-radius:12px;
    box-shadow:0 3px 14px rgba(16,36,60,.055);
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box > .nav-tab-wrapper {
    border-radius:11px 0 0 0;
    background:#f4f7fb;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box > .nav-tab-wrapper > a.nav-tab {
    background:transparent;
    border-bottom-color:#e2e9f1;
    color:#344e68;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box > .nav-tab-wrapper > a.nav-tab:hover {
    background:#e8f0fa;
    color:#194879;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box > .nav-tab-wrapper > a.nav-tab-active,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box > .nav-tab-wrapper > a.nav-tab-active:hover {
    background:#dce9f7;
    color:#173f6e;
    box-shadow:inset 4px 0 #245c9f;
    font-weight:650;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box > .cmb-form > .form-content .cmb2-metabox > .cmb-row:not(.cmb-type-title):not(.highlighted_missing_field):not(.highlighted_alternative_field):not(.error) {
    background:#fff;
    border-color:#e2e9f1;
    box-shadow:none;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box > .cmb-form > .form-content .cmb2-metabox > .cmb-row:not(.cmb-type-title):not(.highlighted_missing_field):not(.highlighted_alternative_field):not(.error):focus-within {
    box-shadow:inset 3px 0 #90b4da;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box > .cmb-form > .form-content .cmb2-metabox > .cmb-row > .cmb-th > label {
    color:#17324d;
    font-weight:650;
    line-height:1.45;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box > .cmb-form > .form-content .cmb2-metabox > .cmb-row > .cmb-td > .cmb2-metabox-description {
    color:#53677c;
    font-size:13px;
    line-height:1.55;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box .cmb-type-title {
    background:#e7eff8;
    border-color:#d5e1ed;
    box-shadow:none;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box .cmb-type-title .cmb2-metabox-title {
    color:#173f6e;
    font-size:15px;
    letter-spacing:.015em;
    font-weight:700;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box .cmb-row:not(.highlighted_missing_field):not(.highlighted_alternative_field):not(.error) .cmb-td :is(input[type=text].regular-text,input[type=url],input[type=email],input[type=tel],input[type=number],input[type=password],textarea.cmb2_textarea):not(.wp-color-picker):not(.select2-search__field):not([aria-invalid=true]):not(.error):not(:disabled):not([readonly]):not([aria-readonly=true]) {
    border-radius:8px;
    border-color:#b9c9d9;
    background-color:#f8fafc;
    color:#17324d;
    box-shadow:0 1px 2px rgba(16,36,60,.035);
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box .cmb-row:not(.highlighted_missing_field):not(.highlighted_alternative_field):not(.error) .cmb-td :is(input[type=text].regular-text,input[type=url],input[type=email],input[type=tel],input[type=number],input[type=password],textarea.cmb2_textarea):not(.wp-color-picker):not(.select2-search__field):not([aria-invalid=true]):not(.error):not(:disabled):not([readonly]):not([aria-readonly=true]):hover {
    border-color:#8faac5;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box .cmb-row:not(.highlighted_missing_field):not(.highlighted_alternative_field):not(.error) .cmb-td :is(input[type=text].regular-text,input[type=url],input[type=email],input[type=tel],input[type=number],input[type=password],textarea.cmb2_textarea):not(.wp-color-picker):not(.select2-search__field):not([aria-invalid=true]):not(.error):not(:disabled):not([readonly]):not([aria-readonly=true]):focus {
    background-color:#fff;
    border-color:#245c9f;
    box-shadow:0 0 0 3px rgba(36,92,159,.10);
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap.cmb2-options-page .cmb2-options-box .submit-box {
    border-top-color:#dbe5ef;
    background:#eff4fa;
    border-bottom-right-radius:11px;
}

/* PSR: liste native piu' leggibili. Mantiene colonne, ordinamenti,
   azioni di riga, stati speciali e comportamento responsive di WordPress. */
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table {
    border-color:#d6e0ea;
    border-radius:12px;
    background:#fff;
    box-shadow:0 3px 14px rgba(16,36,60,.065);
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table > thead > tr > :is(th,td),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table > tfoot > tr > :is(th,td) {
    background:#e5edf6;
    color:#233f5c;
    font-weight:600;
    border-color:#d5e0eb;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table > thead > tr > :first-child {border-top-left-radius:11px;}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table > thead > tr > :last-child {border-top-right-radius:11px;}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table > tfoot > tr > :first-child {border-bottom-left-radius:11px;}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table > tfoot > tr > :last-child {border-bottom-right-radius:11px;}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table.posts > tbody > tr.type-page > td,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table.posts > tbody > tr[class*="type-"] > td,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table.users > tbody > tr > td,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table.tags > tbody > tr > td {
    line-height:1.55;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table:is(.posts,.users,.tags,.media) > tbody > tr > td.column-primary .row-title {
    font-size:14px;
    font-weight:650;
    color:#204e80;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table:is(.posts,.users,.tags,.media) > tbody > tr > td.column-primary .row-title:hover {
    color:#163a60;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table.posts > tbody > tr > td[class*="column-taxonomy-"] > a,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table.posts > tbody > tr > td.column-categories > a,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table.posts > tbody > tr > td.column-tags > a,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table.posts > tbody > tr > td.column-author > a {
    color:#245c9f;
    text-decoration-thickness:1px;
    text-underline-offset:2px;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table.posts > tbody > tr > td.column-date {
    color:#52657a;
    font-variant-numeric:tabular-nums;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table.posts.striped > tbody > tr[class*="type-"]:not(.inline-edit-row):not(.no-items):nth-child(odd) {
    background-color:#f5f8fc;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table.posts.striped > tbody > tr[class*="type-"]:not(.inline-edit-row):not(.no-items):hover {
    background-color:#eaf1fa;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .wp-list-table.posts > tbody > tr[class*="type-"]:not(.inline-edit-row) .post-state {
    background:#e8eef5;
    color:#344e68;
    border:1px solid #d5e0ec;
    border-radius:5px;
    padding:1px 5px;
    font-size:11px;
    font-weight:600;
    box-decoration-break:clone;
    -webkit-box-decoration-break:clone;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .subsubsub a {
    border-radius:5px;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .subsubsub a.current {
    color:#17324d;
    font-weight:650;
    text-decoration:underline;
    text-underline-offset:4px;
    text-decoration-thickness:2px;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .actions select:not(:disabled),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .search-box input[type=search]:not(:disabled),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .current-page:not(:disabled) {
    background-color:#fff;
    border-color:#b3c5d8;
    border-radius:7px;
    box-shadow:0 1px 2px rgba(16,36,60,.04);
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .actions select:focus-visible,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .search-box input[type=search]:focus-visible,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .current-page:focus-visible {
    outline:2px solid #245c9f;
    outline-offset:1px;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .search-box .button:not(:disabled):not(.disabled):not(.button-primary) {
    background:#eaf1fa;
    border-color:#a6bdd5;
    color:#194879;
    font-weight:600;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .search-box .button:not(:disabled):not(.disabled):not(.button-primary):hover {
    background:#dce9f7;
    border-color:#245c9f;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .tablenav-pages-navspan {
    border-radius:7px;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .tablenav-pages a.button:not(.disabled):not([aria-disabled=true]) {
    background:#fff;
    color:#245c9f;
    border-color:#acbfd3;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .tablenav-pages a.button:not(.disabled):not([aria-disabled=true]):hover {
    background:#e8f0fa;
    border-color:#245c9f;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .tablenav-pages .displaying-num,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .tablenav-pages .total-pages {
    color:#52657a;
    font-variant-numeric:tabular-nums;
}

/* PSR: barra strumenti liste, senza cambiare form, link o ordine nativo. */
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .subsubsub > li { color:#aab8c7; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .subsubsub > li > a { color:#345778;border-radius:6px;text-decoration:none; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .subsubsub > li > a:hover { color:#173f6e;background:#e2ebf5; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .subsubsub > li > a.current,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .subsubsub > li > a.current:hover { background:#dce9f7;color:#173f6e;font-weight:650;text-decoration:none; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .subsubsub > li > a .count { color:#52657a;font-variant-numeric:tabular-nums; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .subsubsub > li > a:focus-visible,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .tablenav-pages a.button:focus-visible,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .actions .button:focus-visible,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .search-box .button:focus-visible { outline:2px solid #245c9f;outline-offset:2px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .search-box input[type=search]:not(:disabled) { background:#fff;border-color:#b9c9d9;border-radius:8px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .search-box .button:not(:disabled):not(.disabled):not(.button-primary) { background:#245c9f;border-color:#245c9f;color:#fff;border-radius:8px;font-weight:600; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .search-box .button:not(:disabled):not(.disabled):not(.button-primary):hover { background:#194879;border-color:#194879;color:#fff; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .actions select:not(:disabled) { background-color:#f8fafc;border-color:#bccbda;color:#233f5c;border-radius:7px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .actions select:not(:disabled):hover { border-color:#8faac5; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .actions .button:not(.button-primary):not(.disabled):not(:disabled) { background:#eaf1fa;color:#194879;border-color:#b2c7dc;font-weight:600; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .actions .button:not(.button-primary):not(.disabled):not(:disabled):hover { background:#dce9f7;color:#173f6e;border-color:#245c9f; }
/* Desktop: superficie per filtri e paginazione. Conserva float nativi. */
@media (min-width:1100px) {
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .subsubsub { margin-top:9px;margin-bottom:9px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .subsubsub > li > a { display:inline-block;padding:3px 8px;line-height:1.55; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .search-box { margin-top:7px;margin-bottom:9px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .search-box input[type=search],
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .search-box .button { min-height:34px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav.top,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav.bottom { box-sizing:border-box;display:flow-root;height:auto;min-height:48px;margin:8px 0 12px;padding:8px 10px 4px;border:1px solid #d6e0ea;border-radius:10px;background:#fff;box-shadow:0 2px 6px rgba(16,36,60,.045); }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .actions select,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .actions .button { min-height:32px; }
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa) .tablenav .tablenav-pages .displaying-num { font-weight:500;color:#52657a; }
}
/* Tablet e smartphone: disposizione e altezze native. */

/* PSR: toolbar gestionale. Solo liste native, senza modifiche al DOM. */
@supports selector(:has(*)) {
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .subsubsub > li > a {
    color:#36516d;
    font-weight:550;
    border-radius:8px;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .subsubsub > li > a.current,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .subsubsub > li > a.current:hover {
    color:#fff;
    background:#204e80;
    box-shadow:0 2px 4px rgba(16,36,60,.13);
    text-decoration:none;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .subsubsub > li > a.current .count {
    color:#fff;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .subsubsub > li > a:not(.current):hover {
    color:#173f6e;
    background:#e9f1fa;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .search-box .button:not(:disabled):not(.disabled):not(.button-primary) {
    background:#204e80;
    border-color:#204e80;
    box-shadow:0 2px 4px rgba(16,36,60,.12);
    font-weight:650;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .search-box .button:not(:disabled):not(.disabled):not(.button-primary):hover {
    background:#173a61;
    border-color:#173a61;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .tablenav .actions select:not(:disabled) {
    background-color:#f8fafc;
    border-color:#c3d0de;
    color:#263f59;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .tablenav .actions .button:not(.button-primary):not(.disabled):not(:disabled) {
    color:#204e80;
    background:#edf3fa;
    border-color:#c1d1e1;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .tablenav .actions .button:not(.button-primary):not(.disabled):not(:disabled):hover {
    background:#dce9f7;
    border-color:#8baacb;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .tablenav .tablenav-pages a.button:not(.disabled):not([aria-disabled=true]) {
    background:#f6f9fc;
    border-color:#c3d0de;
    color:#204e80;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .tablenav .tablenav-pages .current-page:not(:disabled) {
    background:#fff;
    border-color:#b3c5d8;
    font-weight:600;
    color:#173f6e;
}
@media (min-width:1100px) {
    /* Mantiene float e controlli nativi: le barre possono andare a capo. */
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .subsubsub {
        box-sizing:border-box;
        background:#fff;
        border:1px solid #dce5ef;
        border-radius:11px;
        padding:4px;
        margin:10px 0;
        box-shadow:0 2px 6px rgba(16,36,60,.04);
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .subsubsub > li {
        color:transparent;
        line-height:2;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .subsubsub > li > a {
        padding:5px 9px;
        line-height:1.55;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .subsubsub > li > a .count {
        display:inline-block;
        padding:0 4px;
        margin-inline-start:2px;
        border-radius:5px;
        background:#eef3f8;
        color:#465e76;
        font-size:11px;
        line-height:1.6;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .subsubsub > li > a.current .count {
        background:rgba(255,255,255,.18);
        color:#fff;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .search-box {
        margin-top:10px;
        margin-bottom:10px;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .search-box input[type=search]:not(:disabled) {
        box-sizing:border-box;
        min-height:38px;
        padding-inline-start:30px;
        background-color:#fff;
        background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23647b93' stroke-width='1.8' stroke-linecap='round'%3E%3Ccircle cx='10.8' cy='10.8' r='6.3'/%3E%3Cpath d='m16 16 4 4'/%3E%3C/svg%3E");
        background-repeat:no-repeat;
        background-position:left 9px center;
        background-size:16px 16px;
        border-radius:9px;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .search-box .button {
        min-height:38px;
        border-radius:9px;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .tablenav.top,
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .tablenav.bottom {
        background:#fff;
        border:1px solid #dce5ef;
        border-radius:12px;
        padding:10px 12px 6px;
        min-height:52px;
        box-shadow:0 2px 8px rgba(16,36,60,.045);
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .tablenav .actions select,
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .tablenav .actions .button {
        min-height:34px;
        border-radius:8px;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .tablenav .tablenav-pages .displaying-num {
        padding:4px 8px;
        background:#f0f4f8;
        border-radius:6px;
        color:#415b74;
        font-weight:600;
    }
}
/* In modalita' RTL l'icona ricerca segue la direzione del campo. */
@media (min-width:1100px) {
    body.rtl.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap:not(.psrc):not(.psr-wa):has(.wp-list-table) .search-box input[type=search]:not(:disabled) {
        background-position:right 9px center;
    }
}
}

/* PSR: componente media. Solo presentazione dei controlli CMB2 esistenti.
   Non spostare i nodi: handleMedia usa il campo precedente al pulsante. */
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled):not([aria-disabled=true]),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled):not([aria-disabled=true]),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled):not([aria-disabled=true]) {
    background-color:#245c9f;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='none' stroke='%23ffffff' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M12 16V4m-5 5 5-5 5 5M4 16v4h16v-4'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-size:16px 16px;
    background-position:left 12px center;
    color:#fff;
    border-color:#245c9f;
    border-radius:9px;
    padding-inline-start:35px;
    padding-inline-end:14px;
    min-height:36px;
    font-weight:600;
    box-shadow:0 2px 5px rgba(16,36,60,.12);
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled):not([aria-disabled=true]):hover,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled):not([aria-disabled=true]):hover,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled):not([aria-disabled=true]):hover {
    background-color:#194879;
    border-color:#194879;
    color:#fff;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box input.cmb2-upload-button[type=button]:focus-visible,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox input.cmb2-upload-button[type=button]:focus-visible,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap input.cmb2-upload-button[type=button]:focus-visible {
    outline:2px solid #245c9f;
    outline-offset:3px;
}
body.rtl.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled):not([aria-disabled=true]),
body.rtl.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled):not([aria-disabled=true]),
body.rtl.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap input.cmb2-upload-button[type=button]:not(:disabled):not(.disabled):not([aria-disabled=true]) {
    background-position:right 12px center;
}
/* I riquadri e le immagini mantengono le dimensioni native e il drag-and-drop. */
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-media-status .img-status,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-media-status .img-status,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap .cmb2-media-status .img-status {
    box-sizing:border-box;
    max-width:100%;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-media-status .img-status > img,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-media-status .img-status > img,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap .cmb2-media-status .img-status > img {
    border:4px solid #fff;
    border-radius:10px !important;
    outline:1px solid #d5e0ec;
    box-shadow:0 3px 8px rgba(16,36,60,.08);
    box-sizing:border-box;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-media-status .img-status .cmb2-remove-file-button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-media-status .img-status .cmb2-remove-file-button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap .cmb2-media-status .img-status .cmb2-remove-file-button {
    width:24px;
    height:24px;
    border-color:#dfb7b7;
    box-shadow:0 2px 5px rgba(16,36,60,.12);
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-media-status .img-status .cmb2-remove-file-button::after,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-media-status .img-status .cmb2-remove-file-button::after,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap .cmb2-media-status .img-status .cmb2-remove-file-button::after {
    font-size:19px;
    line-height:21px;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-media-status .file-status,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-media-status .file-status,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap .cmb2-media-status .file-status {
    box-sizing:border-box;
    max-width:100%;
    padding:9px 12px;
    margin-top:6px;
    background:#f7f9fc;
    border:1px solid #dce5ef;
    border-radius:8px;
    overflow-wrap:anywhere;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-media-status .file-status > a:not(.cmb2-remove-file-button),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-media-status .file-status > a:not(.cmb2-remove-file-button),
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap .cmb2-media-status .file-status > a:not(.cmb2-remove-file-button) {
    color:#245c9f;
    font-weight:600;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb2-media-status .file-status > a.cmb2-remove-file-button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff .cmb2-postbox .cmb2-media-status .file-status > a.cmb2-remove-file-button,
body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap .cmb2-media-status .file-status > a.cmb2-remove-file-button {
    color:#a4262c;
}
@supports selector(:has(*)) {
    /* Configurazione: campo URL, azione e anteprima dentro una superficie unica. */
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box :is(.cmb-type-file,.cmb-type-file-list) > .cmb-td:has(> input.cmb2-upload-button) {
        box-sizing:border-box;
        background:#f5f8fc;
        border-radius:12px;
        box-shadow:inset 0 0 0 1px #dce6f0;
        padding:14px 14px 12px;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box :is(.cmb-type-file,.cmb-type-file-list) > .cmb-td:has(> input.cmb2-upload-button) > .cmb2-metabox-description {
        color:#52657a;
        line-height:1.55;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box :is(.cmb-type-file,.cmb-type-file-list) > .cmb-td:has(> input.cmb2-upload-button) > .cmb2-media-status:has(> .cmb2-media-item) {
        box-sizing:border-box;
        max-width:100%;
        padding:10px 12px;
        margin-top:8px;
        border:1px solid #dce6f0;
        border-radius:10px;
        background:#fff;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box :is(.cmb-type-file,.cmb-type-file-list) > .cmb-td:has(> input.cmb2-upload-button) > .cmb2-media-status > .img-status {
        margin-top:0;
        padding:5px 8px 5px 0;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box :is(.cmb-type-file,.cmb-type-file-list) > .cmb-td:has(> input.cmb2-upload-button) > .cmb2-media-status > .img-status > img {
        margin-top:0;
        max-width:100%;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box :is(.cmb-type-file,.cmb-type-file-list) > .cmb-td:has(> input.cmb2-upload-button) > .cmb2-media-status > .img-status .cmb2-remove-file-button {
        top:0;
        right:1px;
    }
    /* Flex soltanto con coppia input/pulsante nativa, senza riordinare i nodi.
       Gli altri campi, i file multipli e le sidebar conservano il layout CMB2. */
    @media (min-width:1100px) {
        body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-type-file > .cmb-td:has(> input.cmb2-upload-file[type=text] + input.cmb2-upload-button:not(.cmb2-upload-list)) {
            display:flex;
            flex-wrap:wrap;
            align-items:center;
            gap:10px;
        }
        body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-type-file > .cmb-td:has(> input.cmb2-upload-file[type=text] + input.cmb2-upload-button:not(.cmb2-upload-list)) > input.cmb2-upload-file[type=text]:not([hidden]) {
            flex:1 1 240px;
            width:auto !important;
            min-width:0;
            max-width:100%;
            min-height:38px;
            box-sizing:border-box;
            margin:0;
        }
        body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-type-file > .cmb-td:has(> input.cmb2-upload-file[type=text] + input.cmb2-upload-button:not(.cmb2-upload-list)) > input.cmb2-upload-button:not(.cmb2-upload-list) {
            flex:0 0 auto;
            max-width:100%;
            margin:0 !important;
            min-height:38px;
        }
        body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-type-file > .cmb-td:has(> input.cmb2-upload-file[type=text] + input.cmb2-upload-button:not(.cmb2-upload-list)) > :not(input):not([hidden]) {
            flex:1 1 100%;
            min-width:0;
        }
        body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-type-file > .cmb-td:has(> input.cmb2-upload-file[type=text] + input.cmb2-upload-button:not(.cmb2-upload-list)) > .cmb2-metabox-description,
        body.psr-modern-admin:not(.block-editor-page) #wpbody-content .wrap .cmb2-options-box .cmb-type-file > .cmb-td:has(> input.cmb2-upload-file[type=text] + input.cmb2-upload-button:not(.cmb2-upload-list)) > .cmb2-media-status {
            margin-top:0;
        }
    }
}

/* PSR: media anche in metabox, profili utente, tassonomie e altri CMB2.
   Mantiene la struttura dei campi, le colonne, la libreria e i file ordinabili. */
@supports selector(:has(*)) {
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap:not(.cmb2-options-box *) :is(.cmb-type-file,.cmb-type-file-list):not(.highlighted_missing_field):not(.highlighted_alternative_field):not(.error) > .cmb-td:has(> input.cmb2-upload-button) {
        background:#f5f8fc;
        border-radius:12px;
        box-shadow:inset 0 0 0 1px #dce6f0;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap:not(.cmb2-options-box *) :is(.cmb-type-file,.cmb-type-file-list):not(.highlighted_missing_field):not(.highlighted_alternative_field):not(.error) > div.cmb-td:has(> input.cmb2-upload-button) {
        box-sizing:border-box;
        padding:12px;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap:not(.cmb2-options-box *) :is(.cmb-type-file,.cmb-type-file-list):not(.highlighted_missing_field):not(.highlighted_alternative_field):not(.error) > .cmb-td:has(> input.cmb2-upload-button) > input.cmb2-upload-file[type=text]:not([hidden]) {
        box-sizing:border-box;
        max-width:100%;
        border-radius:8px;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap:not(.cmb2-options-box *) :is(.cmb-type-file,.cmb-type-file-list):not(.highlighted_missing_field):not(.highlighted_alternative_field):not(.error) > .cmb-td:has(> input.cmb2-upload-button) > input.cmb2-upload-file[type=text]:not([hidden]):not(:disabled):not([readonly]):not([aria-invalid=true]):not(.error) {
        background:#fff;
        border-color:#b9c9d9;
        color:#17324d;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap:not(.cmb2-options-box *) :is(.cmb-type-file,.cmb-type-file-list):not(.highlighted_missing_field):not(.highlighted_alternative_field):not(.error) > .cmb-td:has(> input.cmb2-upload-button) > input.cmb2-upload-button {
        max-width:100%;
        margin-top:7px;
        margin-bottom:5px;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap:not(.cmb2-options-box *) :is(.cmb-type-file,.cmb-type-file-list):not(.highlighted_missing_field):not(.highlighted_alternative_field):not(.error) > .cmb-td:has(> input.cmb2-upload-button) > .cmb2-metabox-description {
        color:#52657a;
        line-height:1.55;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap:not(.cmb2-options-box *) :is(.cmb-type-file,.cmb-type-file-list):not(.highlighted_missing_field):not(.highlighted_alternative_field):not(.error) > .cmb-td:has(> input.cmb2-upload-button) > .cmb2-media-status:has(> .cmb2-media-item) {
        box-sizing:border-box;
        max-width:100%;
        background:#fff;
        border:1px solid #dce6f0;
        border-radius:10px;
        padding:10px;
        margin-top:8px;
    }
    body.psr-modern-admin:not(.block-editor-page) #wpbody-content .cmb2-wrap:not(.cmb2-options-box *) :is(.cmb-type-file,.cmb-type-file-list):not(.highlighted_missing_field):not(.highlighted_alternative_field):not(.error) > .cmb-td:has(> input.cmb2-upload-button) > .cmb2-media-status > .img-status > img {
        max-width:100%;
    }
}
/* Importazioni native della Trasparenza: nessun wrapper o input sostitutivo. */
body.psr-modern-admin:not(.block-editor-page) #wpbody-content input[type=file]:is(#dci_multi_files,#dci-trasparenza-media-packages,#dci-trasparenza-content-package) {
    box-sizing:border-box;
    max-width:100%;
    background:#f5f8fc;
    border:1px solid #dce6f0;
    border-radius:9px;
    padding:6px;
    color:#344e68;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content input[type=file]:is(#dci_multi_files,#dci-trasparenza-media-packages,#dci-trasparenza-content-package):not(:disabled)::file-selector-button {
    background:#245c9f;
    color:#fff;
    border:1px solid #245c9f;
    border-radius:7px;
    padding:8px 12px;
    margin-inline-end:9px;
    font-weight:600;
    cursor:pointer;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content input[type=file]:is(#dci_multi_files,#dci-trasparenza-media-packages,#dci-trasparenza-content-package):not(:disabled)::file-selector-button:hover {
    background:#194879;
    border-color:#194879;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content input[type=file]:is(#dci_multi_files,#dci-trasparenza-media-packages,#dci-trasparenza-content-package):focus-visible {
    outline:2px solid #245c9f;
    outline-offset:2px;
}


/* PSR: schede laterali dell'editor, riconoscibili ma leggere. */
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff #side-sortables > .postbox {
    border:1px solid #c1d2e3;
    border-radius:10px;
    background:#fff;
    box-shadow:0 3px 10px rgba(16,36,60,.065);
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff #side-sortables > .postbox > .postbox-header {
    background:linear-gradient(110deg,#e8f1fa,#f3f7fb);
    border-bottom-color:#d4e1ee;
    border-radius:9px 9px 0 0;
    box-shadow:inset 3px 0 #6695c4;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff #side-sortables > .postbox.closed > .postbox-header {
    border-radius:9px;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff #side-sortables > .postbox > .postbox-header h2 {
    color:#173f6e;
    font-weight:650;
    line-height:1.45;
    padding-inline-start:13px;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff #side-sortables > .postbox > .postbox-header .handle-actions button:not(:disabled) {
    color:#315d87;
    border-radius:5px;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff #side-sortables > .postbox > .postbox-header .handle-actions button:not(:disabled):hover {
    background-color:#dbe8f5;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff #side-sortables > .postbox > .postbox-header .handle-actions button:focus-visible {
    outline:2px solid #245c9f;
    outline-offset:-3px;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff #side-sortables > .postbox .inside :is(ul.categorychecklist,ul.cmb2-checkbox-list) li {
    line-height:1.5;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff #side-sortables > .postbox .inside :is(ul.categorychecklist,ul.cmb2-checkbox-list) label {
    color:#28445d;
    border-radius:4px;
    overflow-wrap:anywhere;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff #side-sortables > .postbox .inside :is(ul.categorychecklist,ul.cmb2-checkbox-list) label:hover {
    background:#edf4fb;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff #side-sortables > .postbox .inside :is(ul.categorychecklist,ul.cmb2-checkbox-list) input[type=checkbox]:checked + label {
    color:#173f6e;
    font-weight:600;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff #side-sortables > .postbox .inside :is(.button,.cmb2-toggle) :is(svg,.dashicons) {
    vertical-align:middle;
}
body.psr-modern-admin:not(.block-editor-page) #wpbody-content #poststuff #side-sortables > .postbox .inside :is(.button,.cmb2-toggle):not(.button-primary):not(.button-link):not(.button-link-delete):not(:disabled):not(.disabled):not([aria-disabled=true]) {
    border-radius:6px;
    max-width:100%;
}

CSS
        );
    }
    // Script nativo, senza dipendenze: deve partire nell'head, non nel footer.
    wp_register_script('psr-modern-admin', false, array(), '1.0.5', false);
    wp_enqueue_script('psr-modern-admin');
    $data = array(
        'key' => 'psr-modern-nav-v1-' . get_current_blog_id() . '-' . get_current_user_id(),
        'site' => get_bloginfo('name'),
    );
    wp_add_inline_script('psr-modern-admin', 'window.psrModernAdminData = ' . wp_json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) . ';', 'before');
    wp_add_inline_script('psr-modern-admin', <<<'JS'
(function () {
    'use strict';
    function init() {
        var menu = document.getElementById('adminmenu'), cfg = window.psrModernAdminData;
        if (!menu || !cfg || menu.querySelector('.psr-nav-tools')) { return; }
        var groups = [
            ['comunicazione','Comunicazione',['notizia','evento','sito_tematico']],
            ['contenuti_media','Contenuti e media',['page','post','galleria','core:upload.php']],
            ['amministrazione','Amministrazione',['unita_organizzativa','persona_pubblica','incarico','punto_contatto','consiglio']],
            ['territorio','Servizi e territorio',['servizio','fase','luogo','progetto']],
            ['trasparenza','Documenti e trasparenza',['documento_pubblico','elemento_trasparenza','dataset','commissario','bando','titolari_di_incarichi_collaborazione_consulenza']],
            ['pratiche','Pratiche e pagamenti',['pratica','documento_privato','pagamento']],
            ['sportello','Sportello e assistenza',['appuntamento','messaggio','richiesta_assistenza','segnala_disservizio']],
            ['qualita_servizi','FAQ e valutazioni',['domanda_frequente','rating','core:edit-comments.php']],
            ['setup_portale','Impostazioni portale',['core:themes.php','admin:dci_webapp_mobile','core:options-general.php']],
            ['sistema_sicurezza','Sistema e sicurezza',['admin:psr-security-center','core:plugins.php','core:users.php','core:tools.php','plugin:wp-toolkit','admin:wpc-accessi']]
        ];
        var items = Array.from(menu.children).filter(function(li) {
            return li.classList.contains('menu-top') && !!li.querySelector('a.menu-top');
        });
        function type(li) {
            try {
                var a = li.querySelector('a.menu-top'), u = new URL(a.href, document.baseURI);
                var base = new URL(document.baseURI);
                if (u.origin !== base.origin || u.pathname.indexOf('/wp-admin/') === -1) { return null; }
                var file = u.pathname.split('/').pop();
                if (file === 'edit.php') { return u.searchParams.get('post_type') || 'post'; }
                var page = u.searchParams.get('page');
                if (file === 'admin.php' && page) {
                    if (['dci_options','dci_webapp_mobile','psr-security-center','wpc-accessi'].indexOf(page) !== -1) { return 'admin:' + page; }
                }
                if (!page && ['upload.php','themes.php','plugins.php','users.php','tools.php','options-general.php','edit-comments.php'].indexOf(file) !== -1) {
                    return 'core:' + file;
                }
                // WP Toolkit e' un plugin esterno: riconosci soltanto il nome
                // esatto del menu esistente, non link generici o altri plugin.
                var label = li.querySelector('.wp-menu-name');
                if (norm(label ? label.textContent : a.textContent) === 'wp toolkit') { return 'plugin:wp-toolkit'; }
                return null;
            } catch(e) { return null; }
        }
        function make(tag, cls, label) {
            var n = document.createElement(tag); n.className = cls;
            if (label !== undefined) { n.textContent = label; }
            return n;
        }
        function norm(s) { return String(s || '').normalize('NFD').replace(/[\u0300-\u036f]/g,'').toLowerCase().trim(); }
        function active(li) { return li.classList.contains('current') || li.classList.contains('wp-has-current-submenu'); }
        var closed = {};
        try { var v = JSON.parse(localStorage.getItem(cfg.key) || '{}'); if (v && typeof v === 'object') { closed = v; } } catch(e) {}
        // Mantiene lo stato della precedente sezione operativa finche' non
        // viene scelta una preferenza specifica per ciascun nuovo gruppo.
        ['pratiche','sportello','qualita_servizi'].forEach(function(key) {
            if (typeof closed[key] !== 'boolean' && typeof closed.operativa === 'boolean') { closed[key] = closed.operativa; }
        });
        function save() { try { localStorage.setItem(cfg.key, JSON.stringify(closed)); } catch(e) {} }
        var tools = make('li','psr-nav-tools');
        tools.appendChild(make('span','psr-nav-brand',cfg.site || 'Portale comunale'));
        tools.appendChild(make('span','psr-nav-caption','Area amministrativa'));
        var input = make('input','psr-nav-search');
        input.type = 'search'; input.placeholder = 'Cerca una funzione...';
        input.setAttribute('aria-label','Cerca una funzione nel menu'); input.autocomplete = 'off';
        tools.appendChild(input);
        var known = [], sections = [], anchor = null;
        groups.forEach(function(g) {
            var members = [];
            g[2].forEach(function(t) { items.forEach(function(li) { if (type(li) === t) { members.push(li); } }); });
            if (!members.length) { return; }
            var header = make('li','psr-nav-heading'), button = make('button','psr-nav-group',g[1]);
            button.type = 'button'; header.appendChild(button);
            members.forEach(function(li) { known.push(li); });
            if (members.some(active)) { closed[g[0]] = false; }
            sections.push({key:g[0],header:header,button:button,members:members});
        });
        // I nodi nativi vengono spostati, mai clonati: eventi e sottomenu restano intatti.
        // Conserviamo la posizione originale per poter ripristinare il menu su schermi piccoli.
        var records = known.map(function(li) {
            var marker = document.createComment('psr-nav-position');
            menu.insertBefore(marker, li);
            return {li:li,marker:marker};
        });
        for (var i=0;i<items.length;i++) {
            if (known.indexOf(items[i]) !== -1) { anchor = records.find(function(r){return r.li === items[i];}).marker; break; }
        }
        var media = window.matchMedia('(min-width: 961px)'), grouped = false, failed = false;
        function matches(li, q) {
            var label = li.querySelector('.wp-menu-name');
            if (!q || norm(label ? label.textContent : li.querySelector('a.menu-top').textContent).indexOf(q) !== -1) { return true; }
            return Array.from(li.querySelectorAll('.wp-submenu a')).some(function(a) { return norm(a.textContent).indexOf(q) !== -1; });
        }
        function apply() {
            if (!grouped) { return; }
            var q = norm(input.value);
            items.forEach(function(li) { li.classList.toggle('psr-nav-hidden', !matches(li,q)); });
            sections.forEach(function(s) {
                var open = !!q || closed[s.key] !== true;
                s.button.setAttribute('aria-expanded', open ? 'true' : 'false');
                s.header.classList.toggle('psr-nav-hidden', !!q && !s.members.some(function(li){return matches(li,q);}));
                s.members.forEach(function(li) { li.classList.toggle('psr-nav-hidden', !open || !matches(li,q)); });
            });
        }
        sections.forEach(function(s) {
            s.button.addEventListener('click',function() {
                if (input.value) { input.value = ''; }
                closed[s.key] = s.button.getAttribute('aria-expanded') === 'true';
                save(); apply();
            });
        });
        input.addEventListener('input', apply);
        input.addEventListener('keydown', function(e) { if(e.key === 'Escape') { input.value = ''; apply(); } });
        // Anche un errore a meta' del riordino ripristina i nodi nativi.
        function restore() {
            records.forEach(function(r) {
                if (r.marker.parentNode === menu) { menu.insertBefore(r.li,r.marker.nextSibling); }
            });
            sections.forEach(function(s){s.header.remove();});
            items.forEach(function(li){li.classList.remove('psr-nav-hidden');});
            tools.remove(); input.value = ''; grouped = false;
        }
        function sync() {
            if (failed) { return; }
            try { syncLayout(); }
            catch(e) { failed = true; restore(); }
        }
        function syncLayout() {
            var desktop = media.matches && !document.body.classList.contains('folded');
            if (desktop && !grouped) {
                menu.insertBefore(tools,menu.firstChild);
                if (anchor) { sections.forEach(function(s) { menu.insertBefore(s.header,anchor); s.members.forEach(function(li){menu.insertBefore(li,anchor);}); }); }
                grouped = true; apply();
            } else if (!desktop && grouped) {
                restore();
            }
        }
        // Attivazione solo dopo la preparazione dei controlli; il menu nativo rimane utilizzabile.
        document.body.classList.add('psr-modern-admin');
        sync();
        if (failed) { return; }
        if (media.addEventListener) { media.addEventListener('change',sync); } else if (media.addListener) { media.addListener(sync); }
        if (window.MutationObserver) { new MutationObserver(sync).observe(document.body,{attributes:true,attributeFilter:['class']}); }
    }
    // Il menu nativo precede il contenuto della pagina: lo prepariamo appena
    // il suo ultimo controllo (collapse-button) viene inserito dal parser.
    // Se JS non parte non viene mai applicata la classe pending.
    var root = document.documentElement, observer = null, timer = null, done = false;
    function finish(prepare) {
        if (done) { return; }
        done = true;
        if (observer) { observer.disconnect(); }
        if (timer !== null) { window.clearTimeout(timer); }
        document.removeEventListener('DOMContentLoaded', onReady);
        try {
            if (prepare) { init(); }
        } catch(e) {
            // Un browser non compatibile conserva il menu originale.
        } finally {
            root.classList.remove('psr-nav-pending');
        }
    }
    function onReady() { finish(true); }
    function checkMenu() {
        var menu = document.getElementById('adminmenu');
        if (menu && menu.querySelector('#collapse-button')) { finish(true); }
    }
    if (document.readyState !== 'loading') {
        finish(true);
    } else if (window.MutationObserver) {
        // Fail-open: su una pagina insolitamente lenta non nascondiamo il menu
        // indefinitamente e non lo riorganizziamo tardivamente dopo il timeout.
        timer = window.setTimeout(function () { finish(false); }, 1500);
        document.addEventListener('DOMContentLoaded', onReady);
        observer = new MutationObserver(checkMenu);
        observer.observe(root, {childList:true, subtree:true});
        root.classList.add('psr-nav-pending');
        checkMenu();
    } else {
        document.addEventListener('DOMContentLoaded', onReady);
    }
})();
JS
    );
}
add_action('admin_enqueue_scripts', 'psr_modern_admin_enqueue', 100);
