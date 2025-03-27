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

    <!-- Breadcrumb section, placed similarly to the second code -->
    <section class="section bg-white py-2 py-lg-3 py-xl-5">
        <div class="container">
            <div class="row variable-gutters">
                <div class="col-lg-8 col-md-8">
                    <?php get_template_part("template-parts/common/breadcrumb"); ?>
                </div><!-- /col-lg-8 col-md-8 -->

                <div class="col-lg-4 col-md-4">
                    <?php get_template_part("template-parts/single/actions"); ?>
                </div><!-- /col-lg-4 col-md-4 -->
            </div><!-- /row -->
        </div><!-- /container -->
    </section><!-- /section -->

    <!-- Title and description section -->
    <section class="section bg-white py-2 py-lg-3 py-xl-5">
        <div class="container">
            <div class="row">
                <div class="col-lg-8 col-md-8">
                    <div class="section-title">
                        <?php the_archive_title( '<h2 class="mb-0">', '</h2>' ); ?>
                        <?php the_archive_description("<p>", "</p>"); ?>
                    </div><!-- /title-section -->
                </div><!-- /col-lg-8 col-md-8 -->
            </div><!-- /row -->
        </div><!-- /container -->
    </section><!-- /section -->

    <!-- Search bar with autocomplete (from the second code) -->
    <section class="section bg-grey-card">
        <div class="container">
            <form role="search" id="search-form" method="get" class="search-form">
                <button type="submit" class="d-none"></button>
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti i documenti</h2>
                    <div class="col-12 pt-30 pt-lg-50 pb-lg-50">
                        <div class="cmp-input-search">
                            <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                                <div class="input-group">
                                    <label for="autocomplete-search" class="visually-hidden">Cerca una parola chiave</label>
                                    <input type="search" 
                                        class="autocomplete form-control" 
                                        placeholder="Cerca una parola chiave"
                                        id="autocomplete-search"
                                        name="search"
                                        value="<?php echo isset($_GET['search']) ? esc_attr($_GET['search']) : ''; ?>"
                                        data-bs-autocomplete="[]">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit" id="button-search">
                                            Invio
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <p id="autocomplete-label" class="mb-4">
                                <strong><?php echo $wp_query->found_posts; ?> </strong>documenti trovati in ordine alfabetico
                            </p>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section><!-- /section -->

    <!-- Content Section with Grid Layout -->
    <section class="section bg-gray-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-3">
                    <?php get_template_part("template-parts/search/filters", "argomento"); ?>
                </div><!-- /col-lg-3 -->

                <div class="col-lg-9">
                    <div class="row g-4" id="load-more">
                        <?php
                        // WP_Query for retrieving posts (logic from the first code)
                        $max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 6;
                        $query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
                        $args = array(
                            's' => $query,
                            'posts_per_page' => $max_posts,
                            'post_type'      => 'post', // Post generici, modifica se necessario
                            'orderby'        => 'post_title',
                            'order'          => 'DESC',
                        );
                        $the_query = new WP_Query($args);

                        if ( $the_query->have_posts() ) :
                            while ( $the_query->have_posts() ) :
                                $the_query->the_post();
                                get_template_part( 'template-parts/list/article', get_post_type() );
                            endwhile;
                        else :
                            get_template_part( 'template-parts/content', 'none' );
                        endif;

                        wp_reset_postdata();
                        ?>
                    </div><!-- /row -->

                    <!-- Pagination -->
                    <nav class="pagination-wrapper justify-content-center col-12">
                        <?php echo dci_bootstrap_pagination(); ?>
                    </nav>
                </div><!-- /col-lg-9 -->
            </div><!-- /row -->
        </div><!-- /container -->
    </section><!-- /section -->

</main><!-- /main -->

<?php get_footer(); ?>

