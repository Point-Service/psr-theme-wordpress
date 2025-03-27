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

    <!-- Breadcrumb section -->
    <section class="section bg-white py-2 py-lg-3 py-xl-5">
        <div class="container">
            <div class="row variable-gutters">
                <div class="col-lg-8 col-md-8">
                    <?php get_template_part("template-parts/common/breadcrumb"); ?>
                </div>
                <div class="col-lg-4 col-md-4">
                    <?php get_template_part("template-parts/single/actions"); ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Title and description section -->
    <section class="section bg-white py-2 py-lg-3 py-xl-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8">
                    <div class="section-title">
                        <?php the_archive_title('<h2 class="mb-0">', '</h2>'); ?>
                        <?php the_archive_description("<p>", "</p>"); ?>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Search bar -->
    <section class="section bg-grey-card">
        <div class="container">
            <form role="search" method="get" class="search-form" action="<?php echo home_url('/'); ?>">
                <div class="row">
                    <div class="col-12 pt-30 pt-lg-50 pb-lg-50">
                        <div class="cmp-input-search">
                            <div class="form-group mb-2 mb-lg-4">
                                <div class="input-group">
                                    <input type="search" 
                                        class="form-control" 
                                        placeholder="Cerca una parola chiave"
                                        name="s"
                                        value="<?php echo get_search_query(); ?>">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit">Invio</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>

    <!-- Content Section -->
    <section class="section bg-gray-light">
        <div class="container">
            <div class="row">
                <!-- Sidebar Categories -->
                <div class="col-lg-3">
                    <div class="argomenti-wrapper">
                        <h3 class="mb-4">Argomenti</h3>
                        <div class="row">
                            <?php
                            $args = array(
                                'taxonomy' => 'category',
                                'orderby' => 'name',
                                'order' => 'ASC',
                                'exclude' => get_cat_ID('Senza categoria') // Esclude "Senza categoria"
                            );
                            $terms = get_terms($args);

                            if ($terms && !is_wp_error($terms)) :
                                foreach ($terms as $term) :
                            ?>
                                    <div class="col-12 mb-2">
                                        <a href="<?php echo esc_url(get_term_link($term)); ?>" class="argomento-link">
                                            <?php echo esc_html($term->name); ?>
                                        </a>
                                    </div>
                            <?php
                                endforeach;
                            else :
                                echo '<p>Nessuna categoria disponibile.</p>';
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- Main Content -->
                <div class="col-lg-9">
                    <div class="row">
                        <?php if ( have_posts() ) : ?>
                            <?php while ( have_posts() ) : the_post(); ?>
                                <div class="col-md-6 col-lg-4 mb-4"> <!-- Imposta la larghezza per più elementi per riga -->
                                    <?php get_template_part( 'template-parts/list/article', get_post_type() ); ?>
                                </div>
                            <?php endwhile; ?>
                            <div class="col-12 text-center">
                                <nav class="pagination-wrapper">
                                    <?php echo dci_bootstrap_pagination(); ?>
                                </nav>
                            </div>
                        <?php else : ?>
                            <p>Nessun contenuto trovato.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>

