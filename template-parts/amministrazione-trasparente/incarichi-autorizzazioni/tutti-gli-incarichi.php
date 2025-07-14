<?php
global $post;

$max_posts = 10; // 10 elementi per pagina
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : '';
$search_param = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Gestione corretta di paged per query paginata
$paged = 1;
if (get_query_var('paged')) {
    $paged = get_query_var('paged');
} elseif (isset($_GET['paged']) && intval($_GET['paged']) > 0) {
    $paged = intval($_GET['paged']);
}

// URL base archivio CPT
$form_action = get_post_type_archive_link('incarichi_dip');
if (!$form_action) {
    // fallback URL, cambia con il tuo percorso corretto
    $form_action = site_url('/tipi_cat_amm_trasp/incarichi-conferiti-e-autorizzati-ai-dipendenti/');
}

// Recupera anni disponibili dalla data di pubblicazione dei post
global $wpdb;
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date) AS year 
    FROM $wpdb->posts 
    WHERE post_type = 'incarichi_dip' 
      AND post_status = 'publish'
    ORDER BY year DESC
");

// Argomenti query WP_Query
$args = [
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
];

if ($selected_year) {
    $args['date_query'] = [
        [
            'year' => $selected_year,
        ],
    ];
}

if ($search_param) {
    $args['s'] = $search_param;
}

$the_query = new WP_Query($args);
$prefix = "_dci_icad_";
?>

<!-- Form filtro anno -->
<form method="get" action="<?php echo esc_url($form_action); ?>" class="mb-4">
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

<!-- Loop post -->
<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post();
        get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card');
    endwhile;
    wp_reset_postdata(); ?>

    <!-- Paginazione -->
    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php
            echo paginate_links([
                'base'      => esc_url_raw(add_query_arg('paged', '%#%', $form_action)),
                'format'    => '',
                'current'   => max(1, $paged),
                'total'     => $the_query->max_num_pages,
                'add_args'  => [
                    'year'   => $selected_year ?: '',
                    'search' => $search_param ?: '',
                ],
                'prev_text' => __('« Precedente'),
                'next_text' => __('Successivo »'),
                'type'      => 'list',
            ]);
            ?>
        </nav>
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

