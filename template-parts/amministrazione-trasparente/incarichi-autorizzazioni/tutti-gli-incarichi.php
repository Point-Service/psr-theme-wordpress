<?php
global $post;

// Recupero il parametro anno selezionato
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : 0;
$search_param = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
$posts_per_page = 10; // Paginazione a 10 elementi

// --- Recupero anni con post pubblicati per il post type 'incarichi_dip' ---

global $wpdb;
$years = $wpdb->get_col("
    SELECT DISTINCT YEAR(post_date) 
    FROM {$wpdb->posts} 
    WHERE post_type = 'incarichi_dip' 
      AND post_status = 'publish'
    ORDER BY post_date DESC
");

// URL base per il form di filtro: URL archivio custom post type
$form_action = get_post_type_archive_link('incarichi_dip');
if (!$form_action) {
    // fallback: permalink pagina corrente
    $form_action = get_permalink();
}

// --- Costruisco args per WP_Query ---

$args = [
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $posts_per_page,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
];

// Filtro per anno se selezionato
if ($selected_year) {
    $args['date_query'] = [
        [
            'year' => $selected_year,
        ],
    ];
}

// Filtro per ricerca testo se presente
if ($search_param) {
    $args['s'] = $search_param;
}

$the_query = new WP_Query($args);
?>

<!-- FORM filtro anno -->
<form method="get" action="<?php echo esc_url( $form_action ); ?>" class="mb-4">
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
    <div class="row">
        <?php
        while ($the_query->have_posts()) : $the_query->the_post();
            get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card');
        endwhile;
        wp_reset_postdata();
        ?>
    </div>

    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php
            // Funzione di paginazione custom - mantieni i parametri GET 'year' e 'search'
            $big = 999999999; // un numero grande per il placeholder di pagina

            echo paginate_links([
                'base'      => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
                'format'    => '?paged=%#%',
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
