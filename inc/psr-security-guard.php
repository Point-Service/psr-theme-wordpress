<?php
/**
 * PSR emergency guard: exact known malicious sample hashes only.
 * Does not prevent direct PHP execution; web-server blocking is separate.
 */
if (!defined('ABSPATH')) { exit; }

function psr_guard_signatures() {
    return array(
        '618493861791064b9f394f06af7e6d538fc46e25555fc398af8e5d6a1f20a483' => 'translanugos.php',
        '20f91dbe6ded4b46c57f054608379b0e582c4e190277212923c0a37d89b562a7' => 'toddlingness.php',
        '57f33eb6ae24f9f52c9fa21ae43ddcc90a9d06bc5912f70fe9cb586760becbf3' => 'prequiche.php',
    );
}

function psr_guard_install_schedule() {
    if (!wp_next_scheduled('psr_guard_scan')) {
        wp_schedule_event(time() + 30, 'hourly', 'psr_guard_scan');
    }
}
add_action('init', 'psr_guard_install_schedule', 20);

function psr_guard_scan() {
    if (get_transient('psr_guard_running')) { return; }
    set_transient('psr_guard_running', 1, 10 * MINUTE_IN_SECONDS);
    $started = microtime(true);
    $checked = 0;
    $detections = 0;
    $removed = 0;
    $events = array();
    $max_files = 20000;
    $max_time = 12;
    $known_names = array('translanugos.php', 'toddlingness.php', 'prequiche.php');
    $known_hashes = psr_guard_signatures();
    try {
        $root = realpath(ABSPATH);
        if (!$root) { throw new RuntimeException('WordPress root unavailable'); }
        $dir = new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($dir, RecursiveIteratorIterator::LEAVES_ONLY);
        foreach ($iterator as $item) {
            if ($checked >= $max_files || microtime(true) - $started >= $max_time) { break; }
            if ($item->isLink() || !$item->isFile()) { continue; }
            $checked++;
            $file = $item->getPathname();
            $name = strtolower($item->getFilename());
            $is_name = in_array($name, $known_names, true);
            $is_php = strtolower(pathinfo($name, PATHINFO_EXTENSION)) === 'php';
            if (!$is_name && !$is_php) { continue; }
            $size = $item->getSize();
            if ($size === false || $size > 1024 * 1024 || !$item->isReadable()) {
                if ($is_name) { $events[] = 'Sospetto non analizzabile: ' . $file; }
                continue;
            }
            $hash = @hash_file('sha256', $file);
            if ($hash !== false && isset($known_hashes[$hash])) {
                $detections++;
                // Defense against a file swapped between hashing and deletion.
                $before = @lstat($file);
                $recheck = @hash_file('sha256', $file);
                $after = @lstat($file);
                if (!$before || !$after || $before['ino'] !== $after['ino'] || $before['dev'] !== $after['dev'] || $recheck !== $hash || is_link($file)) {
                    $events[] = 'Confermato ma modificato durante verifica: ' . $file;
                    continue;
                }
                if (@unlink($file)) {
                    $removed++;
                    $events[] = 'RIMOSSO campione esatto ' . $known_hashes[$hash] . ': ' . $file;
                } else {
                    $events[] = 'IMPOSSIBILE RIMUOVERE campione esatto: ' . $file;
                }
            } elseif ($is_name) {
                $events[] = 'NOME SOSPETTO, NON ELIMINATO: ' . $file;
            }
            if (count($events) >= 100) { break; }
        }
    } catch (Throwable $e) {
        $events[] = 'Scansione interrotta: ' . $e->getMessage();
    }
    $report = array('when' => gmdate('c'), 'checked' => $checked, 'detections' => $detections, 'removed' => $removed, 'events' => $events);
    update_option('psr_guard_last_report', $report, false);
    foreach ($events as $message) { error_log('[PSR GUARD] ' . $message); }
    delete_transient('psr_guard_running');
}
add_action('psr_guard_scan', 'psr_guard_scan');

function psr_guard_admin_notice() {
    if (!current_user_can('manage_options')) { return; }
    $report = get_option('psr_guard_last_report');
    if (!is_array($report) || empty($report['events'])) { return; }
    echo '<div class="notice notice-warning"><p><strong>PSR Security Guard:</strong> rilevate anomalie nell’ultima scansione (' . esc_html($report['when']) . '). Rimossi ' . intval($report['removed']) . ' campioni confermati. Verifica il log PHP del server.</p></div>';
}
add_action('admin_notices', 'psr_guard_admin_notice');
