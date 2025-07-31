<?php
global $count, $scheda;

// Recupera l'opzione evidenziata
$post_ids = dci_get_option('notizia_evidenziata', 'homepage', true);
$prefix = '_dci_notizia_';

if (is_array($post_ids) && count($post_ids) > 1):
?>
    <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>
    <div id="carosello-evidenza" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5000">
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
                <div class="row g-0">

                    <?php if ($img): ?>
                    <div class="col-12 col-lg-6 image-column ms-auto">
                        <?php dci_get_img($img, 'img-fluid'); ?>
                    </div>
                    <?php endif; ?>

                    <div class="col-12 col-lg-6 text-column">
                        <div class="card w-100 border-0 rounded-0">
                            <div class="card-body">

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

                                            <div class="row mt-2 mb-3">
                                                <div class="col-6">
                                                    <small>Data:</small>
                                                    <p class="fw-semibold font-monospace"><?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?></p>
                                                </div>
                                            </div>

                                            <small>Argomenti: </small>
                                            <?php get_template_part("template-parts/common/badges-argomenti"); ?>

                                            <a class="read-more mt-4 d-inline-flex align-items-center" href="<?php echo get_permalink($post->ID); ?>" aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>">
                                                <span class="text">Vai alla pagina</span>
                                                <svg class="icon ms-1">
                                                    <use xlink:href="#it-arrow-right"></use>
                                                </svg>
                                            </a>
                                        </div>
                                    </div>
                                </div>

                            </div> <!-- card-body -->
                        </div> <!-- card -->
                    </div> <!-- col text-column -->

                </div> <!-- row -->
            </div> <!-- carousel-item -->

            <?php
                $first = false;
                endif;
            endforeach;
            ?>
        </div> <!-- carousel-inner -->

        <button class="carousel-control-prev" type="button" data-bs-target="#carosello-evidenza" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Precedente</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carosello-evidenza" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Successivo</span>
        </button>
    </div> <!-- carosello-evidenza -->


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

                 <?php if ($img): ?>
                    <!-- Immagine -->
                    <div class="col-lg-6 offset-lg-1 order-1 order-lg-2 px-0 px-lg-2">
                        <?php dci_get_img($img, 'img-fluid'); ?>
                    </div>
                <?php endif; ?>
    </div>

<?php
    endif;
endif;
?>



<script>
  document.addEventListener('DOMContentLoaded', function () {
    const carosello = document.getElementById('carosello-evidenza');
    if (!carosello) return;

    function updateCarouselHeight() {
      const activeItem = carosello.querySelector('.carousel-item.active');
      if (activeItem) {
        const inner = carosello.querySelector('.carousel-inner');
        inner.style.height = activeItem.scrollHeight + 'px';
      }
    }

    // Primo calcolo altezza
    updateCarouselHeight();

    // Al cambio slide
    carosello.addEventListener('slid.bs.carousel', updateCarouselHeight);

    // Ricalcola su resize (mobile -> desktop ecc.)
    window.addEventListener('resize', updateCarouselHeight);
  });
</script>



    <style>
/* === Carosello Evidenza (Mobile First) === */
#carosello-evidenza {
  position: relative;
  overflow: hidden;
}

#carosello-evidenza .carousel-inner {
  width: 100%;
  overflow: visible;
}

#carosello-evidenza .carousel-item {
  width: 100%;
  height: auto;
  padding: 1rem 0;
}

#carosello-evidenza .carousel-item .row {
  display: flex;
  flex-direction: column;
  align-items: stretch;
  margin: 0;
}

/* Immagine sopra (mobile) */
#carosello-evidenza .carousel-item .image-column {
  order: -1;
  text-align: center;
  background-color: #f5f5f5;
  padding: 1rem;
}

#carosello-evidenza .carousel-item img.img-fluid {
  max-width: 100%;
  max-height: 250px;
  object-fit: contain;
  display: block;
  margin: 0 auto;
}

/* Testo sotto immagine (mobile) */
#carosello-evidenza .carousel-item .text-column {
  padding: 1rem;
}

#carosello-evidenza .carousel-item .card {
  border: none;
  border-radius: 0;
  width: 100%;
}

#carosello-evidenza .carousel-item .card-body {
  padding: 1rem;
  overflow: visible;
}

/* Controlli carosello */
#carosello-evidenza .carousel-control-prev,
#carosello-evidenza .carousel-control-next {
  width: 5%;
}

/* Titoli, descrizioni, testo */
.carousel-item .card-title {
  font-size: 1.25rem;
  font-weight: 600;
}

.carousel-item .read-more {
  font-size: 0.875rem;
  font-weight: 500;
}

.carousel-item .font-serif {
  font-family: Georgia, serif;
  font-size: 0.95rem;
}

.carousel-item .data {
  font-size: 0.85rem;
  color: #666;
}

.carousel-item .card-body small {
  font-weight: 500;
}

/* === Layout Desktop === */
@media (min-width: 992px) {
  #carosello-evidenza .carousel-item .row {
    flex-direction: row;
  }

  .image-column {
    order: 2;
    padding: 3rem;
    display: flex;
    align-items: center;
    justify-content: center;
  }

  .text-column {
    order: 1;
    padding: 3rem;
    display: flex;
    align-items: center;
  }

  #carosello-evidenza .carousel-item img.img-fluid {
    max-height: 300px;
  }
}

</style>

