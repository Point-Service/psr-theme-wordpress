<?php
global $post;

// Numero elementi per pagina fisso a 10
$posts_per_page = 10;

// Prendo l'anno selezionato da GET (se esiste)
$selected_year = isset($_GET['year']) ? sanitize_text_field($_GET['year']) : '';

// Pagina corrente (paged)
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Recupero anni unici dal meta '_dci_icad_anno_conferimento' presenti nei post
global $wpdb;
$meta_key = '_dci_icad_anno_conferimento';

// Query per estrarre anni distinti presenti
$years = $wpdb->get_col(
    $wpdb->prepare(
        "
        SELECT DISTINCT meta_value FROM $wpdb->postmeta
        WHERE meta_key = %s
        ORDER BY meta_value DESC
        ",
        $meta_key
    )
);

// Prepara argomenti base query WP_Query
$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $posts_per_page,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

// Se è stato selezionato un anno valido, aggiungo meta_query per filtrare
if ( $selected_year && in_array( $selected_year, $years, true ) ) {
    $args['meta_query'] = array(
        array(
            'key'     => $meta_key,
            'value'   => $selected_year,
            'compare' => '=',
        ),
    );
}

$the_query = new WP_Query($args);
$prefix = "_dci_icad_";
?>

<!-- Form filtro anno -->
<form method="get" class="mb-4">
    <label for="year-select">Filtra per anno conferimento:</label>
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
