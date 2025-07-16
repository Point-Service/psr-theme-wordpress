<?php
global $wpdb;

remove_filter('template_redirect', 'redirect_canonical');

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Prendi gli anni disponibili
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date) 
    FROM {$wpdb->posts} 
    WHERE post_type = 'incarichi_dip' 
    AND post_status = 'publish' 
    ORDER BY post_date DESC
");

$args = [
    'post_type' => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby' => 'date',
    'order' => 'DESC',
    'paged' => $paged,
];

if ($year > 0) {
    $args['date_query'] = [['year' => $year]];
}

if (!empty($main_search_query)) {
    $args['s'] = $main_search_query;
}

$the_query = new WP_Query($args);
?>

<form method="GET" action="<?php echo esc_url(get_permalink()); ?>" id="filter-year-form" class="mb-4">
    <label for="year">Seleziona anno:</label>
    <select name="year" id="year" onchange="document.getElementById('filter-year-form').submit();">
        <option value="0"<?php selected(0, $year); ?>>Tutti gli anni</option>
        <?php foreach ($years as $y): ?>
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
            // Genera la base corretta per i link di paginazione
            $base = esc_url(add_query_arg([
                'page' => '%#%',
                'year' => $year > 0 ? $year : null,
                'search' => !empty($main_search_query) ? $main_search_query : null,
            ], get_permalink()));

            // Rimuove eventuali doppie ? o & in più
            $base = preg_replace('/(\?|&)+$/', '', $base);

            $pagination_links = paginate_links([
                'base' => $base,
                'format' => '',
                'current' => $paged,
                'total' => $the_query->max_num_pages,
                'prev_text' => __('&laquo; Precedente'),
                'next_text' => __('Successivo &raquo;'),
                'type' => 'array',
            ]);

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

