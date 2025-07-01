<?php
/**
 * Archivio Tassonomia trasparenza
 *
 * @package Design_Comuni_Italia
 */

global $title, $description, $data_element, $elemento, $sito_tematico_id, $siti_tematici;

get_header();
$obj = get_queried_object();

// Recupera il numero di pagina corrente.
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$orderby = isset($_GET['orderby']) ? $_GET['orderby'] : 'data_desc';

$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type' => 'elemento_trasparenza',
    'tipi_cat_amm_trasp' => $obj->slug,
    'paged' => $paged,
    'orderby' => 'title', // default
    'order' => 'ASC'
);

$the_query = new WP_Query($args);
$categoria = $the_query->posts;

// Funzione per ottenere data pubblicazione in formato Ymd
function get_data_pubblicazione_Ymd($post) {
    $prefix = '_dci_elemento_trasparenza_';
    $arr = dci_get_data_pubblicazione_arr('data_pubblicazione', $prefix, $post->ID);

    if (!empty($arr) && is_array($arr) && count($arr) === 3) {
        return sprintf('%04d%02d%02d', $arr[2], $arr[1], $arr[0]);
    }

    return get_the_date('Ymd', $post);
}

// Ordinamento manuale se richiesto
if ($orderby === 'data_desc') {
    usort($categoria, function ($a, $b) {
        return strcmp(get_data_pubblicazione_Ymd($b), get_data_pubblicazione_Ymd($a));
    });
} elseif ($orderby === 'alpha') {
    usort($categoria, function ($a, $b) {
        return strcmp($a->post_title, $b->post_title);
    });
}

?>

<main>
    <?php
    $title = $obj->name;
    $description = $obj->description;
    $data_element = 'data-element="page-name"';
    get_template_part("template-parts/hero/hero");
    get_template_part("template-parts/amministrazione-trasparente/sottocategorie"); ?>

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

                            <div class="d-flex align-items-center gap-3 mb-4">
                                <p id="autocomplete-label" class="mb-0">
                                    <strong><?php echo $the_query->found_posts; ?></strong> elementi trovati
                                </p>

                                <span class="small text-muted">
                                    (ordinati per 
                                    <?php
                                    echo $orderby === 'alpha' ? 'titolo A-Z' : 'data di pubblicazione più recente';
                                    ?>)
                                </span>

                                <div class="ms-auto">
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['orderby' => 'data_desc'])); ?>" class="btn btn-outline-primary btn-sm <?php echo ($orderby === 'data_desc') ? 'active' : ''; ?>">
                                        Ordina per data
                                    </a>
                                    <a href="?<?php echo http_build_query(array_merge($_GET, ['orderby' => 'alpha'])); ?>" class="btn btn-outline-primary btn-sm <?php echo ($orderby === 'alpha') ? 'active' : ''; ?>">
                                        Ordina A-Z
                                    </a>
                                </div>
                            </div>
                        </div>

                        <?php if (!empty($categoria)) { ?>
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

