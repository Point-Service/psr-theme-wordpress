<?php
/**
 * The template for displaying 404 pages (not found)
 *
 * @link https://codex.wordpress.org/Creating_an_Error_404_Page
 *
 * @package Design_Comuni_Italia
 */

get_header();
?>
<main>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-10">
                <?php get_template_part("template-parts/common/breadcrumb"); ?>
                <div class="cmp-hero">
                    <section class="it-hero-wrapper bg-light align-items-start">
                        <div class="it-hero-text-wrapper pt-0 ps-0 pb-4 pb-lg-60"><br><br>
                            <h2 class="text-center text-danger mb-4"><?php esc_html_e( 'Pagina non trovata', 'design_comuni_italia' ); ?></h2>
                            <p class="text-center text-muted mb-5 fs-5">
                                <?php _e( 
                                    'Siamo spiacenti, ma non siamo riusciti a trovare la pagina o la categoria che stavi cercando. <br><br> 
                                    Ti consigliamo di tornare indietro o esplorare il nostro sito tramite il menu. Puoi anche cliccare il link qui sotto per tornare facilmente alla pagina precedente.',
                                    'design_comuni_italia'
                                ); ?>
                            </p>
                            <div class="text-center">
                                <a href="javascript:history.back();" title="Torna alla pagina precedente" class="btn btn-primary btn-lg">
                                    <?php esc_html_e( 'Torna indietro', 'design_comuni_italia' ); ?>
                                </a>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</main>

<?php
get_footer();

