<?php
$calendario = dci_get_calendar();
$date = array_keys((array)$calendario);

$fisrt_date = explode("-", $date[0]);
$last_date = explode("-", $date[count($date) - 1]);

$currentMonth = date_i18n('F', mktime(0, 0, 0, $fisrt_date[1], 10));

$nextMonth = " ";
if ($fisrt_date[1] != $last_date[1]) {
    $nextMonth = "/" . date_i18n('F', mktime(0, 0, 0, $last_date[1], 10)) . " ";
}
$full_date = $currentMonth . $nextMonth . $fisrt_date[0];

$total_eventi = 0;
foreach ($date as $data) {
    if (is_array($calendario[$data]) && count($calendario[$data])) {
        $eventi = $calendario[$data]['eventi'];
        ++$total_eventi;
    }
}
?>

<div class="container" style="width: 90%; max-width: 1200px; margin: 0 auto; padding: 2rem 0;">
    <div class="row row-title pt-5 pt-lg-60 pb-3" style="display: flex; flex-direction: column; align-items: center;">
        <div class="col-12 d-lg-flex justify-content-between" style="width: 100%;">
            <h2 class="mb-lg-0" style="font-size: 2.5rem; font-weight: 700; color: #333;">Eventi</h2>
        </div>
    </div>

    <div class="row row-calendar" style="display: flex; flex-wrap: wrap; justify-content: space-between;">
        <?php if ($total_eventi > 0) { ?>
        <div class="it-carousel-wrapper it-carousel-landscape-abstract-four-cols it-calendar-wrapper splide" data-bs-carousel-splide style="width: 100%;">
            <div class="it-header-block" style="text-align: center; margin-bottom: 1.5rem;">
                <h1 class="mb-0 home-carousel-title" style="font-size: 2.25rem; font-weight: 600; color: #333;"><?php echo $full_date; ?></h1>
            </div>

            <div class="splide__track" style="overflow-x: auto; display: flex;">
                <ul class="splide__list" style="display: flex; gap: 15px;">
                    <?php foreach ($date as $data) {
                    $arrdata = explode("-", $data);
                    $dayName = date_i18n('D', mktime(0, 0, 0, intval($arrdata[1]), intval($arrdata[2])));
                    if (is_array($calendario[$data]) && count($calendario[$data]))
                        $eventi = $calendario[$data]['eventi'];
                    else $eventi = [];
                    ?>
                    <li class="splide__slide" style="max-width: 320px; flex: 0 0 auto;">
                        <div class="card h-100" style="border: none; border-radius: 20px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1); transition: transform 0.3s ease;">
                            <div class="card-body" style="padding: 2rem; color: #333; display: flex; flex-direction: column; justify-content: space-between;">
                                <!-- Data e giorno -->
                                <div class="mb-4" style="display: flex; align-items: center; justify-content: space-between;">
                                    <div style="font-size: 2.25rem; font-weight: bold; color: #007bff; margin-right: 0.75rem;">
                                        <?php echo $arrdata[2]; ?>
                                    </div>
                                    <div style="font-size: 1rem; text-transform: uppercase; color: #6c757d;">
                                        <?php echo $dayName; ?>
                                    </div>
                                </div>

                                <!-- Eventi -->
                                <?php if (is_array($eventi) && count($eventi)) {
                                    foreach ($eventi as $evento) {
                                        $img = dci_get_meta('immagine', '_dci_evento_', $evento['id']);
                                ?>
                                <div class="d-flex align-items-start mb-4" style="transition: transform 0.3s ease; display: flex; align-items: center; margin-bottom: 1.5rem;">
                                    <?php if ($img): ?>
                                        <div style="width: 70px; height: 70px; overflow: hidden; border-radius: 10px; margin-right: 1rem;">
                                            <?php dci_get_img($img, 'img-fluid'); ?>
                                        </div>
                                    <?php endif; ?>
                                    <div style="flex-grow: 1;">
                                        <a href="<?php echo $evento['link']; ?>" style="text-decoration: none; color: #333; font-weight: 500; font-size: 1rem; transition: color 0.3s ease;">
                                            <?php echo $evento['titolo']; ?>
                                        </a>
                                    </div>
                                </div>
                                <?php }} else { ?>
                                    <p style="color: #6c757d; font-size: 1rem;">Nessun evento per questo giorno.</p>
                                <?php } ?>
                            </div>
                        </div>
                    </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
        <?php } else { ?>
        <div class="it-carousel-wrapper it-carousel-landscape-abstract-four-cols it-calendar-wrapper">
            <div class="it-header-block" style="text-align: center; margin-bottom: 1.5rem;">
                <h3 class="mb-0 home-carousel-title" style="font-size: 1.75rem; font-weight: 500; color: #6c757d;"><?php echo $full_date; ?></h3>
            </div>
        </div>
        <div class="mt-4" style="font-size: 1.2rem; color: #6c757d;">Nessun evento in programma.</div>
        <?php } ?>
    </div>
</div>
