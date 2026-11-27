<?php
/**
 * PSR – menu e barra amministrativa opzionali.
 * Nessuna modifica alle capability, ai link o ai menu registrati da WordPress.
 */
if (!defined('ABSPATH')) { exit; }

function psr_modern_admin_is_owner() {
    return get_current_user_id() === 1 && current_user_can('manage_options');
}
function psr_modern_admin_enabled() {
    return !(defined('PSR_ADMIN_CLASSIC_UI') && PSR_ADMIN_CLASSIC_UI)
        && get_option('psr_modern_admin_ui', '0') === '1';
}
function psr_modern_admin_config_screen() {
    return is_admin() && !is_network_admin() && !is_user_admin()
        && isset($_GET['page']) && $_GET['page'] === 'dci_options';
}
function psr_modern_admin_save() {
    if (!psr_modern_admin_config_screen() || !psr_modern_admin_is_owner()
        || !isset($_POST['psr_modern_admin_save'])) { return; }
    check_admin_referer('psr_modern_admin_save', 'psr_modern_admin_nonce');
    $value = isset($_POST['psr_modern_admin_value']) && $_POST['psr_modern_admin_value'] === '1' ? '1' : '0';
    update_option('psr_modern_admin_ui', $value, false);
    wp_safe_redirect(admin_url('admin.php?page=dci_options'));
    exit;
}
add_action('admin_init', 'psr_modern_admin_save', 90);

function psr_modern_admin_setting() {
    if (!psr_modern_admin_config_screen() || !psr_modern_admin_is_owner()) { return; }
    $forced = defined('PSR_ADMIN_CLASSIC_UI') && PSR_ADMIN_CLASSIC_UI;
    echo '<div class="notice notice-info" style="padding:12px 16px;margin-top:16px">';
    echo '<p><strong>Interfaccia amministrativa PSR</strong></p>';
    echo '<form method="post" action="' . esc_url(admin_url('admin.php?page=dci_options')) . '">';
    wp_nonce_field('psr_modern_admin_save', 'psr_modern_admin_nonce');
    echo '<input type="hidden" name="psr_modern_admin_save" value="1">';
    echo '<label for="psr_modern_admin_value">Stile del menu e della barra superiore: </label> ';
    echo '<select name="psr_modern_admin_value" id="psr_modern_admin_value"' . disabled($forced, true, false) . '>';
    echo '<option value="0"' . selected(get_option('psr_modern_admin_ui', '0'), '0', false) . '>WordPress classico (predefinito)</option>';
    echo '<option value="1"' . selected(get_option('psr_modern_admin_ui', '0'), '1', false) . '>PSR moderno</option></select> ';
    if (!$forced) { submit_button('Salva stile', 'secondary', 'psr_modern_admin_submit', false); }
    echo '<p class="description">La scelta vale per questo portale. Non modifica contenuti, abilitazioni o accessi. Puoi tornare allo stile originale in qualsiasi momento.</p>';
    if ($forced) { echo '<p>Stile classico imposto dalla configurazione di emergenza.</p>'; }
    echo '</form></div>';
}
add_action('admin_notices', 'psr_modern_admin_setting');

function psr_modern_admin_enqueue() {
    if (!psr_modern_admin_enabled() || is_network_admin() || is_user_admin()
        || (defined('IFRAME_REQUEST') && IFRAME_REQUEST)) { return; }
    wp_register_style('psr-modern-admin', false, array(), '1.0.0');
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
    body.psr-modern-admin:not(.folded) #adminmenu .wp-menu-name { padding-top: 10px; padding-bottom: 10px; }
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
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-group { display: flex; align-items: center; justify-content: space-between; width: 100%; border: 0; border-radius: 4px; background: transparent; color: var(--psr-muted); padding: 6px 3px; cursor: pointer; font: 600 10px/1.4 system-ui,sans-serif; text-transform: uppercase; letter-spacing: .045em; text-align: left; }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-group:hover { color: #fff; background: var(--psr-hover); }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-group:after { content: '\203A'; font-size: 17px; line-height: 1; }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-group[aria-expanded="true"]:after { transform: rotate(90deg); }
    body.psr-modern-admin:not(.folded) #adminmenu .psr-nav-hidden { display: none; }
    body.psr-modern-admin:not(.folded) #wpadminbar .ab-top-menu > li > .ab-item { padding-left: 9px; padding-right: 9px; }
}
CSS
    );
    wp_register_script('psr-modern-admin', false, array('jquery'), '1.0.0', true);
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
            ['amministrazione','Amministrazione',['unita_organizzativa','persona_pubblica','incarico','punto_contatto']],
            ['territorio','Servizi e territorio',['servizio','fase','luogo','progetto']],
            ['trasparenza','Documenti e trasparenza',['documento_pubblico','elemento_trasparenza','dataset','commissario','bando','titolari_di_incarichi_collaborazione_consulenza']],
            ['operativa','Gestione operativa',['pratica','documento_privato','messaggio','pagamento','appuntamento','richiesta_assistenza','segnala_disservizio','domanda_frequente','rating']]
        ];
        var items = Array.from(menu.children).filter(function(li) {
            return li.classList.contains('menu-top') && !!li.querySelector('a.menu-top');
        });
        function type(li) {
            try { var u = new URL(li.querySelector('a.menu-top').href, document.baseURI);
                return /\/edit\.php$/.test(u.pathname) ? u.searchParams.get('post_type') : null;
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
        var media = window.matchMedia('(min-width: 961px)'), grouped = false;
        function matches(li, q) {
            var label = li.querySelector('.wp-menu-name');
            return !q || norm(label ? label.textContent : li.querySelector('a.menu-top').textContent).indexOf(q) !== -1;
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
        function sync() {
            var desktop = media.matches && !document.body.classList.contains('folded');
            if (desktop && !grouped) {
                menu.insertBefore(tools,menu.firstChild);
                if (anchor) { sections.forEach(function(s) { menu.insertBefore(s.header,anchor); s.members.forEach(function(li){menu.insertBefore(li,anchor);}); }); }
                grouped = true; apply();
            } else if (!desktop && grouped) {
                records.forEach(function(r){ menu.insertBefore(r.li,r.marker.nextSibling); });
                sections.forEach(function(s){s.header.remove();});
                items.forEach(function(li){li.classList.remove('psr-nav-hidden');});
                tools.remove(); input.value = ''; grouped = false;
            }
        }
        // Attivazione solo dopo la preparazione dei controlli; il menu nativo rimane utilizzabile.
        document.body.classList.add('psr-modern-admin');
        sync();
        if (media.addEventListener) { media.addEventListener('change',sync); } else if (media.addListener) { media.addListener(sync); }
        if (window.MutationObserver) { new MutationObserver(sync).observe(document.body,{attributes:true,attributeFilter:['class']}); }
    }
    if (document.readyState === 'loading') { document.addEventListener('DOMContentLoaded',init); } else { init(); }
})();
JS
    );
}
add_action('admin_enqueue_scripts', 'psr_modern_admin_enqueue', 100);
