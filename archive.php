<?php
/**
 * Template per visualizzazione moderna dell'archivio
 */
$class = "petrol";
get_header();
?>

<main id="main-container" class="main-container <?php echo $class; ?>">
    <section class="section bg-white py-2 py-lg-3 py-xl-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8">
                    <div class="section-title">
                        <?php the_archive_title('<h2 class="mb-0">', '</h2>'); ?>
                        <?php the_archive_description('<p>', '</p>'); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section bg-grey-card">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <form role="search" method="get" action="<?php echo home_url('/'); ?>" class="search-form">
                        <div class="input-group mb-4">
                            <input type="search" class="form-control" placeholder="Cerca..." name="s" value="<?php echo get_search_query(); ?>">
                            <button class="btn btn-primary" type="submit">Cerca</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>

    <section class="section bg-gray-light">
        <div class="container">
            <div class="row">
                <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
                    <div class="col-md-6 col-lg-4 mb-4">
                        <div class="card border-0 shadow-sm rounded-lg p-4">
                            <div class="card-body">
                                <p class="text-muted small">📰 NEWS — <?php echo get_the_date('d M y'); ?></p>
                                <h5 class="card-title font-weight-bold">
                                    <a href="<?php the_permalink(); ?>" class="text-dark text-decoration-none">
                                        <?php the_title(); ?>
                                    </a>
                                </h5>
                                <p class="card-text text-muted">
                                    <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
                                </p>
                                <p class="mb-2"><strong>Argomenti:</strong> 
                                    <?php the_category(' '); ?>
                                </p>
                                <a href="<?php the_permalink(); ?>" class="btn btn-link text-primary p-0">Vai alla pagina →</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
            <nav class="pagination-wrapper justify-content-center mt-4">
                <?php echo paginate_links(); ?>
            </nav>
            <?php else :
                echo '<p class="text-center">Nessun contenuto trovato.</p>';
            endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
