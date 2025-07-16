<?php
global $wpdb;

// Evita redirect automatici di WordPress
remove_filter('template_redirect', 'redirect_canonical');

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Prendi gli anni disponibili dai post pubblicati
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date) 
    FROM {$wpdb->posts} 
    WHERE post_type = 'incarichi_dip' 
    AND post_status = 'publish' 
    ORDER BY post_date DESC
");

$args = [
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
];

if ($year > 0) {
    $args['date_query'] = [['year' => $year]];
}

if (!empty($main_search_query)) {
    $args['s'] = $main_search_query;
}

$the_query = new WP_Query($args);

?>

<!-- Form ricerca e filtro anno -->
<form method="GET" action="<?php echo esc_url(get_permalink()); ?>" id="filter-form" class="mb-4 d-flex flex-wrap align-items-center gap-3">
    <div>
        <label for="search">Cerca:</label>
        <input type="search" id="search" name="search" value="<?php echo esc_attr($main_search_query); ?>" placeholder="Cerca incarichi...">
    </div>
    <div>
        <label for="year">Anno:</label>
        <select name="year" id="year" onchange="document.getElementById('filter-form').submit();">
            <option value="0"<?php selected(0, $year); ?>>Tutti gli anni</option>
            <?php foreach ($years as $y): ?>
                <option value="<?php echo esc_attr($y); ?>" <?php selected($year, $y); ?>><?php echo esc_html($y); ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button type="submit" class="btn btn-primary">Filtra</button>
</form>

<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
        <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
    <?php endwhile; ?>
    <?php wp_reset_postdata(); ?>

    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php
            // Costruisco base e format per paginate_links con add_query_arg:
            $base = add_query_arg('page','%#%');
            $format = '';

            // Se permalink non usa query string, togli eventuali trailing & o ? in $base
            $base = preg_replace('/(\?|&)page=%#%/', '?page=%#%', $base);

            // Assicuriamoci che gli altri parametri di ricerca/anno siano mantenuti:
            $args_pagination = [
                'base'      => $base,
                'format'    => $format,
                'current'   => $paged,
                'total'     => $the_query->max_num_pages,
                'prev_text' => __('&laquo; Precedente'),
                'next_text' => __('Successivo &raquo;'),
                'type'      => 'array',
                'add_args'  => [
                    'search' => $main_search_query ? $main_search_query : false,
                    'year'   => ($year > 0) ? $year : false,
                ],
            ];

            $pagination_links = paginate_links($args_pagination);

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
/* Form filtri */
#filter-form label {
    font-weight: 600;
    margin-right: 0.5rem;
}

#filter-form input[type="search"],
#filter-form select {
    padding: 0.375rem 0.75rem;
    border: 1px solid #ccc;
    border-radius: 0.25rem;
}

/* Paginazione */
.pagination .page-link {
    color: var(--bs-primary);
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
