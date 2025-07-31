<?php
global $count, $scheda;

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
          $luogo_notizia = dci_get_meta("luoghi", $prefix, $post->ID);
          $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
          $tipo = ($tipo_terms && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;
      ?>
      <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
        <div class="container">
          <div class="row align-items-center">
            <!-- Testo a sinistra -->
            <div class="col-12 col-lg-6 order-2 order-lg-1">
              <div class="card border-0">
                <div class="card-body">
                  <div class="category-top d-flex align-items-center mb-2">
                    <svg class="icon icon-sm me-2" aria-hidden="true">
                      <use xlink:href="#it-calendar"></use>
                    </svg>
                    <?php if ($tipo): ?>
                    <a href="<?php echo site_url('tipi_notizia/' . sanitize_title($tipo->name)); ?>" class="category fw-semibold text-uppercase">
                      <?php echo strtoupper($tipo->name); ?>
                    </a>
                    <?php endif; ?>
                  </div>

                  <a href="<?php echo get_permalink($post->ID); ?>" class="text-decoration-none">
                    <h3 class="card-title">
                      <?php echo preg_match('/[A-Z]{5,}/', $post->post_title) ? ucfirst(strtolower($post->post_title)) : $post->post_title; ?>
                    </h3>
                  </a>

                  <p class="font-serif mb-3">
                    <?php echo preg_match('/[A-Z]{5,}/', $descrizione_breve) ? ucfirst(strtolower($descrizione_breve)) : $descrizione_breve; ?>
                  </p>

                  <?php if (is_array($luogo_notizia) && count($luogo_notizia)): ?>
                    <p class="mb-2"><i class="fas fa-map-marker-alt me-1"></i>
                      <?php foreach ($luogo_notizia as $luogo_id):
                        $luogo_post = get_post($luogo_id);
                        if ($luogo_post && !is_wp_error($luogo_post)):
                          echo '<a href="'.esc_url(get_permalink($luogo_post->ID)).'" class="text-secondary text-uppercase me-2">'.esc_html($luogo_post->post_title).'</a>';
                        endif;
                      endforeach; ?>
                    </p>
                  <?php elseif (!empty($luogo_notizia)): ?>
                    <p><i class="fas fa-map-marker-alt me-1"></i><?php echo esc_html($luogo_notizia); ?></p>
                  <?php endif; ?>

                  <p class="mb-2"><small>Data:</small><br>
                    <span class="fw-semibold font-monospace"><?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?></span>
                  </p>

                  <small>Argomenti:</small>
                  <?php get_template_part("template-parts/common/badges-argomenti"); ?>

                  <a href="<?php echo get_permalink($post->ID); ?>" class="btn btn-primary mt-3" aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>">
                    Vai alla pagina
                    <svg class="icon ms-1">
                      <use xlink:href="#it-arrow-right"></use>
                    </svg>
                  </a>
                </div>
              </div>
            </div>

            <!-- Immagine a destra -->
            <?php if ($img): ?>
            <div class="col-12 col-lg-6 order-1 order-lg-2 d-flex justify-content-center">
              <?php dci_get_img($img, 'img-fluid'); ?>
            </div>
            <?php endif; ?>

          </div> <!-- row -->
        </div> <!-- container -->
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
  </div>

<?php
elseif (!empty($post_ids)):
  $post_id = is_array($post_ids) ? $post_ids[0] : $post_ids;
  $post = get_post($post_id);
  if ($post):
    $img = dci_get_meta("immagine", $prefix, $post->ID);
    $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
    $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
    $luogo_notizia = dci_get_meta("luoghi", $prefix, $post->ID);
    $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
    $tipo = ($tipo_terms && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;
?>
  <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>
  <div class="container">
    <div class="row align-items-center">
      <!-- Testo -->
      <div class="col-12 col-lg-5 order-2 order-lg-1">
        <div class="card border-0">
          <div class="card-body">
            <div class="category-top d-flex align-items-center mb-2">
              <svg class="icon icon-sm me-2" aria-hidden="true">
                <use xlink:href="#it-calendar"></use>
              </svg>
              <?php if ($tipo): ?>
                <a href="<?php echo site_url('tipi_notizia/' . sanitize_title($tipo->name)); ?>" class="category fw-semibold text-uppercase">
                  <?php echo strtoupper($tipo->name); ?>
                </a>
              <?php endif; ?>
            </div>

            <a href="<?php echo get_permalink($post->ID); ?>" class="text-decoration-none">
              <h3 class="card-title">
                <?php echo preg_match('/[A-Z]{5,}/', $post->post_title) ? ucfirst(strtolower($post->post_title)) : $post->post_title; ?>
              </h3>
            </a>

            <p class="font-serif mb-3">
              <?php echo preg_match('/[A-Z]{5,}/', $descrizione_breve) ? ucfirst(strtolower($descrizione_breve)) : $descrizione_breve; ?>
            </p>

            <?php if (is_array($luogo_notizia) && count($luogo_notizia)): ?>
              <p><i class="fas fa-map-marker-alt me-1"></i>
                <?php foreach ($luogo_notizia as $luogo_id):
                  $luogo_post = get_post($luogo_id);
                  if ($luogo_post && !is_wp_error($luogo_post)):
                    echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" class="text-secondary text-uppercase me-2">' . esc_html($luogo_post->post_title) . '</a>';
                  endif;
                endforeach; ?>
              </p>
            <?php elseif (!empty($luogo_notizia)): ?>
              <p><i class="fas fa-map-marker-alt me-1"></i><?php echo esc_html($luogo_notizia); ?></p>
            <?php endif; ?>

            <p class="mb-2"><small>Data:</small><br>
              <span class="fw-semibold font-monospace"><?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?></span>
            </p>

            <small>Argomenti:</small>
            <?php get_template_part("template-parts/common/badges-argomenti"); ?>

            <a href="<?php echo get_permalink($post->ID); ?>" class="btn btn-primary mt-3" aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>">
              Vai alla pagina
              <svg class="icon ms-1">
                <use xlink:href="#it-arrow-right"></use>
              </svg>
            </a>
          </div>
        </div>
      </div>

      <!-- Immagine -->
      <?php if ($img): ?>
      <div class="col-12 col-lg-6 offset-lg-1 order-1 order-lg-2 d-flex justify-content-center">
        <?php dci_get_img($img, 'img-fluid'); ?>
      </div>
      <?php endif; ?>
    </div>
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

    updateCarouselHeight();

    carosello.addEventListener('slid.bs.carousel', updateCarouselHeight);
    window.addEventListener('resize', updateCarouselHeight);
  });
</script>

<style>
  /* Mobile first */
  #carosello-evidenza .carousel-item .row {
    flex-direction: column;
  }

  #carosello-evidenza .carousel-item .order-1 {
    order: 1;
  }

  #carosello-evidenza .carousel-item .order-2 {
    order: 2;
  }

  /* Immagine sopra testo in mobile */
  #carosello-evidenza .carousel-item .order-1 {
    margin-bottom: 1rem;
  }

  /* Desktop */
  @media(min-width: 992px) {
    #carosello-evidenza .carousel-item .row {
      flex-direction: row;
    }
    /* testo a sinistra */
    #carosello-evidenza .carousel-item .order-lg-1 {
      order: 1;
    }
    /* immagine a destra */
    #carosello-evidenza .carousel-item .order-lg-2 {
      order: 2;
    }
  }
</style>


