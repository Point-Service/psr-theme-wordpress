<?php
global $post;

// Parametri GET
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 10;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;

// Costruzione della query
$args = array(
    'post_type'      => 'atto_concessione',
    'posts_per_page' => $max_posts,
    'orderby'        => 'meta_value_num',
    'order'          => 'DESC',
    'paged'          => $paged,
);

if (!empty($main_search_query)) {
    $args['s'] = $main_search_query;
}

$the_query = new WP_Query($args);
$prefix = "_dci_atto_concessione_";

// Costruzione URL per mantenere i parametri nella paginazione
$current_url = get_permalink();
$pagination_base_url = add_query_arg(array(
    'search' => $main_search_query,
    'max_posts' => $max_posts,
    'paged' => '%#%',
), $current_url);
?>

<!-- FORM FILTRO -->
<form method="get" class="mb-3 d-flex align-items-center gap-2 filtro-form">
    <label for="search" class="form-label mb-0 me-2">Cerca:</label>
    <input
        type="search"
        id="search"
        name="search"
        class="form-control me-3"
        placeholder="Cerca..."
        value="<?php echo esc_attr($main_search_query); ?>"
    >

    <label for="max-posts" class="form-label mb-0 me-2">Elementi:</label>
    <select id="max-posts" name="max_posts" class="form-select w-auto me-3">
        <?php foreach ([5, 10, 20, 50, 100] as $n) : ?>
            <option value="<?php echo $n; ?>" <?php selected($max_posts, $n); ?>><?php echo $n; ?></option>
        <?php endforeach; ?>
    </select>

    <div class="btn-wrapper">
        <button type="submit" class="btn btn-primary">Filtra</button>
    </div>
</form>

<!-- LOOP -->
<?php if ($the_query->have_posts()) : ?>
    <?php while ($the_query->have_posts()) : $the_query->the_post();
        get_template_part('template-parts/amministrazione-trasparente/atto-concessione/card');
    endwhile;
    wp_reset_postdata(); ?>
    
    <!-- PAGINAZIONE -->
    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php
            echo paginate_links(array(
                'base'      => $pagination_base_url,
                'format'    => '',
                'current'   => $paged,
                'total'     => $the_query->max_num_pages,
                'prev_text' => __('&laquo; Precedente'),
                'next_text' => __('Successivo &raquo;'),
                'type'      => 'list',
            ));
            ?>
        </nav>
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun atto di concessione trovato.
    </div>
<?php endif; ?>

<!-- STILE -->
<style>
form.filtro-form {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-start;
    gap: 1rem;
    padding: 1rem;
    background: #f8f9fa;
    border-radius: 0.5rem;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    margin-bottom: 2rem;
}

form.filtro-form label.form-label {
    font-weight: 600;
    color: #495057;
    margin-bottom: 0;
    align-self: center;
}

form.filtro-form input[type="search"],
form.filtro-form select.form-select {
    flex-grow: 1;
    min-width: 120px;
    max-width: 250px;
    border: 1.5px solid #ced4da;
    transition: border-color 0.3s ease;
}

form.filtro-form input[type="search"]:focus,
form.filtro-form select.form-select:focus {
    border-color: #0d6efd;
    box-shadow: 0 0 6px rgba(13, 110, 253, 0.3);
    outline: none;
}

.btn-wrapper {
    flex-shrink: 0;
    margin-left: auto;
    align-self: flex-start;
}

form.filtro-form button.btn-primary {
    padding: 0.45rem 1.5rem;
    font-weight: 600;
    border-radius: 0.4rem;
    height: 38px;
    cursor: pointer;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

form.filtro-form button.btn-primary:hover {
    background-color: #0b5ed7;
    box-shadow: 0 4px 8px rgba(11, 94, 215, 0.4);
}

.pagination-wrapper .pagination {
    display: flex;
    justify-content: center;
    list-style: none;
    padding-left: 0;
    gap: 0.5rem;
    flex-wrap: wrap;
}

.pagination-wrapper .page-numbers {
    display: inline-block;
    padding: 0.5rem 0.9rem;
    color: #0d6efd;
    border: 1.5px solid #0d6efd;
    border-radius: 0.4rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.25s ease;
    min-width: 40px;
    text-align: center;
}

.pagination-wrapper .page-numbers:hover {
    background-color: #0d6efd;
    color: white;
    box-shadow: 0 0 8px rgba(13, 110, 253, 0.5);
    text-decoration: none;
}

.pagination-wrapper .current {
    background-color: #0d6efd;
    border-color: #0d6efd;
    color: white;
    box-shadow: 0 0 12px rgba(13, 110, 253, 0.75);
    cursor: default;
}

@media (max-width: 576px) {
    form.filtro-form {
        flex-direction: column;
    }

    .btn-wrapper {
        margin-left: 0;
        width: 100%;
        margin-top: 0.5rem;
        align-self: stretch;
        display: flex;
        justify-content: flex-start;
    }

    form.filtro-form button.btn-primary {
        width: auto;
        height: 38px;
    }
}
</style>
