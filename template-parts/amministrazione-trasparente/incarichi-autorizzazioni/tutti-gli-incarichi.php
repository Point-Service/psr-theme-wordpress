<?php
// Evita redirect automatici di WordPress che rovinano i parametri custom
remove_filter('template_redirect', 'redirect_canonical');

global $wpdb;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$selected_year = isset($_GET['filter_year']) ? intval($_GET['filter_year']) : 0;

// Prendi gli anni disponibili dai post pubblicati (per la combo)
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date)
    FROM {$wpdb->posts}
    WHERE post_type = 'incarichi_dip' 
      AND post_status = 'publish'
    ORDER BY post_date DESC
");

// Costruiamo argomenti per WP_Query
$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

if (!empty($main_search_query)) {
    $args['s'] = $main_search_query;
}

if ($selected_year > 0) {
    $args['date_query'] = array(
        array(
            'year' => $selected_year,
        ),
    );
}

// Query personalizzata
$the_query = new WP_Query($args);

// Prendi permalink pagina corrente (senza query string)
$current_url = get_permalink();

// Costruiamo la base URL per paginazione mantenendo parametri search e filter_year
$base_url = add_query_arg(array(
    'search'      => $main_search_query ? $main_search_query : '',
    'filter_year' => $selected_year > 0 ? $selected_year : 0,
    'page'        => '%#%',
), $current_url);
?>

<!-- FORM FILTRO ANNO -->
<form method="get" class="mb-3 d-flex align-items-center gap-2 incarichi-filtro-form">
    <label for="search" class="form-label mb-0 me-2">Cerca:</label>
    <input
        type="search"
        id="search"
        name="search"
        class="form-control me-3"
        placeholder="Cerca..."
        value="<?php echo esc_attr($main_search_query); ?>"
    >

    <label for="filter-year" class="form-label mb-0 me-2">Anno:</label>
    <select id="filter-year" name="filter_year" class="form-select w-auto me-3">
        <option value="0" <?php selected($selected_year, 0); ?>>Tutti gli anni</option>
        <?php foreach ($years as $y) : ?>
            <option value="<?php echo esc_attr($y); ?>" <?php selected($selected_year, $y); ?>>
                <?php echo esc_html($y); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <!-- Inserisco bottone in un div flex -->
    <div class="btn-wrapper">
        <button type="submit" class="btn btn-primary">Filtra</button>
    </div>
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
                'base'      => $base_url,
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

<!-- STILE MIGLIORATO PER FORM E PAGINAZIONE -->
<style>
/* PAGINAZIONE STILIZZATA */
.pagination-wrapper .pagination {
    display: flex;
    justify-content: center;
    list-style: none;
    padding-left: 0;
    margin-top: 1.5rem;
    gap: 0.5rem;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.pagination-wrapper .page-item {
    /* per mantenere lo spazio tra bottoni */
}

.pagination-wrapper .page-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.45rem 0.85rem;
    color: #0d6efd;
    background-color: transparent;
    border: 1.8px solid transparent;
    border-radius: 0.375rem;
    font-weight: 600;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 38px;
    box-shadow: none;
    text-decoration: none;
}

.pagination-wrapper .page-link:hover:not(.active) {
    background-color: #e7f1ff;
    border-color: #0d6efd;
    color: #0a58ca;
    box-shadow: 0 2px 6px rgba(13, 110, 253, 0.3);
    text-decoration: none;
}

.pagination-wrapper .page-item.active .page-link,
.pagination-wrapper .page-link[aria-current="page"] {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    cursor: default;
    box-shadow: 0 4px 12px rgba(13, 110, 253, 0.6);
}

.pagination-wrapper .page-item.disabled .page-link {
    color: #adb5bd;
    pointer-events: none;
    background-color: transparent;
    border-color: transparent;
    cursor: default;
    box-shadow: none;
}


</style>


