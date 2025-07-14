<?php
global $post;
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',    // Ordina per data pubblicazione
    'order'          => 'DESC',    // Dalla più recente alla meno recente
    'paged'          => $paged,
);

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
            <?php echo dci_bootstrap_pagination(); ?>
        </nav>
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>
