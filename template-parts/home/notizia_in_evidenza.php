<?php
global $count, $scheda;

$post_ids = dci_get_option('notizia_evidenziata', 'homepage', true);
$prefix = '_dci_notizia_';

if (is_array($post_ids) && count($post_ids) > 1): ?>
  <section id="novita-in-evidenza" class="container my-5">
    <h2 class="visually-hidden">Novità in evidenza</h2>
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
                $luogo_notizia = dci_get_meta("luoghi", $prefix, $post->ID);
                $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
                $tipo = ($tipo_terms && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;
        ?>
        <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
          <div class="row align-items-center g-4">
            <!-- Testo -->
            <div class="col-lg-5">
              <div class="card h-100">
                <div class="card-body pb-2 d-flex flex-column justify-content-between">
                  <div>
                    <div class="category-top d-flex align-items-center mb-2">
                      <svg class="icon icon-sm me-2" aria-hidden="true">
                        <use xlink:href="#it-calendar"></use>
                      </svg>
                      <?php if ($tipo): ?>
                        <span class="title-xsmall-semi-bold fw-semibold">
                          <a href="<?php echo site_url('tipi_notizia/' . sanitize_title($tipo->name)); ?>" class="text-decoration-none text-uppercase"><?php echo strtoupper($tipo->name); ?></a>
                        </span>
                      <?php endif; ?>
                    </div>

                    <a href="<?php echo get_permalink($post->ID); ?>" class="text-decoration-none">
                      <h3 class="card-title">
                        <?php echo preg_match('/[A-Z]{5,}/', $post->post_title) ? ucfirst(strtolower($post->post_title)) : $post->post_title; ?>
                      </h3>
                    </a>

                    <p class="mb-2 font-serif"><?php echo $descrizione_breve; ?></p>

                    <!-- Luoghi -->
                    <?php if (is_array($luogo_notizia) && count($luogo_notizia)): ?>
                      <p class="text-secondary mb-1">
                        <i class="fas fa-map-marker-alt me-1"></i>
                        <?php foreach ($luogo_notizia as $luogo_id):
                          $luogo_post = get_post($luogo_id);
                          if ($luogo_post && !is_wp_error($luogo_post)) {
                              echo '<a href="' . esc_url(get_permalink($luogo_post->ID)) . '" class="text-uppercase text-decoration-none">' . esc_html($luogo_post->post_title) . '</a> ';
                          }
                        endforeach; ?>
                      </p>
                    <?php endif; ?>

                    <!-- Data -->
                    <p class="mb-1"><small>Data: <strong><?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?></strong></small></p>

                    <!-- Argomenti -->
                    <small>Argomenti:</small>
                    <?php get_template_part("template-parts/common/badges-argomenti"); ?>
                  </div>

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
            <div class="col-lg-6 offset-lg-1">
              <?php if ($img) dci_get_img($img, 'img-fluid rounded'); ?>
            </div>
          </div>
        </div>
        <?php
          $first = false;
          endif;
        endforeach;
        ?>
      </div>

      <!-- Controlli -->
      <button class="carousel-control-prev" type="button" data-bs-target="#carosello-notizie" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Precedente</span>
      </button>
      <button class="carousel-control-next" type="button" data-bs-target="#carosello-notizie" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Successivo</span>
      </button>
    </div>
  </section>

<?php
// CASO SINGOLO
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
    <!-- TESTO -->
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

    <!-- IMMAGINE -->
    <div class="col-lg-6 offset-lg-1 order-1 order-lg-2 px-0 px-lg-2">
      <?php if ($img) {
        dci_get_img($img, 'img-fluid');
      } ?>
    </div>
  </div>

<?php endif; endif; ?>
<style>
  #carosello-notizie .carousel-item {
  padding: 2rem 0;
}

#carosello-notizie .card {
  height: 100%;
}</style>
