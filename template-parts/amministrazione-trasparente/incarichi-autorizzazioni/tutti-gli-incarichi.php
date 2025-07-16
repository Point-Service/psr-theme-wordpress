<?php
// Evita redirect automatici di WordPress che rovinano i parametri custom
remove_filter('template_redirect', 'redirect_canonical');

global $wpdb;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$selected_year = isset($_GET['filter_year']) ? intval($_GET['filter_year']) : 0;

// Prendi gli anni disponibili dai post pubblicati
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date)
    FROM {$wpdb->posts}
    WHERE post_type = 'incarichi_dip' 
      AND post_status = 'publish'
    ORDER BY post_date DESC
");

// Argomenti WP_Query
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
        array('year' => $selected_year),
    );
}

$the_query = new WP_Query($args);
$current_url = get_permalink();
$base_url = add_query_arg([
    'search'      => $main_search_query ?: '',
    'filter_year' => $selected_year ?: 0,
    'page'        => '%#%',
], $current_url);
?>

<!-- FORM FILTRO -->
<form method="get" class="incarichi-filtro-form">
    <div class="form-group">
        <label for="search" class="form-label">Cerca:</label>
        <input type="search" id="search" name="search" class="form-control" placeholder="Cerca..." value="<?php echo esc_attr($main_search_query); ?>">
    </div>

    <div class="form-group">
        <label for="filter-year" class="form-label">Filtra per anno:</label>
        <select id="filter-year" name="filter_year" class="form-select">
            <option value="0" <?php selected($selected_year, 0); ?>>Tutti gli anni</option>
            <?php foreach ($years as $y) : ?>
                <option value="<?php echo esc_attr($y); ?>" <?php selected($selected_year, $y); ?>>
                    <?php echo esc_html($y); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label class="form-label d-none d-md-block">&nbsp;</label>
        <button type="submit" class="btn btn-primary w-100">Filtra</button>
    </div>
</form>


<?php if ($the_query->have_posts()) : ?>
    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
        <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
    <?php endwhile; wp_reset_postdata(); ?>

    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php
            $pagination_links = paginate_links([
                'base'      => $base_url,
                'format'    => '',
                'current'   => $paged,
                'total'     => $the_query->max_num_pages,
                'prev_text' => __('&laquo; Precedente'),
                'next_text' => __('Successivo &raquo;'),
                'type'      => 'array',
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

<!-- STILE MIGLIORATO -->
<style>
form.incarichi-filtro-form {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
    justify-content: center;
    align-items: flex-end;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    max-width: 750px;
    margin: 0 auto 2rem;
}

form.incarichi-filtro-form .form-group {
    display: flex;
    flex-direction: column;
    min-width: 200px;
    flex-grow: 1;
    max-width: 220px;
}

form.incarichi-filtro-form .form-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.3rem;
}

form.incarichi-filtro-form .form-control,
form.incarichi-filtro-form .form-select {
    padding: 0.45rem 0.75rem;
    border: 1.5px solid #ced4da;
    border-radius: 0.4rem;
    transition: border-color 0.3s;
}

form.incarichi-filtro-form .form-control:focus,
form.incarichi-filtro-form .form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.25);
    outline: none;
}

form.incarichi-filtro-form button.btn-primary {
    height: 38px;
    padding: 0 1.5rem;
    font-weight: 600;
    border-radius: 0.4rem;
    white-space: nowrap;
}

form.incarichi-filtro-form button.btn-primary:hover {
    background-color: #0b5ed7;
    box-shadow: 0 4px 8px rgba(11, 94, 215, 0.4);
}

/* Responsive */
@media (max-width: 576px) {
    form.incarichi-filtro-form {
        flex-direction: column;
        align-items: stretch;
    }
    form.incarichi-filtro-form .form-group {
        max-width: 100%;
    }
}
</style>

