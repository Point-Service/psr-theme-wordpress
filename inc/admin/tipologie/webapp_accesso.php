<?php
// =======================================
// Gestione WebApp mobile (menu + permessi + token)
// - Menu visibile solo a chi ha capability: dci_manage_webapp_mobile
// - Modifica URL SOLO per user ID = 1
// - Bottone apre la WebApp aggiungendo &token=... (token variabile a logica tempo)
// - Rimossi i 2 pulsanti verso User Role Editor (restano solo spiegazioni)
// =======================================

define('DCI_WEBAPP_CAP', 'dci_manage_webapp_mobile');
define('DCI_WEBAPP_OPT', 'dci_webapp_mobile_url');

// 🔐 Chiave segreta condivisa con ASP (lunga e casuale)
if (!defined('DCI_WEBAPP_SECRET')) {
  define('DCI_WEBAPP_SECRET', 'tonyluca');
}

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
    DCI_WEBAPP_CAP,
    'dci_webapp_mobile',
    'dci_webapp_mobile_page',
    'dashicons-smartphone',
    58
  );
});

/**
 * Registra opzione URL:
 * - Solo user ID=1 può salvare (protezione lato server)
 */
add_action('admin_init', function () {
  register_setting('dci_webapp_mobile_group', DCI_WEBAPP_OPT, [
    'type' => 'string',
    'sanitize_callback' => function ($value) {
      if (get_current_user_id() !== 1) {
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
 * Token "semplice ma sicuro" a logica tempo:
 * - cambia ogni 60 secondi
 * - non è falsificabile senza SECRET
 */
function dci_make_time_token() {
  $window = (int) floor(time() / 60); // finestra 60s
  return hash('sha256', DCI_WEBAPP_SECRET . '|' . $window);
}

/**
 * Handler: genera token e fa redirect alla WebApp aggiungendo token=
 * URL chiamato dal bottone:
 * admin-post.php?action=dci_webapp_open
 */
add_action('admin_post_dci_webapp_open', function () {

  if (!current_user_can(DCI_WEBAPP_CAP)) {
    wp_die('Non autorizzato.');
  }

  $webapp_url = (string) get_option(DCI_WEBAPP_OPT, '');
  if ($webapp_url === '') {
    wp_die('URL WebApp non configurato.');
  }

  $token = dci_make_time_token();

  // aggiunge ? o & automaticamente
  $sep = (strpos($webapp_url, '?') === false) ? '?' : '&';
  $token = substr(hash('sha256', DCI_WEBAPP_SECRET . '|' . floor(time()/60)), 0, 24);
  $sep = (strpos($webapp_url, '?') === false) ? '?' : '&';
  $target = $webapp_url . $sep . 't=' . $token;


  wp_redirect($target);
  exit;
});

/**
 * Pagina admin
 * - Accesso pagina: solo chi ha capability
 * - Form modifica URL: SOLO user_id=1
 */
function dci_webapp_mobile_page() {
  if (!current_user_can(DCI_WEBAPP_CAP)) {
    wp_die('Non hai i permessi per accedere a questa pagina.');
  }

  $webapp_url   = (string) get_option(DCI_WEBAPP_OPT, '');
  $can_edit_url = (get_current_user_id() === 1);
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
                placeholder="https://assistenza.servizipa.cloud/appcomuni/pannello"
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
        <a href="<?php echo esc_url(admin_url('admin-post.php?action=dci_webapp_open')); ?>"
           class="button button-primary button-hero">
          Apri pannello WebApp
        </a>
        <p style="margin-top:8px; color:#666;">
          Il pulsante genera un token temporaneo e poi reindirizza alla WebApp.
        </p>
      <?php endif; ?>
    </div>

    <hr>

    <h2>Gestione permessi (User Role Editor)</h2>
    <p>
      Concedi la capability <code><?php echo esc_html(DCI_WEBAPP_CAP); ?></code> ai ruoli che devono vedere il menu.
    </p>
  </div>
  <?php
}

