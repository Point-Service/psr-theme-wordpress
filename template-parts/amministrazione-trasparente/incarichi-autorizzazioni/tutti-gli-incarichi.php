<?php
// Evita redirect automatici di WordPress che rovinano i parametri custom
remove_filter('template_redirect', 'redirect_canonical');

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Gestione pagina per paginazione (usiamo ?page=)
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Gestione filtro anno (0 = tutti gli anni)
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : 0;

// Costruiamo argomenti per WP_Query
$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

// Se c'è ricerca testo
if (!empty($main_search_query)) {
    $args['s'] = $main_search_query;
}

// Se filtro anno selezionato > 0 aggiungiamo filtro data pubblicazione
if ($selected_year > 0) {
    $args['date_query'] = array(
        array(
            'year' => $selected_year,
        ),
    );
}

// Query personalizzata
$the_query = new WP_Query($args);

// Per creare la select anno partiamo dall’anno attuale a un anno base, es 10 anni fa
$current_year = date('Y');
$first_year = $current_year - 10;

// Prendi permalink della pagina corrente senza query string per base URL paginazione
$current_url = get_permalink();

// Costruiamo la base URL per la paginazione con filtro anno e ricerca (se presenti)
$base_url = add_query_arg(array(
    'year'   => ($selected_year > 0) ? $selected_year : 0,
    'search' => $main_search_query ? $main_search_query : '',
    'page'   => '%#%',
), $current_url);
?>

<!-- FORM FILTRO ANNO -->
<form method="get" class="mb-3 d-flex align-items-center flex-wrap gap-2">
    <label for="filter-year" class="form-label mb-0 me-2">Filtra per anno:</label>
    <select id="filter-year" name="year" onchange="this.form.submit()" class="form-select w-auto">
        <option value="0" <?php selected($selected_year, 0); ?>>Tutti gli anni</option>
        <?php for ($y = $current_year; $y >= $first_year; $y--) : ?>
            <option value="<?php echo $y; ?>" <?php selected($selected_year, $y); ?>><?php echo $y; ?></option>
        <?php endfor; ?>
    </select>

    <?php if (!empty($main_search_query)) : ?>
        <input type="hidden" name="search" value="<?php echo esc_attr($main_search_query); ?>">
    <?php endif; ?>
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
                'type'      => 'array', // array per personalizzare markup
            ));

            if ($pagination_links) : ?>
                <ul class="pagination justify-content-center">
                    <?php foreach ($pagination_links as $link) :
                        $active = strpos($link, 'current') !== false ? ' active' : '';
                        // Aggiungiamo classi Bootstrap
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
/* Stile paginazione Bootstrap custom */
.pagination .page-link {
    color: var(--bs-primary);
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    margin: 0 0.25rem;
    border-radius: 0.25rem;
    transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out;
    font-weight: 500;
}

.pagination .page-link:hover {
    background-color: var(--bs-primary);
    color: #fff;
    text-decoration: none;
    box-shadow: 0 0 8px rgba(13, 110, 253, 0.6);
}

.pagination .page-item.active .page-link {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: #fff;
    cursor: default;
    box-shadow: 0 0 10px rgba(13, 110, 253, 0.75);
}

.pagination .page-item.disabled .page-link {
    color: var(--bs-secondary);
    pointer-events: none;
    background-color: transparent;
    border-color: transparent;
}

/* Responsive e spaziatura filtro */
form.mb-3 {
    gap: 0.5rem;
}

form select.form-select {
    min-width: 120px;
}

/* Label allineata verticale */
form label.form-label {
    margin-bottom: 0;
    font-weight: 600;
    color: var(--bs-secondary);
}
</style>
