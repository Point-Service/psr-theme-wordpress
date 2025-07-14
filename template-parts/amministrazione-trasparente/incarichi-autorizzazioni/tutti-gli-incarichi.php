<?php
global $post;

$posts_per_page = 10;
$selected_year = isset($_GET['year']) ? sanitize_text_field($_GET['year']) : '';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// --- Ottieni anni unici da post_date di tutti i post 'incarichi_dip' ---
global $wpdb;
$post_type = 'incarichi_dip';
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date) 
    FROM $wpdb->posts
    WHERE post_type = '$post_type' 
      AND post_status = 'publish'
    ORDER BY post_date DESC
");

// Prepara argomenti base query
$args = [
    'post_type'      => $post_type,
    'posts_per_page' => $posts_per_page,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
];

// Se è selezionato un anno valido, aggiungi filtro data
if ( $selected_year && in_array($selected_year, $years, true) ) {
    $args['date_query'] = [
        [
            'year' => intval($selected_year),
        ]
    ];
}

$the_query = new WP_Query($args);
$prefix = "_dci_icad_";
?>

<!-- Form filtro anno -->
<form method="get" class="mb-4">
    <label for="year-select">Filtra per anno pubblicazione:</label>
    <select id="year-select" name="year" onchange="this.form.submit()">
        <option value="">Tutti gli anni</option>
        <?php foreach ($years as $year_option) : ?>
            <option value="<?php echo esc_attr($year_option); ?>" <?php selected($selected_year, $year_option); ?>>
                <?php echo esc_html($year_option); ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if ($the_query->have_posts()) : ?>
    
    <?php while ($the_query->have_posts()) : $the_query->the_post();
        get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card');
    endwhile;
    wp_reset_postdata(); ?>
    
    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php echo dci_bootstrap_pagination($the_query); ?>
        </nav>
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>
