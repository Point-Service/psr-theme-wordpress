<?php
global $post;

// Recupero parametri da URL
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 100;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Paginazione: prima prova con get_query_var(), poi fallback su $_GET['paged']
$paged = get_query_var('paged');
if (!$paged && isset($_GET['paged'])) {
    $paged = intval($_GET['paged']);
}
if ($paged < 1) {
    $paged = 1;
}

// Costruzione argomenti query
$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

// Se presente ricerca, aggiungila alla query
if ($main_search_query) {
    $args['s'] = $main_search_query;
}

// Esegui la query
$the_query = new WP_Query($args);
$prefix = "_dci_icad_";
?>

<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post();
        get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card');
    endwhile;
    wp_reset_postdata(); ?>

    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php
            // Costruisci base URL per mantenere i parametri attuali
            $pagination_base = add_query_arg(null, null);
            $pagination_base = remove_query_arg('paged', $pagination_base);

            echo paginate_links([
                'base'      => $pagination_base . '&paged=%#%',
                'format'    => '',
                'current'   => $paged,
                'total'     => $the_query->max_num_pages,
                'type'      => 'list',
                'prev_text' => __('&laquo; Precedente'),
                'next_text' => __('Successivo &raquo;'),
            ]);
            ?>
        </nav>
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

