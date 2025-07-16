<?php
global $post;

// Imposta quanti post mostrare per pagina (di default 100)
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
if ($max_posts <= 0) $max_posts = 10;

// Cerca termini
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Gestione paged (supporta sia /page/2 che ?paged=2)
$paged = max(1, get_query_var('paged') ? get_query_var('paged') : (isset($_GET['paged']) ? intval($_GET['paged']) : 1));

// Argomenti della query
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
            // Mantieni i parametri della query string nei link di paginazione
            $query_args = [];
            if (!empty($_GET['search'])) {
                $query_args['search'] = sanitize_text_field($_GET['search']);
            }
            if (!empty($_GET['max_posts'])) {
                $query_args['max_posts'] = intval($_GET['max_posts']);
            }

            echo paginate_links([
                'base'      => get_pagenum_link(1) . '%_%',
                'format'    => (strpos(get_pagenum_link(1), '?') !== false ? '&paged=%#%' : '?paged=%#%'),
                'current'   => $paged,
                'total'     => $the_query->max_num_pages,
                'type'      => 'list',
                'add_args'  => $query_args,
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
            ]);
            ?>
        </nav>
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

