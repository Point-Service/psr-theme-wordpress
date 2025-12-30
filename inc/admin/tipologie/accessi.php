<?php
// ================================
// MENU ADMIN - ACCESSI HOMEPAGE AVANZATO
// ================================
function wpc_accessi_admin_menu() {
    add_menu_page(
        'Accessi Homepage',
        'Accessi Homepage',
        'manage_options',
        'wpc-accessi',
        'wpc_accessi_admin_page',
        'dashicons-visibility',
        80
    );
}
add_action('admin_menu', 'wpc_accessi_admin_menu');

// ================================
// PAGINA ADMIN CON SELEZIONE DATA
// ================================
function wpc_accessi_admin_page() {

    if (!current_user_can('manage_options')) return;

    $daily_ips = get_option('wpc_home_daily_ips', array());
    $daily_counts = get_option('wpc_home_daily_counts', array());

    // Selezione data dal GET o default oggi
    $selected_date = isset($_GET['data']) ? sanitize_text_field($_GET['data']) : date('Y-m-d');

    $ips = $daily_ips[$selected_date] ?? array();
    $count = $daily_counts[$selected_date] ?? 0;

    echo '<div class="wrap"><h1>Accessi Homepage</h1>';

    // Form calendario per selezionare il giorno
    echo '<form method="get" style="margin-bottom:15px;">';
    echo '<input type="hidden" name="page" value="wpc-accessi">';
    echo '<label for="data">Seleziona giorno: </label>';
    echo '<input type="date" id="data" name="data" value="' . esc_attr($selected_date) . '">';
    echo '<input type="submit" class="button button-primary" value="Mostra">';
    echo '</form>';

    echo "<p><strong>Accessi univoci:</strong> $count</p>";

    if (empty($ips)) {
        echo '<p>Nessun accesso registrato in questa data.</p></div>';
        return;
    }

    // Tabella IP
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>#</th><th>IP</th></tr></thead><tbody>';

    $i = 1;
    foreach ($ips as $ip) {
        echo '<tr><td>'.$i.'</td><td>'.esc_html($ip).'</td></tr>';
        $i++;
    }

    echo '</tbody></table></div>';
}

