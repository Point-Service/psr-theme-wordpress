<?php
/**
 * PSR CAPTCHA v2 - incremento mirato della protezione del login WordPress.
 * Richiede il modulo preesistente psr-login-security.php, SENZA modificarlo.
 * Il risultato resta protetto da HMAC e non compare nel token HTML.
 * Nessuna scrittura sul database alla sola visualizzazione del login.
 * In assenza di questo file il CAPTCHA originale rimane attivo.
 */
if (!defined('ABSPATH')) { exit; }
if (!function_exists('psr_login_captcha_enabled') || !function_exists('psr_login_captcha_token_valid')) { return; }

/** Non espone gli operandi ne' il risultato nel token. Nessun transient in GET. */
function psr_captcha_v2_token($answer, $nonce, $issued) {
    $payload = (int) $issued . '|' . $nonce . '|' . (int) $answer;
    $signature = hash_hmac('sha256', 'psr-captcha-v2|' . $payload, wp_salt('auth'));
    return 'v2:' . (int) $issued . ':' . $nonce . ':' . $signature;
}
function psr_captcha_v2_form() {
    if (!psr_login_captcha_enabled() || !psr_login_captcha_native_login()) { return; }
    try {
        $a = random_int(7, 17);
        $b = random_int(2, 9);
        $subtract = random_int(0, 1) === 1;
        if ($subtract && $b > $a) { $a = $b + 2; }
        $nonce = bin2hex(random_bytes(16));
        $token = psr_captcha_v2_token($subtract ? $a - $b : $a + $b, $nonce, time());
    } catch (Throwable $e) {
        $token = '';
    }
    if ($token === '') {
        echo '<div class="message">Verifica antispam temporaneamente non disponibile. Riprova tra poco.</div>';
        // Richiediamo comunque un valore, che il validatore non accettera'.
        echo '<input type="hidden" name="psr_login_captcha_token" value="">';
        return;
    }
    echo '<div class="psr-login-captcha">';
    echo '<span class="psr-captcha-eyebrow">VERIFICA ANTISPAM</span>';
    echo '<label for="psr_login_captcha_answer">Quanto fa <strong>' . (int) $a
        . ($subtract ? ' &minus; ' : ' + ') . (int) $b . '</strong>?</label>';
    echo '<div class="psr-captcha-answer"><input type="text" inputmode="numeric" pattern="[0-9]{1,2}" maxlength="2" name="psr_login_captcha_answer" id="psr_login_captcha_answer" required autocomplete="off" aria-describedby="psr-captcha-help" placeholder="Risultato"></div>';
    echo '<span id="psr-captcha-help" class="psr-captcha-help">Risolvi questa semplice operazione per confermare l’accesso.</span>';
    echo '<input type="hidden" name="psr_login_captcha_token" value="' . esc_attr($token) . '">';
    // Honeypot gia' adottato dal vecchio modulo: stesso nome per compatibilita'.
    echo '<div class="psr-captcha-trap" aria-hidden="true"><label for="psr_login_captcha_contact">Lascia vuoto questo campo</label><input type="text" tabindex="-1" autocomplete="off" name="psr_login_captcha_contact" id="psr_login_captcha_contact" value=""></div>';
    echo '</div>';
}
function psr_captcha_v2_valid($token, $answer) {
    if (!is_string($token) || !is_string($answer) || strlen($token) > 180 || strlen($answer) > 8) { return false; }
    // Compatibilita' solo per moduli gia' aperti prima del deploy (TTL originale: 10 min).
    if (strpos($token, 'v2:') !== 0) {
        return psr_login_captcha_token_valid($token, $answer);
    }
    if (!preg_match('/\Av2:([0-9]{10,11}):([a-f0-9]{32}):([a-f0-9]{64})\z/D', $token, $m)
        || !preg_match('/\A[0-9]{1,2}\z/D', $answer)) { return false; }
    $age = time() - (int) $m[1];
    if ($age < -30 || $age > 600) { return false; }
    $payload = $m[1] . '|' . $m[2] . '|' . (int) $answer;
    $expected = hash_hmac('sha256', 'psr-captcha-v2|' . $payload, wp_salt('auth'));
    return hash_equals($expected, $m[3]);
}
function psr_captcha_v2_check($user, $username = '', $password = '') {
    if (!psr_login_captcha_enabled() || !psr_login_captcha_native_login()
        || strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST'
        || (defined('WP_CLI') && WP_CLI)) { return $user; }
    if (is_wp_error($user)) { return $user; }
    $answer = $_POST['psr_login_captcha_answer'] ?? null;
    $token = $_POST['psr_login_captcha_token'] ?? null;
    $trap = $_POST['psr_login_captcha_contact'] ?? '';
    if (!is_string($answer) || !is_string($token) || !is_string($trap) || $trap !== ''
        || !psr_captcha_v2_valid($token, trim($answer))) {
        return new WP_Error('psr_login_captcha_invalid', 'Verifica antispam non corretta. Risolvi l’operazione e riprova.');
    }
    // Non sostituisce i blocchi IP/OTP; blocca riutilizzi come il modulo precedente.
    $use_key = 'psr_captcha_used_' . hash('sha256', $token);
    if (get_transient($use_key) !== false) {
        return new WP_Error('psr_login_captcha_reused', 'Verifica antispam gia\' utilizzata. Ricarica il login e riprova.');
    }
    if (!set_transient($use_key, 1, 10 * MINUTE_IN_SECONDS)) {
        return new WP_Error('psr_login_captcha_unavailable', 'Verifica antispam non disponibile. Riprova.');
    }
    return $user;
}

// Sostituisci soltanto l'azione CAPTCHA, senza toccare filtri OTP, IP o password.
remove_action('login_form', 'psr_login_captcha_form', 10);
remove_filter('authenticate', 'psr_login_captcha_check', 12);
add_action('login_form', 'psr_captcha_v2_form', 10);
add_filter('authenticate', 'psr_captcha_v2_check', 12, 3);
