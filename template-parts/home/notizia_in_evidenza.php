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
                      <?php 
                        $truncated_title = mb_strlen($post->post_title) > 100 ? mb_substr($post->post_title, 0, 100) . '...' : $post->post_title;
                        // Applicazione della logica per maiuscole
                        echo preg_match('/[A-Z]{5,}/', $truncated_title) ? ucfirst(strtolower($truncated_title)) : $truncated_title; ?>
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


<section class="front__hero-news container py-4" style="padding-top:2rem; padding-bottom:2rem;">
  <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>

  <div class="hero-article d-flex flex-column flex-lg-row align-items-center justify-content-center" 
       style="display:flex; flex-direction:column; align-items:center; justify-content:center; gap:2rem;">

    <!-- Immagine -->
    <?php if ($img): ?>
      <div class="hero-article__image-wrapper w-100 w-lg-auto" style="width:100%; max-width:600px;">
        <?php dci_get_img($img, 'hero-article__image w-100', [
          'style' => 'border-radius:0.5rem; object-fit:cover; max-height:400px; width:100%; height:auto;'
        ]); ?>
      </div>
    <?php endif; ?>

    <!-- Contenuto -->
    <div class="hero-article__content w-100" style="width:100%; max-width:700px;">
      <div class="card rounded shadow-sm border border-light" 
           style="border:1px solid #f0f0f0; border-radius:0.5rem; box-shadow:0 2px 4px rgba(0,0,0,0.08); background:#fff;">
        <div class="card-wrapper" style="width:100%;">
          <div class="card-body" style="padding:1.5rem; display:flex; flex-direction:column; gap:0.75rem;">

            <!-- Categoria + Data -->
            <div class="category-top d-flex justify-content-between align-items-center mb-2" 
                 style="display:flex; justify-content:space-between; align-items:center; font-size:0.9rem; color:#6c757d;">
              <?php if ($tipo): ?>
                <a class="category text-decoration-none" 
                   href="<?php echo site_url('tipi_notizia/' . sanitize_title($tipo->name)); ?>"
                   style="text-decoration:none; color:#0066cc; font-weight:600;">
                  <?php echo esc_html($tipo->name); ?>
                </a>
              <?php endif; ?>
              <span class="data" style="font-size:0.9rem; color:#495057;">
                <?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?>
              </span>
            </div>

            <!-- Titolo -->
            <a href="<?php echo get_permalink($post->ID); ?>" class="text-decoration-none" 
               style="text-decoration:none; color:#212529;">
              <h3 class="card-title big-heading h5" 
                  style="font-weight:600; font-size:1.25rem; margin:0.5rem 0;">
                <?php echo preg_match('/[A-Z]{5,}/', $post->post_title) ? ucfirst(strtolower($post->post_title)) : $post->post_title; ?>
              </h3>
            </a>

            <!-- Descrizione -->
            <p class="card-text font-serif" 
               style="font-family:Georgia, 'Times New Roman', serif; font-size:1rem; color:#333; margin:0;">
              <?php echo preg_match('/[A-Z]{5,}/', $descrizione_breve) ? ucfirst(strtolower($descrizione_breve)) : $descrizione_breve; ?>
            </p>

            <!-- Argomenti -->
            <ul class="d-flex flex-wrap gap-1" 
                style="display:flex; flex-wrap:wrap; gap:0.25rem; list-style:none; padding:0; margin:0.75rem 0;">
              <?php get_template_part("template-parts/common/badges-argomenti"); ?>
            </ul>

            <!-- Link “Leggi di più” -->
            <a class="read-more" href="<?php echo get_permalink($post->ID); ?>" 
               style="display:inline-flex; align-items:center; gap:0.3rem; text-decoration:none; font-weight:500; color:#0066cc; margin-top:0.75rem;">
              <span class="text">Leggi di più</span>
              <svg class="icon" aria-hidden="true" aria-label="Leggi di più su <?php echo esc_attr($post->post_title); ?>" 
                   style="width:1rem; height:1rem; fill:currentColor;">
                <use href="/themes/custom/comune-tr/dist/svg/sprites.svg#it-arrow-right"></use>
              </svg>
            </a>

          </div>
        </div>
      </div>
    </div>
  </div>
</section>




<?php
  endif;
endif;
?>

<style>
/* Contenitore del carosello */
#carosello-evidenza {
  position: relative;
  overflow: hidden;
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

  /* Immagine in desktop */
  #carosello-evidenza img.img-evidenza {
    max-width: 100%;
    max-height: 100%;
    margin-left: auto;
    margin-right: 0;
  }
}

/* Stili singolo post con classe personalizzata */
.single-news.single-news-custom .row .col-lg-6.offset-lg-1.order-1.order-lg-2 {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  min-height: 400px;
  padding-right: 5rem; /* margine più ampio a destra immagine */
}

.single-news.single-news-custom .row .col-lg-6.offset-lg-1.order-1.order-lg-2 img.img-fluid {
  max-width: 90%;
  max-height: 400px;
  width: auto;
  height: auto;
  object-fit: contain;
  display: block;
  margin-left: auto;
}

.single-news.single-news-custom .row .col-lg-5.order-2.order-lg-1 {
  padding-left: 0rem; /* spostato più a sinistra */
  padding-right: 0rem;
}

/* Stili originali singolo post senza classe custom (per sicurezza) */
.single-news .row .col-lg-6.offset-lg-1.order-1.order-lg-2 {
  display: flex;
  align-items: center;
  justify-content: flex-end;
  min-height: 400px;
  padding-right: 7rem;
}

.single-news .row .col-lg-6.offset-lg-1.order-1.order-lg-2 img.img-fluid {
  max-width: 90%;
  max-height: 400px;
  width: auto;
  height: auto;
  object-fit: contain;
  display: block;
  margin-left: auto;
  transform: translateX(19px);
}

.single-news .row .col-lg-5.order-2.order-lg-1 {
  padding-left: 1.5rem;
  padding-right: 1rem;
}
  
/* Modifica per centrare l'immagine sui dispositivi mobili */
@media (max-width: 991px) {
  /* Centra l'immagine nel carosello e nei post singoli su mobile */
  #carosello-evidenza .col-img,
  .single-news.single-news-custom .row .col-lg-6.offset-lg-1.order-1.order-lg-2 {
    justify-content: center; /* Centra orizzontalmente */
  }

  /* Centra l'immagine su mobile */
  #carosello-evidenza img.img-evidenza,
  .single-news.single-news-custom .row .col-lg-6.offset-lg-1.order-1.order-lg-2 img.img-fluid {
    margin: 0 auto; /* Centra l'immagine */
    max-width: 100%;
    height: auto;
  }
}
  
</style>
