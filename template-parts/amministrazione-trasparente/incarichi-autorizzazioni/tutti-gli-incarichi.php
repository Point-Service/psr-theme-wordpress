<?php
global $post;

// Determina la pagina corrente (supporta sia permalink che query string)
$paged = 1;
if ( get_query_var('paged') ) {
    $paged = intval(get_query_var('paged'));
} elseif ( isset($_GET['paged']) ) {
    $paged = intval($_GET['paged']);
}
if ( $paged < 1 ) $paged = 1;

// Numero di post per pagina (puoi modificare o prendere da querystring)
$posts_per_page = 2;

// Parametro ricerca (opzionale)
$search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Costruisci args per WP_Query
$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $posts_per_page,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

if ( $search_query ) {
    $args['s'] = $search_query;
}

// Esegui la query
$the_query = new WP_Query($args);
?>

<?php if ( $the_query->have_posts() ) : ?>

    <div class="row">
        <?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>
            <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
        <?php endwhile; ?>
    </div>

    <?php wp_reset_postdata(); ?>

    <?php if ( $the_query->max_num_pages > 1 ) : ?>
        <nav class="my-4" aria-label="Navigazione pagine">
            <ul class="pagination justify-content-center">

                <?php
                // Paginazione bootstrap-stile
                echo str_replace(
                    array('<span class="page-numbers current">', '<a class="page-numbers', '</span>', 'prev page-numbers', 'next page-numbers'),
                    array('<li class="page-item active"><span class="page-link">', '<li class="page-item"><a class="page-link', '</span></li>', 'page-item prev', 'page-item next'),
                    paginate_links(array(
                        'base'      => @add_query_arg('paged','%#%'), // per query string
                        'format'    => '?paged=%#%', // fallback per query string
                        'total'     => $the_query->max_num_pages,
                        'current'   => $paged,
                        'type'      => 'list',
                        'prev_text' => '&laquo; Precedente',
                        'next_text' => 'Successivo &raquo;',
                    ))
                );
                ?>

            </ul>
        </nav>
    <?php endif; ?>

<?php else : ?>

    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>

<?php endif; ?>



