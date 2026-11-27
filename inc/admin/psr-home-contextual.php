<?php
/**
 * PSR – scorciatoie editoriali della home e delle sezioni interne. Opt-in per sito, nessuna modifica
 * a ruoli, CPT, query pubbliche o salvataggi dei contenuti.
 */
if (!defined('ABSPATH')) { exit; }

function psr_home_tools_enabled() {
    return !(defined('PSR_HOME_TOOLS_DISABLED') && PSR_HOME_TOOLS_DISABLED)
        && get_option('psr_home_contextual_tools', '0') === '1';
}
/** Le protezioni PSR esistenti verificano la sessione prima del rendering. */
function psr_home_tools_session() {
    if (!is_user_logged_in() || !psr_home_tools_enabled()) { return false; }
    if (!function_exists('psr_login_requires_otp') || !function_exists('psr_login_session_verified')) { return false; }
    // La sessione e' gia' verificata dai guard PSR; memorizza il risultato
    // solo per la richiesta corrente, non fra utenti o richieste AJAX.
    static $checked = array();
    $user = wp_get_current_user();
    $uid = (int) $user->ID;
    if ($uid <= 0) { return false; }
    if (!array_key_exists($uid, $checked)) {
        $checked[$uid] = !psr_login_requires_otp($user) || psr_login_session_verified($uid);
    }
    return $checked[$uid];
}
function psr_home_tools_begin() {
    if (!psr_home_tools_session()) { return; }
    $GLOBALS['psr_home_tools_rendering'] = true;
    if (!headers_sent()) { nocache_headers(); }
}
function psr_home_tools_scope() {
    if (!psr_home_tools_session()) { return false; }
    if (!empty($GLOBALS['psr_home_tools_rendering']) && !is_admin()) { return true; }
    // Solo i frammenti home gia' ammessi dal dispatcher pubblico; nessun nuovo endpoint.
    if (!wp_doing_ajax() || !function_exists('dci_is_async_template_request')
        || !isset($_POST['action']) || !is_string($_POST['action'])
        || (isset($_POST['template_key']) && !is_string($_POST['template_key']))) { return false; }
    foreach (array('home-notizie','home-calendario','home-servizi','home-argomenti','home-gallery',
        'novita-evidenza','novita-tutte','servizi-evidenza','servizi-tutti','eventi-tutti',
        'luoghi-evidenza','luoghi-tutti','vivere-eventi','vivere-luoghi','amministrazione-evidenza',
        'uffici-tutti','aree-amministrative-tutte','organi-governo-tutti','enti-fondazioni-tutti','politici-tutti','personale-amministrativo-tutti') as $key) {
        if (dci_is_async_template_request($key)) {
            if (!headers_sent()) { nocache_headers(); }
            return true;
        }
    }
    if (isset($_POST['action'], $_POST['load_card_type']) && is_string($_POST['action']) && is_string($_POST['load_card_type'])
        && $_POST['action'] === 'load_more'
        && in_array($_POST['load_card_type'], array('notizia','servizio','categoria_servizio','luogo','personale-amministrativo'), true)) {
        if (!headers_sent()) { nocache_headers(); }
        return true;
    }
    return false;
}
function psr_home_tools_style() {
    if (empty($GLOBALS['psr_home_tools_rendering']) || !psr_home_tools_session()) { return; }
    wp_register_style('psr-home-contextual-tools', false, array(), '1.0.4');
    wp_enqueue_style('psr-home-contextual-tools');
    wp_add_inline_style('psr-home-contextual-tools', '
.psr-home-actions{display:flex;flex-wrap:wrap;align-items:center;gap:5px;padding:5px 0;max-width:100%;position:relative}
.psr-home-section-actions{margin:0 auto;padding:6px 12px;max-width:1280px}
.psr-home-action-label{font-size:12px;font-weight:700;color:#28445d;margin-right:4px}
a.psr-home-action{display:inline-flex!important;align-items:center;gap:4px;min-height:26px;padding:3px 7px;border:1px solid #aac2d9;border-radius:6px;background:#fff;color:#164c80!important;font:600 12px/1.3 system-ui,sans-serif;text-decoration:none!important;white-space:normal;max-width:100%;position:relative;z-index:2;box-shadow:none}
a.psr-home-action:hover{background:#e8f2fd;color:#103c65!important}
a.psr-home-action:focus-visible{outline:2px solid #0b5da7;outline-offset:2px}
.psr-home-action svg{width:14px;height:14px;fill:currentColor;flex:none;pointer-events:none}
.psr-home-item-actions{display:inline-flex;vertical-align:middle;gap:3px;padding:2px 0;margin-inline-start:4px;position:relative;z-index:3;pointer-events:auto!important}
.psr-home-item-actions a.psr-home-action{box-sizing:border-box;justify-content:center;width:26px;height:26px;min-height:26px;padding:0;flex:none;border-radius:5px;pointer-events:auto!important;cursor:pointer}
.psr-home-item-actions a.psr-home-action svg{width:14px;height:14px}
.argomento-card .category-top .psr-home-item-actions{flex:none}
.argomento-card .psr-home-item-actions,.argomento-card .psr-home-item-actions a.psr-home-action{pointer-events:auto!important}
.psr-home-action.psr-home-icon-only svg{fill:none;stroke:currentColor;stroke-width:1.8;stroke-linecap:round;stroke-linejoin:round}
.gallery-item > .psr-home-item-actions{position:absolute;top:8px;right:8px;z-index:5}
/* Gli avvisi colorano tutti gli SVG: isolare la matita, non le icone pubbliche. */
.home-message .msg .psr-home-item-actions{vertical-align:middle;align-items:center;padding:0;margin-inline-start:8px}
.home-message .msg .psr-home-item-actions a.psr-home-icon-only{background:#fff!important;color:#164c80!important;border-color:#aac2d9!important}
.home-message .msg .psr-home-item-actions a.psr-home-icon-only:hover{background:#e8f2fd!important}
.home-message .msg .psr-home-item-actions a.psr-home-icon-only svg{background:transparent!important;color:inherit!important;fill:none!important;stroke:currentColor!important;width:14px!important;height:14px!important;margin:0!important;display:block}
.home-message .msg .psr-home-item-actions a.psr-home-icon-only svg path{fill:none!important;stroke:currentColor!important}
/* Accesso rapido: la matita resta sorella del link pubblico, dentro il
   perimetro della card. Nessun anchor annidato o sovrapposizione ai titoli. */
#accesso-rapido .custom-styles > div:has(> .psr-home-item-actions){position:relative}
#accesso-rapido .custom-styles > div:has(> .psr-home-item-actions) > a.card{padding-bottom:36px!important}
#accesso-rapido .custom-styles > div > .psr-home-item-actions{position:absolute;inset:auto calc(var(--bs-gutter-x, 24px) / 2 + 8px) 7px auto;z-index:4;margin:0;padding:0}
@media(max-width:576px){#accesso-rapido .custom-styles > div:has(> .psr-home-item-actions) > a.card{padding-bottom:44px!important}}
/* Card e matita condividono lo stesso stato hover, pur restando link distinti. */
@media (hover:hover) and (pointer:fine){
    #accesso-rapido .custom-styles > div:has(> .psr-home-item-actions) > .psr-home-item-actions{transition:transform 0.3s ease}
    #accesso-rapido .custom-styles > div:has(> .psr-home-item-actions):hover > a.card{transform:translateY(-5px);box-shadow:0 4px 10px rgba(0,0,0,0.2)}
    #accesso-rapido .custom-styles > div:has(> .psr-home-item-actions):hover > .psr-home-item-actions{transform:translateY(-5px)}
}
@media (prefers-reduced-motion:reduce){
    #accesso-rapido .custom-styles > div:has(> .psr-home-item-actions) > a.card,
    #accesso-rapido .custom-styles > div:has(> .psr-home-item-actions) > .psr-home-item-actions{transition:none!important;transform:none!important}
}
/* Browser senza :has: conservare il comando in flusso anziche sovrapporlo. */
@supports not selector(:has(*)){#accesso-rapido .custom-styles > div > .psr-home-item-actions{position:relative;inset:auto;margin:4px 0}}

@media(max-width:576px){a.psr-home-action{min-height:32px}.psr-home-item-actions a.psr-home-action{width:34px;height:34px;min-height:34px}.psr-home-section-actions{padding:6px 10px}}
/* Siti tematici: stesso posizionamento di Accesso rapido. Il link di
   modifica resta esterno al link della card, ma visivamente al suo interno. */
@supports selector(:has(*)){
    .card-wrapper:has(> a.sito-tematico-card):has(> .psr-home-item-actions){position:relative}
    .card-wrapper:has(> a.sito-tematico-card):has(> .psr-home-item-actions) > a.sito-tematico-card{padding-bottom:36px!important;min-width:0}
    .card-wrapper:has(> a.sito-tematico-card) > .psr-home-item-actions{position:absolute;inset:auto calc(var(--bs-gutter-x, 24px) / 2 + 8px) 8px auto;z-index:4;margin:0;padding:0}
    @media(max-width:576px){
        .card-wrapper:has(> a.sito-tematico-card):has(> .psr-home-item-actions) > a.sito-tematico-card{padding-bottom:44px!important}
    }
    @media(hover:hover) and (pointer:fine){
        .card-wrapper:has(> a.sito-tematico-card) > .psr-home-item-actions{transition:transform 0.3s ease}
        .card-wrapper:has(> a.sito-tematico-card):has(> .psr-home-item-actions):hover > a.sito-tematico-card{transform:translateY(-5px);box-shadow:0 4px 10px rgba(0,0,0,0.2);background-color:#e0e0e0}
        .card-wrapper:has(> a.sito-tematico-card):has(> .psr-home-item-actions):hover > a.sito-tematico-card .card-title{color:#0056b3}
        .card-wrapper:has(> a.sito-tematico-card):hover > .psr-home-item-actions{transform:translateY(-5px)}
    }
    @media(prefers-reduced-motion:reduce){
        .card-wrapper:has(> a.sito-tematico-card):has(> .psr-home-item-actions) > a.sito-tematico-card,
        .card-wrapper:has(> a.sito-tematico-card) > .psr-home-item-actions{transition:none!important;transform:none!important}
    }
}
');
}
add_action('wp_enqueue_scripts', 'psr_home_tools_style', 100);

/** Allowlist: solo tipologie pubbliche esplicitamente supportate. */
function psr_home_tools_type($type) {
    if (!in_array($type, array('notizia','evento','servizio','sito_tematico','galleria','luogo','documento_pubblico','progetto','page','unita_organizzativa','persona_pubblica'), true)) { return null; }
    $object = get_post_type_object($type);
    return $object && !empty($object->show_ui) && is_post_type_viewable($object) ? $object : null;
}
function psr_home_tools_type_can($type, $operation) {
    $object = psr_home_tools_type($type);
    return $object && isset($object->cap->{$operation})
        && $object->cap->{$operation} !== 'do_not_allow'
        && current_user_can($object->cap->{$operation});
}
function psr_home_tools_can_create($type) {
    // post-new.php richiede entrambi i permessi, anche quando sono distinti.
    return psr_home_tools_type_can($type, 'edit_posts') && psr_home_tools_type_can($type, 'create_posts');
}
function psr_home_tools_option_url($page, $anchor = '') {
    if (!current_user_can('manage_options') || !function_exists('new_cmb2_box')) { return ''; }
    $allowed = array('dci_options','homepage','home_messages','strip_home','servizi','accesso_rapido','Galleria','link_utili','assistenza','footer','socials','ricerca','novita','luoghi','vivi','amministrazione');
    if (!in_array($page, $allowed, true)) { return ''; }
    return add_query_arg('page', $page, admin_url('admin.php')) . ($anchor !== '' ? '#' . rawurlencode($anchor) : '');
}
function psr_home_tools_link($url, $label, $icon = 'pencil', $icon_only = false) {
    if (!$url) { return; }
    $icon = in_array($icon, array('pencil','plus','settings','list'), true) ? $icon : 'pencil';
    echo '<a class="psr-home-action' . ($icon_only ? ' psr-home-icon-only' : '') . '" href="' . esc_url($url)
        . '" title="' . esc_attr($label) . '" aria-label="' . esc_attr($label) . '">';
    if ($icon_only && $icon === 'pencil') {
        // Icona autonoma: non dipende dallo sprite SVG della singola pagina.
        echo '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M12 20H21M16 4L20 8M4 20L5 15L17 3A2.8 2.8 0 0 1 21 7L9 19Z"/></svg>';
    } else {
        echo '<svg aria-hidden="true" focusable="false"><use href="#it-' . esc_attr($icon) . '"></use></svg>';
    }
    if (!$icon_only) { echo esc_html($label); }
    echo '</a>';
}

/** Barra di sezione, anche quando il contenuto pubblico e' vuoto. */
function psr_home_tools_section($section) {
    if (!psr_home_tools_scope()) { return; }
    $sections = array(
        'novita-highlight' => array('Novità in evidenza', '', 'novita', 'contenuti_evidenziati', 'Configura evidenza'),
        'novita-all' => array('Tutte le notizie', 'notizia', '', '', ''),
        'services-highlight' => array('Servizi in evidenza', '', 'servizi', 'servizi_evidenziati', 'Configura evidenza'),
        'services-all' => array('Tutti i servizi', 'servizio', '', '', ''),
        'places-highlight' => array('Luoghi in evidenza', '', 'luoghi', 'luoghi_evidenziati', 'Configura evidenza'),
        'places-all' => array('Tutti i luoghi', 'luogo', '', '', ''),
        'vivi-events' => array('Eventi in evidenza', 'evento', 'vivi', 'eventi_evidenziati', 'Configura evidenza'),
        'vivi-places' => array('Luoghi in evidenza', 'luogo', 'vivi', 'luoghi_evidenziati', 'Configura evidenza'),
        'admin-highlight' => array('Amministrazione in evidenza', '', 'amministrazione', 'notizia_evidenziata', 'Configura evidenza'),
        'units' => array('Unità organizzative', 'unita_organizzativa', '', '', ''),
        'people' => array('Persone pubbliche', 'persona_pubblica', '', '', ''),
        'header' => array('Intestazione', '', 'dci_options', '', 'Configura intestazione'),
        'banner' => array('Immagini iniziali', '', 'homepage', 'immagine', 'Gestisci immagini'),
        'messages' => array('Avvisi in home', '', 'home_messages', 'messages_repeat', 'Aggiungi / gestisci avvisi'),
        'news' => array('Notizie', 'notizia', 'homepage', 'ck_notizie_automatico', 'Configura evidenza'),
        'events' => array('Eventi', 'evento', '', '', ''),
        'topics' => array('Argomenti', '', 'homepage', 'argomenti_evidenziati_1_repeat', 'Configura argomenti'),
        'strip' => array('Strip Home', '', 'strip_home', 'strip_items_repeat', 'Aggiungi / gestisci collegamenti'),
        'services' => array('Servizi', 'servizio', 'servizi', 'servizi_evidenziati', 'Configura evidenza'),
        'thematic' => array('Siti tematici', 'sito_tematico', 'homepage', 'siti_tematici', 'Configura selezione'),
        'quick' => array('Accesso rapido', '', 'accesso_rapido', 'quickboxes_repeat', 'Aggiungi / gestisci box'),
        'gallery' => array('Galleria', '', 'Galleria', '', 'Configura galleria'),
        'links' => array('Link utili', '', 'link_utili', 'link_repeat', 'Aggiungi / gestisci link'),
        'map' => array('Mappa', 'luogo', 'homepage', 'ck_show_map', 'Configura mappa'),
        'weather' => array('Meteo', '', 'dci_options', 'meteo_city', 'Configura meteo'),
        'assistance' => array('Assistenza e contatti', '', 'assistenza', 'numero_verde', 'Configura assistenza'),
        'footer' => array('Footer', '', 'footer', '', 'Configura footer'),
        'socials' => array('Social network', '', 'socials', '', 'Configura social')
    );
    if (!isset($sections[$section])) { return; }
    list($title, $type, $page, $anchor, $config_label) = $sections[$section];
    if ($section === 'gallery' && function_exists('dci_get_option')
        && dci_get_option('stile_galleria','galleria') === 'foto-gallery') { $type = 'galleria'; }
    // L'elenco Maggioli e' alimentato esternamente: non offrire creazione locale.
    if ($section === 'services-all' && function_exists('dci_get_option')
        && strlen((string) dci_get_option('servizi_maggioli_url', 'servizi')) >= 5) { $type = ''; }
    $new = $type && !in_array($section, array('units','people'), true) && psr_home_tools_can_create($type) ? add_query_arg('post_type', $type, admin_url('post-new.php')) : '';
    $manage = $type && psr_home_tools_type_can($type, 'edit_posts') ? add_query_arg('post_type', $type, admin_url('edit.php')) : '';
    $config = $page ? psr_home_tools_option_url($page, $anchor) : '';
    $tax = $section === 'topics' ? get_taxonomy('argomenti') : null;
    $tax_url = $tax && !empty($tax->show_ui) && current_user_can($tax->cap->manage_terms)
        ? add_query_arg(array('taxonomy'=>'argomenti'), admin_url('edit-tags.php')) : '';
    $menu = $section === 'header' && current_user_can('edit_theme_options') ? admin_url('nav-menus.php') : '';
    if (!$new && !$manage && !$config && !$tax_url && !$menu) { return; }
    echo '<nav class="psr-home-actions psr-home-section-actions" aria-label="' . esc_attr('Gestione ' . $title) . '">';
    echo '<span class="psr-home-action-label">' . esc_html($title) . ':</span>';
    $type_object = $type ? psr_home_tools_type($type) : null;
    $new_label = $type_object && !empty($type_object->labels->singular_name) ? 'Aggiungi ' . $type_object->labels->singular_name : 'Aggiungi';
    $manage_label = $type_object && !empty($type_object->labels->name) ? 'Gestisci ' . $type_object->labels->name : 'Gestisci';
    psr_home_tools_link($new, $new_label, 'plus');
    psr_home_tools_link($manage, $manage_label, 'list');
    psr_home_tools_link($config, $config_label, 'settings');
    psr_home_tools_link($tax_url, 'Gestisci argomenti', 'list');
    psr_home_tools_link($menu, 'Gestisci menu', 'settings');
    echo '</nav>';
}
function psr_home_tools_edit_post($post_id) {
    if (!psr_home_tools_scope()) { return; }
    if (!is_int($post_id) && !is_string($post_id)) { return; }
    if (!preg_match('/^[1-9][0-9]*$/D', (string) $post_id)) { return; }
    $post_id = absint($post_id);
    $post = $post_id ? get_post($post_id) : null;
    if (!$post instanceof WP_Post || $post->post_status !== 'publish' || $post->post_password !== ''
        || !psr_home_tools_type($post->post_type) || !current_user_can('edit_post', $post_id)) { return; }
    if (function_exists('dci_members_can_user_view_post')
        && !dci_members_can_user_view_post(get_current_user_id(), $post_id)) { return; }
    if (wp_doing_ajax() && isset($_POST['action'], $_POST['load_card_type']) && $_POST['action'] === 'load_more') {
        $types = array('notizia'=>'notizia','servizio'=>'servizio','categoria_servizio'=>'servizio','luogo'=>'luogo','personale-amministrativo'=>'persona_pubblica');
        $card_type = $_POST['load_card_type'];
        if (!is_string($card_type) || !isset($types[$card_type]) || $types[$card_type] !== $post->post_type) { return; }
    }
    $url = get_edit_post_link($post_id, 'raw');
    if (!$url) { return; }
    echo '<span class="psr-home-item-actions">';
    psr_home_tools_link($url, 'Modifica', 'pencil', true);
    echo '</span>';
}
function psr_home_tools_edit_topic($term) {
    if (!psr_home_tools_scope() || !$term instanceof WP_Term || $term->taxonomy !== 'argomenti'
        || !current_user_can('edit_term', $term->term_id)) { return; }
    $url = get_edit_term_link($term->term_id, 'argomenti');
    if (!$url) { return; }
    echo '<span class="psr-home-item-actions">';
    psr_home_tools_link($url, 'Modifica argomento', 'pencil', true);
    echo '</span>';
}

/** Righe ripetibili: riferimento opaco, ricalcolato sui dati correnti nel backend. */
function psr_home_tools_groups() {
    return array('accesso_rapido'=>'quickboxes','link_utili'=>'link','home_messages'=>'messages','strip_home'=>'strip_items');
}
function psr_home_tools_row_signature($row) {
    return is_array($row) ? hash_hmac('sha256', serialize($row), wp_salt('nonce')) : '';
}
function psr_home_tools_edit_row($page, $row) {
    if (!psr_home_tools_scope() || !current_user_can('manage_options')) { return; }
    $groups = psr_home_tools_groups();
    if (!isset($groups[$page]) || !is_array($row)) { return; }
    $url = psr_home_tools_option_url($page);
    if (!$url) { return; }
    $sig = psr_home_tools_row_signature($row);
    $url = add_query_arg('psr_home_row', $sig, $url) . '#' . $groups[$page] . '_repeat';
    echo '<span class="psr-home-item-actions">';
    psr_home_tools_link($url, 'Modifica elemento', 'pencil', true);
    echo '</span>';
}
function psr_home_tools_focus_row() {
    if (!psr_home_tools_session() || !current_user_can('manage_options') || is_network_admin() || is_user_admin()
        || !isset($_GET['page'], $_GET['psr_home_row']) || !is_string($_GET['page']) || !is_string($_GET['psr_home_row'])) { return; }
    $page = sanitize_key(wp_unslash($_GET['page']));
    $sig = wp_unslash($_GET['psr_home_row']);
    $groups = psr_home_tools_groups();
    if (!isset($groups[$page]) || !preg_match('/^[a-f0-9]{64}$/D', $sig)) { return; }
    $opts = get_option($page);
    $rows = is_array($opts) && isset($opts[$groups[$page]]) ? $opts[$groups[$page]] : null;
    // CMB2 genera gli ID con indici consecutivi. In caso di ambiguita' resta il gruppo.
    if (!is_array($rows) || !$rows || array_keys($rows) !== range(0, count($rows)-1)) { return; }
    $matches = array();
    foreach ($rows as $i => $row) {
        if (hash_equals(psr_home_tools_row_signature($row), $sig)) { $matches[] = $i; }
    }
    if (count($matches) !== 1) { return; }
    $id = 'cmb-group-' . $groups[$page] . '-' . $matches[0];
    echo '<script>(function(){var id=' . wp_json_encode($id, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)
        . ';function focusRow(){var row=document.getElementById(id);if(!row||!row.classList.contains("cmb-repeatable-grouping"))return;row.classList.remove("closed");row.style.outline="3px solid #2271b1";row.style.outlineOffset="2px";row.scrollIntoView({block:"start"});}if(document.readyState==="loading"){document.addEventListener("DOMContentLoaded",focusRow);}else{focusRow();}})();</script>';
}
add_action('admin_footer', 'psr_home_tools_focus_row', 100);

/** Attivazione solo nelle pagine pubbliche esplicitamente supportate. */
function psr_home_tools_internal_begin() {
    if (!psr_home_tools_session() || is_admin() || is_404() || is_preview()) { return; }
    $templates = array('novita','eventi','servizi','luoghi','vivere-il-comune','amministrazione','uffici','aree-amministrative','organi-governo','enti-e-fondazioni','politici','personale-amministrativo');
    $templates = array_map(static function($name) { return 'page-templates/' . $name . '.php'; }, $templates);
    if (is_page_template($templates) || is_post_type_archive('notizia')
        || is_tax(array('tipi_notizia','categorie_servizio','tipi_evento'))
        || is_singular(array('notizia','evento','servizio'))) { psr_home_tools_begin(); }
}
add_action('template_redirect','psr_home_tools_internal_begin', 99);

function psr_home_tools_category($type) {
    if (!psr_home_tools_scope() || !psr_home_tools_type($type)) { return; }
    $term = get_queried_object();
    $allowed = array('notizia'=>'tipi_notizia','servizio'=>'categorie_servizio','evento'=>'tipi_evento');
    if (!$term instanceof WP_Term || !isset($allowed[$type]) || $term->taxonomy !== $allowed[$type]) { return; }
    $tax = get_taxonomy($term->taxonomy);
    if (!$tax || empty($tax->show_ui) || !is_object_in_taxonomy($type, $term->taxonomy)) { return; }
    $external = $type === 'servizio' && function_exists('dci_get_option')
        && strlen((string) dci_get_option('servizi_maggioli_url', 'servizi')) >= 5;
    $new = !$external && psr_home_tools_can_create($type) ? add_query_arg('post_type', $type, admin_url('post-new.php')) : '';
    $manage = !$external && psr_home_tools_type_can($type, 'edit_posts') ? add_query_arg('post_type', $type, admin_url('edit.php')) : '';
    $edit = current_user_can('edit_term', $term->term_id) ? get_edit_term_link($term->term_id, $term->taxonomy) : '';
    if (!$new && !$manage && !$edit) { return; }
    $object = psr_home_tools_type($type);
    echo '<nav class="psr-home-actions psr-home-section-actions" aria-label="Gestione contenuti">';
    psr_home_tools_link($new, 'Aggiungi ' . $object->labels->singular_name, 'plus');
    psr_home_tools_link($manage, 'Gestisci ' . $object->labels->name, 'list');
    psr_home_tools_link($edit, 'Modifica categoria', 'pencil');
    echo '</nav>';
}
