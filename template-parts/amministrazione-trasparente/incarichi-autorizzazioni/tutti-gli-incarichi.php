<?php
global $wpdb;

// Evita redirect automatici di WordPress (utile per paginazione custom)
remove_filter('template_redirect', 'redirect_canonical');

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Recupera anni distinti dai post pubblicati di tipo incarichi_dip
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date) 
    FROM {$wpdb->posts} 
    WHERE post_type = 'incarichi_dip' 
    AND post_status = 'publish' 
    ORDER BY post_date DESC
");

$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

// Se è stato selezionato un anno specifico, aggiungi filtro meta_query
if ($year && $year > 0) {
    $args['date_query'] = array(
        array(
            'year' => $year,
        ),
    );
}

if (!empty($main_search_query)) {
    $args['s'] = $main_search_query;
}

$the_query = new WP_Query($args);
?>

<form method="GET" action="<?php echo esc_url( get_permalink() ); ?>" id="filter-year-form" class="mb-4">
    <label for="year">Seleziona anno:</label>
    <select name="year" id="year" onchange="document.getElementById('filter-year-form').submit();">
        <option value="0"<?php selected(0, $year); ?>>Tutti gli anni</option>
        <?php foreach ($years as $y) : ?>
            <option value="<?php echo esc_attr($y); ?>" <?php selected($year, $y); ?>><?php echo esc_html($y); ?></option>
        <?php endforeach; ?>
    </select>
    <input type="hidden" name="search" value="<?php echo esc_attr($main_search_query); ?>">
</form>

<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
        <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
    <?php endwhile; ?>
    <?php wp_reset_postdata(); ?>

    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php
            $pagination_links = paginate_links(array(
                'base'      => add_query_arg(
                    array_filter([
                        'page'  => '%#%',
                        'year'  => $year > 0 ? $year : null,
                        'search'=> !empty($main_search_query) ? $main_search_query : null,
                    ]),
                    esc_url(get_permalink())
                ),
                'format'    => '',
                'current'   => $paged,
                'total'     => $the_query->max_num_pages,
                'prev_text' => __('&laquo; Precedente'),
                'next_text' => __('Successivo &raquo;'),
                'type'      => 'array',
            ));

            if ($pagination_links) : ?>
                <ul class="pagination justify-content-center">
                    <?php foreach ($pagination_links as $link) :
                        $active = strpos($link, 'current') !== false ? ' active' : '';
                        $link = str_replace('<a ', '<a class="page-link" ', $link);
                        $link = str_replace('<span class="current">', '<span class="page-link active" aria-current="page">', $link);
                    ?>
                        <li class="page-item<?php echo $active; ?>"><?php echo $link; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </nav>
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

<style>
.pagination .page-link {
    color: var(--bs-primary); /* usa il colore primario Bootstrap */
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    margin: 0 0.25rem;
    border-radius: 0.25rem;
    transition: background-color 0.15s ease-in-out;
}

.pagination .page-link:hover {
    background-color: var(--bs-primary);
    color: white;
    text-decoration: none;
}

.pagination .page-item.active .page-link {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
    cursor: default;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.pagination .page-item.disabled .page-link {
    color: var(--bs-secondary);
    pointer-events: none;
    background-color: transparent;
    border-color: transparent;
}
</style>
