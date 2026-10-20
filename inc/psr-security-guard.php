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
        '65958f829ae60eccbec3d7de8812ac3dbc74d38ef53f217b0a0a1883e343deec' => 'microbearers.php',
        '20f91dbe6ded4b46c57f054608379b0e582c4e190277212923c0a37d89b562a7' => 'toddlingness.php',
        '57f33eb6ae24f9f52c9fa21ae43ddcc90a9d06bc5912f70fe9cb586760becbf3' => 'prequiche.php',
        'dd30dbc4c6d6e94cbf09368188479e6da73f4e5dcf6011f25b66a658429ca4ee' => 'MU recovery channel wp-link-helper (due copie con nomi diversi)',
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

/**
 * Famiglia delle web shell PHP camuffate con un login a opacita' 3%.
 * I nuovi nomi indicati dal proprietario, SENZA campioni allegati, non sono
 * prova sufficiente per una rimozione: isolare soltanto se il contenuto
 * presenta contemporaneamente queste caratteristiche molto specifiche.
 * Non esegue, non include e non valuta codice trovato sul disco.
 */
function psr_guard_matches_hidden_filemanager_family($file) {
    if (!is_file($file) || is_link($file)) { return false; }
    $bytes = @filesize($file);
    if ($bytes === false || $bytes < 1500 || $bytes > 1024 * 1024) { return false; }
    $body = @file_get_contents($file);
    if (!is_string($body)) { return false; }
    $markers = array('opacity:.03', 'session_start(', '$_SESSION[',
        '$_FILES[', 'scandir(', 'rawurldecode(', 'DIRECTORY_SEPARATOR',
        'htmlspecialchars(', 'fopen(', 'fwrite(', 'rename(', 'unlink(');
    foreach ($markers as $marker) {
        if (strpos($body, $marker) === false) { return false; }
    }
    // Questi campioni sono volutamente riempiti con istruzioni inattive.
    return substr_count($body, 'if(false)') >= 4;
}

/** I nuovi indicatori vengono trattati come sospetti finche' il contenuto non e' verificato. */
function psr_guard_new_shell_names() {
    return array('underpenup.php','paltererness.php','semibuxeous.php','transfraktur.php','ultraruruing.php','microbearers.php');
}

/**
 * Firma comportamentale stretta del canale di persistenza MU confermato:
 * NON basata sul nome casuale wp-<hex>.php, che da solo non prova nulla.
 * Applicabile soltanto ai PHP direttamente dentro mu-plugins.
 */
function psr_guard_matches_mu_recovery($file) {
    if (!is_file($file) || is_link($file)) { return false; }
    $bytes = @filesize($file);
    if ($bytes === false || $bytes > 1024 * 1024) { return false; }
    $body = @file_get_contents($file);
    if (!is_string($body)) { return false; }
    foreach (array('wprc_reinstall(', 'wprc_reclaim(', 'wprc_reactivate(',
        'wprc_optname(', 'WPRC_SLUG', 'wprc_plugin_dir(') as $marker) {
        if (strpos($body, $marker) === false) { return false; }
    }
    return true;
}

/** Blocca le chiamate tramite WordPress HTTP API al C2 rilevato nei campioni.
 * Non è un firewall: non intercetta chiamate cURL/stream eseguite direttamente.
 */
function psr_guard_prevent_known_egress($preempt, $args, $url) {
    if (defined('PSR_GUARD_BLOCK_KNOWN_EGRESS') && !PSR_GUARD_BLOCK_KNOWN_EGRESS) { return $preempt; }
    if ($preempt !== false || !is_string($url)) { return $preempt; }
    $host = parse_url($url, PHP_URL_HOST);
    if (!is_string($host)) { return $preempt; }
    $host = strtolower(rtrim($host, '.'));
    if ($host === 'cachelayer.top' || substr($host, -strlen('.cachelayer.top')) === '.cachelayer.top') {
        return new WP_Error('psr_guard_known_host_blocked', 'Destinazione remota non autorizzata bloccata dal controllo di sicurezza.');
    }
    return $preempt;
}
add_filter('pre_http_request', 'psr_guard_prevent_known_egress', 1, 3);

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
    $newName = in_array($name, psr_guard_new_shell_names(), true);
    $familyMatch = !$matched && !$variant && $newName && psr_guard_matches_hidden_filemanager_family($file);
    $muRoot = realpath(WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'mu-plugins');
    $isMuRootFile = $muRoot && realpath(dirname($file)) === $muRoot;
    $recovery = !$matched && !$variant && !$familyMatch && $isMuRootFile && psr_guard_matches_mu_recovery($file);
    if ($matched || $variant || $familyMatch || $recovery) {
        $detections++;
        $before = @lstat($file);
        $again = @hash_file('sha256', $file);
        $after = @lstat($file);
        if (!$before || !$after || $before['dev'] !== $after['dev'] || $before['ino'] !== $after['ino'] ||
            $again !== $hash || is_link($file) || ($variant && !psr_guard_matches_slakersest($file)) ||
            ($familyMatch && !psr_guard_matches_hidden_filemanager_family($file)) ||
            ($recovery && !psr_guard_matches_mu_recovery($file))) {
            $events[] = 'FILE CAMBIATO DURANTE LA VERIFICA: ' . $file;
            return;
        }
        $reason = $matched ? 'SHA256 confermato' : ($recovery ? 'persistenza MU wp-link-helper verificata' : ($familyMatch ? 'firma comportamentale webshell verificata' : 'variante slakersest confermata'));
        if (psr_guard_isolate($file, $reason, $events)) { $removed++; }
        return;
    }
    if (in_array($name, array('translanugos.php','toddlingness.php','prequiche.php','slakersest.php',
        'overcoqu.php','wp-e92754ad0d8a.php','wp-b697fed82d65.php',
        'wp-d5086527eae9.php','wp-ce624c7f52b7.php',
        'underpenup.php','paltererness.php','semibuxeous.php','transfraktur.php','ultraruruing.php','microbearers.php'), true) ||
        ($isMuRootFile && preg_match('/^wp-[a-f0-9]{12}\.php$/i', $name))) {
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
    // Sondaggio rapido dei 5 indicatori in directory precise, non ricorsivo.
    // Consente il rilevamento entro il prossimo controllo (WP-Cron non e' garantito
    // su siti senza traffico) senza rallentare tutte le richieste WordPress.
    $roots = array(ABSPATH, WP_CONTENT_DIR, WP_PLUGIN_DIR,
        WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'mu-plugins',
        WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'uploads');
    foreach ($roots as $root) {
        $realRoot = realpath($root);
        if (!$realRoot || !psr_guard_path_within($realRoot, realpath(ABSPATH))) { continue; }
        foreach (psr_guard_new_shell_names() as $suspect) {
            $candidate = $realRoot . DIRECTORY_SEPARATOR . $suspect;
            if (is_file($candidate) && !is_link($candidate)) {
                psr_guard_verify_file($candidate, $suspect, $events, $detections, $removed);
            }
        }
    }
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
        // Solo PHP di primo livello: il contenuto Plesk wp-toolkit/ non viene toccato.
        // Nomi wp-<hex> variabili: verifica i byte, NON rimuove per nome.
        $entries = @scandir($mu);
        if (is_array($entries)) {
            $count = 0;
            foreach ($entries as $entry) {
                if ($entry === '.' || $entry === '..' || !preg_match('/\.php$/i', $entry)) { continue; }
                if (++$count > 500) {
                    $events[] = 'SCANSIONE MU PARZIALE: oltre 500 PHP di primo livello';
                    break;
                }
                $candidate = $mu . DIRECTORY_SEPARATOR . $entry;
                if (is_file($candidate) && !is_link($candidate)) {
                    psr_guard_verify_file($candidate, strtolower($entry), $events, $detections, $removed);
                }
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
    if ($iso > 0) {
        // Gli MU-plugin hanno gia' eseguito il bootstrap prima del tema.
        // Un callback malevolo su shutdown potrebbe ripristinare il plugin nello
        // stesso request: un secondo controllo, registrato DOPO quel callback,
        // lo isola nuovamente. Non garantisce protezione contro tutte le persistenze.
        add_action('shutdown', 'psr_guard_post_shutdown_scan', PHP_INT_MAX);
    }
    if ($events) {
        foreach ($events as $event) { error_log('[PSR GUARD QUICK] ' . $event); }
        if (count($events) > 0) {
            update_option('psr_guard_quick_report', array('when' => gmdate('c'), 'events' => $events), false);
        }
    }
}
add_action('init', 'psr_guard_quick_scan', 30);

function psr_guard_post_shutdown_scan() {
    $events = array(); $det = 0; $iso = 0;
    psr_guard_targeted_scan($events, $det, $iso);
    foreach ($events as $event) { error_log('[PSR GUARD SHUTDOWN] ' . $event); }
}

function psr_guard_admin_notice() {
    // Avvisi riservati esclusivamente all'amministratore WordPress con ID 1.
    // Scansioni, quarantena e logging non dipendono dall'utente collegato.
    if (get_current_user_id() !== 1 || !current_user_can('manage_options')) { return; }
    $report = get_option('psr_guard_last_report');
    $quick = get_option('psr_guard_quick_report');
    if ((!is_array($report) || empty($report['events'])) && (!is_array($quick) || empty($quick['events']))) { return; }
    echo '<div class="notice notice-warning"><p><strong>PSR Security Guard:</strong> possibili file non autorizzati rilevati o isolati. Verificare i log PHP e la quarantena su Plesk. Nessun elemento di wp-toolkit è stato rimosso automaticamente.</p></div>';
}
add_action('admin_notices', 'psr_guard_admin_notice');
