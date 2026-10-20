<?php
/**
 * PSR Security Guard — contenimento degli indicatori segnalati dal proprietario.
 * Integra la protezione del tema; NON protegge richieste dirette a file PHP.
 * I file isolati vengono spostati FUORI dalla document root, mai cancellati.
 */
if (!defined('ABSPATH')) { exit; }

function psr_guard_signatures() {
    return array(
        '618493861791064b9f394f06af7e6d538fc46e25555fc398af8e5d6a1f20a483' => 'translanugos.php',
        '20f91dbe6ded4b46c57f054608379b0e582c4e190277212923c0a37d89b562a7' => 'toddlingness.php',
        '57f33eb6ae24f9f52c9fa21ae43ddcc90a9d06bc5912f70fe9cb586760becbf3' => 'prequiche.php',
    );
}

function psr_guard_matches_slakersest($file) {
    if (!is_file($file) || is_link($file) || @filesize($file) > 1024 * 1024) { return false; }
    $contents = @file_get_contents($file);
    if (!is_string($contents)) { return false; }
    foreach (array('pelycogram','autosportswo','subperso','opacity:.03','function abu(','function dulbe(') as $marker) {
        if (strpos($contents, $marker) === false) { return false; }
    }
    return true;
}

function psr_guard_path_within($child, $parent) {
    return $child === $parent || strpos($child, rtrim($parent, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) === 0;
}

/**
 * Isola i file fuori dal web. Fail closed se la cartella sicura non è disponibile.
 * Si può impostare PSR_GUARD_QUARANTINE_BASE in wp-config.php per Plesk.
 */
function psr_guard_quarantine_base() {
    $docroot = isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
    if (!$docroot) { return false; }
    $base = defined('PSR_GUARD_QUARANTINE_BASE') ? PSR_GUARD_QUARANTINE_BASE : dirname($docroot) . DIRECTORY_SEPARATOR . '.psr-security-quarantine';
    if (!is_string($base) || $base === '' || (substr($base, 0, 1) !== DIRECTORY_SEPARATOR)) { return false; }
    if (is_link($base)) { return false; }
    if (!is_dir($base) && !@mkdir($base, 0700, true)) { return false; }
    $real = realpath($base);
    if (!$real || psr_guard_path_within($real, $docroot)) { return false; }
    // Non riporre mai la quarantena dentro temi, plugin o upload WordPress.
    $content = realpath(WP_CONTENT_DIR);
    if ($content && psr_guard_path_within($real, $content)) { return false; }
    @chmod($real, 0700);
    return is_writable($real) ? $real : false;
}

/** Quarantena atomica quando possibile, con nome non eseguibile. */
function psr_guard_isolate($source, $reason, &$events) {
    if (!file_exists($source) || is_link($source)) { return false; }
    $qbase = psr_guard_quarantine_base();
    if (!$qbase) {
        $events[] = 'NON ISOLATO (quarantena fuori web non disponibile): ' . $source;
        return false;
    }
    $isdir = is_dir($source);
    $name = preg_replace('/[^a-zA-Z0-9._-]/', '_', basename($source));
    $suffix = $isdir ? '.folder.quarantined' : '.file.quarantined';
    try { $token = bin2hex(random_bytes(6)); } catch (Throwable $e) { return false; }
    $target = $qbase . DIRECTORY_SEPARATOR . gmdate('Ymd_His') . '_' . $token . '_' . $name . $suffix;
    // Il nome di destinazione non termina in .php, né si trova nella document root.
    if (@rename($source, $target)) {
        $events[] = 'ISOLATO ' . $reason . ': ' . $source . ' => ' . $target;
        return true;
    }
    $events[] = 'ISOLAMENTO FALLITO ' . $reason . ': ' . $source;
    return false;
}

function psr_guard_verify_file($file, $name, &$events, &$detections, &$removed) {
    if (!is_file($file) || is_link($file) || !is_readable($file)) { return; }
    $size = @filesize($file);
    if ($size === false || $size > 1024 * 1024) {
        $events[] = 'FILE NON ANALIZZATO (dimensione): ' . $file;
        return;
    }
    $hash = @hash_file('sha256', $file);
    $signatures = psr_guard_signatures();
    $matched = is_string($hash) && isset($signatures[$hash]);
    $variant = !$matched && $name === 'slakersest.php' && psr_guard_matches_slakersest($file);
    if ($matched || $variant) {
        $detections++;
        $before = @lstat($file);
        $again = @hash_file('sha256', $file);
        $after = @lstat($file);
        if (!$before || !$after || $before['dev'] !== $after['dev'] || $before['ino'] !== $after['ino'] ||
            $again !== $hash || is_link($file) || ($variant && !psr_guard_matches_slakersest($file))) {
            $events[] = 'FILE CAMBIATO DURANTE LA VERIFICA: ' . $file;
            return;
        }
        if (psr_guard_isolate($file, $matched ? 'SHA256 confermato' : 'variante slakersest confermata', $events)) { $removed++; }
        return;
    }
    if (in_array($name, array('translanugos.php','toddlingness.php','prequiche.php','slakersest.php',
        'overcoqu.php','wp-e92754ad0d8a.php','wp-b697fed82d65.php'), true)) {
        $events[] = 'FILE SOSPETTO DA VERIFICARE (non isolato): ' . $file;
    }
}

/**
 * I nomi di queste 2 cartelle e di overcoqu.php sono stati indicati dal
 * proprietario come installazioni non autorizzate. Si isola SOLO il percorso
 * esatto dentro plugins, mai altri plugin né tutta mu-plugins.
 * Interruttore di emergenza: define('PSR_GUARD_AUTO_ISOLATE_NAMED_PLUGIN_PATHS', false);
 */
function psr_guard_targeted_scan(&$events, &$detections, &$removed) {
    $content = realpath(WP_CONTENT_DIR);
    if (!$content) { return; }
    $plugins = realpath(WP_PLUGIN_DIR);
    if ($plugins && psr_guard_path_within($plugins, $content)) {
        foreach (array('wp-link-helper', 'wordpress-security') as $name) {
            $path = $plugins . DIRECTORY_SEPARATOR . $name;
            if (!is_dir($path) || is_link($path)) { continue; }
            $detections++;
            if (!defined('PSR_GUARD_AUTO_ISOLATE_NAMED_PLUGIN_PATHS') || PSR_GUARD_AUTO_ISOLATE_NAMED_PLUGIN_PATHS) {
                if (psr_guard_isolate($path, 'plugin nominato come non autorizzato', $events)) { $removed++; }
            } else { $events[] = 'PLUGIN SOSPETTO NON ISOLATO (modalità segnalazione): ' . $path; }
        }
        $namedfile = $plugins . DIRECTORY_SEPARATOR . 'overcoqu.php';
        if (is_file($namedfile) && !is_link($namedfile)) {
            $detections++;
            if (!defined('PSR_GUARD_AUTO_ISOLATE_NAMED_PLUGIN_PATHS') || PSR_GUARD_AUTO_ISOLATE_NAMED_PLUGIN_PATHS) {
                if (psr_guard_isolate($namedfile, 'file nominato come non autorizzato', $events)) { $removed++; }
            } else { $events[] = 'FILE SOSPETTO NON ISOLATO (modalità segnalazione): ' . $namedfile; }
        }
    }
    $mu = $content . DIRECTORY_SEPARATOR . 'mu-plugins';
    if (is_dir($mu) && !is_link($mu)) {
        // NON rimuovere mai mu-plugins/wp-toolkit.php o mu-plugins/wp-toolkit/.
        foreach (array('wp-e92754ad0d8a.php', 'wp-b697fed82d65.php') as $name) {
            $candidate = $mu . DIRECTORY_SEPARATOR . $name;
            if (is_file($candidate)) {
                psr_guard_verify_file($candidate, $name, $events, $detections, $removed);
            }
        }
    }
}

function psr_guard_scan() {
    if (get_transient('psr_guard_running')) { return; }
    set_transient('psr_guard_running', 1, 10 * MINUTE_IN_SECONDS);
    $started = microtime(true); $checked = 0; $detections = 0; $removed = 0; $events = array();
    try {
        psr_guard_targeted_scan($events, $detections, $removed);
        $root = realpath(ABSPATH);
        if (!$root) { throw new RuntimeException('Root WordPress non disponibile'); }
        $dir = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
        $it = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($it as $item) {
            if ($checked >= 20000 || microtime(true) - $started >= 12 || count($events) >= 100) { break; }
            if ($item->isLink() || !$item->isFile()) { continue; }
            $checked++;
            $name = strtolower($item->getFilename());
            if ($name === 'wpsecurity.log') {
                $events[] = 'LOG DA PROTEGGERE E CONSERVARE (NON ELIMINATO): ' . $item->getPathname();
                continue;
            }
            if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'php') { continue; }
            psr_guard_verify_file($item->getPathname(), $name, $events, $detections, $removed);
        }
    } catch (Throwable $e) { $events[] = 'Scansione interrotta: ' . $e->getMessage(); }
    $report = array('when' => gmdate('c'), 'checked' => $checked, 'detections' => $detections,
        'removed' => $removed, 'events' => array_slice($events, 0, 100));
    update_option('psr_guard_last_report', $report, false);
    foreach ($report['events'] as $event) { error_log('[PSR GUARD] ' . $event); }
    delete_transient('psr_guard_running');
}

function psr_guard_install_schedule() {
    if (!wp_next_scheduled('psr_guard_scan')) {
        wp_schedule_event(time() + 30, 'hourly', 'psr_guard_scan');
    }
}
add_action('init', 'psr_guard_install_schedule', 20);
add_action('psr_guard_scan', 'psr_guard_scan');

/** Breve controllo mirato ogni 5 minuti, senza scansione ricorsiva del sito. */
function psr_guard_quick_scan() {
    if (get_transient('psr_guard_quick_scan_cooldown')) { return; }
    set_transient('psr_guard_quick_scan_cooldown', 1, 5 * MINUTE_IN_SECONDS);
    $events = array(); $det = 0; $iso = 0;
    psr_guard_targeted_scan($events, $det, $iso);
    if ($events) {
        foreach ($events as $event) { error_log('[PSR GUARD QUICK] ' . $event); }
        if (count($events) > 0) {
            update_option('psr_guard_quick_report', array('when' => gmdate('c'), 'events' => $events), false);
        }
    }
}
add_action('init', 'psr_guard_quick_scan', 30);

function psr_guard_admin_notice() {
    if (!current_user_can('manage_options')) { return; }
    $report = get_option('psr_guard_last_report');
    $quick = get_option('psr_guard_quick_report');
    if ((!is_array($report) || empty($report['events'])) && (!is_array($quick) || empty($quick['events']))) { return; }
    echo '<div class="notice notice-warning"><p><strong>PSR Security Guard:</strong> possibili file non autorizzati rilevati o isolati. Verificare i log PHP e la quarantena su Plesk. Nessun elemento di wp-toolkit è stato rimosso automaticamente.</p></div>';
}
add_action('admin_notices', 'psr_guard_admin_notice');
