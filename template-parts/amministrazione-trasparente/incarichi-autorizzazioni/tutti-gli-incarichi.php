<?php
global $post;

// Numero di post per pagina, con un valore di fallback di 10
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 2;
// Se è presente un termine di ricerca
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';

// Determina la pagina corrente
$paged = max(1, get_query_var('paged') ? get_query_var('paged') : (isset($_GET['paged']) ? intval($_GET['paged']) : 1));

// Impostazioni per la query
$args = array(
    'post_type'      => 'incarichi_dip', // Post type "incarichi_dip"
    'posts_per_page' => $max_posts,      // Numero di post per pagina
    'orderby'        => 'date',          // Ordina per data
    'order'          => 'DESC',          // Ordinamento decrescente
    'paged'          => $paged,         // Pagina corrente
);

// Aggiungi il parametro di ricerca, se presente
if ($main_search_query) {
    $args['s'] = $main_search_query;
}

// Esegui la query
$the_query = new WP_Query($args);
$prefix = "_dci_icad_";
?>

<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post();
        // Carica il template per ogni post
        get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card');
    endwhile;
    wp_reset_postdata(); ?>

    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php 
            // Verifica se ci sono più di una pagina
            if ($the_query->max_num_pages > 1) {
                // Usa la funzione standard di WordPress per la paginazione
                the_posts_pagination(array(
                    'mid_size' => 2,
                    'prev_text' => '&laquo; Precedente',
                    'next_text' => 'Successivo &raquo;',
                ));
            }
            ?>

            kkkkkkkkkkkkkkkkkkkkkkkkkkkkk
        </nav>
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

