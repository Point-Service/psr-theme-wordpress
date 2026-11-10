<?php
/**
 * Unit + integration checks for progressive, low-impact nightly scanner.
 * Run only via WP-CLI in an isolated WordPress installation.
 */
if (!defined('WP_CLI') || !WP_CLI || !defined('ABSPATH')) { exit(1); }
function psr_night_ci_assert($condition, $message) {
    if (!$condition) { fwrite(STDERR, "FAIL: {$message}\n"); exit(1); }
    fwrite(STDOUT, "PASS: {$message}\n");
}

psr_night_ci_assert(function_exists('psr_guard_scan_batch'), 'Progressive scanner loaded');
psr_night_ci_assert(function_exists('psr_guard_quick_scan'), 'Original quick scanner preserved');
psr_night_ci_assert(has_action('init','psr_guard_quick_scan') !== false, 'Quick 5 minute scan hook retained');
psr_night_ci_assert(wp_next_scheduled('psr_guard_scan') !== false, 'Nightly scan is scheduled');
psr_night_ci_assert(wp_get_schedule('psr_guard_scan') === false,
    'Old hourly recurring scan replaced with one-off nightly events');

wp_clear_scheduled_hook('psr_guard_scan');
psr_night_ci_assert(wp_schedule_event(time()+120, 'hourly', 'psr_guard_scan') === true,
    'Old hourly scan can be recreated in CI only');
update_option('psr_guard_scan_schedule_version', 1, false);
psr_guard_install_schedule();
psr_night_ci_assert((int)get_option('psr_guard_scan_schedule_version') === 2
    && wp_next_scheduled('psr_guard_scan') !== false
    && wp_get_schedule('psr_guard_scan') === false,
    'Existing hourly scan migrates atomically to single overnight event');


$tz = wp_timezone();
$atNight = (new DateTimeImmutable('2026-11-10 01:30:00', $tz))->getTimestamp();
$beforeNight = (new DateTimeImmutable('2026-11-10 00:20:00', $tz))->getTimestamp();
$afterNight = (new DateTimeImmutable('2026-11-10 03:01:00', $tz))->getTimestamp();
psr_night_ci_assert(psr_guard_night_window($atNight), 'Nightly window includes 01:30');
psr_night_ci_assert(!psr_guard_night_window($beforeNight) && !psr_guard_night_window($afterNight),
    'Deep scan excluded from day and late night');
$offset = psr_guard_scan_start_offset();
psr_night_ci_assert($offset>=0 && $offset<1200, 'Nightly starts staggered by a safe 0-20 minute offset');
psr_night_ci_assert(psr_guard_next_scan_time($atNight) === $atNight + 120,
    'One short pass scheduled two minutes later after staggered start');
$next = psr_guard_next_scan_time($afterNight);
$expected = (new DateTimeImmutable('2026-11-11 01:00:00', $tz))->getTimestamp() + $offset;
psr_night_ci_assert($next === $expected,
    'After 03:00 following scan is next night with the same per-site offset');
$next = psr_guard_next_scan_time($beforeNight);
$expected = (new DateTimeImmutable('2026-11-10 01:00:00', $tz))->getTimestamp() + $offset;
psr_night_ci_assert($next === $expected,
    'Before 01:00 deep scan waits for the staggered start');
$justAfter = (new DateTimeImmutable('2026-11-10 01:00:01', $tz))->getTimestamp();
if ($offset > 1) {
    psr_night_ci_assert(psr_guard_next_scan_time($justAfter)===$expected,
        'A scheduled scan cannot start before its per-site slot');
}

$root = sys_get_temp_dir() . '/psr-scan-ci-' . bin2hex(random_bytes(6));
if (!mkdir($root, 0700, true)) { throw new RuntimeException('Temp directory unavailable'); }
$outside = tempnam(sys_get_temp_dir(), 'psr-scan-outside-');
$created = array();
try {
    $dirs = array('','a','a/b','a/b/c','d','d/e');
    foreach ($dirs as $rel) {
        if ($rel !== '' && !is_dir($root.'/'.$rel)) { mkdir($root.'/'.$rel,0700,true); }
    }
    $expected = 0;
    foreach ($dirs as $n=>$rel) {
        for($i=0; $i<5; $i++){
            $name = $root . ($rel===''?'':'/'.$rel) . '/ci-'.$n.'-'.$i.'.php';
            file_put_contents($name, '<?php /* PSR safe fixture */ ');
            $created[]=$name; $expected++;
        }
    }
    // The scanner must visit ordinary non-PHP files too, without reading them.
    $pdf=$root.'/d/e/test-document.pdf';
    file_put_contents($pdf,"%PDF-1.4\n%%EOF");
    $created[]=$pdf; $expected++;
    $link=$root.'/outside-symlink.php';
    $linked = function_exists('symlink') && @symlink($outside,$link);
    if($linked){$created[]=$link;}
    $state = psr_guard_new_scan_state(realpath($root));
    $passes=0; $finished=false; $maxBatch=0;
    do {
        $result=psr_guard_scan_batch($state, realpath($root), 4, 0.6);
        $passes++; $maxBatch=max($maxBatch, $result['visited']);
        psr_night_ci_assert($result['visited'] <= 4, 'Per-pass work cap never exceeded (pass '.$passes.')');
        $finished = $result['finished'];
        if($passes>100){throw new RuntimeException('Scanner did not finish bounded traversal');}
    } while(!$finished);
    psr_night_ci_assert($passes > 4, 'Deep scan progressed across many separate calls');
    psr_night_ci_assert($state['checked'] === $expected, 'Every regular test file visited exactly once');
    psr_night_ci_assert($state['unreadable'] === 0, 'No unreadable paths for normal test tree');
    psr_night_ci_assert(count($state['stack'])===0, 'Cursor is empty only after the full traversal');
    psr_night_ci_assert($maxBatch<=4, 'Per-call maximum respected');
    psr_night_ci_assert(!file_exists($root.'/wpsecurity.log'), 'No unrequested write in fixture');
    if($linked){psr_night_ci_assert(file_exists($outside), 'Symbolic link target outside root untouched');}
    psr_night_ci_assert(empty($state['events']), 'Clean fixture does not raise malware detections');
    echo "PASS: {$expected} files checked in {$passes} small chunks\n";

    // Benchmark with a large directory: a single pass must never walk it all.
    $stress = $root.'/stress';
    mkdir($stress, 0700);
    for ($i=0;$i<1200;$i++) {
        file_put_contents($stress.'/entry-'.$i.'.txt', 'test');
    }
    $stressState = psr_guard_new_scan_state(realpath($stress));
    $stressBatch = psr_guard_scan_batch($stressState, realpath($stress));
    psr_night_ci_assert($stressBatch['visited'] <= 400 && !$stressBatch['finished'],
        'Large directory scanned in a bounded chunk rather than one heavy operation');
    psr_night_ci_assert($stressBatch['seconds'] < 2.0,
        'Large directory pass returns promptly on the isolated test server');
    for($i=0;$i<1200;$i++){ @unlink($stress.'/entry-'.$i.'.txt'); }
    @rmdir($stress);
} finally {
    foreach ($created as $name) { @unlink($name); }
    if ($outside) { @unlink($outside); }
    foreach (array('a/b/c','a/b','a','d/e','d') as $sub) { @rmdir($root.'/'.$sub); }
    @rmdir($root);
}
echo "ALL NIGHT-SCANNER SMOKE TESTS PASSED\n";
