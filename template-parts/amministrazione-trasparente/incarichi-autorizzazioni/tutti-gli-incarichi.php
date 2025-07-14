<?php
global $post;

$max_posts = 10; // post per pagina
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : '';
$search_param = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = max(1, get_query_var('paged', 1));

// Ottieni URL archivio CPT (assicurati che funzioni, altrimenti sostituisci con URL corretto)
$form_action = get_post_type_archive_link('incarichi_dip');
if (!$form_action) {
    $form_action = site_url('/tipi_cat_amm_trasp/incarichi-conferiti-e-autorizzati-ai-dipendenti/');
}

// Recupera gli anni dal DB per mostrare solo quelli con post
global $wpdb;
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date) AS year 
    FROM $wpdb->posts 
    WHERE post_type = 'incarichi_dip' 
      AND post_status = 'publish'
    ORDER BY year DESC
");

// Imposta args WP_Query
$args = [
    'post_type' => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC',
];

if ($selected_year) {
    $args['date_query'] = [['year' => $selected_year]];
}

if ($search_param) {
    $args['s'] = $search_param;
}

$the_query = new WP_Query($args);
?>

<form method="get" action="<?php echo esc_url($form_action); ?>" class="mb-4">
    <label for="year-select">Filtra per anno pubblicazione:</label>
    <select id="year-select" name="year" onchange="this.form.submit()">
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

<?php if ($the_query->have_posts()): ?>
    <?php
    while ($the_query->have_posts()):
        $the_query->the_post();
        get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card');
    endwhile;
    wp_reset_postdata();
    ?>

    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php
            echo paginate_links([
                'base' => esc_url_raw(add_query_arg('paged', '%#%', $form_action)),
                'format' => '',
                'current' => $paged,
                'total' => $the_query->max_num_pages,
                'add_args' => [
                    'year' => $selected_year ?: '',
                    'search' => $search_param ?: '',
                ],
                'prev_text' => __('« Precedente'),
                'next_text' => __('Successivo »'),
                'type' => 'list',
            ]);
            ?>
        </nav>
    </div>
<?php else: ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>


