<?php
global $post, $load_posts, $load_card_type;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 2;
$load_posts = 2;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Prima carichiamo tutti i post (senza paginazione)
$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => -1,  // Recupera tutti i post per ordinare manualmente
    'post_status'    => 'publish',
    's'              => $main_search_query,
);

$the_query = new WP_Query($args);
$posts = $the_query->posts;

// Ora ordiniamo manualmente in base al campo personalizzato (esempio con dci_get_data_pubblicazione_ts)
$prefix = "_dci_icad_";
usort($posts, function($a, $b) use ($prefix) {
    return dci_get_data_pubblicazione_ts("data", $prefix, $b->ID) - dci_get_data_pubblicazione_ts("data", $prefix, $a->ID);
});

// Calcoliamo offset e slice per la paginazione manuale
$offset = ($paged - 1) * $max_posts;
$paged_posts = array_slice($posts, $offset, $max_posts);

// Creiamo un nuovo WP_Query fittizio per la paginazione
$the_query->posts = $paged_posts;
$the_query->post_count = count($paged_posts);
$the_query->found_posts = count($posts);
$the_query->max_num_pages = ceil(count($posts) / $max_posts);


?>

<?php if ($the_query->have_posts()) : ?>
    
    <?php while ($the_query->have_posts()) : $the_query->the_post();
        get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card');
    endwhile;
    wp_reset_postdata(); ?>
    
    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php echo dci_bootstrap_pagination(); ?>
        </nav>
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>
<?php get_template_part("template-parts/search/more-results"); ?>
