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
                      <?php
                        if(mb_strlen($descrizione_breve) > 150){
                          $descrizione_limitata = mb_substr($descrizione_breve, 0, 150) . '...';
                        } else {
                          $descrizione_limitata = $descrizione_breve;
                        }
                        echo preg_match('/[A-Z]{5,}/', $descrizione_limitata) ? ucfirst(strtolower($descrizione_limitata)) : $descrizione_limitata;
                      ?>
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
<div class="row single-news single-news-custom">
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
</div>
<?php
  endif;
endif;
?>

<style>
/* Contenitore del carosello */
#carosello-evidenza {
  position: relative;
  overflow: hidden;
  width: 110%; /* Allarga leggermente il carosello */
  margin-left: -5%; /* Centra meglio il carosello */
}

/* Altezza minima slide */
#carosello-evidenza .carousel-item {
  min-height: 400px;
}

/* Evita overflow visivo */
#carosello-evidenza .carousel-inner {
  border-radius: 0;
  overflow: hidden;
}

/* Immagine: container grigio */
#carosello-evidenza .col-img {
  display: flex;
  align-items: center;
  justify-content: center; /* centrato per mobile */
  padding: 0 1rem;
  min-height: 300px;
}

/* Immagine: stile base */
#carosello-evidenza img.img-evidenza {
  max-width: 90%;
  max-height: 300px;
  width: auto;
  height: auto;
  object-fit: contain;
  display: block;
  margin: 0 auto; /* centrato per mobile */
}

/* Testo della card */
#carosello-evidenza .card-body {
  display: flex;
  flex-direction: column;
  justify-content: flex-start; /* Allinea contenuto in alto */
  min-height: 320px;
  padding: 0 1rem;
}


/* Versione desktop (da 992px in su) */
@media (min-width: 992px) {
  #carosello-evidenza .card-body {
    padding: 0 1rem;
  }

  /* Box immagine */
  #carosello-evidenza .col-img {
    justify-content: flex-end; /* spinge immagine a destra */
    padding: 0 2rem;
    min-height: 400px;
  }

  /* Immagine in desktop - aumentiamo la larghezza orizzontale */
  #carosello-evidenza img.img-evidenza {
    max-width: 120%; /* Aumenta la larghezza */
    max-height: 100%;
    margin-left: auto;
    margin-right: 0;
    object-fit: cover; /* Assicura che l'immagine riempi lo spazio senza distorsioni */
  }

  /* Frecce di navigazione */
  .carousel-control-prev, .carousel-control-next {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    z-index: 1;
    background-color: rgba(0, 0, 0, 0.5); /* Colore semi-trasparente per le frecce */
    width: 50px; /* Larghezza delle frecce */
    height: 50px; /* Altezza delle frecce */
    border-radius: 50%; /* Rende le frecce circolari */
  }

  /* Freccia sinistra */
  .carousel-control-prev {
    left: -20px; /* Sposta la freccia a sinistra */
  }

  /* Freccia destra */
  .carousel-control-next {
    right: -20px; /* Sposta la freccia a destra */
  }

  /* Controllo visibilità frecce su mobile */
  @media (max-width: 991px) {
    .carousel-control-prev, .carousel-control-next {
      width: 40px; /* Frecce più piccole su mobile */
      height: 40px;
    }
  }
}
  
/* Modifica per centrare l'immagine sui dispositivi mobili */
@media (max-width: 991px) {
  /* Centra l'immagine nel carosello */
  #carosello-evidenza .col-img {
    justify-content: center; /* Centra orizzontalmente */
  }

  /* Centra l'immagine su mobile */
  #carosello-evidenza img.img-evidenza {
    margin: 0 auto; /* Centra l'immagine */
    max-width: 100%;
    height: auto;
  }
}

  
</style>
