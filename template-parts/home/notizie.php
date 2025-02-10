<?php
global $count, $scheda;

$post_id = dci_get_option('notizia_evidenziata', 'homepage', true)[0] ?? null;
$prefix = '_dci_notizia_';

if ($post_id) {
    $post = get_post($post_id);
    $img = dci_get_meta("immagine", $prefix, $post->ID);
    $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
    $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
    $argomenti = dci_get_meta("argomenti", $prefix, $post->ID);
    $luoghi = dci_get_meta("luoghi", $prefix, $post->ID); // Recupero dell'array dei luoghi
    
    // Debug per $luoghi
    echo '<pre>DEBUG $luoghi: ';
    print_r($luoghi);
    echo '</pre>';

    // Forziamo $luoghi ad essere un array, se non lo è già
    if (!is_array($luoghi)) {
        $luoghi = [];
    }
}

$schede = [];
for ($i = 1; $i <= 20; $i++) {
    $schede[] = dci_get_option("schede_evidenziate_$i", 'homepage', true)[0] ?? null;
}
?>

<section id="notizie" aria-describedby="novita-in-evidenza">
    <div class="section-content">
        <div class="container">
            <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>
            <?php if ($post_id) { ?>
                <div class="row">
                    <div class="col-lg-5 order-2 order-lg-1">
                        <div class="card mb-1">
                            <div class="card-body pb-5">
                                <div class="category-top">
                                    <svg class="icon icon-sm" aria-hidden="true">
                                        <use xlink:href="#it-calendar"></use>
                                    </svg>
                                    <span class="title-xsmall-semi-bold fw-semibold"><?php echo esc_html($post->post_type) ?></span>
                                    <?php if (is_array($arrdata) && count($arrdata)) { ?>
                                        <span class="data fw-normal"><?php echo esc_html($arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]); ?></span>
                                    <?php } ?>
<?php if (is_array($luoghi) && count($luoghi)) { ?>
    <span class="luogo fw-normal"> - 
        <?php 
        // Recupera il nome del luogo per ogni ID in $luoghi
        $luoghi_nomi = [];
        foreach ($luoghi as $luogo_id) {
            $luogo = get_post($luogo_id); // Ottieni il post associato all'ID
            if ($luogo) {
                $luoghi_nomi[] = $luogo->post_title; // Aggiungi il nome del luogo all'array
            }
        }
        
        // Unisci i nomi dei luoghi con una virgola e mostrali
        echo implode(", ", $luoghi_nomi);
        ?>
    </span>
<?php } ?>
                                </div>
                                <a href="<?php echo esc_url(get_permalink($post->ID)); ?>" class="text-decoration-none">
                                    <h3 class="card-title"><?php echo esc_html($post->post_title); ?></h3>
                                </a>
                                <p class="mb-4 font-serif pt-3"><?php echo esc_html($descrizione_breve); ?></p>
                                <hr style="margin-bottom: 10px; width: 200px; height: 1px; background-color: grey; border: none;">
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
