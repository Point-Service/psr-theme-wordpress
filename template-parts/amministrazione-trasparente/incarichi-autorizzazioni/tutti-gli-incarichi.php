<?php
global $post;

$max_posts = 10;
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : '';
$search_param = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = max(1, get_query_var('paged')); // Garantisco che sia almeno 1

// URL base archive CPT
$form_action = get_post_type_archive_link('incarichi_dip');
if (!$form_action) {
    $form_action = site_url('/tipi_cat_amm_trasp/incarichi-conferiti-e-autorizzati-ai-dipendenti/');
}

// Pulisco URL da parametri
$base_url = remove_query_arg(['paged'], $form_action);

// Estrazione anni disponibili
global $wpdb;
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date) AS year 
    FROM $wpdb->posts 
    WHERE post_type = 'incarichi_dip' 
      AND post_status = 'publish'
    ORDER BY year DESC
");

$args = [
    'post_type' => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby' => 'date',
    'order' => 'DESC',
    'paged' => $paged,
];

if ($selected_year) {
    $args['date_query'] = [
        ['year' => $selected_year],
    ];
}

if ($search_param) {
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
    <nav aria-label="Navigazione pagine">
        <ul class="pagination justify-content-center">
            <?php
            $big = 999999999; // numero grande per sostituzione paged

            $paginate_links = paginate_links([
                'base'      => esc_url_raw(add_query_arg(['paged' => $big], $base_url)),
                'format'    => '?paged=%#%',
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
                foreach ($paginate_links as $link) {
                    // Evidenzio la pagina corrente
                    $active = strpos($link, 'current') !== false ? ' active' : '';
                    echo '<li class="page-item' . $active . '">' . str_replace('page-numbers', 'page-link', $link) . '</li>';
                }
            }
            ?>
        </ul>
    </nav>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>


