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
/**
 * Colori del login coordinati con l'header istituzionale.
 * I Comuni personalizzano spesso .it-header-center-wrapper nel CSS aggiuntivo.
 * Qui leggiamo solo valori colore, senza caricare l'HTML/CSS del sito pubblico
 * sulla schermata sensibile di autenticazione.
 */
function psr_login_color_hex($value, $custom_css = '') {
    if (!is_string($value)) { return ''; }
    $value = trim($value);
    // Consente variabili locali del CSS aggiuntivo, con al massimo 2 livelli.
    for ($i = 0; $i < 2 && preg_match('/^var\(\s*(--[a-zA-Z0-9_-]+)\s*(?:,\s*([^()]+))?\s*\)$/', $value, $m); $i++) {
        $variable = preg_quote($m[1], '/');
        if (preg_match_all('/' . $variable . '\s*:\s*([^;}]+)/i', $custom_css, $matches) && !empty($matches[1])) {
            $value = trim(end($matches[1]));
        } elseif (!empty($m[2])) {
            $value = trim($m[2]);
        } else { return ''; }
    }
    if (preg_match('/^#([0-9a-f]{3}|[0-9a-f]{6})$/i', $value, $m)) {
        $hex = strtolower($m[1]);
        if (strlen($hex) === 3) {
            return '#' . $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        return '#' . $hex;
    }
    // Forme RGB semplici utilizzate dai browser e nei CSS personalizzati.
    if (preg_match('/^rgba?\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})(?:\s*,\s*(1(?:\.0+)?|0?\.\d+|0))?\s*\)$/i', $value, $m)) {
        if (max((int)$m[1], (int)$m[2], (int)$m[3]) > 255) { return ''; }
        if (isset($m[4]) && (float)$m[4] < 1) { return ''; }
        return sprintf('#%02x%02x%02x', (int)$m[1], (int)$m[2], (int)$m[3]);
    }
    return '';
}
function psr_login_header_color() {
    $css = function_exists('wp_get_custom_css') ? (string)wp_get_custom_css() :
        (function_exists('get_custom_css') ? (string)get_custom_css() : '');
    $css = preg_replace('~/\*.*?\*/~s', '', $css);
    $chosen = '';
    $best = 0;
    // Cerchiamo gli stessi livelli d'intestazione utilizzati dal tema.
    if (preg_match_all('/([^{}]+)\{([^{}]*)\}/s', $css, $rules, PREG_SET_ORDER)) {
        foreach ($rules as $rule) {
            $selector = $rule[1];
            if (strpos($selector, '.it-header-center-wrapper') !== false) { $priority = 3; }
            elseif (strpos($selector, '.it-header-navbar-wrapper') !== false) { $priority = 2; }
            elseif (strpos($selector, '.it-header-slim-wrapper') !== false) { $priority = 1; }
            else { continue; }
            if (preg_match('/:(hover|focus|active|before|after)\b/i', $selector)) { continue; }
            if (!preg_match_all('/(?:^|;)\s*background(?:-color)?\s*:\s*([^;]+)/i', $rule[2], $declarations)) { continue; }
            foreach ($declarations[1] as $declaration) {
                $declaration = trim(preg_replace('/\s*!important\s*$/i', '', $declaration));
                $hex = psr_login_color_hex($declaration, $css);
                // Per header con gradiente personalizzato usiamo la prima tinta solida.
                if ($hex === '' && strpos($declaration, 'gradient(') !== false
                    && preg_match('/#[0-9a-fA-F]{6}(?![0-9a-fA-F])|#[0-9a-fA-F]{3}(?![0-9a-fA-F])/', $declaration, $match)) {
                    $hex = psr_login_color_hex($match[0], $css);
                }
                if ($hex !== '' && $priority >= $best) {
                    $chosen = $hex;
                    $best = $priority;
                }
            }
        }
    }
    if ($chosen === '') {
        // Colore istituzionale predefinito di Bootstrap Italia.
        $chosen = '#0066cc';
    }
    // Override controllato, utile in un sito con header generato da CSS esterni.
    $overridden = apply_filters('psr_login_institutional_header_color', $chosen);
    $safe = psr_login_color_hex($overridden, $css);
    return $safe !== '' ? $safe : $chosen;
}
function psr_login_color_rgb($hex) {
    return array(hexdec(substr($hex, 1, 2)), hexdec(substr($hex, 3, 2)), hexdec(substr($hex, 5, 2)));
}
function psr_login_color_mix($hex, $target, $ratio) {
    $a = psr_login_color_rgb($hex);
    $b = psr_login_color_rgb($target);
    $result = array();
    for ($i = 0; $i < 3; $i++) { $result[] = (int)round($a[$i] * (1 - $ratio) + $b[$i] * $ratio); }
    return sprintf('#%02x%02x%02x', $result[0], $result[1], $result[2]);
}
function psr_login_color_contrast_white($hex) {
    $rgb = psr_login_color_rgb($hex);
    $linear = array();
    foreach ($rgb as $value) {
        $v = $value / 255;
        $linear[] = $v <= .04045 ? $v / 12.92 : pow(($v + .055) / 1.055, 2.4);
    }
    $luminance = .2126 * $linear[0] + .7152 * $linear[1] + .0722 * $linear[2];
    return 1.05 / ($luminance + .05);
}
function psr_login_color_accessible($hex, $minimum) {
    $value = $hex;
    // Tonalita' sempre della stessa famiglia cromatica, ma con contrasto sufficiente.
    for ($i = 0; $i < 20 && psr_login_color_contrast_white($value) < $minimum; $i++) {
        $value = psr_login_color_mix($value, '#000000', .08);
    }
    return $value;
}
function psr_login_enqueue_header_palette() {
    if (!wp_style_is('psr-login', 'enqueued')) { return; }
    $original = psr_login_header_color();
    $button = psr_login_color_accessible($original, 4.7);
    $panel = psr_login_color_accessible(psr_login_color_mix($original, '#0d243a', .28), 8.5);
    $hover = psr_login_color_mix($button, '#000000', .16);
    $panel_dark = psr_login_color_mix($panel, '#000000', .2);
    $panel_light = psr_login_color_mix($panel, '#ffffff', .12);
    $soft = psr_login_color_mix($original, '#ffffff', .94);
    $border = psr_login_color_mix($original, '#ffffff', .78);
    $highlight = psr_login_color_mix($original, '#ffffff', .70);
    $declarations = array(
        '--psr-header-original' => $original,
        '--psr-header-button' => $button,
        '--psr-header-hover' => $hover,
        '--psr-header-panel' => $panel,
        '--psr-header-panel-dark' => $panel_dark,
        '--psr-header-panel-light' => $panel_light,
        '--psr-header-soft' => $soft,
        '--psr-header-border' => $border,
        '--psr-header-highlight' => $highlight,
    );
    $parts = array();
    foreach ($declarations as $name => $value) { $parts[] = $name . ':' . $value; }
    // Soli esadecimali normalizzati: nessun CSS arbitrario dal database.
    wp_add_inline_style('psr-login', 'body.login{' . implode(';', $parts) . '}');
}
add_action('login_enqueue_scripts', 'psr_login_enqueue_header_palette', 50);

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
