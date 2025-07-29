<?php
global $count, $scheda;

$posts_ids = dci_get_option('notizia_evidenziata', 'homepage', true); // Ottieni gli ID dei post
$prefix = '_dci_notizia_';

// Se c'è un singolo post ID (non è un array), trattalo come singolo
if ($posts_ids && !is_array($posts_ids)) {
    $posts_ids = array($posts_ids); // Converte il singolo ID in un array
}

// Verifica che ci siano effettivamente post
if ($posts_ids && is_array($posts_ids)) {
    $args = array(
        'post_type' => 'post', // Sostituisci con il tipo di post che usi, se diverso
        'post__in' => $posts_ids, // Limita la query agli ID dei post in evidenza
        'posts_per_page' => count($posts_ids), // Recupera solo il numero di post necessari
        'orderby' => 'post__in', // Ordina secondo l'ordine degli ID
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) : ?>
        <h2 id="novita-in-evidenza" class="visually-hidden">Novità in evidenza</h2>     
        <div id="carouselExample" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $active_class = 'active'; // Prima slide è attiva
                while ($query->have_posts()) : $query->the_post();
                    $img = dci_get_meta("immagine", $prefix, get_the_ID());
                    $arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, get_the_ID());
                    $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
                    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, get_the_ID());
                    $argomenti = dci_get_meta("argomenti", $prefix, get_the_ID());
                    $luogo_notizia = dci_get_meta("luoghi", $prefix, get_the_ID());
                    $tipo_terms = wp_get_post_terms(get_the_ID(), 'tipi_notizia');
                    $tipo = ($tipo_terms && !is_wp_error($tipo_terms)) ? $tipo_terms[0] : null;
                ?>
                    <div class="carousel-item <?php echo $active_class; ?>">
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
                                        <a href="<?php echo get_permalink(); ?>" class="text-decoration-none">
                                            <h3 class="card-title"><?php the_title(); ?></h3>
                                        </a>
                                        <p class="mb-2 font-serif"><?php echo $descrizione_breve; ?></p>
                                        <?php if ($luogo_notizia) : ?>
                                            <span class="data fw-normal"><i class="fas fa-map-marker-alt"></i> <?php echo $luogo_notizia; ?></span>
                                        <?php endif; ?>
                                        <p class="fw-semibold font-monospace">
                                            <?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?>
                                        </p>
                                        <a class="read-more" href="<?php echo get_permalink(); ?>">
                                            Vai alla pagina
                                            <svg class="icon ms-0">
                                                <use xlink:href="#it-arrow-right"></use>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6 offset-lg-1">
                                <?php if ($img) { dci_get_img($img, 'img-fluid'); } ?>
                            </div>
                        </div>
                    </div>
                <?php 
                    $active_class = ''; // Rimuovi "active" per gli altri item
                endwhile;
                ?>
            </div>
            <!-- Controlli del carosello -->
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExample" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExample" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    <?php endif; wp_reset_postdata(); ?>
<?php 
} else {
    // Se non ci sono post o l'ID non è valido
    // Mostra il singolo post (caso default)
    if ($post_id) {
        $post = get_post($post_id);
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
                        <?php if ($luogo_notizia) : ?>
                            <span class="data fw-normal"><i class="fas fa-map-marker-alt"></i> <?php echo $luogo_notizia; ?></span>
                        <?php endif; ?>
                        <p class="fw-semibold font-monospace">
                            <?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?>
                        </p>
                        <a class="read-more" href="<?php echo get_permalink($post->ID); ?>">
                            Vai alla pagina
                            <svg class="icon ms-0">
                                <use xlink:href="#it-arrow-right"></use>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
            <div class="col-lg-6 offset-lg-1">
                <?php if ($img) { dci_get_img($img, 'img-fluid'); } ?>
            </div>
        </div>
    <?php
    }
}
?>
