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

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;

// Legge il tipo di ordinamento da GET (default: data)
$order_by = isset($_GET['order_by']) && in_array($_GET['order_by'], ['alpha', 'date']) ? $_GET['order_by'] : 'date';

// Parametri base query
$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type' => 'elemento_trasparenza',
    'tipi_cat_amm_trasp' => $obj->slug,
    'paged' => $paged,
);

// Setta orderby e order per WP_Query di base
if ($order_by === 'alpha') {
    $args['orderby'] = 'title';
    $args['order'] = 'ASC';
} else {
    // Default order by date inserimento, DESC
    $args['orderby'] = 'date';
    $args['order'] = 'DESC';
}

$the_query = new WP_Query($args);

// Funzione per estrarre data pubblicazione in formato Ymd
function get_data_pubblicazione_Ymd($post, $prefix = '_dci_elemento_trasparenza_') {
    $arrayDataPubblicazione = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
    if ($arrayDataPubblicazione 
        && !empty($arrayDataPubblicazione[0]) 
        && !empty($arrayDataPubblicazione[1]) 
        && !empty($arrayDataPubblicazione[2])) {
        // Sistema anno, nel caso fosse a 2 cifre
        $year = intval($arrayDataPubblicazione[2]);
        if ($year < 100) {
            $year += 2000;
        }
        // YYYYMMDD stringa
        return sprintf('%04d%02d%02d', $year, $arrayDataPubblicazione[1], $arrayDataPubblicazione[0]);
    } else {
        // Se manca data pubblicazione usa post_date
        return date('Ymd', strtotime($post->post_date));
    }
}

// Se ordinamento per data pubblicazione: ordina l’array dei post PHP
if ($order_by === 'date') {
    usort($the_query->posts, function($a, $b) {
        $dateA = get_data_pubblicazione_Ymd($a);
        $dateB = get_data_pubblicazione_Ymd($b);
        // Ordine decrescente (più recente prima)
        return strcmp($dateB, $dateA);
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

        <?php if ($obj->name == "Contratti Pubblici") { ?>
            <div class="container my-5">
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti i bandi di gara</h2>
                    <div class="col-12 col-lg-8 pt-20 pt-lg-20 pb-lg-20"></div>
                    <div class="row g-3" id="load-more">
                        <?php get_template_part("template-parts/bandi-di-gara/tutti-bandi"); ?>
                    </div>
                    <!-- <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?> -->
                </div>
            </div>
        </div>
    <?php } else { ?>
        <form role="search" id="search-form" method="get" class="search-form">
            <button type="submit" class="d-none"></button>
            <div class="container">
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti i documenti della trasparenza</h2>

                    <!-- Tasti ordinamento -->
                    <div class="mb-3">
                        <strong>Ordina per: </strong>
                        <a href="<?php echo esc_url(add_query_arg(['order_by' => 'alpha', 'paged' => 1], remove_query_arg('paged'))); ?>" 
                           class="btn btn-outline-primary <?php echo $order_by === 'alpha' ? 'active' : ''; ?>">
                            Alfanumerico
                        </a>
                        <a href="<?php echo esc_url(add_query_arg(['order_by' => 'date', 'paged' => 1], remove_query_arg('paged'))); ?>" 
                           class="btn btn-outline-primary <?php echo $order_by === 'date' ? 'active' : ''; ?>">
                            Data pubblicazione
                        </a>
                    </div>

                    <!-- Descrizione ordinamento -->
                    <p class="mb-4">
                        <?php
                        if ($order_by === 'alpha') {
                            echo 'Elementi ordinati alfabeticamente';
                        } else {
                            echo 'Elementi ordinati per data pubblicazione (dal più recente)';
                        }
                        ?>
                    </p>

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
                                        <svg class="icon icon-sm icon-primary" role="img"
                                            aria-labelledby="autocomplete-label">
                                            <use href="#it-search"></use>
                                        </svg>
                                    </span>
                                </div>
                            </div>
                            <p id="autocomplete-label" class="mb-4">
                                <strong><?php echo $the_query->found_posts; ?></strong> elementi trovati in ordine
                                <?php echo $order_by === 'alpha' ? 'alfanumerico' : 'per data pubblicazione'; ?>
                            </p>
                        </div>
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

                    <!-- Colonna destra: link utili -->
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>

                    <!-- Paginazione originale -->
                    <div class="row my-4">
                        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
                            <?php echo dci_bootstrap_pagination(); ?>
                        </nav>
                    </div>
                </div>
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
