<?php
global $post;

$prefix = '_dci_evento_';
$img = dci_get_meta('immagine', $prefix, $post->ID);
$descrizione = dci_get_meta('descrizione_breve', $prefix, $post->ID);
$start_timestamp = dci_get_meta('data_orario_inizio', $prefix, $post->ID);
$start_date = date_i18n('d/m', date($start_timestamp));
$start_date_arr = explode('-', date_i18n('d-F-Y-H-i', date($start_timestamp)));
$end_timestamp = dci_get_meta("data_orario_fine", $prefix, $post->ID);
$end_date = date_i18n('d/m', date($end_timestamp));
$end_date_arr = explode('-', date_i18n('d-F-Y-H-i', date($end_timestamp)));
$tipo_evento = get_the_terms($post->ID,'tipi_evento')[0];
$arrdata = explode('-', date_i18n("j-F-Y", $start_timestamp));
$luogo_evento = dci_get_meta("luogo_evento", $prefix, $post->ID);
if ($luogo_evento_id) $luogo_evento = get_post($luogo_evento_id);
?>

<div class="col-lg-6 col-xl-4">
    <div class="card-wrapper shadow-sm rounded border border-light pb-0">
        <div class="card no-after rounded">
            <div class="img-responsive-wrapper">
                <div class="img-responsive img-responsive-panoramic">
                    <figure class="img-wrapper">
                        <?php dci_get_img($img ?: get_template_directory_uri()."/assets/img/repertorio/abdul-a-CxRBtNe243k-unsplash.jpg", 'rounded-top img-fluid', 'medium_large'); ?>
                    </figure>
                    <div class="card-calendar d-flex flex-column justify-content-center">
                        <span class="card-date"><?php echo $arrdata[0]; ?></span>
                        <span class="card-day"><?php echo $arrdata[1]; ?></span>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="category-top">
                    <a class="category text-decoration-none"
                        href="<?= get_term_link($tipo_evento->term_id); ?>">
                        <?php echo $tipo_evento->name; ?>
                    </a>
                    <?php if ($start_timestamp && $end_timestamp && $start_date != $end_date) { ?>
                    <span class="data u-grey-light">dal <?php echo $start_date; ?> al <?php echo $end_date; ?></span>
                    <?php } ?>
                </div>
                <h3 class="card-title">
                    <a class="text-decoration-none"
                        href="<?php echo get_permalink($post->ID); ?>"
                        data-element="live-category-link">
                        <?php echo $post->post_title ?>
                    </a>
                </h3>
                <p class="card-text text-secondary pb-3">
                    <?php echo $descrizione; ?>
                </p>
                
                    <?php if (!empty($luogo_evento)) { ?>
                        <span class="data fw-normal">📍 
                            <?php 
                            // Ottieni i dettagli del luogo
                            $luogo_post = get_post($luogo_evento);
                            
                            if ($luogo_post && !is_wp_error($luogo_post)) {
                                // Stampa il nome del luogo come link
                                echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" title="' . esc_attr($luogo_post->post_title) . '" class="t-primary text-uppercase">' . esc_html($luogo_post->post_title) . '</a>';
                            }
                            ?>
                        </span>
                    <?php } elseif (!empty($luogo_notizia)) { ?>
                        <span class="data fw-normal"> | 📍 
                            <?php echo esc_html($luogo_notizia); ?>
                        </span>
                    <?php } ?>
                     <hr style="margin-bottom: 25px; width: 200px; height: 1px; background-color: grey; border: none;">

                
                <a class="read-more t-primary text-uppercase"
                    href="<?php echo get_permalink($post->ID); ?>"
                    aria-label="Leggi di più sulla pagina di <?php echo $post->post_title ?>">
                    <span class="text">Leggi di più</span>
                    <span class="visually-hidden"></span>
                    <svg class="icon icon-primary icon-xs ml-10">
                        <use href="#it-arrow-right"></use>
                    </svg>
                </a>
            </div>
        </div>
    </div>
</div>

