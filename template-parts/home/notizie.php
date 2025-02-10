<?php
global $count, $scheda;
// Per mostrare la notizia più recente
// $args = array('post_type' => 'notizia',
//              'posts_per_page' => 1,
//         'orderby' => 'date',
//         'order' => 'DESC'
// );
// $posts = get_posts($args);
// $post = array_shift($posts);

$post_id = dci_get_option('notizia_evidenziata','homepage', true )[0] ?? null;
$prefix= '_dci_notizia_';

if($post_id) $post = get_post($post_id);
$img = dci_get_meta("immagine", $prefix, $post->ID);
$arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
$monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
$descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
$argomenti = dci_get_meta("argomenti", $prefix, $post->ID);
$luoghi = dci_get_meta("luoghi", $prefix, $post->ID);



// Popolo schede in evidenza
//Se non è vuota l'aggiungo
$schede = [];
for ($i = 1; $i <= 20; $i++) {
    $schede[] = dci_get_option("schede_evidenziate_$i", 'homepage', true)[0] ?? null;
}

?>

<!-- Tag section is opened in home.php -->
<section id="notizie" aria-describedby="novita-in-evidenza">
    <div class="section-content">
        <div class="container">
            <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>
            <?php if ($post_id) {
                $overlapping = "card-overlapping";
            ?>
                <div class="row">
                    <div class="col-lg-5 order-2 order-lg-1">
                        <div class="card mb-1">
                            <div class="card-body pb-5">
                                <div class="category-top">
                                    <svg class="icon icon-sm" aria-hidden="true">
                                        <use xlink:href="#it-calendar"></use>
                                    </svg>
                                    <span class="title-xsmall-semi-bold fw-semibold"><?php echo $post->post_type ?></span>                         
                                    <?php if (is_array($arrdata) && count($arrdata)) { ?>
                                        <span class="data fw-normal"><?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?></span>
                                    <?php } ?>
                                </div>
                                <a href="<?php echo get_permalink($post->ID); ?>" class="text-decoration-none">
                                    <h3 class="card-title">
                                        <?php echo $post->post_title ?>
                                    </h3>
                                </a>
                                <p class="mb-4 font-serif pt-3">
                                    <?php echo $descrizione_breve ?>                       
                                </p>
                                  <?php if(is_array($luoghi) && count($luoghi)) { ?>
                                           <?php get_template_part("template-parts/single/luoghi"); ?>
                                  <?php }?>
                               
                               Argomenti: <?php get_template_part("template-parts/common/badges-argomenti"); ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 offset-lg-1 order-1 order-lg-2 px-0 px-lg-2">
                        <?php if ($img) {
                            dci_get_img($img, 'img-fluid');
                        } ?>
                    </div>
                </div>

            <?php }
            if ($posts && is_array($posts) && count($posts) > 0) { ?>
                <?php if (!$post_id) { ?>
                    <div class="row row-title pt-lg-60 pb-3">
                        <div class="col-12 d-lg-flex justify-content-between">
                            <h2 id="ultime-news" class="visually-hidden">Ultime news</h2>
                        </div>
                    </div>
                <?php } ?>
                <div class="row mb-2">
                    <div class="card-wrapper px-0 <?php echo $overlapping; ?> card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
                        <?php
                        foreach ($posts as $post) {
                            if ($post) {
                                get_template_part("template-parts/home/notizia-evidenza");
                            }
                        }
                        ?>
                    </div>
                    
                </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

    

    <div class="container">
      <div class="row mb-2">
        <div class="card-wrapper px-0 card-overlapping card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
                <div class="row my-4 justify-content-md-center">                 
                       <a href="<?php echo dci_get_template_page_url("page-templates/novita.php"); ?>" class="btn btn-primary-outline mt-40" >   
                         <svg class="icon icon-sm" aria-hidden="true">
                           <use xlink:href="#it-calendar"></use>
                          </svg>
                          Visualizza tutte le novità
                     </a>                 
              </div>
            </div>
          </div>
         </div>
  
<p></p>
<section id="calendario">
  <div class="section section-muted pb-90 pb-lg-50 px-lg-5 pt-0">
    <div class="container">
      <div class="row mb-2">
        <div class="card-wrapper px-0 card-overlapping card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
        <?php $count=1;
        foreach ($schede as $scheda) {
          if ($scheda) 
            get_template_part("template-parts/home/scheda-evidenza");
          ++$count;
        } ?>
        </div>
      </div>
    </div>
<!-- Tag section is closed in home.php -->
