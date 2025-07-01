<?php
/**
 * Archivio Tassonomia trasparenza con ordinamento per data pubblicazione o titolo
 */

get_header();
$obj = get_queried_object();

$paged = max(1, get_query_var('paged'));
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : 'data_desc';

// Funzione di supporto per popolare _data_pubblicazione_ordinabile su tutti i post
function aggiorna_meta_data_pubblicazione_ordinabile() {
    $args = [
        'post_type' => 'elemento_trasparenza',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    ];
    $all_posts = new WP_Query($args);
    if ($all_posts->have_posts()) {
        while ($all_posts->have_posts()) {
            $all_posts->the_post();
            $post_id = get_the_ID();

            // Prendi data personalizzata
            $prefix = '_dci_elemento_trasparenza_';
            $data_pub = get_post_meta($post_id, $prefix . 'data_pubblicazione', true);

            // Pulizia e formattazione: deve essere numerico YYYYMMDD
            if (!$data_pub) {
                // Se non esiste, usa data WP
                $data_pub = get_the_date('Ymd', $post_id);
            } else {
                // rimuove tutto tranne numeri
                $data_pub = preg_replace('/[^0-9]/', '', $data_pub);

                // se non è lunga 8 caratteri (YYYYMMDD), fallback
                if (strlen($data_pub) !== 8) {
                    $data_pub = get_the_date('Ymd', $post_id);
                }
            }

            update_post_meta($post_id, '_data_pubblicazione_ordinabile', intval($data_pub));
        }
    }
    wp_reset_postdata();
}

// Esegui questa funzione UNA SOLA VOLTA, poi commentala
// aggiorna_meta_data_pubblicazione_ordinabile();

$args = [
    'post_type'      => 'elemento_trasparenza',
    'posts_per_page' => $max_posts,
    'paged'          => $paged,
    's'              => $query,
    'tax_query'      => [
        [
            'taxonomy' => 'tipi_cat_amm_trasp',
            'field'    => 'slug',
            'terms'    => $obj->slug,
        ]
    ],
];

if ($orderby === 'alpha') {
    $args['orderby'] = 'title';
    $args['order'] = 'ASC';
} else {
    $args['meta_key'] = '_data_pubblicazione_ordinabile';
    $args['orderby'] = 'meta_value_num';
    $args['order'] = 'DESC';
}

$the_query = new WP_Query($args);

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
                            <?php while ($the_query->have_posts()) : $the_query->the_post();
                                $elemento = get_post();
                                get_template_part("template-parts/amministrazione-trasparente/card");
                            endwhile; ?>
                        </div>
                        <?php wp_reset_postdata(); ?>
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
