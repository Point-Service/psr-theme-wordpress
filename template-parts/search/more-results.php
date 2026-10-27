<?php 
global $the_query, $load_posts, $wp_the_query, $load_card_type, $additional_filter, $filter_ids, $label, $label_no_more, $tax_query, $classes;

if (!$the_query) $the_query = $wp_query;
if (!$load_posts) $load_posts = 6;

//set default labels & classes
if (!$label) $label = 'Carica altri risultati';
if (!$label_no_more) $label_no_more = 'Nessun altro risultato';
if (!$classes) $classes = 'btn btn-outline-primary pt-15 pb-15 pl-90 pr-90 mb-30 mb-lg-50 full-mb text-button';

$request_params_json = wp_json_encode(wp_unslash($_GET));
$additional_filter_json = wp_json_encode($additional_filter);
$tax_query_json = wp_json_encode($tax_query);
$filter_ids_json = wp_json_encode($filter_ids);

$query = $the_query->query;
$post_types = isset($query['post_type']) ? $query['post_type'] : null;
if ( !$post_types ) $post_types = dci_get_sercheable_tipologie();

$post_types_json = wp_json_encode($post_types);
$query_search = isset($_GET['search']) && is_scalar($_GET['search'])
    ? sanitize_text_field(wp_unslash($_GET['search'])) : '';

// URLSearchParams interpreta gli stessi parametri; codifica i valori per evitare iniezioni HTML/JS.
$query_params = '?' . http_build_query(array(
    'post_count' => absint($the_query->post_count),
    'load_posts' => absint($load_posts),
    'search' => $query_search,
    'post_types' => $post_types_json,
    'load_card_type' => sanitize_key((string) $load_card_type),
    'query_params' => $request_params_json,
    'filter_ids' => $filter_ids_json,
    'tax_query' => $tax_query_json,
    'additional_filter' => $additional_filter_json,
), '', '&', PHP_QUERY_RFC3986);
// Stringa JS sicura anche in presenza di apici, backtick e caratteri HTML speciali.
$query_params_js = esc_attr(wp_json_encode(
    $query_params,
    JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT
));

if($the_query->post_count < $the_query->found_posts) {
?> 
<div class="d-flex justify-content-center mt-4" id="load-more-btn">
    <?php if(get_parent_template() === 'servizi.php') {
        ?><button type="button"
            class="<?php echo esc_attr($classes); ?>" onclick='handleOnClick(<?php echo $query_params_js; ?>)'
            data-element="load-other-cards"
        >
    <?php } else {
        ?><button type="button"
            class="<?php echo esc_attr($classes); ?>" onclick='handleOnClick(<?php echo $query_params_js; ?>)'
        >
    <?php } ?>

    <span class=""><?php echo esc_html($label); ?></span>
    </button> 
</div>
<p class="text-center text-paragraph-regular-medium mt-4 mb-0 d-none" id="no-more-results">
    <?php echo esc_html($label_no_more); ?>
</p>
<?php } else { ?>
<p class="text-center text-paragraph-regular-medium mt-4 mb-0" id="no-more-results">
    <?php echo esc_html($label_no_more); ?>
</p>
<?php } ?>

<!-- Pagination -->
<!-- <nav class="pagination-wrapper" aria-label="Navigazione della pagina">
    <?php #echo dci_bootstrap_pagination(); ?>
</nav> -->
