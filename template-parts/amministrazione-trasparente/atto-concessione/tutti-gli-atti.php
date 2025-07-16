<?php
// Numero elementi per pagina (puoi impostare fisso o dinamico da URL)
$posts_per_page = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;

// Recupera la pagina corrente da query var o da URL (parametro paged)
$paged = max(1, get_query_var('paged') ? get_query_var('paged') : (isset($_GET['paged']) ? intval($_GET['paged']) : 1));

// Parametro ricerca generica (opzionale)
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Argomenti per la query
$args = [
    'post_type' => 'incarichi_dip',   // Cambia con il tuo CPT
    'posts_per_page' => $posts_per_page,
    'paged' => $paged,
    'orderby' => 'date',
    'order' => 'DESC',
];

// Se c'è ricerca, aggiungi 's' => $search_query
if (!empty($search_query)) {
    $args['s'] = $search_query;
}

// Esegui la query
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
            // Costruisco la paginazione
            echo paginate_links([
                'base' => esc_url(add_query_arg('paged', '%#%')),
                'format' => '?paged=%#%',
                'current' => $paged,
                'total' => $the_query->max_num_pages,
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'type' => 'list',
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
