<?php
global $post;

// Numero massimo di post per pagina (con fallback)
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 3;

// Termine di ricerca, se presente
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Pagina corrente (compatibile con URL tipo ?paged=2 su pagina personalizzata)
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

// Query personalizzata per il CPT "incarichi_dip"
$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

// Aggiunge il termine di ricerca, se c'è
if ($main_search_query) {
    $args['s'] = $main_search_query;
}

// Esecuzione della query
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
        <div class="row my-4">
            <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
                <?php
                // Controlla che la funzione di paginazione Bootstrap personalizzata esista
                if (function_exists('dci_bootstrap_pagination')) {
                    echo dci_bootstrap_pagination(array(
                        'total'   => $the_query->max_num_pages,
                        'current' => $paged,
                    ));
                } else {
                    // Fallback: usa paginate_links classico
                    echo paginate_links(array(
                        'total'     => $the_query->max_num_pages,
                        'current'   => $paged,
                        'prev_text' => '&laquo;',
                        'next_text' => '&raquo;',
                        'type'      => 'list',
                    ));
                }
                ?>
            </nav>
        </div>
    <?php endif; ?>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

