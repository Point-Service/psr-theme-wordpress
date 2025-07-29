<?php
global $count, $scheda;

// Ottieni l'ID dei post in evidenza (multi-elemento possibile)
$post_ids = dci_get_option('notizia_evidenziata', 'homepage', true);

$prefix = '_dci_notizia_';

// Verifica se ci sono più di un elemento
if ($post_ids && count($post_ids) > 1) {
    // Multi-elemento: carosello
    ?>
    <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>
    <div id="carosello-notizie" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $first = true;
            foreach ($post_ids as $post_id) {
                $post = get_post($post_id);
                if ($post) {
                    $img = dci_get_meta("immagine", $prefix, $post->ID);
                    $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
                    $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
                    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
                    $argomenti = dci_get_meta("argomenti", $prefix, $post->ID);
                    $luogo_notizia = dci_get_meta("luoghi", $prefix, $post->ID);

                    $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
                    $tipo = $tipo_terms && !is_wp_error($tipo_terms) ? $tipo_terms[0] : null;

                    ?>
                    <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                        <div class="row">
                            <div class="col-lg-5">
                                <div class="card mb-0">
                                    <div class="card-body pb-2">
                                        <div class="category-top d-flex align-items-center">
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
                                            <h3 class="card-title"><?php echo $post->post_title; ?></h3>
                                        </a>
                                        <p class="mb-2 font-serif"><?php echo $descrizione_breve; ?></p>
                                        <?php if ($luogo_notizia) { ?>
                                            <span class="data fw-normal"><i class="fas fa-map-marker-alt"></i> <?php echo esc_html($luogo_notizia); ?></span>
                                        <?php } ?>
                                        <div class="row mt-2 mb-1">
                                            <div class="col-6">
                                                <small>Data:</small>
                                                <p class="fw-semibold font-monospace">
                                                    <?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?>
                                                </p>
                                            </div>
                                        </div>
                                        <small>Argomenti: </small>
                                        <?php get_template_part("template-parts/common/badges-argomenti"); ?>
                                        <a class="read-more" href="<?php echo get_permalink($post->ID); ?>" aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" style="display: inline-flex; align-items: center; margin-top: 30px;">
                                            <span class="text">Vai alla pagina</span>
                                            <svg class="icon">
                                                <use xlink:href="#it-arrow-right"></use>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 offset-lg-1 px-0 px-lg-2">
                                <?php if ($img) {
                                    dci_get_img($img, 'img-fluid');
                                } ?>
                            </div>
                        </div>
                    </div>
                    <?php
                    $first = false;
                }
            }
            ?>
        </div>
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
} else {
    // Singolo elemento: visualizza normalmente
    $post_id = $post_ids[0] ?? null;
    if ($post_id) {
        $post = get_post($post_id);
        $img = dci_get_meta("immagine", $prefix, $post->ID);
        $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
        $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
        $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $post->ID);
        $argomenti = dci_get_meta("argomenti", $prefix, $post->ID);
        $luogo_notizia = dci_get_meta("luoghi", $prefix, $post->ID);

        $tipo_terms = wp_get_post_terms($post->ID, 'tipi_notizia');
        $tipo = $tipo_terms && !is_wp_error($tipo_terms) ? $tipo_terms[0] : null;

        ?>
        <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>     
        <div class="row">
            <div class="col-lg-5 order-2 order-lg-1">
                <div class="card mb-0">
                    <div class="card-body pb-2">
                        <div class="category-top d-flex align-items-center">
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
                            <h3 class="card-title"><?php echo $post->post_title; ?></h3>
                        </a>
                        <p class="mb-2 font-serif"><?php echo $descrizione_breve; ?></p>
                        <?php if ($luogo_notizia) { ?>
                            <span class="data fw-normal"><i class="fas fa-map-marker-alt"></i> <?php echo esc_html($luogo_notizia); ?></span>
                        <?php } ?>
                        <div class="row mt-2 mb-1">
                            <div class="col-6">
                                <small>Data:</small>
                                <p class="fw-semibold font-monospace">
                                    <?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?>
                                </p>
                            </div>
                        </div>
                        <small>Argomenti: </small>
                        <?php get_template_part("template-parts/common/badges-argomenti"); ?>
                        <a class="read-more" href="<?php echo get_permalink($post->ID); ?>" aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" style="display: inline-flex; align-items: center; margin-top: 30px;">
                            <span class="text">Vai alla pagina</span>
                            <svg class="icon">
                                <use xlink:href="#it-arrow-right"></use>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 offset-lg-1 px-0 px-lg-2">
                <?php if ($img) {
                    dci_get_img($img, 'img-fluid');
                } ?>
            </div>
        </div>
        <?php
    }
}
?>
