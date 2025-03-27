<?php
/**
 * The template for displaying archive with modern design
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#archive
 *
 * @package Design_Comuni_Italia
 */

$class = "petrol";
get_header();
?>
    <div class="container" id="main-container">
            <div class="row">
                <div class="col px-lg-4">
                    <?php get_template_part("template-parts/common/breadcrumb"); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 px-lg-4 py-lg-2">
                    <?php if (preg_match('/[A-Z]{5,}/', get_the_title())) {
                        echo '<h1 data-audio>' . ucfirst(strtolower(get_the_title())) . '</h1>';
                    } else {
                        echo '<h1 data-audio>' . get_the_title() . '</h1>';
                    }
                    ?>
                    <h2 class="visually-hidden">Dettagli del documento</h2>
                    <p data-audio>
                      <?php the_archive_title(); ?>
                    </p>
                </div>
                <div class="col-lg-3 offset-lg-1">
                    <?php
                    $inline = true;
                    get_template_part('template-parts/single/actions');
                    ?>
                </div>
            </div>
        </div><!-- ./main-container -->

    <!-- Content Section with Grid Layout -->
    <section class="section bg-gray-light">
        <div class="container">
            <div class="row">
                <?php if ( have_posts() ) : ?>
                    <?php while ( have_posts() ) : the_post(); ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card border rounded shadow-sm p-4 h-100" style="border: 1px solid #ddd; background: #fff;">
                                <div class="card-body">
                                    <!-- Badge con il titolo dell'archivio -->
                                    <span class="badge bg-light text-dark mb-2 px-3 py-1" style="font-weight: 600;">
                                        <?php the_archive_title(); ?>
                                    </span>

                                    <!-- Badge con la categoria/tipologia -->
                                    <?php 
                                        $terms = get_the_terms(get_the_ID(), 'category');
                                        if ($terms && !is_wp_error($terms)) : ?>
                                            <span class="badge bg-secondary text-white mb-2 px-3 py-1">
                                                <?php echo esc_html($terms[0]->name); ?>
                                            </span>
                                    <?php endif; ?>

                                    <!-- Titolo -->
                                    <h5 class="card-title">
                                        <a href="<?php the_permalink(); ?>" class="text-dark text-decoration-none"><?php the_title(); ?></a>
                                    </h5>

                                    <!-- Data di pubblicazione -->
                                    <p class="text-muted small mb-2">Pubblicato il <?php echo get_the_date('d M Y'); ?></p>

                                    <!-- Descrizione migliorata -->
                                    <p class="card-text">
                                        <?php 
                                            $excerpt = get_the_excerpt();
                                            if (!empty($excerpt)) {
                                                echo esc_html($excerpt);
                                            } else {
                                                $content = wp_strip_all_tags(get_the_content());
                                                echo !empty($content) ? wp_trim_words($content, 20, '...') : 'Nessuna descrizione disponibile.';
                                            }
                                        ?>
                                    </p>

                                    <!-- Link alla pagina -->
                                    <a href="<?php the_permalink(); ?>" class="text-primary text-decoration-none">Vai alla pagina →</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>

                    <!-- Paginazione -->
                    <div class="col-12 d-flex justify-content-center">
                        <?php echo dci_bootstrap_pagination(); ?>
                    </div>

                <?php else : ?>
                    <p class="text-center">Nessun contenuto trovato.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
