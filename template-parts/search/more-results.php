global $the_query, $load_posts, $wp_the_query, $load_card_type, $additional_filter, $filter_ids, $label, $label_no_more, $tax_query, $classes;

if (!$the_query) $the_query = $wp_query;
if (!$load_posts) $load_posts = 6;

// Set default labels & classes
if (!$label) $label = 'Carica altri risultati';
if (!$label_no_more) $label_no_more = 'Nessun altro risultato';
if (!$classes) $classes = 'btn btn-outline-primary pt-15 pb-15 pl-90 pr-90 mb-30 mb-lg-50 full-mb text-button';

// Controllo paginazione
$paged = get_query_var('paged') ? get_query_var('paged') : 1;

// Controllo se esiste una tassonomia valida
$tax_query_active = (!empty($tax_query) && is_array($tax_query));

// Costruzione della query
$query_args = [
    'post_type'      => isset($query['post_type']) ? $query['post_type'] : dci_get_sercheable_tipologie(),
    'posts_per_page' => $load_posts,
    'paged'          => $paged, // Aggiunta della paginazione
];

// Se esiste una tassonomia valida, la aggiungiamo
if ($tax_query_active) {
    $query_args['tax_query'] = $tax_query;
}

$the_query = new WP_Query($query_args);

// Serializzazione per l'uso in JavaScript
$query_params = json_encode($_GET);
$additional_filter = json_encode($additional_filter);
$filter_ids = json_encode($filter_ids);
$tax_query_json = $tax_query_active ? json_encode($tax_query) : 'null';
$post_types = json_encode($query_args['post_type']);

$query_params = '?post_count='.$the_query->post_count.'&load_posts='.$load_posts.'&post_types='.$post_types.'&load_card_type='.$load_card_type.'&query_params='.$query_params.'&filter_ids='.$filter_ids.'&tax_query='.$tax_query_json.'&additional_filter='.$additional_filter.'&paged='.($paged + 1);

if ($the_query->have_posts()) { 
    while ($the_query->have_posts()) {
        $the_query->the_post();
        // Qui inserisci il tuo template per visualizzare i post
    }

    if ($the_query->post_count < $the_query->found_posts) { ?>
        <div class="d-flex justify-content-center mt-4" id="load-more-btn">
            <button type="button"
                class="<?php echo $classes; ?>" 
                onclick='handleOnClick(`<?php echo $query_params; ?>`)'
                data-element="load-other-cards">
                <span class=""><?php echo $label; ?></span>
            </button>
        </div>
    <?php }
} else { ?>
    <p class="text-center text-paragraph-regular-medium mt-4 mb-0" id="no-more-results">
        <?php echo $label_no_more; ?>
    </p>
<?php }
wp_reset_postdata(); ?>

<!-- Navigazione Paginazione -->
<nav class="pagination-wrapper" aria-label="Navigazione della pagina">
    <?php
    echo paginate_links([
        'total'   => $the_query->max_num_pages,
        'current' => $paged,
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
    ]);
    ?>
</nav>

