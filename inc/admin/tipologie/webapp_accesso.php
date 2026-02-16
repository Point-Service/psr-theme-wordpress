<?php
// =======================================
// Admin page: Gestione WebApp mobile
// =======================================

add_action('admin_menu', function () {
  add_menu_page(
    'Gestione WebApp mobile',
    'Gestione WebApp mobile',
    'manage_options',
    'dci_webapp_mobile',
    'dci_webapp_mobile_page',
    'dashicons-smartphone',
    58
  );
});

add_action('admin_init', function () {
  register_setting('dci_webapp_mobile_group', 'dci_webapp_mobile_url', [
    'type'              => 'string',
    'sanitize_callback' => function ($value) {
      $value = trim((string)$value);
      if ($value === '') return '';
      // Se incollano senza schema
      if (!preg_match('~^https?://~i', $value)) $value = 'https://' . $value;
      return esc_url_raw($value);
    },
    'default' => '',
  ]);
});

function dci_webapp_mobile_find_ure_url() {
  // Slug più comuni del plugin User Role Editor
  $candidates = [
    admin_url('users.php?page=user-role-editor'),
    admin_url('users.php?page=users-user-role-editor'),
    admin_url('admin.php?page=user-role-editor'),
    admin_url('admin.php?page=users-user-role-editor'),
  ];

  // Non possiamo verificare con certezza quale esiste senza conoscere il plugin,
  // quindi restituiamo il primo candidato e mostriamo anche un link alternativo.
  return $candidates;
}

function dci_webapp_mobile_page() {
  if (!current_user_can('manage_options')) return;

  $webapp_url = get_option('dci_webapp_mobile_url', '');
  $ure_links = dci_webapp_mobile_find_ure_url();

  ?>
  <div class="wrap">
    <h1>Gestione WebApp mobile</h1>

    <p>Da qui puoi impostare l’URL del pannello WebApp e gestire gli accessi tramite ruoli e permessi (User Role Editor).</p>

    <hr>

    <h2>URL pannello WebApp</h2>
    <form method="post" action="options.php">
      <?php settings_fields('dci_webapp_mobile_group'); ?>
      <table class="form-table" role="presentation">
        <tr>
          <th scope="row"><label for="dci_webapp_mobile_url">URL WebApp</label></th>
          <td>
            <input
              type="text"
              id="dci_webapp_mobile_url"
              name="dci_webapp_mobile_url"
              value="<?php echo esc_attr($webapp_url); ?>"
              class="regular-text"
              placeholder="https://webapp.comune.it/admin"
            />
            <p class="description">Incolla l’URL del pannello di gestione WebApp mobile.</p>
          </td>
        </tr>
      </table>
      <?php submit_button('Salva URL'); ?>
    </form>

    <div style="margin-top:16px;">
      <?php if (!empty($webapp_url)) : ?>
        <a href="<?php echo esc_url($webapp_url); ?>" class="button button-primary button-hero" target="_blank" rel="noopener noreferrer">
          Apri pannello WebApp
        </a>
      <?php else: ?>
        <p><em>Inserisci e salva l’URL della WebApp per visualizzare il pulsante.</em></p>
      <?php endif; ?>
    </div>

    <hr>

    <h2>Gestione accessi (User Role Editor)</h2>
    <p>Usa User Role Editor per concedere/rimuovere permessi ai ruoli che possono accedere alle pagine di gestione.</p>

    <div style="display:flex; gap:12px; flex-wrap:wrap; margin-top:12px;">
      <a href="<?php echo esc_url($ure_links[0]); ?>" class="button button-secondary">
        Apri User Role Editor
      </a>
      <a href="<?php echo esc_url($ure_links[1]); ?>" class="button">
        Link alternativo URE
      </a>
    </div>

    <p style="margin-top:10px; color:#666;">
      Se cliccando non si apre la pagina corretta, dimmi quale URL vedi nella barra quando apri User Role Editor,
      e lo imposto fisso.
    </p>

  </div>
  <?php
}
