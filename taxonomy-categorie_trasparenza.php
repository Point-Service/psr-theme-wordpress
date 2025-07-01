<?php
/**
 * Archivio Tassonomia trasparenza con ordinamento personalizzato per data di pubblicazione o titolo
 */

global $title, $description, $data_element, $elemento, $sito_tematico_id, $siti_tematici;

get_header();
$obj = get_queried_object();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'data_desc';

$args = [
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type' => 'elemento_trasparenza',
    'paged' => $paged,
    'tax_query' => [
        [
            'taxonomy' => 'tipi_cat_amm_trasp',
            'field'    => 'slug',
            'terms'    => $obj->slug,
        ],
    ],
];

// Query senza ordinamento specifico per data
$the_query = new WP_Query($args);

// Funzione per ottenere data pubblicazione come intero YYYYMMDD
function estrai_data_pubblicazione_int($post) {
    $prefix = '_dci_elemento_trasparenza_';
    $arrayDataPubblicazione = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);

    if (!empty($arrayDataPubblicazione) && count($arrayDataPubblicazione) === 3) {
        $giorno = str_pad($arrayDataPubblicazione[0], 2, '0', STR_PAD_LEFT);
        $mese = str_pad($arrayDataPubblicazione[1], 2, '0', STR_PAD_LEFT);
        $anno = intval($arrayDataPubblicazione[2]);
        if ($anno < 100) {
            $anno += 2000;
        }
        return intval("{$anno}{$mese}{$giorno}");
    }

    // fallback: usa la data di pubblicazione WordPress
    return intval(get_the_date('Ymd', $post));
}

// Se abbiamo risultati e ordinamento per data, ordiniamo i post in PHP
if ($the_query->have_posts() && $orderby === 'data_desc') {
    $posts_array = $the_query->posts;

    usort($posts_array, function($a, $b) {
        $dataA = estrai_data_pubblicazione_int($a);
        $dataB = estrai_data_pubblicazione_int($b);
        return $dataB <=> $dataA; // ordine decrescente (più recente prima)
    });

    // Sovrascriviamo i post nell'oggetto WP_Query con quelli ordinati
    $the_query->posts = $posts_array;
    $the_query->post_count = count($posts_array);
} elseif ($orderby === 'alpha' && $the_query->have_posts()) {
    // Ordina alfabeticamente per titolo in PHP (se vuoi)
    $posts_array = $the_query->posts;
    usort($posts_array, function($a, $b) {
        return strcasecmp($a->post_title, $b->post_title);
    });
    $the_query->posts = $posts_array;
    $the_query->post_count = count($posts_array);
}

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

<div class="bg-grey-card">
    <form role="search" id="search-form" method="get" class="search-form">
        <button type="submit" class="d-none"></button>
        <div class="container">
            <div class="row">
                <h2 class="visually-hidden">Esplora tutti i documenti della trasparenza</h2>

                <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                    <div class="cmp-input-search">
                        <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                            <div class="input-group">
                                <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                                <input type="search" class="autocomplete form-control" placeholder="Cerca una parola chiave" id="autocomplete-two" name="search" value="<?php echo esc_attr($query); ?>">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit">Invio</button>
                                </div>
                                <span class="autocomplete-icon" aria-hidden="true">
                                    <svg class="icon icon-sm icon-primary" role="img" aria-labelledby="autocomplete-label">
                                        <use href="#it-search"></use>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center mb-3">
                            <label class="me-2">Ordina per:</label>
                            <select name="orderby" class="form-select w-auto" onchange="this.form.submit()">
                                <option value="data_desc" <?php selected($orderby, 'data_desc'); ?>>Data (più recenti)</option>
                                <option value="alpha" <?php selected($orderby, 'alpha'); ?>>Titolo (A-Z)</option>
                            </select>
                        </div>

                        <p id="autocomplete-label" class="mb-4">
                            <strong><?php echo $the_query->found_posts; ?></strong> elementi trovati in ordine
                            <?php echo $orderby === 'alpha' ? 'alfabetico' : 'di data (decrescente)'; ?>
                        </p>
                    </div>

                    <?php if ($the_query->have_posts()) { ?>
                        <div class="row g-4" id="load-more">
                            <?php
                            // Ciclo manuale su $the_query->posts perché li abbiamo ordinati in PHP
                            foreach ($the_query->posts as $elemento) {
                                setup_postdata($elemento);
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

                    <div class="row my-4">
                        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
                            <?php echo dci_bootstrap_pagination(); ?>
                        </nav>
                    </div>
                </div>

                <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
            </div>
        </div>
    </form>
</div>
</main>

<?php
get_template_part("template-parts/common/valuta-servizio");
get_template_part("template-parts/common/assistenza-contatti");
get_footer();
?>

