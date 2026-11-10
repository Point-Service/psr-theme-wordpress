<?php
/** Test-only setup: isolated WordPress CI database, never production. */
if (!defined('ABSPATH')) { exit(1); }
$today = new DateTime('now', wp_timezone());
$start = (clone $today)->modify('-1 day');
$end = (clone $today)->modify('+70 days');
$office_id = wp_insert_post(array(
    'post_type'=>'unita_organizzativa',
    'post_title'=>'Test Prenotazioni PSR',
    'post_status'=>'publish',
), true);
$hours_id = wp_insert_post(array(
    'post_type'=>'post',
    'post_title'=>'Orari di test PSR',
    'post_status'=>'draft',
), true);
if (is_wp_error($office_id) || is_wp_error($hours_id) || !$office_id || !$hours_id) {
    throw new RuntimeException('Could not create the isolated booking fixture');
}
update_post_meta($office_id, '_dci_unita_organizzativa_orario_uo', $hours_id);
update_post_meta($hours_id, '_dci_orario_data_inizio', $start->format('d-m-Y'));
update_post_meta($hours_id, '_dci_orario_data_fine', $end->format('d-m-Y'));
foreach (array('lun','mar','mer','gio','ven','sab','dom') as $day) {
    update_post_meta($hours_id, '_dci_orario_' . $day . '_mattina', '08:00-12:00');
}
$request = new WP_REST_Request('GET','/wp/v2/appuntamenti/ufficio/');
$request->set_param('id', (int)$office_id);
$request->set_param('month', (int)$today->format('n'));
$request->set_param('year', (int)$today->format('Y'));
$slots = dci_get_appuntamenti_ufficio($request);
if (!is_array($slots) || !$slots) { throw new RuntimeException('Test office has no slots'); }
$payload = array(
    'office' => wp_json_encode(array('id'=>$office_id,'name'=>'Test Prenotazioni PSR')),
    'service' => wp_json_encode(array('id'=>1,'name'=>'Test Servizio')),
    'appointment' => wp_json_encode($slots[0]),
    'nonce' => wp_create_nonce('dci_segnalazione_disservizio'),
);
if (!file_put_contents('/tmp/psr-e2e-fixture.json', wp_json_encode($payload))) {
    throw new RuntimeException('Fixture JSON not written');
}
echo 'PASS: CI-only office, booking slot and disservice nonce fixture prepared' . PHP_EOL;
