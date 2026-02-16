<?php
// =======================================
// Gestione WebApp mobile (menu + permessi)
// - Menu visibile solo a chi ha capability: dci_manage_webapp_mobile
// - Modifica URL SOLO per user ID = 1
// =======================================

define('DCI_WEBAPP_CAP', 'dci_manage_webapp_mobile');
define('DCI_WEBAPP_OPT', 'dci_webapp_mobile_url');

/**
 * (Opzionale ma consigliato) assegna la capability all'amministratore (role)
 * così vedi subito il menu. Poi la gestisci via User Role Editor.
 */
add_action('init', function () {
  $role = get_role('administrator');
  if ($role && !$role->has_cap(DCI_WEBAPP_CAP)) {
    $role->add_cap(DCI_WEBAPP_CAP);
  }
}, 20);

/**
 * Crea voce di menu admin:
 * - se non hai la capability -> NON vedi proprio il menu
 */
add_action('admin_menu', function () {
  add_menu_page(
    'Gestione WebApp mobile',
    'Gestione WebApp mobile',
    DCI_WEBAPP_CAP,            // <-- controllato via User Role Editor
    'dci_webapp_mobile',
    'dci_webapp_mobile_page',
    'dashicons-smartphone',
    58
  );
});

/**
 * Registra opzione URL ma:
 * - Solo user ID=1 può salvare (protezione lato server)
 */
add_action('admin_init', function () {
  register_setting('dci_webapp_mobile_group', DCI_WEBAPP_OPT, [
    'type' => 'string',
    'sanitize_callback' => function ($value) {
      // blocca la modifica per chiunque non sia user_id=1
      if (get_current_user_id() !== 1) {
        // restituisco il valore già salvato (non cambia nulla)
        return (string) get_option(DCI_WEBAPP_OPT, '');
      }

      $value = trim((string)$value);
      if ($value === '') return '';

      if (!preg_match('~^https?://~i', $value)) {
        $value = 'https://' . $value;
      }
      return esc_url_raw($value);
    },
    'default' => '',
  ]);
});

/**
 * Pagina admin
 * - Accesso pagina: solo chi ha capability (già gestito dal menu, ma doppio check)
 * - Form modifica URL: SOLO user_id=1
 */
function dci_webapp_mobile_page() {
  if (!current_user_can(DCI_WEBAPP_CAP)) {
    wp_die('Non hai i permessi per accedere a questa pagina.');
  }

  $webapp_url = (string) get_option(DCI_WEBAPP_OPT, '');
  $can_edit_url = (get_current_user_id() === 1);

  // Link User Role Editor (slug più comuni)
  $ure_links = [
    admin_url('users.php?page=user-role-editor'),
    admin_url('admin.php?page=user-role-editor'),
    admin_url('users.php?page=users-user-role-editor'),
    admin_url('admin.php?page=users-user-role-editor'),
  ];
  ?>
  <div class="wrap">
    <h1>Gestione WebApp mobile</h1>

    <p>
      Questa pagina è visibile solo a chi ha il permesso:
      <code><?php echo esc_html(DCI_WEBAPP_CAP); ?></code>
      (assegnalo da <em>User Role Editor</em>).
    </p>

    <hr>

    <h2>Pannello WebApp</h2>

    <?php if ($can_edit_url): ?>
      <form method="post" action="options.php">
        <?php settings_fields('dci_webapp_mobile_group'); ?>
        <table class="form-table" role="presentation">
          <tr>
            <th scope="row"><label for="<?php echo esc_attr(DCI_WEBAPP_OPT); ?>">URL WebApp</label></th>
            <td>
              <input
                type="text"
                id="<?php echo esc_attr(DCI_WEBAPP_OPT); ?>"
                name="<?php echo esc_attr(DCI_WEBAPP_OPT); ?>"
                value="<?php echo esc_attr($webapp_url); ?>"
                class="regular-text"
                placeholder="https://webapp.comune.it/admin"
              />
              <p class="description">Solo l’utente con ID=1 può modificare questo valore.</p>
            </td>
          </tr>
        </table>
        <?php submit_button('Salva URL'); ?>
      </form>
    <?php else: ?>
      <p><em>URL WebApp configurato dall’amministratore (utente ID=1). Non hai permessi per modificarlo.</em></p>
      <?php if (!empty($webapp_url)): ?>
        <p><code><?php echo esc_html($webapp_url); ?></code></p>
      <?php else: ?>
        <p><em>URL non configurato.</em></p>
      <?php endif; ?>
    <?php endif; ?>

    <div style="margin-top:16px;">
      <?php if (!empty($webapp_url)) : ?>
        <a href="<?php echo esc_url($webapp_url); ?>"
           class="button button-primary button-hero"
           target="_blank" rel="noopener noreferrer">
          Apri pannello WebApp
        </a>
      <?php endif; ?>
    </div>

    <hr>

    <h2>Gestione permessi (User Role Editor)</h2>
    <p>
      Concedi la capability <code><?php echo esc_html(DCI_WEBAPP_CAP); ?></code> ai ruoli che devono vedere il menu.
    </p>

    <div style="display:flex; gap:12px; flex-wrap:wrap;">
      <a href="<?php echo esc_url($ure_links[0]); ?>" class="button button-secondary">Apri User Role Editor</a>
      <a href="<?php echo esc_url($ure_links[1]); ?>" class="button">Link alternativo URE</a>
    </div>

    <p style="margin-top:10px; color:#666;">
      Se i link non aprono User Role Editor, dimmi l’URL esatto che vedi quando lo apri dal tuo WP e te lo imposto fisso.
    </p>
  </div>
  <?php
}

