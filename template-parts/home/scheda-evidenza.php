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
                    <?php if (!empty($luogo_notizia) && is_array($luogo_notizia)) { ?>
                        <span class="data fw-normal"> | 📍 
                            <?php 
                            foreach ($luogo_notizia as $luogo) {
                                if (isset($luogo['name'], $luogo['url'])) {
                                    echo '<a href="' . esc_url($luogo['url']) . '" title="' . esc_attr($luogo['name']) . '">' . esc_html($luogo['name']) . '</a> ';
                                }
                            }
                            ?>
                        </span>
                    <?php } ?>
                <?php } ?>
            </div>
            <p class="card-title text-paragraph-medium u-grey-light"><?php echo esc_html($post->post_title); ?></p>
            <p class="text-paragraph-card u-grey-light m-0" style="margin-bottom: 40px!important;"><?php echo esc_html($descrizione_breve); ?></p>    
            <hr style="margin-bottom: 20px; width: 200px; height: 1px; background-color: grey; border: none;">
            <div class="card-body">Argomenti: <?php get_template_part("template-parts/common/badges-argomenti"); ?></div>   
            <hr style="margin-bottom: 40px; width: 200px; height: 1px; background-color: grey; border: none;">
        </div>
        <div class="card-image card-image-rounded pb-5">            
            <?php dci_get_img($img); ?>
        </div>

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
</div>

<?php } else { ?>
<div class="card card-teaser no-after rounded shadow-sm mb-0 border border-light">
    <div class="card-body pb-5">
        <div class="category-top">ooooo
            <span class="category title-xsmall-semi-bold fw-semibold"><?php echo esc_html($page->post_title); ?></span>
            <?php if (is_array($arrdata) && count($arrdata)) { ?>
                <span class="data fw-normal">
                    <?php echo esc_html($arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]); ?>
                </span>
                <?php if (!empty($luogo_notizia) && is_array($luogo_notizia)) { ?>
                    <span class="data fw-normal"> | 📍 
                        <?php 
                        foreach ($luogo_notizia as $luogo) {
                            if (isset($luogo['name'], $luogo['url'])) {
                                echo '<a href="' . esc_url($luogo['url']) . '" title="' . esc_attr($luogo['name']) . '">' . esc_html($luogo['name']) . '</a> ';
                            }
                        }
                        ?>
                    </span>
                <?php } ?>
            <?php } ?>
        </div>
        <p class="card-title text-paragraph-medium u-grey-light"><?php echo esc_html($post->post_title); ?></p>
        <p class="text-paragraph-card u-grey-light m-0"><?php echo esc_html($descrizione_breve); ?></p>    
        <hr style="margin-bottom: 20px; width: 200px; height: 1px; background-color: grey; border: none;">
        <div class="card-body">Argomenti: <?php get_template_part("template-parts/common/badges-argomenti"); ?></div>            
        <hr style="margin-bottom: 20px; width: 200px; height: 1px; background-color: grey; border: none;">
        <a class="read-more" href="<?php echo esc_url(get_permalink($post->ID)); ?>" 
           aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
           title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>">
            <span class="text">Vai alla pagina</span>
            <svg class="icon ms-0">
                <use xlink:href="#it-arrow-right"></use>
            </svg>
        </a>
    </div>
</div>
<?php } ?>
