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

<main id="main-container" class="main-container <?php echo $class; ?>">
    <!-- Title and description section -->
    <section class="section bg-white py-2 py-lg-3 py-xl-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8">
                    <div class="section-title">
                        <?php the_archive_title('<h2 class="mb-0">', '</h2>'); ?>
                        <?php the_archive_description('<p>', '</p>'); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Content Section with Grid Layout -->
    <section class="section bg-gray-light">
        <div class="container">
            <div class="row">
                <?php if ( have_posts() ) : ?>
                    <?php while ( have_posts() ) : the_post(); ?>
                        <div class="col-lg-4 col-md-6 mb-4">
                            <div class="card border-0 shadow-sm p-4 h-100">
                                <div class="card-body">
                                    <span class="badge bg-secondary mb-2">
                                       <?php the_archive_title(); ?>
                                    </span>
                                    <h5 class="card-title"><a href="<?php the_permalink(); ?>" class="text-dark text-decoration-none"><?php the_title(); ?></a></h5>
                                    <p class="text-muted small mb-2">Pubblicato il <?php echo get_the_date('d M Y'); ?></p>
                                    <p class="card-text">
                                        <?php echo get_the_excerpt() ?: 'Nessuna descrizione disponibile.'; ?>
                                    </p>
                                    <a href="<?php the_permalink(); ?>" class="text-primary">Vai alla pagina →</a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
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
