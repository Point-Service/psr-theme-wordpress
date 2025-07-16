<?php
// Evita redirect automatici di WordPress
remove_filter('template_redirect', 'redirect_canonical');

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$year_filter = isset($_GET['year']) ? intval($_GET['year']) : 0;

// Prepara args query base
$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

// Se c'è ricerca, aggiungila
if (!empty($main_search_query)) {
    $args['s'] = $main_search_query;
}

// Se filtro anno attivo, aggiungi meta query per l'anno
if ($year_filter) {
    $args['date_query'] = array(
        array(
            'year' => $year_filter,
        ),
    );
}

$the_query = new WP_Query($args);

// Funzione per creare dropdown anni dinamico (dal primo al più recente)
function dci_year_dropdown($selected_year = 0) {
    global $wpdb;
    // Prendi il primo anno pubblicato dei post incarichi_dip
    $first_year = $wpdb->get_var("
        SELECT YEAR(MIN(post_date))
        FROM {$wpdb->posts}
        WHERE post_type = 'incarichi_dip' AND post_status = 'publish'
    ");

    $current_year = date('Y');
    echo '<form method="GET" class="mb-3">';
    // Mantieni altri parametri GET tipo search, max_posts, page
    if (!empty($_GET['search'])) {
        echo '<input type="hidden" name="search" value="' . esc_attr($_GET['search']) . '">';
    }
    if (!empty($_GET['max_posts'])) {
        echo '<input type="hidden" name="max_posts" value="' . intval($_GET['max_posts']) . '">';
    }
    // Non mantenere page per partire da 1 quando cambio anno
    echo '<select name="year" onchange="this.form.submit()" class="form-select w-auto d-inline-block">';
    echo '<option value="0"' . selected($selected_year, 0, false) . '>Tutti gli anni</option>';
    for ($y = $current_year; $y >= $first_year; $y--) {
        echo '<option value="' . $y . '"' . selected($selected_year, $y, false) . '>' . $y . '</option>';
    }
    echo '</select>';
    echo '</form>';
}

?>

<?php if ($the_query->have_posts()) : ?>

    <!-- Dropdown filtro anno -->
    <?php dci_year_dropdown($year_filter); ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
        <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
    <?php endwhile; ?>
    <?php wp_reset_postdata(); ?>

    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php
            $pagination_links = paginate_links(array(
                'base'      => add_query_arg('page', '%#%'),
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

            <p class="text-center mt-2 mb-0">
                Pagina <?php echo $paged; ?> di <?php echo $the_query->max_num_pages; ?>
                <?php if ($year_filter) : ?>
                    - Anno <?php echo $year_filter; ?>
                <?php endif; ?>
            </p>
        </nav>
    </div>

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

