<?php
global $count, $scheda;

// Recupera l'opzione evidenziata
$post_ids = dci_get_option('notizia_evidenziata', 'homepage', true);
$prefix = '_dci_notizia_';

if (is_array($post_ids) && count($post_ids) > 1):
?>
    <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>
    <div id="carosello-notizie" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
        <div class="carousel-inner">
            <?php
            $first = true;
            foreach ($post_ids as $post_id):
                $post = get_post($post_id);
                if ($post):
                    $img = dci_get_meta("immagine", $prefix, $post->ID);
                    $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
                    $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
                    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
                    $argomenti = dci_get_meta("argomenti", $prefix, $post->ID);
                    $luogo_notizia = dci_get_meta("luoghi", $prefix, $post->ID);
                    $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
                    $tipo = ($tipo_terms && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;
            ?>
                <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                    <div class="row align-items-stretch g-0">
                    
                        <!-- Immagine -->
                        <div class="col-lg-6 order-1 order-lg-2 px-0 px-lg-2">
                            <?php if ($img) {
                                dci_get_img($img, 'img-responsive-carousel');
                            } ?>
                        </div>
                        
                        <!-- Testo -->
                        <div class="col-lg-6 order-2 order-lg-1 d-flex align-items-center">
                            <div class="card w-100 border-0 rounded-0">
                                <div class="card-body py-4 px-3 px-lg-4">
                                    <div class="category-top d-flex align-items-center mb-2">
                                        <svg class="icon icon-sm me-2" aria-hidden="true">
                                            <use xlink:href="#it-calendar"></use>
                                        </svg>
                                        <?php if ($tipo): ?>
                                            <span class="title-xsmall-semi-bold fw-semibold">
                                                <a href="<?php echo site_url('tipi_notizia/' . sanitize_title($tipo->name)); ?>" class="category title-xsmall-semi-bold fw-semibold"><?php echo strtoupper($tipo->name); ?></a>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <a href="<?php echo get_permalink($post->ID); ?>" class="text-decoration-none">
                                        <h3 class="card-title mb-3">
                                            <?php echo preg_match('/[A-Z]{5,}/', $post->post_title) ? ucfirst(strtolower($post->post_title)) : $post->post_title; ?>
                                        </h3>
                                    </a>
                                    <p class="mb-3 font-serif">
                                        <?php echo preg_match('/[A-Z]{5,}/', $descrizione_breve) ? ucfirst(strtolower($descrizione_breve)) : $descrizione_breve; ?>
                                    </p>

                                    <!-- Luoghi -->
                                    <?php if (is_array($luogo_notizia) && count($luogo_notizia)): ?>
                                        <span class="data fw-normal d-block mb-2">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php foreach ($luogo_notizia as $luogo_id):
                                                $luogo_post = get_post($luogo_id);
                                                if ($luogo_post && !is_wp_error($luogo_post)): ?>
                                                    <a href="<?php echo esc_url(get_permalink($luogo_post->ID)); ?>" class="card-text text-secondary text-uppercase pb-1"><?php echo esc_html($luogo_post->post_title); ?></a>
                                                <?php endif;
                                            endforeach; ?>
                                        </span>
                                    <?php elseif (!empty($luogo_notizia)): ?>
                                        <span class="data fw-normal d-block mb-2">
                                            <i class="fas fa-map-marker-alt me-1"></i><?php echo esc_html($luogo_notizia); ?>
                                        </span>
                                    <?php endif; ?>

                                    <!-- Data -->
                                    <div class="row mt-2 mb-3">
                                        <div class="col-6">
                                            <small>Data:</small>
                                            <p class="fw-semibold font-monospace"><?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?></p>
                                        </div>
                                    </div>

                                    <!-- Argomenti -->
                                    <small>Argomenti: </small>
                                    <?php get_template_part("template-parts/common/badges-argomenti"); ?>

                                    <!-- Pulsante -->
                                    <a class="read-more mt-4 d-inline-flex align-items-center" href="<?php echo get_permalink($post->ID); ?>" aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>">
                                        <span class="text">Vai alla pagina</span>
                                        <svg class="icon ms-1">
                                            <use xlink:href="#it-arrow-right"></use>
                                        </svg>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
                $first = false;
                endif;
            endforeach;
            ?>
        </div>

        <!-- Controlli carosello -->
        <button class="carousel-control-prev" type="button" data-bs-target="#carosello-notizie" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Precedente</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carosello-notizie" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Successivo</span>
        </button>
    </div>

<?php
// CASO SINGOLO POST
elseif (!empty($post_ids)):
    $post_id = is_array($post_ids) ? $post_ids[0] : $post_ids;
    $post = get_post($post_id);
    if ($post):
        $img = dci_get_meta("immagine", $prefix, $post->ID);
        $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
        $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
        $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
        $argomenti = dci_get_meta("argomenti", $prefix, $post->ID);
        $luogo_notizia = dci_get_meta("luoghi", $prefix, $post->ID);
        $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
        $tipo = ($tipo_terms && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;
?>

    <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>
    <div class="row">
        <!-- Testo -->
        <div class="col-lg-5 order-2 order-lg-1">
            <div class="card mb-0">
                <div class="card-body pb-2">
                    <div class="category-top d-flex align-items-center mb-2">
                        <svg class="icon icon-sm me-2" aria-hidden="true">
                            <use xlink:href="#it-calendar"></use>
                        </svg>
                        <?php if ($tipo): ?>
                            <span class="title-xsmall-semi-bold fw-semibold">
                                <a href="<?php echo site_url('tipi_notizia/' . sanitize_title($tipo->name)); ?>" class="category title-xsmall-semi-bold fw-semibold"><?php echo strtoupper($tipo->name); ?></a>
                            </span>
                        <?php endif; ?>
                    </div>

                    <a href="<?php echo get_permalink($post->ID); ?>" class="text-decoration-none">
                        <h3 class="card-title">
                            <?php echo preg_match('/[A-Z]{5,}/', $post->post_title) ? ucfirst(strtolower($post->post_title)) : $post->post_title; ?>
                        </h3>
                    </a>

                    <p class="mb-2 font-serif">
                        <?php echo preg_match('/[A-Z]{5,}/', $descrizione_breve) ? ucfirst(strtolower($descrizione_breve)) : $descrizione_breve; ?>
                    </p>

                    <!-- Luoghi -->
                    <?php if (is_array($luogo_notizia) && count($luogo_notizia)): ?>
                        <span class="data fw-normal"><i class="fas fa-map-marker-alt me-1"></i>
                            <?php foreach ($luogo_notizia as $luogo_id):
                                $luogo_post = get_post($luogo_id);
                                if ($luogo_post && !is_wp_error($luogo_post)) {
                                    echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" class="card-text text-secondary text-uppercase pb-1">' . esc_html($luogo_post->post_title) . '</a> ';
                                }
                            endforeach; ?>
                        </span>
                    <?php elseif (!empty($luogo_notizia)): ?>
                        <span class="data fw-normal"><i class="fas fa-map-marker-alt me-1"></i><?php echo esc_html($luogo_notizia); ?></span>
                    <?php endif; ?>

                    <!-- Data -->
                    <div class="row mt-2 mb-1">
                        <div class="col-6">
                            <small>Data:</small>
                            <p class="fw-semibold font-monospace"><?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?></p>
                        </div>
                    </div>

                    <!-- Argomenti -->
                    <small>Argomenti: </small>
                    <?php get_template_part("template-parts/common/badges-argomenti"); ?>

                    <a class="read-more mt-4 d-inline-flex align-items-center" href="<?php echo get_permalink($post->ID); ?>">
                        <span class="text">Vai alla pagina</span>
                        <svg class="icon ms-1">
                            <use xlink:href="#it-arrow-right"></use>
                        </svg>
                    </a>
                </div>
            </div>
        </div>

        <!-- Immagine -->
        <div class="col-lg-6 order-1 order-lg-2 px-0 px-lg-2">
            <?php if ($img) {
                dci_get_img($img, 'img-fluid');
            } ?>
        </div>
    </div>

<?php
    endif;
endif;
?>

<!-- STILI -->
<style>
.carousel-item {
    min-height: 400px;
}
.carousel-inner {
    border-radius: 0;
    overflow: hidden;
}
#carosello-notizie img.img-fluid,
#carosello-notizie img.cover-img {
    width: 100%;
    height: auto;
    display: block;
}

@media (min-width: 992px) {
    #carosello-notizie .carousel-item .col-lg-6.order-1 {
        max-height: 100%;
    }

    #carosello-notizie .carousel-item .d-none.d-lg-block {
        height: 100%;
    }

    #carosello-notizie .carousel-item .d-none.d-lg-block img.cover-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    #carosello-notizie .card-body {
        padding-left: 3rem;
        padding-right: 3rem;
    }
}

#carosello-notizie .card-body {
    padding-left: 1rem;
    padding-right: 1rem;
}

    #carosello-notizie .img-responsive-carousel {
    width: 100%;
    max-height: 100%;
    object-fit: contain;
    display: block;
    margin: 0 auto;
}

@media (min-width: 992px) {
    #carosello-notizie .col-lg-6.order-1 {
        display: flex;
        justify-content: center;
        align-items: center;
        max-height: 100%;
        overflow: hidden;
        background-color: #f8f9fa; /* opzionale: colore di sfondo */
    }

    #carosello-notizie .img-responsive-carousel {
        max-width: 100%;
        max-height: 400px;
        height: auto;
    }
}

    #carosello-notizie .card-body {
    padding-left: 1.5rem;
    padding-right: 1.5rem;
    overflow: visible;
}

#carosello-notizie .col-lg-6.order-2.order-lg-1 {
    overflow: visible !important;
    max-height: none !important;
}

.read-more {
    white-space: nowrap;
    overflow: visible;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}

    
/* Sposta a sinistra il testo nel singolo elemento */
@media (min-width: 992px) {
    .row > .col-lg-5.order-2.order-lg-1 {
        padding-left: 0.5rem; /* regola questo valore a piacere */
    }
}
</style>
