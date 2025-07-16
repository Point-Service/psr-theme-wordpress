<?php
// Evita redirect automatici di WordPress (utile per paginazioni custom)
remove_filter('template_redirect', 'redirect_canonical');

// Recupera parametri querystring
$max_posts = isset($_GET['max_posts']) ? intval($_GET['max_posts']) : 5;
$main_search_query = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : '';

// Usa ?page= invece di ?paged=
$paged = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;

// Costruisci argomenti WP_Query
$args = array(
    'post_type'      => 'incarichi_dip',
    'posts_per_page' => $max_posts,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'paged'          => $paged,
);

// Se c’è ricerca, aggiungi
if (!empty($main_search_query)) {
    $args['s'] = $main_search_query;
}

// Se è stato selezionato un anno, filtriamo per anno (data di pubblicazione)
if (!empty($selected_year)) {
    $args['date_query'] = array(
        array(
            'year' => $selected_year,
        ),
    );
}

$the_query = new WP_Query($args);

// Funzione per generare base URL paginazione senza parametri di pagina e mantenendo filtro/ricerca
function get_pagination_base_url() {
    // Prende URL corrente con tutti i parametri
    $url = remove_query_arg(array('page', 'paged'), esc_url_raw(add_query_arg(NULL, NULL)));

    // Rimuove eventuali trailing ? o &
    $url = rtrim($url, '?&');

    // Aggiunge la sintassi per pagina
    if (strpos($url, '?') === false) {
        $base = trailingslashit($url) . '?page=%#%';
    } else {
        $base = $url . '&page=%#%';
    }

    return $base;
}

$base = get_pagination_base_url();
?>

<!-- FORM FILTRO ANNO E RICERCA -->
<form method="get" class="mb-4 d-flex gap-2 align-items-center justify-content-center flex-wrap">
    <input type="text" name="search" value="<?php echo esc_attr($main_search_query); ?>" placeholder="Cerca..." class="form-control" style="max-width: 200px;">

    <select name="year" class="form-select" style="max-width: 150px;">
        <option value="">Tutti gli anni</option>
        <?php
        $current_year = date('Y');
        for ($y = $current_year; $y >= 2000; $y--) {
            $selected = ($y == $selected_year) ? 'selected' : '';
            echo "<option value=\"$y\" $selected>$y</option>";
        }
        ?>
    </select>

    <button type="submit" class="btn btn-primary">Filtra</button>
</form>

<?php if ($the_query->have_posts()) : ?>

    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
        <?php get_template_part('template-parts/amministrazione-trasparente/incarichi-autorizzazioni/card'); ?>
    <?php endwhile; ?>
    <?php wp_reset_postdata(); ?>

    <div class="row my-4">
        <nav class="pagination-wrapper justify-content-center col-12" aria-label="Navigazione pagine">
            <?php
            $pagination_links = paginate_links(array(
                'base'      => $base,
                'format'    => '',
                'current'   => $paged,
                'total'     => $the_query->max_num_pages,
                'prev_text' => __('&laquo; Precedente'),
                'next_text' => __('Successivo &raquo;'),
                'type'      => 'array',
            ));

            if ($pagination_links) : ?>
                <ul class="pagination justify-content-center">
                    <?php foreach ($pagination_links as $link) :
                        $active = strpos($link, 'current') !== false ? ' active' : '';
                        // Aggiungiamo classi Bootstrap 'page-link' e 'page-item'
                        $link = str_replace('<a ', '<a class="page-link" ', $link);
                        $link = str_replace('<span class="current">', '<span class="page-link active" aria-current="page">', $link);
                    ?>
                        <li class="page-item<?php echo $active; ?>"><?php echo $link; ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </nav>
    </div>

<?php else : ?>
    <div class="alert alert-info text-center" role="alert">
        Nessun incarico conferito trovato.
    </div>
<?php endif; ?>

<style>
.pagination .page-link {
    color: var(--bs-primary);
    background-color: transparent;
    border: 1px solid transparent;
    padding: 0.375rem 0.75rem;
    margin: 0 0.25rem;
    border-radius: 0.25rem;
    transition: background-color 0.15s ease-in-out;
}

.pagination .page-link:hover {
    background-color: var(--bs-primary);
    color: white;
    text-decoration: none;
}

.pagination .page-item.active .page-link {
    background-color: var(--bs-primary);
    border-color: var(--bs-primary);
    color: white;
    cursor: default;
    box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
}

.pagination .page-item.disabled .page-link {
    color: var(--bs-secondary);
    pointer-events: none;
    background-color: transparent;
    border-color: transparent;
}

form.mb-4 {
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}
</style>


