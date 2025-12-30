<?php
// ===============================
// MENU ADMIN
// ===============================
function wpc_accessi_admin_menu() {
    add_menu_page(
        'Accessi Homepage',       // Titolo pagina
        'Accessi Homepage',       // Titolo menu
        'manage_options',         // Permessi (solo admin)
        'wpc-accessi',            // Slug menu
        'wpc_accessi_admin_page', // Funzione che genera contenuto
        'dashicons-visibility',   // Icona
        80                        // Posizione menu
    );
}
add_action('admin_menu', 'wpc_accessi_admin_menu');

// ===============================
// PAGINA ADMIN: TABELLA ACCESSI
// ===============================
function wpc_accessi_admin_page() {

    if (!current_user_can('manage_options')) return;

    $logs = get_option('wpc_access_log', array());

    echo '<div class="wrap"><h1>Accessi Homepage</h1>';

    if (empty($logs)) {
        echo '<p>Nessun accesso registrato.</p></div>';
        return;
    }

    echo '<table class="widefat fixed striped">';
    echo '<thead>
            <tr>
                <th>Data</th>
                <th>Ora</th>
                <th>IP</th>
                <th>User Agent</th>
            </tr>
          </thead><tbody>';

    foreach (array_reverse($logs) as $log) {
        echo '<tr>
                <td>'.esc_html($log['date']).'</td>
                <td>'.esc_html($log['time']).'</td>
                <td>'.esc_html($log['ip']).'</td>
                <td style="font-size:11px">'.esc_html($log['ua']).'</td>
              </tr>';
    }

    echo '</tbody></table></div>';
}

