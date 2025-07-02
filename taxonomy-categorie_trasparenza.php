<?php 
/**
 * Archivio Tassonomia trasparenza
 *
 * @package Design_Comuni_Italia
 */

global $title, $description, $data_element, $elemento, $sito_tematico_id, $siti_tematici;

get_header();
$obj = get_queried_object();

// Pulisco la query di ricerca
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$search_field = $_GET['search_field'] ?? 'all';

// Impostazioni base
$paged = max(1, get_query_var('paged', 1));
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$prefix = '_dci_elemento_trasparenza_';
$order = $_GET['order_type'] ?? 'data_desc';

// Variabili per query
$meta_query = [];
$search_in_title = false;

// Se il campo ricerca è 'all', devo cercare in titolo O meta_descrizione O meta_data_pubblicazione
// WordPress non supporta "OR" diretto fra titolo e meta_query quindi usiamo filtro posts_search + meta_query (rel 'OR' per i meta)

if (!empty($query)) {
    switch ($search_field) {
        case 'title':
            // Solo titolo -> uso query nativa WP con 's'
            $search_in_title = true;
            break;

        case 'descrizione':
            // Solo descrizione
            $meta_query[] = [
                'key'     => $prefix . 'descrizione',
                'value'   => $query,
                'compare' => 'LIKE',
                'type'    => 'CHAR',
            ];
            break;

        case 'data_pubblicazione':
            // Solo data_pubblicazione
            $meta_query[] = [
                'key'     => $prefix . 'data_pubblicazione',
                'value'   => $query,
                'compare' => 'LIKE',
                'type'    => 'CHAR',
            ];
            break;

        case 'all':
        default:
            // Cerca in meta descrizione OR meta data_pubblicazione
            $meta_query = [
                'relation' => 'OR',
                [
                    'key'     => $prefix . 'descrizione',
                    'value'   => $query,
                    'compare' => 'LIKE',
                    'type'    => 'CHAR',
                ],
                [
                    'key'     => $prefix . 'data_pubblicazione',
                    'value'   => $query,
                    'compare' => 'LIKE',
                    'type'    => 'CHAR',
                ],
            ];
            // Per titolo, aggiungiamo filtro posts_search (vedi sotto)
            $search_in_title = true;
            break;
    }
}

// Filtro per aggiungere ricerca titolo quando cerchiamo su meta_query + titolo insieme
// Funziona solo se $search_in_title è true e c'è una meta_query

if ($search_in_title) {
    add_filter('posts_search', function ($search, $wp_query) use ($query, $search_field) {
        global $wpdb;

        // Per sicurezza evitiamo di modificare altre query
        if (empty($query)) {
            return $search;
        }

        // Escape per LIKE
        $like = '%' . $wpdb->esc_like($query) . '%';

        // Se non c'è già una clausola, creiamola solo per titolo
        if (empty($search)) {
            return $wpdb->prepare(" AND {$wpdb->posts}.post_title LIKE %s", $like);
        } else {
            // Se c'è una clausola esistente (meta_query), inserisco OR titolo dentro le parentesi
            // Cerca il primo AND ( e inserisci OR titolo dentro le parentesi
            // es. AND ( (meta_key=descrizione LIKE ...) OR (meta_key=data_pubblicazione LIKE ...) )
            $search = preg_replace(
                '/\bAND\s*\(/i',
                "AND ( {$wpdb->posts}.post_title LIKE '{$like}' OR ",
                $search,
                1
            );
            return $search;
        }
    }, 10, 2);
}

// Costruisco args base della query

$args = [
    'posts_per_page'     => $max_posts,
    'post_type'          => 'elemento_trasparenza',
    'tipi_cat_amm_trasp' => $obj->slug,
    'paged'              => $paged,
];

// Aggiungo ordine e ordinamento
if ($order == 'alfabetico_asc' || $order == 'alfabetico_desc') {
    $args['orderby'] = 'title';
    $args['order'] = ($order == 'alfabetico_desc') ? 'DESC' : 'ASC';
} else {
    // Ordina per meta_key data_pubblicazione (stringa)
    $args['orderby'] = 'meta_value';
    $args['meta_key'] = $prefix . 'data_pubblicazione';
    $args['meta_type'] = 'CHAR';
    $args['order'] = ($order == 'data_desc') ? 'DESC' : 'ASC';
}

// Aggiungo ricerca a seconda del caso

if ($search_in_title && $search_field == 'title') {
    // Solo ricerca titolo standard
    $args['s'] = $query;
} elseif (!$search_in_title && !empty($meta_query)) {
    // Solo meta query senza titolo
    $args['meta_query'] = $meta_query;
} elseif ($search_in_title && !empty($meta_query)) {
    // Cerca in meta_query + titolo: meta_query + filtro posts_search aggiunto sopra
    $args['meta_query'] = $meta_query;
    // Non metto 's' perché la ricerca titolo viene fatta con filtro posts_search (altrimenti WP fa AND tra s e meta_query)
}

// Eseguo query
$the_query = new WP_Query($args);

// Recupero opzioni siti tematici
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
                                <div class="input-group" style="flex-wrap: nowrap;">
                                    <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                                    <input type="search" class="autocomplete form-control"
                                        placeholder="Cerca una parola chiave" id="autocomplete-two" name="search"
                                        value="<?php echo esc_attr($query); ?>" data-bs-autocomplete="[]">

                                    <select name="search_field" class="form-select" style="max-width: 200px; margin-left: 10px;">
                                        <option value="all" <?php selected($search_field, 'all'); ?>>Tutti i campi</option>
                                        <option value="title" <?php selected($search_field, 'title'); ?>>Titolo</option>
                                        <option value="descrizione" <?php selected($search_field, 'descrizione'); ?>>Descrizione</option>
                                        <option value="data_pubblicazione" <?php selected($search_field, 'data_pubblicazione'); ?>>Data pubblicazione</option>
                                    </select>

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
                                <?php echo ($order == 'alfabetico_asc' || $order == 'alfabetico_desc') ? "alfabetico" : "di pubblicazione"; ?>
                                <?php echo ($order == 'data_desc' || $order == 'alfabetico_desc') ? "(Discendente)" : "(Ascendente)"; ?>
                            </p>
                        </div>

                        <div class="form-group mb-4">
                            <span style="font-size: 1.2rem; font-weight: bold; color: #333;">Ordina per</span>
                            <select id="order-select" name="order_type" class="form-control">
                                <option value="data_desc" <?php selected($order, 'data_desc'); ?>>Data (Discendente)</option>
                                <option value="data_asc" <?php selected($order, 'data_asc'); ?>>Data (Ascendente)</option>
                                <option value="alfabetico_asc" <?php selected($order, 'alfabetico_asc'); ?>>Alfabetico (Ascendente)</option>
                                <option value="alfabetico_desc" <?php selected($order, 'alfabetico_desc'); ?>>Alfabetico (Discendente)</option>
                            </select>
                        </div>

                        <?php if ($the_query->have_posts()) { ?>
                            <div class="row g-4" id="load-more">
                                <?php while ($the_query->have_posts()) {
                                    $the_query->the_post();
                                    $elemento = $post;
                                    $load_card_type = "elemento_trasparenza";
                                    get_template_part("template-parts/amministrazione-trasparente/card");
                                } ?>
                            </div>
                            <?php wp_reset_postdata(); ?>
                        <?php } else { ?>
                            <div class="alert alert-info text-center" role="alert">
                                Nessun post trovato.
                            </div>
                        <?php } ?>
                    </div>

                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>

                    <div class="row my-4">
                        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
                            <?php echo dci_bootstrap_pagination(); ?>
                        </nav>
                    </div>
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

<script>
    // Submit automatico on change dell'ordinamento
    document.getElementById('order-select').addEventListener('change', function() {
        setTimeout(function() {
            document.getElementById('search-form').submit();
        }, 100);
    });
</script>

