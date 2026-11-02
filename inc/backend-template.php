<?php



// includo i singoli file di template di backend
foreach (glob(get_template_directory() ."/inc/admin/*.php") as $filename)
{
	require $filename;
}

//includo comuni_config.php
require get_template_directory()."/inc/comuni_config.php";


//custom js icone bootstrap -- fontawsome icon picker
function dci_icon_script() {

    if (!dci_is_theme_admin_screen()) { return; }

    wp_register_script( 'dci-icon-script', get_template_directory_uri() . '/inc/admin-js/admin.js', array('jquery'), false, true);

    wp_enqueue_script('dci-icon-script');

    $dci_data =   array( 'stylesheet_directory_uri' => get_template_directory_uri() );

    wp_localize_script( 'dci-icon-script', 'dci_data', $dci_data );

}

/** Usa le risorse singole del core: load-styles/load-scripts possono essere negati dal server. */
function psr_individual_core_assets() {
    $GLOBALS['concatenate_scripts'] = false;
}
/**
 * Non consentire sessioni persistenti dal login WordPress.
 * Prima che wp-login.php e l'OTP PSR leggano il flag ricordami,
 * lo eliminiamo lato server (anche da richieste POST costruite manualmente).
 * Non interferisce con i cookie di sessione e con l'OTP obbligatorio.
 */
function psr_login_disable_remember_me() {
    unset($_POST['rememberme'], $_REQUEST['rememberme']);
}
add_action('login_init', 'psr_login_disable_remember_me', -1000);

add_action('login_init', 'psr_individual_core_assets', 5);
add_action('admin_init', 'psr_individual_core_assets', 5);

/** Logo configurabile con lo stemma gia' presente nella configurazione Comune. */
function psr_login_logo($text = '') {
    $options = get_option('dci_options', array());
    $logo = is_array($options) && isset($options['stemma_comune']) && is_string($options['stemma_comune'])
        ? $options['stemma_comune'] : '';
    if ($logo === '') {
        $logo = wp_get_attachment_image_url((int)get_theme_mod('custom_logo'), 'medium');
    }
    if (!$logo) { $logo = get_template_directory_uri() . '/assets/img/logo-point.webp'; }
    return '<img class="psr-login-logo" src="' . esc_url($logo) . '" alt="" width="72" height="72">'
        . '<span class="psr-login-portal">' . esc_html(get_bloginfo('name')) . '</span>'
        . '<span class="psr-login-caption">Area riservata</span>';
}
function smallenvelop_login_message( $message ) {
    // I messaggi core/plugin (recupero, errori, OTP) devono restare intatti.
    return $message . '<p class="psr-login-webmail"><span>Webmail istituzionale</span>'
        . '<a href="https://webmailssl.it">Apri webmail</a></p>';
}
function my_login_logo_url() {
    return home_url();
}

function dci_enqueue_stylesheets() {
    $relative = '/assets/css/psr-login.css';
    $local = get_template_directory() . $relative;
    if (is_readable($local)) {
        wp_enqueue_style('psr-login', get_template_directory_uri() . $relative,
            array('login'), (string)filemtime($local));
    }
}
add_action( 'admin_enqueue_scripts', 'dci_icon_script' );
add_action( 'login_enqueue_scripts', 'dci_enqueue_stylesheets' );
/** Il layout mantiene i form WordPress originali e raggruppa solo link e lingua. */
function psr_login_layout_script() {
    $relative = '/assets/js/psr-login-layout.js';
    $local = get_template_directory() . $relative;
    if (is_readable($local)) {
        wp_enqueue_script('psr-login-layout', get_template_directory_uri() . $relative,
            array(), (string) filemtime($local), false);
    }
}
add_action('login_enqueue_scripts', 'psr_login_layout_script');

/** CAPTCHA aggiuntivo opzionale: in sua assenza resta attivo quello esistente. */
$psr_captcha_upgrade = get_template_directory() . '/inc/psr-captcha-upgrade.php';
if (is_readable($psr_captcha_upgrade)) { require_once $psr_captcha_upgrade; }

add_filter( 'login_headerurl', 'my_login_logo_url' );
add_filter( 'login_headertext', 'psr_login_logo' );
add_filter( 'login_message', 'smallenvelop_login_message' );

/**
 * Sezione istituzionale puramente visiva: non sostituisce i form di WordPress.
 * login_header stampa questo elemento all'interno del body, prima di #login.
 * Nessun wrapper aperto, input spostato, script o modifica ai controlli OTP/CAPTCHA.
 */
function psr_login_render_brand_panel() {
    global $interim_login;
    // I login temporanei incorporati devono mantenere il layout WordPress originale.
    if (!empty($interim_login)) { return; }

    $site_name = get_bloginfo('name');
    echo '<aside class="psr-login-brand" aria-label="Informazioni sul portale">';
    echo '<div class="psr-brand-inner">';
    echo '<span class="psr-brand-mark" aria-hidden="true"><span class="psr-brand-mark-inner"></span></span>';
    echo '<span class="psr-brand-eyebrow">PORTALE ISTITUZIONALE</span>';
    echo '<p class="psr-brand-title">Uno spazio dedicato.<br><span>Un accesso protetto.</span></p>';
    echo '<p class="psr-brand-description">Accedi all\'area riservata di <strong>' . esc_html($site_name) . '</strong> per gestire le attivit&agrave; amministrative.</p>';
    echo '<div class="psr-brand-assurance"><span class="psr-assurance-dot" aria-hidden="true"></span> Accesso riservato agli utenti autorizzati</div>';
    echo '<p class="psr-brand-footer">SERVIZI DIGITALI &middot; AREA AMMINISTRATIVA</p>';
    echo '</div></aside>';
}
add_action('login_header', 'psr_login_render_brand_panel', 20);
