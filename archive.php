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
                <div class="row variable-gutters sticky-sidebar-container">
                    <div class="col-lg-3 bg-white bg-white-left">
						<?php get_template_part("template-parts/search/filters", "argomento"); ?>
                    </div>
                    <div class="col-lg-7 offset-lg-1 pt84">
						<?php if ( have_posts() ) : ?>
							<?php
							/* Start the Loop */
							while ( have_posts() ) :
								the_post();
								get_template_part( 'template-parts/list/article', get_post_type() );

							endwhile;
							?>
                            <nav class="pagination-wrapper justify-content-center col-12">
								<?php echo dci_bootstrap_pagination(); ?>
                            </nav>
						<?php
						else :

							get_template_part( 'template-parts/content', 'none' );

						endif;
						?>
                    </div><!-- /col-lg-8 -->
                </div><!-- /row -->
            </div><!-- /container -->
        </section>

</main><!-- /main -->

<?php get_footer(); ?>

