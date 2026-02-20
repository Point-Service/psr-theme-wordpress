<?php
// =======================================
// Gestione WebApp mobile (menu + permessi + token ts/sig)
// - Menu visibile solo a chi ha capability: dci_manage_webapp_mobile
// - Modifica URL SOLO per user ID = 1
// - Bottone apre la WebApp aggiungendo ?ts=...&sig=...
// - Token compatibile con il tuo ASP (NO crypto)
// =======================================

define('DCI_WEBAPP_CAP', 'dci_manage_webapp_mobile');
define('DCI_WEBAPP_OPT', 'dci_webapp_mobile_url');

// 🔐 Chiave segreta condivisa con ASP (deve essere IDENTICA)
if (!defined('DCI_WEBAPP_SECRET')) {
  define('DCI_WEBAPP_SECRET', 'tonyluca');
}

// stessa tolleranza dell'ASP (in secondi)
if (!defined('DCI_WEBAPP_MAX_SKEW_SEC')) {
  define('DCI_WEBAPP_MAX_SKEW_SEC', 120);
}

/**
 * (Opzionale) assegna la capability all'amministratore così vedi subito il menu.
 * Poi la gestisci via User Role Editor.
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

      // blocca la modifica per chiunque non sia user_id=1
      if (get_current_user_id() !== 1) {
        return (string) get_option(DCI_WEBAPP_OPT, '');
      }

      $value = trim((string)$value);
      if ($value === '') return '';

      // se inseriscono senza schema, aggiunge https://
      if (!preg_match('~^https?://~i', $value)) {
        $value = 'https://' . $value;
      }

      return esc_url_raw($value);
    },
    'default' => '',
  ]);
});

/**
 * Genera la sig compatibile con ASP:
 * expected = Asc(Left(SECRET,1)) & Len(SECRET) & (ts Mod 97)
 */
function dci_make_sig_for_ts($ts) {
  $secret = (string) DCI_WEBAPP_SECRET;
  if ($secret === '') {
    // se segreto vuoto (non dovrebbe), evita warning
    $first = 0;
  } else {
    $first = ord($secret[0]); // Asc(Left(secret,1))
  }

  $len = strlen($secret);
  $mod = ((int)$ts) % 97;

  return (string)$first . (string)$len . (string)$mod;
}

/**
 * Handler: genera ts/sig e fa redirect alla WebApp
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

  $ts = time();

  // nonce casuale (16 char)
  $n = wp_generate_password(16, false, false);

  // se nel tuo URL hai già Ente=..., lo leggiamo da lì per la firma
  $parsed = wp_parse_url($webapp_url);
  $ente = '';
  if (!empty($parsed['query'])) {
    parse_str($parsed['query'], $q);
    if (!empty($q['Ente'])) $ente = (string)$q['Ente'];
    if (!empty($q['ente'])) $ente = (string)$q['ente'];
  }

  if ($ente === '') {
    wp_die('Nell’URL WebApp deve esserci Ente=... (serve per la firma).');
  }

  // SumAscii(n) in PHP
  $sum = 0;
  for ($i=0; $i<strlen($n); $i++) $sum += ord($n[$i]);

  $secret = (string) DCI_WEBAPP_SECRET;

  // expected = Asc(firstSecret) & Len(secret) & (ts mod 997) & Len(n) & (SumAscii(n) mod 997) & Len(ente)
  $sig = (string)ord($secret[0])
       . (string)strlen($secret)
       . (string)($ts % 997)
       . (string)strlen($n)
       . (string)($sum % 997)
       . (string)strlen($ente);

  $sep = (strpos($webapp_url, '?') === false) ? '?' : '&';
  $target = $webapp_url
          . $sep . 'ts=' . rawurlencode((string)$ts)
          . '&n=' . rawurlencode($n)
          . '&sig=' . rawurlencode($sig);

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
            <th scope="row">
              <label for="<?php echo esc_attr(DCI_WEBAPP_OPT); ?>">URL WebApp</label>
            </th>
            <td>
              <input
                type="text"
                id="<?php echo esc_attr(DCI_WEBAPP_OPT); ?>"
                name="<?php echo esc_attr(DCI_WEBAPP_OPT); ?>"
                value="<?php echo esc_attr($webapp_url); ?>"
                class="regular-text"
                placeholder="https://assistenza.servizipa.cloud/appcomuni/pannello_admin.asp?Ente=nomeente"
              />
              <p class="description">Solo l’utente con ID=1 può modificare questo valore.</p>
              <p class="description">Pin per sbloccare i campi protetti 170186</p>
              <?php submit_button('Salva URL'); ?>
            </td>
          </tr>
        </table>
      </form>
    <?php else: ?>
      <p><em>URL WebApp configurato dall’amministratore (utente ID=1). Non hai permessi per modificarlo.</em></p>

      <p style="margin-top:8px; color:#666;">
        Il pulsante genera un token temporaneo (valido ~<?php echo (int)DCI_WEBAPP_MAX_SKEW_SEC; ?>s) e poi reindirizza alla WebApp.
        Parametri aggiunti: <code>ts</code> e <code>sig</code>.
      </p>

      <?php if (!empty($webapp_url)): ?>
        <p><code><?php echo esc_html($webapp_url); ?></code></p>
      <?php else: ?>
        <p><em>URL non configurato.</em></p>
      <?php endif; ?>
    <?php endif; ?>

    <div style="margin-top:16px;">
      <?php if (!empty($webapp_url)) : ?>
        <a target="_black" href="<?php echo esc_url(admin_url('admin-post.php?action=dci_webapp_open')); ?>"
           class="button button-primary button-hero">
          Apri pannello WebApp
        </a>
      <?php endif; ?>
    </div>






<h2>Pianifica Notifica Push</h2>

<form method="post">

  <?php wp_nonce_field('dci_push_plan', 'dci_push_nonce'); ?>

  <table class="form-table">

    <tr>
      <th>Titolo</th>
      <td>
        <input type="text" name="dci_title" required class="regular-text">
      </td>
    </tr>

    <tr>
      <th>Messaggio</th>
      <td>
        <textarea name="dci_msg" required class="large-text"></textarea>
      </td>
    </tr>

    <tr>
      <th>Data e Ora</th>
      <td>
        <input type="datetime-local" name="dci_time" required>
      </td>
    </tr>

  </table>

  <?php submit_button('Pianifica Notifica'); ?>

</form>







    
    <hr>
  </div>
  <?php
}


// ===============================
// CRON INVIO NOTIFICHE PUSH
// ===============================

add_action('dci_send_scheduled_push', 'dci_send_scheduled_push_callback');

function dci_send_scheduled_push_callback($data) {

  $webapp_url = get_option(DCI_WEBAPP_OPT);

  if (empty($webapp_url)) return;

  $title = urlencode($data['title']);
  $msg   = urlencode($data['msg']);

  // genera token
  $ts = time();
  $n  = wp_generate_password(16, false, false);

  // Legge Ente
  $parsed = wp_parse_url($webapp_url);
  parse_str($parsed['query'] ?? '', $q);

  $ente = $q['Ente'] ?? $q['ente'] ?? '';

  if (!$ente) return;

  // SumAscii
  $sum = 0;
  for ($i=0; $i<strlen($n); $i++) {
    $sum += ord($n[$i]);
  }

  $secret = DCI_WEBAPP_SECRET;

  $sig =
      ord($secret[0])
    . strlen($secret)
    . ($ts % 997)
    . strlen($n)
    . ($sum % 997)
    . strlen($ente);

  // Costruzione URL
  $sep = (strpos($webapp_url, '?') === false) ? '?' : '&';

  $url = $webapp_url . $sep .
    'ts=' . $ts .
    '&n=' . $n .
    '&sig=' . $sig .
    '&notificapush=ok' .
    '&Titolo=' . $title .
    '&msg=' . $msg;

  // Chiamata HTTP
  wp_remote_get($url, [
    'timeout' => 15
  ]);
}

// ===============================
// SALVATAGGIO PIANIFICAZIONE
// ===============================

add_action('admin_init', function() {

  if (!isset($_POST['dci_push_nonce'])) return;

  if (!wp_verify_nonce($_POST['dci_push_nonce'], 'dci_push_plan')) return;

  if (!current_user_can(DCI_WEBAPP_CAP)) return;

  $title = sanitize_text_field($_POST['dci_title']);
  $msg   = sanitize_textarea_field($_POST['dci_msg']);
  $time  = sanitize_text_field($_POST['dci_time']);

  if (!$title || !$msg || !$time) return;

  $timestamp = strtotime($time);

  if ($timestamp <= time()) return;

  $data = [
    'title' => $title,
    'msg'   => $msg
  ];

  // Pulisce eventuali doppioni
  wp_clear_scheduled_hook('dci_send_scheduled_push', [$data]);
  if (wp_next_scheduled('dci_send_scheduled_push', [$data])) {
    return;
  }

  // Pianifica
  wp_schedule_single_event(
    $timestamp,
    'dci_send_scheduled_push',
    [$data]
  );

  add_action('admin_notices', function() {
  echo '<div class="notice notice-success is-dismissible">
          <p>✅ Notifica push pianificata correttamente.</p>
        </div>';
});
});
