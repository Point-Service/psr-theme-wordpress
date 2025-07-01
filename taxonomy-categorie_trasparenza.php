<?php
global $title, $description, $data_element, $elemento;

get_header();
$obj = get_queried_object();

$paged = max(1, get_query_var('paged'));
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'data_desc';

// Query base con paginazione regolare
$args = [
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type' => 'elemento_trasparenza',
    'paged' => $paged,
    'tax_query' => [
        [
            'taxonomy' => 'tipi_cat_amm_trasp',
            'field' => 'slug',
            'terms' => $obj->slug,
        ],
    ],
];

if ($orderby === 'alpha') {
    $args['orderby'] = 'title';
    $args['order'] = 'ASC';
} else {
    // Default: ordino per data inserimento (non preciso ma serve come fallback)
    $args['orderby'] = 'date';
    $args['order'] = 'DESC';
}

$the_query = new WP_Query($args);

// Funzione per ottenere la data da un post, meta oppure data post
function get_data_ordinamento($post) {
    $prefix = '_dci_elemento_trasparenza_';
    $data_pub_arr = dci_get_data_pubblicazione_arr("data_pubblicazione", $prefix, $post->ID);
    if (!empty($data_pub_arr) && count($data_pub_arr) === 3) {
        $year = $data_pub_arr[2] < 100 ? 2000 + $data_pub_arr[2] : $data_pub_arr[2];
        $month = str_pad($data_pub_arr[1], 2, '0', STR_PAD_LEFT);
        $day = str_pad($data_pub_arr[0], 2, '0', STR_PAD_LEFT);
        return intval("{$year}{$month}{$day}");
    } else {
        return intval(get_the_date('Ymd', $post));
    }
}

if ($orderby === 'data_desc' && $the_query->have_posts()) {
    $posts = $the_query->posts;
    usort($posts, function($a, $b) {
        return get_data_ordinamento($b) <=> get_data_ordinamento($a);
    });
    $the_query->posts = $posts;
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

                    <?php if ($the_query->have_posts()) : ?>
                        <div class="row g-4" id="load-more">
                            <?php
                            while ($the_query->have_posts()) {
                                $the_query->the_post();
                                $elemento = get_post();
                                get_template_part("template-parts/amministrazione-trasparente/card");
                            }
                            wp_reset_postdata();
                            ?>
                        </div>
                    <?php else : ?>
                        <div class="alert alert-info text-center" role="alert">
                            Nessun post trovato.
                        </div>
                    <?php endif; ?>

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


