<?php
global $post;

// Imposta il numero massimo di post per pagina (con fallback)
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;

// Ricerca
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Pagina corrente
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

// Argomenti WP_Query
$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'paged'          => $paged,
    'orderby'        => 'date',
    'order'          => 'DESC',
);

// Aggiungi la ricerca se presente
if ($main_search_query) {
    $args['s'] = $main_search_query;
}

// Esegui la query
$the_query = new WP_Query($args);
$prefix = '_dci_icad_';
?>

<?php if ($the_query->have_posts()) : ?>
    <div class="row">
        <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
            <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
        <?php endwhile; ?>
    </div>

    <?php wp_reset_postdata(); ?>

    <?php if ($the_query->max_num_pages > 1): ?>
        <nav class="my-4">
            <ul class="pagination justify-content-center">

                <?php
                $pagination_links = paginate_links(array(
                    'base'      => add_query_arg('paged', '%#%'),
                    'format'    => '',
                    'current'   => $paged,
                    'total'     => $the_query->max_num_pages,
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'type'      => 'array',
                ));

                foreach ((array) $pagination_links as $link) {
                    if (strpos($link, 'current') !== false) {
                        echo '<li class="page-item active"><span class="page-link">' . strip_tags($link) . '</span></li>';
                    } else {
                        echo '<li class="page-item">' . str_replace('page-numbers', 'page-link', $link) . '</li>';
                    }
                }
                ?>

            </ul>
        </nav>
    <?php endif; ?>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

