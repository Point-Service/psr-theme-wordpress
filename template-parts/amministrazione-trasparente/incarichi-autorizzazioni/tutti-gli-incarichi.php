<?php
global $post;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 2;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

$args = [
    'post_type' => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby' => 'date',
    'order' => 'DESC',
    'paged' => $paged,
];

if ($main_search_query) {
    $args['s'] = $main_search_query;
}

$the_query = new WP_Query($args);
?>

<?php if ($the_query->have_posts()) : ?>
    <div class="row">
        <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
            <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
        <?php endwhile; ?>
    </div>

    <?php wp_reset_postdata(); ?>

    <?php if ($the_query->max_num_pages > 1): ?>
        <nav class="my-4" aria-label="Paginazione">
            <ul class="pagination justify-content-center">
                <?php
                $paginate_links = paginate_links([
                    'total' => $the_query->max_num_pages,
                    'current' => $paged,
                    'type' => 'array',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                ]);

                if (is_array($paginate_links)) {
                    foreach ($paginate_links as $link) {
                        $class = strpos($link, 'current') !== false ? 'page-item active' : 'page-item';
                        // Cambia classi link per Bootstrap 5
                        $link = str_replace('page-numbers', 'page-link', $link);
                        echo "<li class=\"$class\">$link</li>";
                    }
                }
                ?>
            </ul>
        </nav>
    <?php endif; ?>

<?php else : ?>
    <div class="alert alert-info text-center">Nessun incarico conferito trovato.</div>
<?php endif; ?>
