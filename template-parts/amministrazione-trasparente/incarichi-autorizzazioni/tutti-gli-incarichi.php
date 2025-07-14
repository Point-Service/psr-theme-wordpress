<?php
global $post;

$max_posts = 5;

// Prendo parametri GET, puliti
$selected_year = isset($_GET['year']) && is_numeric($_GET['year']) ? intval($_GET['year']) : '';
$search_param = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = 1;
if (get_query_var('paged')) {
    $paged = max(1, intval(get_query_var('paged')));
} elseif (isset($_GET['paged'])) {
    $paged = max(1, intval($_GET['paged']));
}

// URL archivio CPT
$form_action = get_post_type_archive_link('incarichi_dip');
if (!$form_action) {
    $form_action = site_url('/tipi_cat_amm_trasp/incarichi-conferiti-e-autorizzati-ai-dipendenti/');
}

// Prendo anni disponibili dal DB
global $wpdb;
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date) AS year 
    FROM $wpdb->posts 
    WHERE post_type = 'incarichi_dip' 
      AND post_status = 'publish' 
    ORDER BY year DESC
");

// Costruisco query WP
$args = [
    'post_type' => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby' => 'date',
    'order' => 'DESC',
    'paged' => $paged,
];

if ($selected_year) {
    $args['date_query'] = [['year' => $selected_year]];
}

if ($search_param) {
    $args['s'] = $search_param;
}

$the_query = new WP_Query($args);

// Costruisco base URL senza parametri di paginazione
$base_url = remove_query_arg('paged', $form_action);

// Mantengo solo parametri valorizzati
$query_args = [];
if ($selected_year) {
    $query_args['year'] = $selected_year;
}
if ($search_param) {
    $query_args['search'] = $search_param;
}

// Aggiungo parametri GET
$base_url_with_args = add_query_arg($query_args, $base_url);
$base_url_with_args = rtrim($base_url_with_args, '/');
?>

<!-- Form filtro anno -->
<form method="get" action="<?php echo esc_url($base_url); ?>" id="filter-form" class="mb-4">
    <label for="year-select">Filtra per anno pubblicazione:</label>
    <select id="year-select" name="year">
        <option value="">Tutti gli anni</option>
        <?php foreach ($years as $year_option): ?>
            <option value="<?php echo esc_attr($year_option); ?>" <?php selected($selected_year, $year_option); ?>>
                <?php echo esc_html($year_option); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if ($search_param): ?>
        <input type="hidden" name="search" value="<?php echo esc_attr($search_param); ?>">
    <?php endif; ?>
</form>

<script>
    document.getElementById('year-select').addEventListener('change', function() {
        if (this.value === '') {
            this.removeAttribute('name');
        } else {
            this.setAttribute('name', 'year');
        }
        this.form.submit();
    });
</script>

<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post();
        get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card');
    endwhile;
    wp_reset_postdata();
    ?>

    <!-- PAGINAZIONE BOOTSTRAP -->
    <?php
    $total_pages = $the_query->max_num_pages;

    if ($total_pages > 1) :

        $paginate_links = paginate_links([
            'base' => $base_url_with_args . '%_%',
            'format' => '?paged=%#%',
            'current' => $paged,
            'total' => $total_pages,
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'type' => 'array',
        ]);

        if ($paginate_links) {
            echo '<nav aria-label="Navigazione pagine"><ul class="pagination justify-content-center">';
            foreach ($paginate_links as $link) {
                $active = strpos($link, 'current') !== false ? ' active' : '';
                $link = str_replace('page-numbers', 'page-link', $link);
                echo '<li class="page-item' . $active . '">' . $link . '</li>';
            }
            echo '</ul></nav>';
        }

    endif;
    ?>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

