<?php
remove_filter('template_redirect', 'redirect_canonical');

global $wpdb;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date)
    FROM {$wpdb->posts}
    WHERE post_type = 'incarichi_dip' AND post_status = 'publish'
    ORDER BY post_date DESC
");

$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

if ($selected_year > 0) {
    $args['date_query'] = array(
        array(
            'year' => $selected_year,
        ),
    );
}

if (!empty($main_search_query)) {
    $args['s'] = $main_search_query;
}

$the_query = new WP_Query($args);

// Costruiamo URL base per paginazione e form
// Rimuoviamo 'page' per evitare conflitti nella paginazione
$base_url = remove_query_arg('page');
?>

<form method="GET" action="<?php echo esc_url( $base_url ); ?>" class="mb-4 d-flex flex-wrap gap-2 align-items-center">

    <select name="year" onchange="this.form.submit()" class="form-select" style="width:auto;">
        <option value="0" <?php selected($selected_year, 0); ?>>Tutti gli anni</option>
        <?php foreach ($years as $year) : ?>
            <option value="<?php echo esc_attr($year); ?>" <?php selected($selected_year, $year); ?>><?php echo esc_html($year); ?></option>
        <?php endforeach; ?>
    </select>

    <input
        type="text"
        name="search"
        placeholder="Cerca incarico..."
        class="form-control"
        style="max-width: 300px;"
        value="<?php echo esc_attr($main_search_query); ?>"
    />

    <button type="submit" class="btn btn-primary">Cerca</button>
</form>

<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
        <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
    <?php endwhile; ?>
    <?php wp_reset_postdata(); ?>

    <nav class="pagination-wrapper" aria-label="Navigazione pagine">
        <?php
        $pagination_links = paginate_links(array(
            'base'      => add_query_arg('page', '%#%', $base_url),
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

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

<style>
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
