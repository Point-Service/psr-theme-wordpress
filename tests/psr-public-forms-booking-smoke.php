<?php
/**
 * Run with "wp eval-file" on an isolated WordPress DB, never production.
 * Checks public AJAX request validation, limits and server-side booking slots.
 */
if (!defined('ABSPATH')) { fwrite(STDERR, "WordPress required\n"); exit(1); }
function psr_pf_assert($condition, $label) {
    if (!$condition) { fwrite(STDERR, "FAIL: {$label}\n"); exit(1); }
    fwrite(STDOUT, "PASS: {$label}\n");
}
psr_pf_assert(function_exists('psr_booking_slot_available'), 'Server booking guard loaded');
psr_pf_assert(function_exists('psr_public_guard_rate_allow'), 'Public rate guard loaded');
psr_pf_assert(function_exists('psr_auth_lock'), 'Atomic authentication locks available');

$now = new DateTime('now', wp_timezone());
$first_day = (clone $now)->modify('-3 days');
$last_day = (clone $now)->modify('+70 days');
$office = wp_insert_post(array('post_type'=>'unita_organizzativa','post_status'=>'publish','post_title'=>'CI Booking Office'), true);
$hours = wp_insert_post(array('post_type'=>'post','post_status'=>'draft','post_title'=>'CI Booking Hours'), true);
psr_pf_assert(!is_wp_error($office) && !is_wp_error($hours) && $office > 0 && $hours > 0, 'Mock office and hours created');
try {
    update_post_meta($office, '_dci_unita_organizzativa_orario_uo', $hours);
    update_post_meta($hours, '_dci_orario_data_inizio', $first_day->format('d-m-Y'));
    update_post_meta($hours, '_dci_orario_data_fine', $last_day->format('d-m-Y'));
    foreach (array('lun','mar','mer','gio','ven','sab','dom') as $day) {
        update_post_meta($hours, '_dci_orario_' . $day . '_mattina', '08:00-12:00');
    }
    $request = new WP_REST_Request('GET', '/wp/v2/appuntamenti/ufficio/');
    $request->set_param('id', (int) $office);
    $request->set_param('month', (int) $now->format('n'));
    $request->set_param('year', (int) $now->format('Y'));
    $slots = dci_get_appuntamenti_ufficio($request);
    psr_pf_assert(is_array($slots) && count($slots)>0, 'Existing public calendar produces available slots');
    $slot = $slots[0];
    $start = DateTime::createFromFormat('!Y-m-d\TH:i', $slot['startDate'], wp_timezone());
    psr_pf_assert($start instanceof DateTime && $start > $now, 'Calendar returns a future slot');
    $valid_office = array('id'=>(string)$office, 'name'=>'CI Booking Office');
    psr_pf_assert(psr_booking_slot_available($valid_office, $slot), 'Valid calendar slot is accepted');
    $fabricated = $slot;
    $fabricated['startDate'] = (clone $start)->modify('+1 minute')->format('Y-m-d\TH:i');
    psr_pf_assert(!psr_booking_slot_available($valid_office, $fabricated), 'Fabricated time is rejected');
    psr_pf_assert(!psr_booking_slot_available(array('id'=>$hours), $slot), 'Non-office content rejected');
    psr_pf_assert(!psr_booking_slot_available(array('id'=>0), $slot), 'Missing office rejected');
    psr_pf_assert(!psr_booking_slot_available($valid_office, array('startDate'=>'oops','endDate'=>'oops')), 'Malformed dates rejected');

    $booking = array(
      'action'=>'save_appuntamento','privacy'=>'1',
      'name'=>'Giulia','surname'=>'Rossi', 'email'=>'giulia@example.test',
      'moreDetails'=>'Descrizione prova', 'office'=>wp_json_encode($valid_office),
      'appointment'=>wp_json_encode($slot),
      'service'=>wp_json_encode(array('id'=>5,'name'=>'Servizio di prova')),
    );
    $limits = psr_public_guard_limits();
    psr_pf_assert(psr_public_guard_valid_fields($booking, 'save_appuntamento', $limits), 'Unchanged booking form payload accepted');
    $booking['appointment']='not JSON';
    psr_pf_assert(!psr_public_guard_valid_fields($booking, 'save_appuntamento', $limits), 'Malformed booking request rejected');
    $assistance = array(
      'action'=>'save_richiesta_assistenza', 'nome'=>'Giulia','cognome'=>'Rossi',
      'email'=>'giulia@example.test','servizio'=>'Servizio',
      'privacyChecked'=>'1'
    );
    psr_pf_assert(psr_public_guard_valid_fields($assistance,'save_richiesta_assistenza',$limits), 'Existing assistance form accepted');
    psr_pf_assert(psr_public_guard_valid_fields(array('action'=>'save_rating','title'=>'Voto','star'=>'5'),'save_rating',$limits), 'Existing rating accepted');

    $old_ip = $_SERVER['REMOTE_ADDR'] ?? null;
    $_SERVER['REMOTE_ADDR']='8.8.8.8';
    try {
        psr_pf_assert(psr_public_guard_rate_allow('psr_ci_booking_rate', 2, ''), 'First request permitted');
        psr_pf_assert(psr_public_guard_rate_allow('psr_ci_booking_rate', 2, ''), 'Second request permitted');
        psr_pf_assert(!psr_public_guard_rate_allow('psr_ci_booking_rate', 2, ''), 'Third request blocked');
        for ($i=0;$i<10;$i++) {
            psr_pf_assert(psr_public_guard_rate_allow('psr_ci_email',100,'ci@example.test'), 'Email limit request ' . ($i+1));
        }
        psr_pf_assert(!psr_public_guard_rate_allow('psr_ci_email',100,'ci@example.test'), 'Email abuse after ten requests blocked');
        psr_pf_assert(psr_public_guard_rate_allow('psr_ci_email',100,'different@example.test'), 'Unrelated email remains permitted');
    } finally {
        if ($old_ip===null) { unset($_SERVER['REMOTE_ADDR']); } else { $_SERVER['REMOTE_ADDR']=$old_ip; }
    }
} finally {
    wp_delete_post($office, true);
    wp_delete_post($hours, true);
}
fwrite(STDOUT,"PSR public forms / booking smoke checks passed\n");
