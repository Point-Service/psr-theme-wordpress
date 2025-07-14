<?php
global $post;

$max_posts = 5;
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : '';
$search_param = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Paged da query var e da GET (fallback)
$paged = max(1, get_query_var('paged'));
if (isset($_GET['paged'])) {
    $paged = max(1, intval($_GET['paged']));
}

// Base URL archive
$form_action = get_post_type_archive_link('incarichi_dip');
if (!$form_action) {
    $form_action = site_url('/tipi_cat_amm_trasp/incarichi-conferiti-e-autorizzati-ai-dipendenti/');
}

// Estraggo gli anni dal DB
global $wpdb;
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date) AS year 
    FROM $wpdb->posts 
    WHERE post_type = 'incarichi_dip' AND post_status = 'publish'
    ORDER BY year DESC
");

// Preparo query args
$args = [
    'post_type' => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby' => 'date',
    'order' => 'DESC',
    'paged' => $paged,
];

if (!empty($selected_year)) {
    $args['date_query'] = [
        ['year' => $selected_year],
    ];
}

if (!empty($search_param)) {
    $args['s'] = $search_param;
}

$the_query = new WP_Query($args);

// PREPARO L'URL PER FORM E PAGINAZIONE
// 1. Rimuovo 'paged' e parametri vuoti ('year' se vuoto)
$query_args = [];
if (!empty($selected_year)) {
    $query_args['year'] = $selected_year;
}
if (!empty($search_param)) {
    $query_args['search'] = $search_param;
}

// 2. Costruisco base URL senza paged (per form e paginazione)
$base_url = remove_query_arg('paged', $form_action);
$base_url = add_query_arg($query_args, $base_url);
?>

<!-- FORM FILTRO ANNO -->
<form method="get" action="<?php echo esc_url($base_url); ?>" class="mb-4" id="filter-form">
    <label for="year-select">Filtra per anno pubblicazione:</label>
    <select id="year-select" name="year">
        <option value="">Tutti gli anni</option>
        <?php foreach ($years as $year_option) : ?>
            <option value="<?php echo esc_attr($year_option); ?>" <?php selected($selected_year, $year_option); ?>>
                <?php echo esc_html($year_option); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if (!empty($search_param)): ?>
        <input type="hidden" name="search" value="<?php echo esc_attr($search_param); ?>">
    <?php endif; ?>
</form>

<script>
    const yearSelect = document.getElementById('year-select');
    yearSelect.addEventListener('change', function() {
        if (this.value === '') {
            this.removeAttribute('name'); // NON inviare il parametro year se vuoto
        } else {
            this.setAttribute('name', 'year'); // Assicura che venga inviato solo se non vuoto
        }
        this.form.submit();
    });
</script>


<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post();
        get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card');
    endwhile;
    wp_reset_postdata(); ?>

    <!-- PAGINAZIONE BOOTSTRAP -->
    <?php
    // Prendo URL corrente senza 'paged' (per costruire la base)
    $current_url = remove_query_arg('paged', $base_url);

    // Costruisco base paginazione con placeholder
    // Controllo se URL termina con slash
    $base = trailingslashit($current_url) . 'page/%#%/';

    $paginate_links = paginate_links([
        'base' => $base,
        'format' => '',
        'current' => $paged,
        'total' => $the_query->max_num_pages,
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
        'type' => 'array',
        'add_args' => [], // tutti i parametri già presenti in $base
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
    ?>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>
