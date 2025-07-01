<?php
/**
 * Archivio Tassonomia trasparenza
 */

global $title, $description, $data_element, $elemento, $sito_tematico_id, $siti_tematici;

get_header();
$obj = get_queried_object();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$search_query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'data';

// QUERY BASE
$args = array(
    's' => $search_query,
    'posts_per_page' => $max_posts,
    'post_type' => 'elemento_trasparenza',
    'tipi_cat_amm_trasp' => $obj->slug,
    'paged' => $paged,
    'orderby' => 'title',
    'order' => 'ASC'
);

$the_query = new WP_Query($args);

// Funzione per ottenere la data pubblicazione in formato Ymd
function get_data_pubblicazione_Ymd($post, $prefix = '_dci_elemento_trasparenza_') {
    $data = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
    if ($data && !empty($data[0]) && !empty($data[1]) && !empty($data[2])) {
        $day = str_pad(intval($data[0]), 2, "0", STR_PAD_LEFT);
        $month = str_pad(intval($data[1]), 2, "0", STR_PAD_LEFT);
        $year = intval($data[2]);
        if ($year < 100) $year += 2000;
        return sprintf('%04d%02d%02d', $year, $month, $day);
    } else {
        return date('Ymd', strtotime($post->post_date));
    }
}

// Ordina per data se richiesto
if ($orderby === 'data') {
    usort($the_query->posts, function($a, $b) {
        $dateA = get_data_pubblicazione_Ymd($a);
        $dateB = get_data_pubblicazione_Ymd($b);
        return $dateB <=> $dateA; // DESC
    });
}

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
            <input type="hidden" name="orderby" value="<?php echo esc_attr($orderby); ?>">
            <div class="container">
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti i documenti della trasparenza</h2>

                    <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                        <div class="cmp-input-search">
                            <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                                <div class="input-group">
                                    <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                                    <input type="search" class="autocomplete form-control"
                                           placeholder="Cerca una parola chiave" id="autocomplete-two" name="search"
                                           value="<?php echo esc_attr($search_query); ?>" data-bs-autocomplete="[]">
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

                            <!-- Tasti ordinamento -->
                            <div class="mb-4">
                                <strong><?php echo $the_query->found_posts; ?></strong> elementi trovati -
                                Ordinati per:
                                <strong><?php echo $orderby === 'data' ? 'Data di pubblicazione' : 'Titolo (A-Z)'; ?></strong>
                                <div class="mt-2">
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['orderby' => 'data'])); ?>"
                                       class="btn btn-outline-primary btn-sm me-2 <?php echo $orderby === 'data' ? 'active' : ''; ?>">
                                        Ordina per Data
                                    </a>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['orderby' => 'title'])); ?>"
                                       class="btn btn-outline-primary btn-sm <?php echo $orderby === 'title' ? 'active' : ''; ?>">
                                        Ordina Alfabeticamente
                                    </a>
                                </div>
                            </div>
                        </div>

                        <?php if ($the_query->found_posts > 0): ?>
                            <div class="row g-4" id="load-more">
                                <?php foreach ($the_query->posts as $elemento):
                                    get_template_part("template-parts/amministrazione-trasparente/card");
                                endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-info text-center" role="alert">
                                Nessun post trovato.
                            </div>
                        <?php endif; ?>

                        <!-- Paginazione -->
                        <div class="row my-4">
                            <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
                                <?php echo dci_bootstrap_pagination(); ?>
                            </nav>
                        </div>
                    </div>

                    <!-- Sidebar -->
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

