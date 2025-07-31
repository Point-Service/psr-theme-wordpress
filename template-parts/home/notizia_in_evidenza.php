<?php
global $count, $scheda;

// Recupera l'opzione evidenziata
$post_ids = dci_get_option('notizia_evidenziata', 'homepage', true);
$prefix = '_dci_notizia_';

if (is_array($post_ids) && count($post_ids) > 1):
?>
  <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>
  <div id="carosello-evidenza" class="carousel slide" data-bs-ride="carousel">
    <div class="carousel-inner">

      <?php foreach ($post_ids as $index => $post_id):
        $post = get_post($post_id);
        if (!$post) continue;

        $img = dci_get_meta("immagine", $prefix, $post->ID);
        $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
        $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
        $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
        $tipo = ($tipo_terms && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;

        $luogo_notizia = dci_get_meta("luoghi", $prefix, $post->ID);
        $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
      ?>

        <div class="carousel-item <?php if ($index === 0) echo 'active'; ?>">
          <div class="container">
            <div class="row flex-column flex-lg-row align-items-center g-0">

              <!-- Testo -->
              <div class="col-12 col-lg-6 order-2 order-lg-1 d-flex align-items-center">
                <div class="card-body">
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

              <!-- Immagine -->
              <div class="col-12 col-lg-6 order-1 order-lg-2 col-img">
                <?php if ($img) {
                  dci_get_img($img, 'img-fluid img-evidenza');
                } ?>
              </div>

            </div>
          </div>
        </div>

      <?php endforeach; ?>
    </div>

    <!-- Controlli -->
    <button class="carousel-control-prev" type="button" data-bs-target="#carosello-evidenza" data-bs-slide="prev">
      <span class="carousel-control-prev-icon"></span>
      <span class="visually-hidden">Precedente</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#carosello-evidenza" data-bs-slide="next">
      <span class="carousel-control-next-icon"></span>
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

<style>
 /* Contenitore del carosello */
#carosello-evidenza {
  position: relative;
  overflow: visible; /* Permette contenuto visibile */
  padding: 1rem 0;
}

/* Carousel inner */
#carosello-evidenza .carousel-inner {
  border-radius: 0;
  overflow: visible !important; /* evita contenuti nascosti */
}

/* Altezza minima slide - tolto min-height fisso per mobile */
#carosello-evidenza .carousel-item {
  min-height: auto;
  display: none; /* Nascondi tutti di default */
  align-items: center;
  justify-content: center;
  padding: 1rem 0;
  width: 100%; /* assicurati che occupi tutta la larghezza */
}

#carosello-evidenza .carousel-item.active {
  display: flex !important; /* Solo attivo è flex e visibile */
  flex-wrap: wrap;
}


  
/* Row all'interno del carosello: permette wrap */
#carosello-evidenza .row {
  flex-wrap: wrap;
  margin-left: 0;
  margin-right: 0;
}

/* Colonna immagine */
#carosello-evidenza .col-img {
  display: flex;
  align-items: center;
  justify-content: flex-end; /* immagine a destra */
  padding: 0 2rem;
  min-height: 300px;
  flex: 1 1 50%;
}

/* Immagine nel carosello */
#carosello-evidenza img.img-evidenza {
  max-width: 100%;
  max-height: 100%;
  object-fit: contain;
  display: block;
  margin-left: auto;
  margin-right: 0;
}

/* Colonna testo */
#carosello-evidenza .card-body {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  padding-left: 3rem; /* margine sinistro aumentato */
  padding-right: 1rem;
  flex: 1 1 50%;
  min-height: auto;
}

/* Titolo e descrizione in singolo elemento (non carousel) */
.row > .col-lg-5 {
  padding-left: 3rem !important; /* margine sinistro testo più ampio */
  padding-right: 1rem !important;
}

/* Immagine singolo elemento */
.row > .col-lg-6.offset-lg-1 {
  padding-left: 1rem !important;
  padding-right: 2rem !important;
  display: flex;
  align-items: center;
  justify-content: flex-end;
}

/* Immagine singolo */
.row > .col-lg-6.offset-lg-1 img {
  max-width: 100%;
  height: auto;
  object-fit: contain;
}

/* --- MOBILE --- */
@media (max-width: 991px) {
  #carosello-evidenza .carousel-item {
    flex-direction: column !important;
    padding: 1rem;
    min-height: auto;
  }

  #carosello-evidenza .col-img {
    justify-content: center !important;
    padding: 0 1rem 1rem;
    min-height: 200px;
  }

  #carosello-evidenza img.img-evidenza {
    max-width: 90%;
    max-height: 300px;
    margin: 0 auto;
  }

  #carosello-evidenza .card-body {
    padding-left: 1rem !important; /* margine sinistro ridotto per mobile */
    padding-right: 1rem !important;
    font-size: 1rem;
  }

  /* Ordine testo sopra immagine nel carousel */
  #carosello-evidenza .col-12.col-lg-6.order-2.order-lg-1 {
    order: 1 !important;
  }

  #carosello-evidenza .col-12.col-lg-6.order-1.order-lg-2 {
    order: 2 !important;
  }

  /* Singolo elemento testo margini ridotti */
  .row > .col-lg-5 {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }

  .row > .col-lg-6.offset-lg-1 {
    padding-left: 1rem !important;
    padding-right: 1rem !important;
  }
}

</style>

