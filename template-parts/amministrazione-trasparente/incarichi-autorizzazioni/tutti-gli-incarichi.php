<?php
global $post;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 2;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

$paged = max(1, get_query_var('paged') ? get_query_var('paged') : (isset($_GET['paged']) ? intval($_GET['paged']) : 1));

$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

if ($main_search_query) {
    $args['s'] = $main_search_query;
}

$the_query = new WP_Query($args);
$prefix = "_dci_icad_";
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
                echo str_replace(
                    array('<span class="page-numbers current">', '<a class="page-numbers', '</span>', 'prev page-numbers', 'next page-numbers'),
                    array('<li class="page-item active"><span class="page-link">', '<li class="page-item"><a class="page-link', '</span></li>', 'page-item prev', 'page-item next'),
                    paginate_links(array(
                        'total'     => $the_query->max_num_pages,
                        'current'   => $paged,
                        'type'      => 'list',
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
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

