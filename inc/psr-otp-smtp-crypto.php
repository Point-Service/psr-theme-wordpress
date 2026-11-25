<?php
/**
 * PSR OTP SMTP: authenticated encryption and local secret file handling.
 * No credentials, default server, or default encryption key in source.
 */
if (!defined('ABSPATH') && PHP_SAPI !== 'cli') { exit; }

function psr_otp_crypto_available() {
    return function_exists('sodium_crypto_aead_xchacha20poly1305_ietf_encrypt')
        && function_exists('sodium_crypto_aead_xchacha20poly1305_ietf_decrypt')
        && function_exists('sodium_memzero');
}

function psr_otp_smtp_settings_valid($settings) {
    if (!is_array($settings)) { return false; }
    $host = $settings['host'] ?? null;
    $port = $settings['port'] ?? null;
    $user = $settings['user'] ?? null;
    return is_string($host) && strlen($host) <= 253
        && preg_match('/\A(?=.{1,253}\z)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\z/iD', $host)
        && is_int($port) && in_array($port, array(465, 587), true)
        && is_string($user) && strlen($user) <= 254 && filter_var($user, FILTER_VALIDATE_EMAIL);
}

function psr_otp_smtp_aad($settings) {
    return "psr-otp-smtp-v1\n" . $settings['host'] . "\n" . $settings['port'] . "\n" . $settings['user'];
}
function psr_otp_smtp_encrypt($settings, $password, $key) {
    if (!psr_otp_crypto_available() || !psr_otp_smtp_settings_valid($settings)
        || !is_string($key) || strlen($key) !== 32
        || !is_string($password) || strlen($password) < 1 || strlen($password) > 1024
        || preg_match('/[\x00\r\n]/', $password)) { return false; }
    try {
        $nonce = random_bytes(24);
        $cipher = sodium_crypto_aead_xchacha20poly1305_ietf_encrypt($password, psr_otp_smtp_aad($settings), $nonce, $key);
        return array('version'=>1, 'host'=>$settings['host'], 'port'=>$settings['port'], 'user'=>$settings['user'],
            'nonce'=>base64_encode($nonce), 'ciphertext'=>base64_encode($cipher));
    } catch (Throwable $e) { return false; }
}
function psr_otp_smtp_decrypt($settings, $key) {
    if (!psr_otp_crypto_available() || !psr_otp_smtp_settings_valid($settings)
        || ($settings['version'] ?? null) !== 1 || !is_string($key) || strlen($key) !== 32
        || !is_string($settings['nonce'] ?? null) || strlen($settings['nonce']) !== 32
        || !is_string($settings['ciphertext'] ?? null) || strlen($settings['ciphertext']) > 1400) { return false; }
    $nonce = base64_decode($settings['nonce'], true);
    $cipher = base64_decode($settings['ciphertext'], true);
    if (!is_string($nonce) || strlen($nonce) !== 24 || !is_string($cipher) || strlen($cipher) < 17 || strlen($cipher) > 1040) { return false; }
    try {
        $password = sodium_crypto_aead_xchacha20poly1305_ietf_decrypt($cipher, psr_otp_smtp_aad($settings), $nonce, $key);
        return is_string($password) && $password !== '' && !preg_match('/[\x00\r\n]/', $password) ? $password : false;
    } catch (Throwable $e) { return false; }
}

/** Derive from explicit, installation-local wp-config keys, never database fallback salts. */
function psr_otp_smtp_site_key() {
    if (!psr_otp_crypto_available()) { return false; }
    $material = array();
    foreach (array('AUTH_KEY','SECURE_AUTH_KEY','LOGGED_IN_KEY','NONCE_KEY') as $name) {
        $v = defined($name) ? constant($name) : '';
        if (!is_string($v) || strlen($v) < 32 || stripos($v,'put your unique phrase here') !== false) { return false; }
        $material[] = $v;
    }
    if (count(array_unique($material)) < 4) { return false; }
    return hash_hkdf('sha256', implode("\0",$material), 32, 'PSR OTP SMTP DB v1|' . (int)get_current_blog_id());
}
