<?php 
/**
 * Archivio Tassonomia trasparenza
 */

global $title, $description, $data_element, $elemento, $sito_tematico_id, $siti_tematici, $tipo_personalizzato;

$obj = get_queried_object();

/* =========================
   REDIRECT TERMINI
========================= */
if ($obj instanceof WP_Term && isset($obj->taxonomy) && $obj->taxonomy === 'tipi_cat_amm_trasp') {
    $term_url = trim((string) get_term_meta($obj->term_id, 'term_url', true));
    $open_new_window = get_term_meta($obj->term_id, 'open_new_window', true);

    if ($term_url !== '') {
        $redirect_url = esc_url_raw($term_url);

        if (!empty($redirect_url)) {
            if (!empty($open_new_window)) {
                $fallback_url = home_url('/amministrazione-trasparente');
                ?>
                <!doctype html>
                <html <?php language_attributes(); ?>>
                <head>
                    <meta charset="<?php bloginfo('charset'); ?>">
                    <script>
                        window.addEventListener('load', function () {
                            window.open(<?php echo wp_json_encode($redirect_url); ?>, '_blank', 'noopener');
                            window.location.replace(<?php echo wp_json_encode($fallback_url); ?>);
                        });
                    </script>
                </head>
                <body></body>
                </html>
                <?php
                exit;
            }

            wp_redirect($redirect_url, 302);
            exit;
        }
    }
}

get_header();

/* =========================
   STILI (TUOI)
========================= */
dci_render_trasparenza_light_bg_style();

/* =========================
   PAGINAZIONE + QUERY FIX
========================= */

// pagina corrente
$paged = get_query_var('paged') ? (int)get_query_var('paged') : 1;

// parametri
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$order = isset($_GET['order_type']) ? $_GET['order_type'] : 'data_desc';

// query
$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type' => 'elemento_trasparenza',
    'paged' => $paged,
    'tax_query' => array(
        array(
            'taxonomy' => 'tipi_cat_amm_trasp',
            'field'    => 'slug',
            'terms'    => $obj->slug,
        ),
    ),
);

// ordinamento
if ($order === 'alfabetico_asc' || $order === 'alfabetico_desc') {
    $args['orderby'] = 'title';
    $args['order'] = ($order === 'alfabetico_desc') ? 'DESC' : 'ASC';
} else {
    $args['orderby'] = 'date';
    $args['order'] = ($order === 'data_desc') ? 'DESC' : 'ASC';
}

$the_query = new WP_Query($args);

// 🔴 FIX ULTIMA PAGINA (FONDAMENTALE)
if ($paged > $the_query->max_num_pages && $the_query->max_num_pages > 0) {

    $url = get_pagenum_link($the_query->max_num_pages);

    if ($query || $order) {
        $url = add_query_arg(array(
            'search' => $query,
            'order_type' => $order,
        ), $url);
    }

    wp_redirect($url);
    exit;
}

// 🔴 PAGINAZIONE CORRETTA (QUESTA RISOLVE IL BUG)
$base = str_replace(999999999, '%#%', esc_url(get_pagenum_link(999999999)));

$pagination_links = paginate_links(array(
    'base'      => $base,
    'format'    => '',
    'current'   => $paged,
    'total'     => $the_query->max_num_pages,
    'mid_size'  => 2,
    'type'      => 'array',
    'prev_text' => '«',
    'next_text' => '»',
    'add_args'  => array(
        'search' => $query,
        'order_type' => $order,
    ),
));

$siti_tematici = !empty(dci_get_option("siti_tematici", "trasparenza")) ? dci_get_option("siti_tematici", "trasparenza") : [];
?>

<main>
<?php
$title = $obj->name;
$description = $obj->description;
$data_element = 'data-element="page-name"';
get_template_part("template-parts/hero/hero");
?>

<div class="bg-grey-card dci-at-layout">

<?php 
/* =========================
   QUI NON TOCCO LA TUA LOGICA
========================= */
if ($obj->name == "Contratti Pubblici" && dci_get_option("ck_bandidigaratemplatepersonalizzato", "Trasparenza") !== 'false') { ?>

    <div class="container my-5">
        <div class="row g-4">
            <div class="col-12 col-lg-8">
                <?php get_template_part("template-parts/bandi-di-gara/tutti-bandi"); ?>
            </div>
            <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
        </div>
    </div>

<?php } else { ?>

<form method="get" id="search-form">

<div class="container">
<div class="row">

<div class="col-12 col-lg-8">

<!-- LOOP -->
<?php if ($the_query->have_posts()) : ?>

<div class="row g-4">
<?php foreach ($the_query->posts as $elemento) {
    $load_card_type = "elemento_trasparenza";
    get_template_part("template-parts/amministrazione-trasparente/card");
} ?>
</div>

<!-- PAGINAZIONE -->
<?php if (!empty($pagination_links)) : ?>
<div class="row my-4">
<nav class="pagination-wrapper col-12">
<ul class="pagination justify-content-center">

<?php foreach ($pagination_links as $link) : ?>
<li class="page-item <?php echo strpos($link, 'current') !== false ? 'active' : ''; ?>">
<?php echo str_replace('page-numbers', 'page-link', $link); ?>
</li>
<?php endforeach; ?>

</ul>
</nav>
</div>
<?php endif; ?>

<?php else : ?>
<p>Nessun contenuto disponibile</p>
<?php endif; ?>

</div>

<?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>

</div>
</div>

</form>

<?php } ?>

</div>
</main>

<?php
wp_reset_postdata();
get_footer();
?>

