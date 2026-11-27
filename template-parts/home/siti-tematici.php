<?php
global $sito_tematico_id, $count, $location;

$siti_tematici = dci_get_option('siti_tematici', $location??'homepage');
if (is_array($siti_tematici) && count($siti_tematici)) {
?>

  <div class="container mb-4">
    <div class="row">
      <h2 class="mb-0">Siti tematici</h2>
    </div>
    <div class="pt-4 pt-lg-30">
      <div class="row gy-4">
        <?php
        $count = 0;
        foreach ($siti_tematici as $sito_tematico_id) {
        ?>
          <div class="col-12 col-md-6 col-lg-4 card-wrapper pb-0">
            <?php
            get_template_part("template-parts/sito-tematico/card");
            if (function_exists('psr_home_tools_edit_post')) { psr_home_tools_edit_post($sito_tematico_id); }
            ?>
          </div>
        <?php
        ++$count;
        }
        ?>
      </div>
    </div>
  </div>
<?php } ?>
