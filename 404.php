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
                        <section class="it-hero-wrapper bg-white align-items-start">
                            <div class="it-hero-text-wrapper pt-0 ps-0 pb-4 pb-lg-60">
                                <!-- Inizio testo modificato -->
                                <h2 class="text-center text-primary mb-4"><?php esc_html_e( 'Pagina non trovata', 'design_comuni_italia' ); ?></h2>
                                <p class="text-center custom-text"><?php _e( 
                                    'Siamo spiacenti, ma non siamo riusciti a trovare la pagina o la categoria che stavi cercando. <br><br> 
                                    Ti consigliamo di tornare indietro o esplorare il nostro sito tramite il menu. Puoi anche cliccare il link qui sotto per tornare facilmente alla pagina precedente.',
                                    'design_comuni_italia'
                                ); ?></p>
                                <div class="text-center">
                                    <a href="javascript:history.back();" title="Torna alla pagina precedente" class="btn btn-primary btn-lg">
                                        <?php esc_html_e( 'Torna indietro', 'design_comuni_italia' ); ?>
                                    </a>
                                </div>
                                <!-- Fine testo modificato -->
                            </div>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </main>

<?php
get_footer();
?>

<style>
    /* Personalizzazione del testo */
    .custom-text {
        font-size: 18px;
        color: #343a40; /* Grigio scuro per il testo */
        line-height: 1.6;
        text-align: center;
        margin-bottom: 30px;
    }

    .it-hero-wrapper {
        background-color: #f7f7f7;
        padding: 40px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Ombra leggera */
    }

    .it-hero-text-wrapper h2 {
        font-size: 36px;
        font-weight: bold;
        color: #007bff; /* Colore blu per il titolo */
        margin-bottom: 20px;
    }

    .it-hero-text-wrapper .btn {
        margin-top: 20px;
        padding: 15px 30px;
        background-color: #007bff;
        color: white;
        font-size: 18px;
        border-radius: 50px;
        transition: background-color 0.3s ease;
    }

    .it-hero-text-wrapper .btn:hover {
        background-color: #0056b3;
    }
</style>

