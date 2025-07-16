<?php
global $post;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 2;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Qui prendi 'paged' SOLO da $_GET, così svincolato da rewrite e permalink
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

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
?>

<?php if ($the_query->have_posts()) : ?>
    <div class="row">
        <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
            <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
        <?php endwhile; ?>
    </div>
    <?php wp_reset_postdata(); ?>

    <?php if ($the_query->max_num_pages > 1) : ?>
        <nav class="my-4" aria-label="Paginazione">
            <ul class="pagination justify-content-center">
                <?php
                $base_url = strtok($_SERVER['REQUEST_URI'], '?'); // URL base senza query
                $query_args = $_GET;
                
                // Funzione helper per creare link paginati con ?paged=N
                function custom_paginate_link($page_num, $base_url, $query_args, $current) {
                    $query_args['paged'] = $page_num;
                    $url = $base_url . '?' . http_build_query($query_args);
                    $active = ($page_num == $current) ? 'active' : '';
                    return "<li class='page-item $active'><a class='page-link' href='$url'>$page_num</a></li>";
                }

                // Link precedente
                if ($paged > 1) {
                    $prev_page = $paged - 1;
                    echo custom_paginate_link($prev_page, $base_url, $query_args, 0);
                }

                // Link numerici (puoi limitarli a max 5 ad esempio)
                $max_links = 5;
                $start = max(1, $paged - floor($max_links / 2));
                $end = min($the_query->max_num_pages, $start + $max_links - 1);

                for ($i = $start; $i <= $end; $i++) {
                    echo custom_paginate_link($i, $base_url, $query_args, $paged);
                }

                // Link successivo
                if ($paged < $the_query->max_num_pages) {
                    $next_page = $paged + 1;
                    echo custom_paginate_link($next_page, $base_url, $query_args, 0);
                }
                ?>
            </ul>
        </nav>
    <?php endif; ?>

<?php else : ?>
    <div class="alert alert-info text-center">Nessun incarico conferito trovato.</div>
<?php endif; ?>

