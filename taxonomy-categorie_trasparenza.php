<?php
global $title, $description, $data_element, $elemento;

get_header();
$obj = get_queried_object();

$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
$order_by = isset($_GET['order_by']) ? $_GET['order_by'] : 'date';

// Costruzione parametri ordine
if ($order_by === 'alpha') {
    // Ordina alfabeticamente per titolo
    $orderby = 'title';
    $order = 'ASC';
} else {
    // Ordina per data_pubblicazione (meta) se presente, altrimenti post_date
    // Qui impostiamo meta_query per prendere in considerazione meta, poi ordinare
    $orderby = array(
        'meta_value_num' => 'DESC',  // data_pubblicazione come numero (timestamp o formato YYYYMMDD)
        'date' => 'DESC'
    );
    $order = '';
}

$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type' => 'elemento_trasparenza',
    'tipi_cat_amm_trasp' => $obj->slug,
    'paged' => $paged,
);

// Se ordiniamo per titolo
if ($order_by === 'alpha') {
    $args['orderby'] = $orderby;
    $args['order'] = $order;
} else {
    // Ordine per data_pubblicazione o post_date
    // Prima recupero data_pubblicazione come meta key
    $args['meta_key'] = $prefix = '_dci_elemento_trasparenza_data_pubblicazione'; // o come si chiama il meta key corretto
    $args['orderby'] = array(
        'meta_value_num' => 'DESC',
        'date' => 'DESC',
    );
}

$the_query = new WP_Query($args);
?>

<main>
    <div class="container my-4">
        <form method="get" id="ordering-form" class="mb-4 d-flex align-items-center gap-3">

            <!-- Mantieni ricerca -->
            <input type="hidden" name="search" value="<?php echo esc_attr($query); ?>">

            <!-- Bottone Ordina Alfabeticamente -->
            <button type="submit" name="order_by" value="alpha" class="btn btn-outline-primary
                <?php echo ($order_by === 'alpha') ? 'active' : ''; ?>">
                Ordina A-Z
            </button>

            <!-- Bottone Ordina per Data -->
            <button type="submit" name="order_by" value="date" class="btn btn-outline-primary
                <?php echo ($order_by === 'date') ? 'active' : ''; ?>">
                Ordina per Data
            </button>

            <div class="ms-auto fw-semibold">
                Ordinamento: 
                <?php 
                    echo ($order_by === 'alpha') ? 'Alfabetico' : 'Data inserimento (o pubblicazione)'; 
                ?>
            </div>
        </form>

        <?php if ($the_query->have_posts()) : ?>
            <div class="row g-4" id="load-more">
                <?php 
                foreach ($the_query->posts as $elemento) {
                    $load_card_type = "elemento_trasparenza";
                    get_template_part("template-parts/amministrazione-trasparente/card");
                } 
                ?>
            </div>
        <?php else : ?>
            <div class="alert alert-info text-center" role="alert">
                Nessun post trovato.
            </div>
        <?php endif; ?>

        <div class="row my-4">
            <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
                <?php
                // Mantieni parametri GET come order_by e search nella paginazione
                echo dci_bootstrap_pagination([
                    'add_args' => [
                        'order_by' => $order_by,
                        'search' => $query
                    ]
                ]);
                ?>
            </nav>
        </div>
    </div>
</main>

<?php
get_footer();
?>
