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

// PAGINA ADMIN: CONTATORE E DETTAGLI
function wpc_accessi_admin_page() {

    if (!current_user_can('manage_options')) return;

    $logs = get_option('wpc_access_log', array());
    $today = current_time('Y-m-d');

    // Conta accessi univoci oggi
    $count_today = 0;
    $logs_today = array();
    foreach ($logs as $log) {
        if ($log['date'] === $today) {
            $count_today++;
            $logs_today[] = $log;
        }
    }

    echo '<div class="wrap"><h1>Accessi Homepage</h1>';
    echo "<p><strong>Accessi univoci oggi:</strong> $count_today</p>";

    if (empty($logs_today)) {
        echo '<p>Nessun accesso registrato oggi.</p></div>';
        return;
    }

    // Tabella dettagli
    echo '<table class="widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>Data</th>
                <th>Ora</th>
                <th>IP</th>
                <th>User Agent</th>
            </tr>
          </thead><tbody>';

    foreach ($logs_today as $log) {
        echo '<tr>
                <td>'.esc_html($log['date']).'</td>
                <td>'.esc_html($log['time']).'</td>
                <td>'.esc_html($log['ip']).'</td>
                <td style="font-size:11px">'.esc_html($log['ua']).'</td>
              </tr>';
    }

    echo '</tbody></table></div>';
}
