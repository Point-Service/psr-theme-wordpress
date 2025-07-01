<?php
global $title, $description, $data_element, $elemento;

get_header();
$obj = get_queried_object();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'data_desc';

// Recupera TUTTI i post senza ordinamento complesso
$args = array(
    's' => $query,
    'posts_per_page' => -1, // prendi tutti
    'post_type' => 'elemento_trasparenza',
    'tax_query' => array(
        array(
            'taxonomy' => 'tipi_cat_amm_trasp',
            'field' => 'slug',
            'terms' => $obj->slug,
        ),
    ),
);

$all_posts = get_posts($args);

// Funzione per ottenere la data da un post, meta oppure data post
function get_data_ordinamento($post) {
    $prefix = '_dci_elemento_trasparenza_';
    $data_pub_arr = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
    if (!empty($data_pub_arr) && count($data_pub_arr) === 3) {
        // Normalizza anno (assumendo due cifre per anni recenti)
        $year = $data_pub_arr[2] < 100 ? 2000 + $data_pub_arr[2] : $data_pub_arr[2];
        $month = str_pad($data_pub_arr[1], 2, '0', STR_PAD_LEFT);
        $day = str_pad($data_pub_arr[0], 2, '0', STR_PAD_LEFT);
        return intval("{$year}{$month}{$day}");
    } else {
        // fallback: data post in formato YYYYMMDD
        return intval(get_the_date('Ymd', $post));
    }
}

// Ordina array PHP
if ($orderby === 'alpha') {
    usort($all_posts, function($a, $b) {
        return strcasecmp($a->post_title, $b->post_title);
    });
} else {
    usort($all_posts, function($a, $b) {
        return get_data_ordinamento($b) <=> get_data_ordinamento($a);
    });
}

// Pagina manuale
$total_posts = count($all_posts);
$offset = ($paged - 1) * $max_posts;
$posts_paged = array_slice($all_posts, $offset, $max_posts);

// Setup per WP_Query compatibilità
$the_query = new WP_Query();
$the_query->posts = $posts_paged;
$the_query->post_count = count($posts_paged);
$the_query->found_posts = $total_posts;
$the_query->max_num_pages = ceil($total_posts / $max_posts);
$the_query->query_vars = $args;
$the_query->current_post = -1;

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
                            <strong><?php echo $total_posts; ?></strong> elementi trovati in ordine
                            <?php echo $orderby === 'alpha' ? 'alfabetico' : 'di data (decrescente)'; ?>
                        </p>
                    </div>

                    <?php if (!empty($posts_paged)) { ?>
                        <div class="row g-4" id="load-more">
                            <?php 
                            foreach ($posts_paged as $elemento) {
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
                            <?php
                            echo dci_bootstrap_pagination($the_query->max_num_pages, $paged);
                            ?>
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


