<?php
global $scheda, $count;

$post = get_post($scheda['scheda_'.$count.'_contenuto'][0]);
$img = dci_get_meta('immagine');
$descrizione_breve = dci_get_meta('descrizione_breve');
$icon = dci_get_post_type_icon_by_id($post->ID);
$page = get_page_by_path( dci_get_group($post->post_type) ); 
$argomenti = dci_get_meta("argomenti", '_dci_notizia_', $post->ID);
$luogo_notizia = dci_get_meta("luoghi", '_dci_notizia_', $post->ID); // Recupera il luogo della notizia

$arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", '_dci_notizia_', $post->ID);
$monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));

$page_macro_slug = dci_get_group($post->post_type);
$page_macro = get_page_by_path($page_macro_slug);
?>

<?php if ($img) { ?>
<div class="card card-teaser card-teaser-image card-flex no-after rounded shadow-sm border border-light mb-0">
    <div class="card-image-wrapper with-read-more">
        <div class="card-body p-3 u-grey-light">
            <div class="category-top">
                <span class="category title-xsmall-semi-bold fw-semibold"><?php echo esc_html($page->post_title); ?></span>
                <?php if (is_array($arrdata) && count($arrdata)) { ?>
                    <span class="data fw-normal">
                        <?php echo esc_html($arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]); ?>
                    </span>          
                <?php } ?>
            </div>
            <p class="card-title text-paragraph-medium u-grey-light"><?php echo esc_html($post->post_title); ?></p>
            <p class="text-paragraph-card u-grey-light m-0" style="margin-bottom: 40px!important;"><?php echo esc_html($descrizione_breve); ?></p> 
            <?php if (is_array($luogo_notizia) && count($luogo_notizia)) { ?><br><br>
            <span class="data fw-normal">📍 
                <?php 
                foreach ($luogo_notizia as $luogo_id) {
                    // Ottieni i dettagli del luogo
                    $luogo_post = get_post($luogo_id);
                    
                    if ($luogo_post && !is_wp_error($luogo_post)) {
                        // Stampa il nome del luogo come link
                        echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" title="' . esc_attr($luogo_post->post_title) . '" class="card-text text-secondary text-uppercase pb-3">' . esc_html($luogo_post->post_title) . '</a> ';
                    }
                }
                ?>
            </span>
          <?php } elseif (!empty($luogo_notizia)) { ?>
            <span class="data fw-normal"> | 📍 
                <?php echo esc_html($luogo_notizia); ?>
            </span>
        <?php } ?>
            <hr style="margin-bottom: 20px; width: 200px; height: 1px; background-color: grey; border: none;">
            <div class="card-body">Argomenti: <?php get_template_part("template-parts/common/badges-argomenti"); ?></div>   
            <hr style="margin-bottom: 40px; width: 200px; height: 1px; background-color: grey; border: none;">



        <a class="read-more ps-3"
           href="<?php echo esc_url(get_permalink($post->ID)); ?>"
           aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
           title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
           style="display: inline-flex; align-items: center; margin-top: 30px;">
            <span class="text">Vai alla pagina</span>
            <svg class="icon">
                <use xlink:href="#it-arrow-right"></use>
            </svg>
        </a>
        </div>
        <div class="card-image card-image-rounded pb-5">            
            <?php dci_get_img($img); ?>
        </div>
    </div>
</div>

<?php } else { ?>
<div class="card card-teaser no-after rounded shadow-sm mb-0 border border-light">
    <div class="card-body pb-5">
        <div class="category-top">
            <span class="category title-xsmall-semi-bold fw-semibold"><?php echo esc_html($page->post_title); ?></span>
            <?php if (is_array($arrdata) && count($arrdata)) { ?>
                <span class="data fw-normal">
                    <?php echo esc_html($arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]); ?>
                </span>
            <?php } ?>
        </div>
        <p class="card-title text-paragraph-medium u-grey-light"><?php echo esc_html($post->post_title); ?></p>
        <p class="text-paragraph-card u-grey-light m-0"><?php echo esc_html($descrizione_breve); ?></p>  
        <?php if (is_array($luogo_notizia) && count($luogo_notizia)) { ?><br><br>
            <span class="data fw-normal">📍 
                <?php 
                foreach ($luogo_notizia as $luogo_id) {
                    // Ottieni i dettagli del luogo
                    $luogo_post = get_post($luogo_id);
                    
                    if ($luogo_post && !is_wp_error($luogo_post)) {
                        // Stampa il nome del luogo come link
                        echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" title="' . esc_attr($luogo_post->post_title) . '" class="card-text text-secondary text-uppercase pb-3">' . esc_html($luogo_post->post_title) . '</a> ';
                    }
                }
                ?>
            </span>
        <?php } elseif (!empty($luogo_notizia)) { ?>
            <span class="data fw-normal"> | 📍 
                <?php echo esc_html($luogo_notizia); ?>
            </span>
        <?php } ?>
        <hr style="margin-bottom: 20px; width: 200px; height: 1px; background-color: grey; border: none;">
        <div class="card-body">Argomenti: <?php get_template_part("template-parts/common/badges-argomenti"); ?></div>            
        <hr style="margin-bottom: 20px; width: 200px; height: 1px; background-color: grey; border: none;">    
        
        <!-- Pulsante "Tutte le novità" con stile inline -->
        <div style="text-align: center; margin-top: 20px;">
            <a href="URL_DELLE_NOVITA" 
               title="Tutte le novità" 
               style="display: inline-block; background-color: #007bff; color: #fff; padding: 10px 20px; border-radius: 5px; text-decoration: none; font-weight: bold; text-transform: uppercase;">
                Tutte le novità
            </a>
        </div>
    </div>
</div>
<?php } ?>
