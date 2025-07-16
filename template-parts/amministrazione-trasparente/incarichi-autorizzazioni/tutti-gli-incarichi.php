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
<form method="get" class="mb-3 d-flex align-items-center flex-wrap gap-2 incarichi-filtro-form">
    <label for="search" class="form-label mb-0 me-2">Cerca:</label>
    <input
        type="search"
        id="search"
        name="search"
        class="form-control me-3"
        placeholder="Cerca..."
        value="<?php echo esc_attr($main_search_query); ?>"
    >

    <label for="filter-year" class="form-label mb-0 me-2">Filtra per anno:</label>
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
/* FORM STILIZZATO */
form.incarichi-filtro-form {
    display: flex;
    flex-wrap: nowrap; /* forziamo riga unica per affiancare bottone */
    align-items: flex-start; /* allineo tutto in alto, a sinistra */
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    max-width: 700px;
    margin: 0 0 2rem 0; /* allineo il form a sinistra */
}

form.incarichi-filtro-form label.form-label {
    margin-bottom: 0;
    font-weight: 600;
    color: #495057;
    min-width: 90px;
}

form.incarichi-filtro-form input[type="search"],
form.incarichi-filtro-form select.form-select {
    flex-grow: 1;
    min-width: 150px;
    max-width: 250px;
    border: 1.5px solid #ced4da;
    transition: border-color 0.3s ease;
}

form.incarichi-filtro-form input[type="search"]:focus,
form.incarichi-filtro-form select.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 6px rgba(13, 110, 253, 0.3);
    outline: none;
}

.btn-wrapper {
    flex-shrink: 0; /* evita che il bottone si restringa */
}

form.incarichi-filtro-form button.btn-primary {
    padding: 0.45rem 1.5rem;
    font-weight: 600;
    border-radius: 0.4rem;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
    height: 38px; /* per allineamento verticale con input */
    align-self: flex-start; /* bottone in alto */
    margin-top: 0; /* rimuovo margin-top */
}

form.incarichi-filtro-form button.btn-primary:hover {
    background-color: #0b5ed7;
    box-shadow: 0 4px 8px rgba(11, 94, 215, 0.4);
}

/* PAGINAZIONE */
.pagination-wrapper .pagination {
    justify-content: center;
    margin-top: 1rem;
}

.pagination-wrapper .page-link {
    color: #0d6efd;
    border-radius: 0.3rem;
    padding: 0.4rem 0.9rem;
    font-weight: 500;
    transition: background-color 0.25s ease, color 0.25s ease;
}

.pagination-wrapper .page-link:hover {
    background-color: #0d6efd;
    color: #fff;
    box-shadow: 0 0 8px rgba(13, 110, 253, 0.6);
    text-decoration: none;
}

.pagination-wrapper .page-item.active .page-link {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: #fff;
    cursor: default;
    box-shadow: 0 0 12px rgba(13, 110, 253, 0.9);
}

.pagination-wrapper .page-item.disabled .page-link {
    color: #6c757d;
    pointer-events: none;
    background-color: transparent;
    border-color: transparent;
}

/* RESPONSIVE FORM */
@media (max-width: 576px) {
    form.incarichi-filtro-form {
        flex-wrap: wrap; /* torna a wrap su mobile per impilare gli elementi */
        align-items: stretch;
        gap: 0.75rem;
        padding: 0.75rem;
    }

    form.incarichi-filtro-form label.form-label {
        min-width: auto;
    }

    form.incarichi-filtro-form input[type="search"],
    form.incarichi-filtro-form select.form-select,
    form.incarichi-filtro-form button.btn-primary {
        max-width: 100%;
        flex-grow: unset;
    }

    .btn-wrapper {
        flex-shrink: unset; /* su mobile lascia fluido */
    }
}
</style>


