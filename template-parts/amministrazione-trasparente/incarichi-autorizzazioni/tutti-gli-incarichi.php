<?php
global $post;

$max_posts = 5;
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : '';
$search_param = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Recupero il numero pagina (paged) da query var o da GET per sicurezza
$paged = max(1, get_query_var('paged'));
if (isset($_GET['paged'])) {
    $paged = max(1, intval($_GET['paged']));
}

// URL base archive CPT
$form_action = get_post_type_archive_link('incarichi_dip');
if (!$form_action) {
    $form_action = site_url('/tipi_cat_amm_trasp/incarichi-conferiti-e-autorizzati-ai-dipendenti/');
}

// Pulisco URL da parametri 'paged'
$base_url = remove_query_arg(['paged'], $form_action);

// Estrazione anni disponibili tramite query diretta al DB
global $wpdb;
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date) AS year 
    FROM $wpdb->posts 
    WHERE post_type = 'incarichi_dip' 
      AND post_status = 'publish'
    ORDER BY year DESC
");

// Costruzione args WP_Query
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
$prefix = "_dci_icad_";
?>

<!-- Form filtro anno -->
<form method="get" action="<?php echo esc_url($base_url); ?>" class="mb-4">
    <label for="year-select">Filtra per anno pubblicazione:</label>
    <select id="year-select" name="year" onchange="this.form.submit()">
        <option value="">Tutti gli anni</option>
        <?php foreach ($years as $year_option) : ?>
            <option value="<?php echo esc_attr($year_option); ?>" <?php selected($selected_year, $year_option); ?>>
                <?php echo esc_html($year_option); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if ($search_param): ?>
        <input type="hidden" name="search" value="<?php echo esc_attr($search_param); ?>">
    <?php endif; ?>
</form>

<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post();
        get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card');
    endwhile;
    wp_reset_postdata(); ?>

    <!-- PAGINAZIONE BOOTSTRAP -->
    <?php
    $big = 999999999; // numero grande per sostituzione paged
    $base = str_replace($big, '%#%', esc_url(get_pagenum_link($big)));

    $paginate_links = paginate_links([
        'base'      => $base,
        'format'    => '',
        'current'   => $paged,
        'total'     => $the_query->max_num_pages,
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
        'type'      => 'array',
        'add_args'  => array_filter([
            'year' => $selected_year ?: null,
            'search' => $search_param ?: null,
        ]),
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

