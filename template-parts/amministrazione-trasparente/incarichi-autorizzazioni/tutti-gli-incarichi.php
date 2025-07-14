<?php
global $post;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = max(1, get_query_var('paged') ? get_query_var('paged') : (isset($_GET['paged']) ? intval($_GET['paged']) : 1));

$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',    // Ordina per data pubblicazione
    'order'          => 'DESC',    // Dalla più recente alla meno recente
    'paged'          => $paged,
);

if ($main_search_query) {
    $args['s'] = $main_search_query;
}

$the_query = new WP_Query($args);
$prefix = "_dci_icad_";

function dci_bootstrap_pagination() {
    global $the_query;

    $total_pages = $the_query->max_num_pages;
    if ($total_pages <= 1) {
        return ''; // Nessuna paginazione se solo una pagina
    }

    $current_page = max(1, get_query_var('paged') ? get_query_var('paged') : (isset($_GET['paged']) ? intval($_GET['paged']) : 1));

    // Base URL senza parametro paged
    $base_url = get_pagenum_link(1);
    $base_url = remove_query_arg('paged', $base_url);

    // Conserva altri parametri GET, eccetto paged
    $query_args = [];
    foreach ($_GET as $key => $value) {
        if ($key !== 'paged') {
            $query_args[$key] = sanitize_text_field($value);
        }
    }

    $paginate_links = paginate_links([
        'base'      => add_query_arg($query_args, $base_url) . '%_%',
        'format'    => (strpos($base_url, '?') === false ? '?' : '&') . 'paged=%#%',
        'current'   => $current_page,
        'total'     => $total_pages,
        'prev_text' => '&laquo;',
        'next_text' => '&raquo;',
        'type'      => 'array',
    ]);

    if (empty($paginate_links)) {
        return '';
    }

    $pagination_html = '<ul class="pagination justify-content-center">';
    foreach ($paginate_links as $link) {
        $active = strpos($link, 'current') !== false ? ' active' : '';
        $link = str_replace('page-numbers', 'page-link', $link);
        $pagination_html .= '<li class="page-item' . $active . '">' . $link . '</li>';
    }
    $pagination_html .= '</ul>';

    return $pagination_html;
}
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
