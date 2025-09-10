<?php
$calendario = dci_get_calendar();
$date = array_keys((array)$calendario);

$fisrt_date = explode("-", $date[0]);
$last_date = explode("-", $date[count($date) - 1]);

$currentMonth = date_i18n('F', mktime(0, 0, 0, $fisrt_date[1], 10));

$nextMonth = " ";
if ($fisrt_date[1] != $last_date[1]) {
	$nextMonth = "/".date_i18n('F', mktime(0, 0, 0, $last_date[1], 10))." ";
}
$full_date = $currentMonth . $nextMonth . $fisrt_date[0];

$total_eventi = 0;
foreach ($date as $data) {
	if ( is_array($calendario[$data]) && count($calendario[$data]) ) {
	$eventi = $calendario[$data]['eventi'];
	++$total_eventi;
	}
 }

?>

<div class="container">
	<div class="row row-title pt-5 pt-lg-60 pb-3">
		<div class="col-12 d-lg-flex justify-content-between">
			<h2 class="mb-lg-0">Eventi</h2>
		</div>
	</div>
	<div class="row row-calendar">
		<?php if ($total_eventi > 0 ) { ?>
		<div
		class="it-carousel-wrapper it-carousel-landscape-abstract-four-cols it-calendar-wrapper splide"
		data-bs-carousel-splide
		>
			<div class="it-header-block">
				<div class="it-header-block-title">
					<h1 class="mb-0 text-center home-carousel-title"><?php echo $full_date; ?></h3>
				</div>
			</div>
			<div class="splide__track">
				<ul class="splide__list it-carousel-all">
					<?php foreach ($date as $data) {
					$arrdata =  explode("-", $data);
					$dayName = date_i18n('D', mktime(0, 0, 0,intval($arrdata[1]), intval($arrdata[2])));

					if ( is_array($calendario[$data]) && count($calendario[$data]) )
					$eventi = $calendario[$data]['eventi'];
					else $eventi = [];
					?>
	<li class="splide__slide">
  <div class="card h-100" style="border: none; border-radius: 16px; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05); overflow: hidden;">
    <div class="card-body d-flex flex-column justify-content-between" style="padding: 1.5rem;">
      <div class="mb-3">
        <div class="d-flex align-items-center mb-2">
          <div style="font-size: 2rem; font-weight: bold; color: #0d6efd; margin-right: 0.5rem;"><?php echo $arrdata[2]; ?></div>
          <div style="text-transform: uppercase; font-size: 0.875rem; color: #6c757d;"><?php echo $dayName; ?></div>
        </div>

        <?php if (is_array($eventi) && count($eventi)) {
          foreach ($eventi as $evento) {
            $img = dci_get_meta('immagine', '_dci_evento_', $evento['id']);
        ?>
          <div class="d-flex align-items-start mb-3">
            <?php if ($img): ?>
              <div style="width: 60px; height: 60px; overflow: hidden; border-radius: 8px; margin-right: 1rem;">
                <?php dci_get_img($img, 'img-fluid'); ?>
              </div>
            <?php endif; ?>
            <div style="flex-grow: 1;">
              <a href="<?php echo $evento['link']; ?>" style="text-decoration: none; color: #212529; font-weight: 500;">
                <?php echo $evento['titolo']; ?>
              </a>
            </div>
          </div>
        <?php } } else { ?>
          <p style="color: #6c757d;">Nessun evento per questo giorno.</p>
        <?php } ?>
      </div>
    </div>
  </div>
</li>

					<?php } ?>
				</ul>
			</div>
		</div>
		<?php } else {?>
		<div class="it-carousel-wrapper it-carousel-landscape-abstract-four-cols it-calendar-wrapper">
			<div class="it-header-block">
				<div class="it-header-block-title">
				<h3 class="mb-0 text-center home-carousel-title"><?php echo $full_date; ?></h3>
				</div>
			</div>
		</div>
		<div class="mt-4"> Nessun evento in programma. </div>
		<?php } ?>
	</div>
</div><!-- /container -->
</div><!-- /div.section in notizie.php -->
