<?php
global $count, $scheda;

// Recupera l'opzione evidenziata
$post_ids = dci_get_option('notizia_evidenziata', 'homepage', true);
$prefix = '_dci_notizia_';

if (is_array($post_ids) && count($post_ids) > 1):
?>
<div id="carosello-notizie" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
    <div class="carousel-inner">
        <?php
        $first = true;
        foreach ($post_ids as $post_id):
            $post = get_post($post_id);
            if ($post):
                // Meta
                $img = dci_get_meta("immagine", $prefix, $post->ID);
                $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
                $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
                $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
                $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
                $tipo = ($tipo_terms && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;
        ?>
        <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
            <div class="row g-0 align-items-stretch">
                <!-- Colonna testo -->
                <div class="col-lg-5 d-flex align-items-center bg-white position-relative z-1 p-4">
                    <div class="card w-100 border-0 rounded-0">
                        <div class="card-body">
                            <div class="category-top mb-2">
                                <?php if ($tipo): ?>
                                    <a href="<?php echo site_url('tipi_notizia/' . sanitize_title($tipo->name)); ?>" class="fw-semibold text-uppercase small">
                                        <?php echo strtoupper($tipo->name); ?>
                                    </a>
                                <?php endif; ?>
                            </div>
                            <a href="<?php echo get_permalink($post->ID); ?>" class="text-decoration-none">
                                <h3 class="card-title"><?php echo esc_html($post->post_title); ?></h3>
                            </a>
                            <p class="mb-3"><?php echo esc_html($descrizione_breve); ?></p>
                            <p class="mb-2">
                                <small>Data: <?php echo "{$arrdata[0]} {$monthName} {$arrdata[2]}"; ?></small>
                            </p>
                            <a class="read-more d-inline-flex align-items-center" href="<?php echo get_permalink($post->ID); ?>">
                                <span class="text">Vai alla pagina</span>
                                <svg class="icon ms-1"><use xlink:href="#it-arrow-right"></use></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Colonna immagine -->
                <div class="col-lg-7 p-0">
                    <?php if ($img): ?>
                        <div class="carousel-img-wrapper">
                            <?php dci_get_img($img, 'img-fluid w-100 h-100 object-cover'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php
            $first = false;
            endif;
        endforeach;
        ?>
    </div>

    <!-- CONTROLLI -->
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
                    <div class="col-lg-6 offset-lg-1 order-1 order-lg-2 px-0 px-lg-2">
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
/* Contenitore carosello */
#carosello-notizie {
    position: relative;
    z-index: 1;
    margin-bottom: 2rem;
}

/* Altezza minima per ogni slide */
#carosello-notizie .carousel-item {
    min-height: 300px;
}

/* Wrapper immagine per gestire dimensioni e sfondo */
#carosello-notizie .carousel-img-wrapper {
    width: 100%;
    height: 100%;
    min-height: 300px;
    max-height: 400px;
    background-color: #f5f5f5;
    display: flex;
    justify-content: center;
    align-items: center;
    overflow: hidden;
}

/* Immagine adattata dentro il wrapper */
#carosello-notizie img {
    object-fit: cover;
    width: 100%;
    height: 100%;
    display: block;
}

/* Padding interno per il testo */
#carosello-notizie .card-body {
    padding: 1.5rem;
}

/* Colonna testo: posizione relativa e z-index per evitare sovrapposizioni */
#carosello-notizie .col-lg-5 {
    background-color: #fff;
    position: relative;
    z-index: 2;
    display: flex;
    align-items: center;
}

/* Pulsante “Vai alla pagina” stile */
#carosello-notizie .read-more {
    color: #0d6efd;
    font-weight: 600;
    text-decoration: none;
}

#carosello-notizie .read-more:hover {
    text-decoration: underline;
}

/* Controlli carosello */
.carousel-control-prev-icon,
.carousel-control-next-icon {
    filter: invert(1); /* Se vuoi icone bianche su sfondo scuro */
}

/* Responsive per schermi piccoli */
@media (max-width: 991.98px) {
    #carosello-notizie .col-lg-5,
    #carosello-notizie .col-lg-7 {
        flex: 0 0 100%;
        max-width: 100%;
        padding: 1rem;
    }

    #carosello-notizie .carousel-img-wrapper {
        min-height: 200px;
        max-height: none;
    }

    #carosello-notizie img {
        height: auto;
        object-fit: contain;
    }

    #carosello-notizie .card-body {
        padding: 1rem 0;
    }
}

/* Stile specifico per testo singolo post (non carosello) */
.single-post-container {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    margin-bottom: 2rem;
}

.single-post-container .text-col {
    flex: 1 1 40%;
    background-color: #fff;
    padding: 1.5rem;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.single-post-container .image-col {
    flex: 1 1 55%;
    overflow: hidden;
}

.single-post-container .image-col img {
    width: 100%;
    height: auto;
    object-fit: cover;
}

/* Mobile per singolo post */
@media (max-width: 767.98px) {
    .single-post-container {
        flex-direction: column;
    }
    .single-post-container .text-col,
    .single-post-container .image-col {
        flex: 1 1 100%;
        padding: 0;
    }
}

</style>
