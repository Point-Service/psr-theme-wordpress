<?php
// Disattiva il redirect canonico per far funzionare ?paged
remove_filter('template_redirect', 'redirect_canonical');

global $post;

// Numero massimo di post per pagina
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;

// Testo ricerca
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Paginazione
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

// Query args
$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

// Se c'è ricerca
if ($main_search_query) {
    $args['s'] = $main_search_query;
}

// La query
$the_query = new WP_Query($args);
$prefix = "_dci_icad_";
?>

<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
        <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
    <?php endwhile; ?>
    <?php wp_reset_postdata(); ?>

    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php
            echo paginate_links(array(
                'base'    => add_query_arg('paged', '%#%'),
                'format'  => '',
                'current' => $paged,
                'total'   => $the_query->max_num_pages,
                'prev_text' => __('&laquo; Precedente'),
                'next_text' => __('Successivo &raquo;'),
                'type'    => 'list',
            ));
            ?>
        </nav>
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>


