<?php
// MENU ADMIN
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

function wpc_accessi_admin_page() {
    if (!current_user_can('manage_options')) return;

    $daily_ips = get_option('wpc_home_daily_ips', array());
    $today = date('Y-m-d');
    $ips_today = $daily_ips[$today] ?? array();

    echo '<div class="wrap"><h1>Accessi oggi</h1>';

    if (empty($ips_today)) {
        echo '<p>Nessun accesso registrato oggi.</p></div>';
        return;
    }

    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>IP</th></tr></thead><tbody>';

    foreach ($ips_today as $ip) {
        echo '<tr><td>'.esc_html($ip).'</td></tr>';
    }

    echo '</tbody></table></div>';
}

}
