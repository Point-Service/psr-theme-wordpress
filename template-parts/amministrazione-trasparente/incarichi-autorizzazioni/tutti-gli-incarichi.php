<?php
// Evita redirect automatici di WordPress
remove_filter('template_redirect', 'redirect_canonical');

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Usa ?page=2 invece di ?paged=2
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

if (!empty($main_search_query)) {
    $args['s'] = $main_search_query;
}


$the_query = new WP_Query($args);
?>

<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
        <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
    <?php endwhile; ?>
    <?php wp_reset_postdata(); ?>

    <div class="row my-4">
      


 <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
    <?php
    $pagination_links = paginate_links(array(
        'base'      => add_query_arg('page', '%#%'),
        'format'    => '',
        'current'   => $paged,
        'total'     => $the_query->max_num_pages,
        'prev_text' => __('&laquo; Precedente'),
        'next_text' => __('Successivo &raquo;'),
        'type'      => 'array', // otteniamo array per personalizzare markup
    ));

    if ($pagination_links) : ?>
        <ul class="pagination justify-content-center">
            <?php foreach ($pagination_links as $link) :
                $active = strpos($link, 'current') !== false ? ' active' : '';
                // aggiungiamo classi Bootstrap 'page-link' e 'page-item'
                $link = str_replace('<a ', '<a class="page-link" ', $link);
                $link = str_replace('<span class="current">', '<span class="page-link active" aria-current="page">', $link);
            ?>
                <li class="page-item<?php echo $active; ?>"><?php echo $link; ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</nav>

        
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

