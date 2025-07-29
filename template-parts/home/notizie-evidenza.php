<?php
global $scheda;

$post = get_post($scheda) ?? null;
$img = dci_get_meta('immagine');
$descrizione_breve = dci_get_meta('descrizione_breve');
$icon = dci_get_post_type_icon_by_id($post->ID);
$page = get_page_by_path(dci_get_group($post->post_type));
$page_macro_slug = dci_get_group($post->post_type);
$page_macro = get_page_by_path($page_macro_slug);

// Recuperiamo i post da mostrare nel carosello
$args = array(
    'post_type' => 'your_post_type', // Sostituisci con il tipo di post che vuoi mostrare
    'posts_per_page' => 5, // Numero massimo di post da mostrare nel carosello
    'post_status' => 'publish',
    'orderby' => 'date', // Ordina per data (o quello che preferisci)
);
$query = new WP_Query($args);
?>
ddddddddddddddddd
<?php if ($query->have_posts()) : ?>
    <div id="postCarousel" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php
            $first = true;
            while ($query->have_posts()) : $query->the_post();
                $img = dci_get_meta('immagine');
                $descrizione_breve = dci_get_meta('descrizione_breve');
                $icon = dci_get_post_type_icon_by_id(get_the_ID());
                ?>
                <div class="carousel-item <?php echo $first ? 'active' : ''; ?>">
                    <div class="card card-teaser <?php echo $img ? 'card-teaser-image' : ''; ?> card-flex no-after rounded shadow-sm border border-light mb-0">
                        <?php if ($img) { ?>
                            <div class="card-image-wrapper with-read-more">
                                <div class="card-body p-3 u-grey-light">
                                    <div class="category-top">
                                        <span class="category fw-semibold"><?php echo $page->post_title ?></span>
                                    </div>
                                    <h3 class="card-title h5"><?php echo get_the_title() ?></h3>
                                    <p class="card-text text-secondary" style="margin-bottom: 40px!important;"><?php echo $descrizione_breve ?></p>
                                </div>
                                <div class="card-image card-image-rounded pb-5">
                                    <?php dci_get_img($img); ?>
                                </div>
                            </div>
                            <a class="read-more ps-3" href="<?php echo get_permalink(); ?>" aria-label="Vai alla pagina <?php echo get_the_title(); ?>" title="Vai alla pagina <?php echo get_the_title(); ?>">
                                <span class="text">Vai alla pagina</span>
                                <svg class="icon">
                                    <use xlink:href="#it-arrow-right"></use>
                                </svg>
                            </a>
                        <?php } else { ?>
                            <div class="card-body pb-5">
                                <div class="category-top">
                                    <span class="category title-xsmall-semi-bold fw-semibold"><?php echo $page->post_title ?></span>
                                </div>
                                <h3 class="card-title h5"><?php echo get_the_title() ?></h3>
                                <p class="card-text text-secondary"><?php echo $descrizione_breve ?></p>
                            </div>
                            <a class="read-more" href="<?php echo get_permalink(); ?>" aria-label="Vai alla pagina <?php echo get_the_title(); ?>" title="Vai alla pagina <?php echo get_the_title(); ?>">
                                <span class="text">Vai alla pagina</span>
                                <svg class="icon ms-0">
                                    <use xlink:href="#it-arrow-right"></use>
                                </svg>
                            </a>
                        <?php } ?>
                    </div>
                </div>
                <?php
                $first = false; // Rimuove l'`active` dalla prima slide dopo la prima
            endwhile;
            ?>
        </div>

        <!-- Controlli del carosello -->
        <button class="carousel-control-prev" type="button" data-bs-target="#postCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#postCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>
<?php endif; ?>

<?php wp_reset_postdata(); ?>

