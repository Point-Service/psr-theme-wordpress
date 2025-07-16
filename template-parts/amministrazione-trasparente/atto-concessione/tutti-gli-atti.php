<?php
// Imposta quanti elementi mostrare per pagina (default 5, override da URL con max_posts)
$posts_per_page = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;
if ($posts_per_page <= 0) {
    $posts_per_page = 5;
}

// Prendi il parametro paged dalla query var o da GET, fallback a 1
$paged = 1;
if (get_query_var('paged')) {
    $paged = intval(get_query_var('paged'));
} elseif (isset($_GET['paged'])) {
    $paged = intval($_GET['paged']);
}
if ($paged <= 0) {
    $paged = 1;
}

// Parametro di ricerca generica
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Costruisco gli argomenti della query WP
$args = [
    'post_type'      => 'incarichi_dip',  // Cambia con il tuo CPT
    'posts_per_page' => $posts_per_page,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
];

// Aggiungo ricerca se presente
if (!empty($search_query)) {
    $args['s'] = $search_query;
}

// Eseguo la query
$the_query = new WP_Query($args);
?>

<?php if ($the_query->have_posts()) : ?>
    <div class="row">
        <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
            <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
        <?php endwhile; ?>
    </div>

    <nav class="my-4">
        <ul class="pagination justify-content-center">
            <?php
            // Mantieni i parametri attuali tranne paged (per inserirlo dinamicamente)
            $query_args = $_GET;
            unset($query_args['paged']); // Rimuovo paged perché lo gestisco io nella paginazione

            // Funzione per aggiungere i parametri alla base url
            $base_url = esc_url(add_query_arg($query_args, get_permalink()));

            echo paginate_links([
                'base'      => $base_url . '%_%',
                'format'    => (strpos($base_url, '?') === false ? '?paged=%#%' : '&paged=%#%'),
                'current'   => $paged,
                'total'     => $the_query->max_num_pages,
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'type'      => 'list',
            ]);
            ?>
        </ul>
    </nav>

    <?php wp_reset_postdata(); ?>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

