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
    $luogo_notizia = dci_get_meta("luoghi", $prefix, $post->ID);
}

$schede = [];
for ($i = 1; $i <= 20; $i++) {
    $schede[] = dci_get_option("schede_evidenziate_$i", 'homepage', true)[0] ?? null;
}
?>

<style>
/* CSS mirato alla sezione delle schede evidenziate */
#notizie .card-teaser-wrapper {
    max-width: 98%; /* Riduce la larghezza della sezione delle schede evidenziate */
    margin: 0 auto; /* Centra la sezione orizzontalmente */
}



/* Rendi le schede responsabili (più piccole su schermi piccoli) */
@media (max-width: 768px) {
    #notizie .card-teaser-wrapper .card {
        max-width: 100%; /* Su schermi piccoli, ogni scheda occupa l'intera larghezza */
        flex: 0 0 100%;
    }
}
</style>
<section id="notizie" aria-describedby="novita-in-evidenza">
    <div class="section-content">
        <div class="container">
            <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>
            <?php if ($post_id) { ?>
                <div class="row">
                    <!-- Colonna con i dettagli della notizia -->
                    <div class="col-lg-5 order-2 order-lg-1">
                        <div class="card mb-0"> <!-- Ridotto spazio con mb-0 -->
                            <div class="card-body pb-2"> <!-- Ridotto spazio con pb-2 -->
                                <div class="category-top">
                                    <svg class="icon icon-sm" aria-hidden="true">
                                        <use xlink:href="#it-calendar"></use>
                                    </svg>
                                    <span class="title-xsmall-semi-bold fw-semibold"><?php echo $post->post_type ?></span>
                                </div> 
                                <a href="<?php echo get_permalink($post->ID); ?>" class="text-decoration-none">
                                 <h3 class="card-title"><?php echo $post->post_title; ?></h3> 
                                </a>
                                <p class="mb-2 font-serif"><?php echo $descrizione_breve; ?></p>
                                
                                <!-- Luoghi -->
                                <?php if (is_array($luogo_notizia) && count($luogo_notizia)) { ?>
                                    <span class="data fw-normal">📍 
                                        <?php 
                                        foreach ($luogo_notizia as $luogo_id) {
                                            $luogo_post = get_post($luogo_id);
                                            if ($luogo_post && !is_wp_error($luogo_post)) {
                                                echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" title="' . esc_attr($luogo_post->post_title) . '" class="card-text text-secondary text-uppercase pb-1">' . esc_html($luogo_post->post_title) . '</a> ';
                                            }
                                        }
                                        ?>
                                    </span>
                                <?php } elseif (!empty($luogo_notizia)) { ?>
                                    <span class="data fw-normal">📍 
                                        <?php echo esc_html($luogo_notizia); ?>
                                    </span>
                                <?php } ?>

                                <!-- Data pubblicazione -->
                                <div class="row mt-2 mb-1"> <!-- Ridotto margine tra elementi -->
                                    <div class="col-6">
                                        <small>Data:</small>
                                        <p class="fw-semibold font-monospace">
                                            <?php if (is_array($arrdata) && count($arrdata)) { ?>
                                                <span class="data fw-normal">
                                                    <?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?>
                                                </span>
                                            <?php } ?>
                                        </p>
                                    </div>
                                </div>

                                <!-- Argomenti -->
                                <small>Argomenti: </small>
                                <?php get_template_part("template-parts/common/badges-argomenti"); ?>     

                <a class="read-more"
                   href="<?php echo get_permalink($post->ID); ?>"
                   aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                   title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                   style="display: inline-flex; align-items: center; margin-top: 30px;">
                    <span class="text">Vai alla pagina</span>
                    <svg class="icon">
                        <use xlink:href="#it-arrow-right"></use>
                    </svg>
                </a>

                                
                            </div>
                        </div>
                    </div>
                    <!-- Colonna con l'immagine -->
                    <div class="col-lg-6 offset-lg-1 order-1 order-lg-2 px-0 px-lg-2">
                        <?php if ($img) {
                            dci_get_img($img, 'img-fluid');
                        } ?>
                    </div>
                </div>

             
                <!-- Sezione delle schede -->
                <div class="row mb-1">
                    <div class="card-wrapper px-0 <?php echo $overlapping; ?> card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
                        <?php 
                        $count = 1;
                        foreach ($schede as $scheda) {
                            if ($scheda) {
                                get_template_part("template-parts/home/scheda-evidenza");
                            }
                            ++$count;
                        } 
                        ?>                       
                         <button type="button" class="btn btn-outline-primary">Tutte le novità
                            <svg class="icon">
                                <use xlink:href="#it-arrow-right"></use>
                            </svg>
                        </button>           
                    </div>                  
                </div>

            <?php } ?>
        </div>
    </div>
</section>
