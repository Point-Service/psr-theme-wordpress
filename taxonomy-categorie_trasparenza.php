<?php
/**
 * Archivio Tassonomia trasparenza
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#custom-taxonomies
 * @package Design_Comuni_Italia
 */

global $title, $description, $data_element, $elemento, $sito_tematico_id, $siti_tematici;

get_header();
$obj = get_queried_object();

// Recupera il numero di pagina corrente.
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Parametri GET
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$order_by = isset($_GET['order_by']) ? sanitize_text_field($_GET['order_by']) : 'alpha';

// Costruzione args query con ordinamento dinamico
$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type' => 'elemento_trasparenza',
    'tipi_cat_amm_trasp' => $obj->slug,
    'paged' => $paged,
);

if ($order_by === 'date') {
    $args['orderby'] = 'date';
    $args['order'] = 'DESC'; // Data più recente prima
} else {
    $args['orderby'] = 'title';
    $args['order'] = 'ASC';   // Ordine alfabetico
}

$the_query = new WP_Query($args);

$additional_filter = array(
    array(
        'taxonomy' => 'tipi_cat_amm_trasp',
        'field' => 'slug',
        'terms' => $obj->slug
    )
);

$siti_tematici = !empty(dci_get_option("siti_tematici", "trasparenza")) ? dci_get_option("siti_tematici", "trasparenza") : [];
?>

<main>
    <?php
    $title = $obj->name;
    $description = $obj->description;
    $data_element = 'data-element="page-name"';
    get_template_part("template-parts/hero/hero"); 
    get_template_part("template-parts/amministrazione-trasparente/sottocategorie"); 
    ?>

    <div class="bg-grey-card container my-4">

        <!-- Pulsanti ordinamento -->
        <div class="sorting-options mb-4">
            <?php 
            // Mantieni i parametri GET esistenti tranne order_by
            $base_params = $_GET;
            unset($base_params['order_by']);
            ?>
            <a href="?<?php echo http_build_query(array_merge($base_params, ['order_by' => 'alpha'])); ?>" class="<?php echo $order_by === 'alpha' ? 'active' : ''; ?>">
                Ordina Alfabetico
            </a> |
            <a href="?<?php echo http_build_query(array_merge($base_params, ['order_by' => 'date'])); ?>" class="<?php echo $order_by === 'date' ? 'active' : ''; ?>">
                Ordina per Data
            </a>
        </div>

        <?php if ($obj->name == "Contratti Pubblici") { ?>
            <div class="row">
                <h2 class="visually-hidden">Esplora tutti i bandi di gara</h2>
                <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20"></div>
                <div class="row g-3" id="load-more">
                    <?php get_template_part("template-parts/bandi-di-gara/tutti-bandi"); ?>
                </div>
                <!-- <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?> -->
            </div>
        <?php } else { ?>

            <form role="search" id="search-form" method="get" class="search-form">
                <button type="submit" class="d-none"></button>
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti i documenti della trasparenza</h2>

                    <!-- Colonna sinistra: risultati -->
                    <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                        <div class="cmp-input-search">
                            <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                                <div class="input-group">
                                    <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                                    <input type="search" class="autocomplete form-control"
                                        placeholder="Cerca una parola chiave" id="autocomplete-two" name="search"
                                        value="<?php echo esc_attr($query); ?>" data-bs-autocomplete="[]">
                                    <div class="input-group-append">
                                        <button class="btn btn-primary" type="submit" id="button-3">Invio</button>
                                    </div>
                                    <span class="autocomplete-icon" aria-hidden="true">
                                        <svg class="icon icon-sm icon-primary" role="img" aria-labelledby="autocomplete-label">
                                            <use href="#it-search"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <p id="autocomplete-label" class="mb-4">
                                <strong><?php echo $the_query->found_posts; ?></strong> elementi trovati in ordine
                                <?php echo $order_by === 'date' ? 'per data' : 'alfabetico'; ?>
                            </p>
                        </div>

                        <?php if ($the_query->have_posts()) { ?>
                            <div class="row g-4" id="load-more">
                                <?php
                                while ($the_query->have_posts()) {
                                    $the_query->the_post();
                                    $elemento = $post;
                                    $load_card_type = "elemento_trasparenza";
                                    get_template_part("template-parts/amministrazione-trasparente/card");
                                }
                                wp_reset_postdata();
                                ?>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-info text-center" role="alert">
                                Nessun post trovato.
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Colonna destra: link utili -->
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                </div>

                <div class="row my-4">
                    <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
                        <?php
                        // Mantieni tutti i parametri GET per la paginazione
                        $big = 999999999; // un numero grande per sostituire nel paginate_links
                        echo paginate_links(array(
                            'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                            'format' => '?paged=%#%',
                            'current' => max(1, $paged),
                            'total' => $the_query->max_num_pages,
                            'add_args' => $_GET, // importantissimo per mantenere search e order_by
                            'prev_text' => '« Precedente',
                            'next_text' => 'Successivo »',
                        ));
                        ?>
                    </nav>
                </div>
            </form>

        <?php } ?>

    </div>
</main>

<?php
get_template_part("template-parts/common/valuta-servizio");
get_template_part("template-parts/common/assistenza-contatti");
get_footer();
?>
