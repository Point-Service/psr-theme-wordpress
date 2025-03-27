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
              <?php
                    // Ottieni il titolo dell'archivio senza prefissi
                    $archive_title = single_term_title('', false);
                    
                    // Ottieni l'URL della pagina 'Amministrazione'
                    $amministrazione_url = get_permalink(get_page_by_path('amministrazione'));
                    
                    // Verifica se il titolo dell'archivio è 'Dataset' o 'Incarichi'
                    if ($archive_title === 'Dataset' || $archive_title === 'Incarichi') {
                        // Costruisce il breadcrumb
                        echo '<br><nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="' . esc_url(home_url()) . '"><strong>Home</strong></a></li>
                                    <li class="breadcrumb-item"><a href="' . esc_url($amministrazione_url) . '"><strong>Amministrazione</strong></a></li>
                                    <li class="breadcrumb-item active" aria-current="page">' . esc_html($archive_title) . '</li>
                                </ol>
                              </nav>';                    
                       
                    } else {
                        // Include il breadcrumb predefinito per altri casi
                        get_template_part('template-parts/common/breadcrumb');
                    }
                    ?>

                </div>
            </div>
            <div class="row">
                <div class="col-lg-8 px-lg-4 py-lg-2">
                <h1 data-audio> <?php the_archive_title(); ?></h1>
                        <?php
                        // Visualizza la descrizione appropriata in base al titolo dell'archivio
                        if ($archive_title === 'Dataset') {
                            echo '<p>In questa sezione sono disponibili i dataset pubblicati dall\'Autorità Nazionale Anticorruzione (ANAC), contenenti informazioni dettagliate sui contratti pubblici in Italia, inclusi appalti, stazioni appaltanti e altri dati rilevanti.</p>';
                        } elseif ($archive_title === 'Incarichi') {
                            echo '<p>Questa sezione fornisce informazioni sugli obblighi di pubblicazione riguardanti i titolari di incarichi di collaborazione o consulenza, come disciplinato dall\'articolo 15 del Decreto Legislativo 33/2013.</p>';
                        }
                        ?>
                    <h2 class="visually-hidden">Dettagli del documento</h2>
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
                                     <div class="col px-lg-4">
                                        <?php the_archive_title(); ?>
                                     </div>
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
                                                echo !empty($content) ? wp_trim_words($content, 20, '...') : '';
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
