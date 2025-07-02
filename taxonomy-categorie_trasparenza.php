<?php 
/**
 * Archivio Tassonomia trasparenza
 *
 * @package Design_Comuni_Italia
 */

global $title, $description, $data_element, $elemento, $sito_tematico_id, $siti_tematici;

get_header();
$obj = get_queried_object();

// Forza la ricerca nel titolo anche se c'è meta_query
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$search_field = $_GET['search_field'] ?? 'all';

add_filter('posts_search', function ($search, $wp_query) use ($query, $search_field) {
    global $wpdb;

    if (!is_admin() && !empty($query) && $search_field === 'all') {
        $search = " AND ({$wpdb->posts}.post_title LIKE '%" . esc_sql($wpdb->esc_like($query)) . "%')";
    }

    return $search;
}, 10, 2);

// Recupera il numero di pagina corrente.
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$prefix = '_dci_elemento_trasparenza_';

// Gestione ordinamento
$order = isset($_GET['order_type']) ? $_GET['order_type'] : 'data_desc';

$meta_query = array('relation' => 'OR');
$search_in_title = false;

if (!empty($query)) {
switch ($search_field) {
    case 'title':
        $search_in_title = true;
        break;

    case 'descrizione':
        $meta_query[] = array(
            'key' => $prefix . 'descrizione',
            'value' => '%' . $query . '%',
            'compare' => 'LIKE'
        );
        break;

    case 'data_pubblicazione':
        $meta_query[] = array(
            'key' => $prefix . 'data_pubblicazione',
            'value' => '%' . $query . '%',
            'compare' => 'LIKE',
            'type' => 'CHAR'
        );
        break;

    case 'all':
    default:
        $search_in_title = true;
        $meta_query[] = array(
            'key' => $prefix . 'descrizione',
            'value' => '%' . $query . '%',
            'compare' => 'LIKE'
        );
        $meta_query[] = array(
            'key' => $prefix . 'data_pubblicazione',
            'value' => '%' . $query . '%',
            'compare' => 'LIKE',
            'type' => 'CHAR'
        );
        break;
}

}

$args = array(
    'posts_per_page' => $max_posts,
    'post_type' => 'elemento_trasparenza',
    'tipi_cat_amm_trasp' => $obj->slug,
    'paged' => $paged,
    'meta_key' => $prefix . 'data_pubblicazione',
    'orderby' => ($order == 'alfabetico_asc' || $order == 'alfabetico_desc') ? 'title' : 'meta_value_num',
    'order' => ($order == 'data_desc' || $order == 'alfabetico_desc') ? 'DESC' : 'ASC',
);

if ($search_in_title) {
    $args['s'] = $query;
}
if (!$search_in_title || $search_field === 'all') {
    $args['meta_query'] = $meta_query;
}

$the_query = new WP_Query($args);
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

                    <!-- Colonna sinistra -->
                    <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                        <div class="cmp-input-search">
                            <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                                <div class="input-group" style="flex-wrap: nowrap;">
                                    <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                                    <input type="search" class="autocomplete form-control"
                                        placeholder="Cerca una parola chiave" id="autocomplete-two" name="search"
                                        value="<?php echo $query; ?>" data-bs-autocomplete="[]">

                                    <select name="search_field" class="form-select" style="max-width: 200px; margin-left: 10px;">
                                        <option value="all" <?php echo ($search_field === 'all') ? 'selected' : ''; ?>>Tutti i campi</option>
                                        <option value="title" <?php echo ($search_field === 'title') ? 'selected' : ''; ?>>Titolo</option>
                                        <option value="descrizione" <?php echo ($search_field === 'descrizione') ? 'selected' : ''; ?>>Descrizione</option>
                                        <option value="data_pubblicazione" <?php echo ($search_field === 'data_pubblicazione') ? 'selected' : ''; ?>>Data pubblicazione</option>
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
                                <?php echo ($order == 'desc' || $order == 'alfabetico_desc') ? "(Discendente)" : "(Ascendente)"; ?>
                            </p>
                        </div>

                        <!-- Ordinamento -->
                        <div class="form-group mb-4">
                            <span style="font-size: 1.2rem; font-weight: bold; color: #333;">Ordina per</span>
                            <select id="order-select" name="order_type" class="form-control">
                                <option value="data_desc" <?php echo ($order == 'data_desc') ? 'selected' : ''; ?>>Data (Discendente)</option>
                                <option value="data_asc" <?php echo ($order == 'data_asc') ? 'selected' : ''; ?>>Data (Ascendente)</option>
                                <option value="alfabetico_asc" <?php echo ($order == 'alfabetico_asc') ? 'selected' : ''; ?>>Alfabetico (Ascendente)</option>
                                <option value="alfabetico_desc" <?php echo ($order == 'alfabetico_desc') ? 'selected' : ''; ?>>Alfabetico (Discendente)</option>
                            </select>
                        </div>

                        <!-- Risultati -->
                        <?php if ($the_query->found_posts != 0) { ?>
                            <?php $categoria = $the_query->posts; ?>
                            <div class="row g-4" id="load-more">
                                <?php foreach ($categoria as $elemento) {
                                    $load_card_type = "elemento_trasparenza";
                                    get_template_part("template-parts/amministrazione-trasparente/card");
                                } ?>
                            </div>
                        <?php } else { ?>
                            <div class="alert alert-info text-center" role="alert">
                                Nessun post trovato.
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Sidebar -->
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
    document.getElementById('order-select').addEventListener('change', function() {
        setTimeout(function() {
            document.getElementById('search-form').submit();
        }, 100);
    });
</script>

