<?php
global $the_query, $load_posts, $load_card_type, $tax_query, $additional_filter, $filter_ids;

$query = $_GET['search'] ?? null;

// Identifica la categoria in base alla pagina corrente
switch ($post->post_name){
    case 'politici': $tipo_incarico = 'politico'; $descrizione = 'del personale'; break;
    case 'personale-amministrativo': $tipo_incarico = 'amministrativo'; $descrizione = 'del personale'; break;
    case 'personale-sanitario': $tipo_incarico = 'sanitario'; $descrizione = 'del personale'; break;
    case 'personale-socio-assistenziale': $tipo_incarico = 'socio-assistenziale'; $descrizione = 'del personale'; break;
    case 'altro': $tipo_incarico = 'altro'; $descrizione = 'del personale'; break;
}

// Filtra gli incarichi in base al tipo di incarico identificato
$tax_query = array(
    array (
        'taxonomy' => 'tipi_incarico',
        'field' => 'slug',
        'terms' => $tipo_incarico
    )
);

$args_incarichi = array(
    'post_type' => 'incarico',
    'tax_query' => $tax_query,
    'posts_per_page' => -1
);

$incarichi = get_posts($args_incarichi);
$persone_incarichi = array(); // Array per raccogliere ID persone e incarichi

// Recupera tutti gli incarichi per ogni persona
foreach($incarichi as $incarico) {
    $persone = get_post_meta($incarico->ID, '_dci_incarico_persona'); // Recupera le persone associate all'incarico
    foreach($persone as $persona) {
        $persone_incarichi[] = array('persona_id' => $persona, 'incarico_id' => $incarico->ID);
    }
}

// Estrai solo gli ID delle persone per la query principale (quindi la persona può comparire più volte se ha più incarichi)
$persone_ids = array_column($persone_incarichi, 'persona_id');

$search_value = isset($_GET['search']) ? $_GET['search'] : null;
$args = array(
    's'                 => $search_value,
    'posts_per_page'    => -1,
    'post_type'         => 'persona_pubblica',
    'post_status'       => 'publish',
    'orderby'           => 'post_title',
    'order'             => 'ASC',
    'post__in'          => empty($persone_ids) ? [0] : array_unique($persone_ids),
);

$the_query = new WP_Query($args);
$persone = $the_query->posts;
?>
<div class="bg-grey-card py-3">
    <form role="search" id="search-form" method="get" class="search-form">
        <button type="submit" class="d-none"></button>
        <div class="container">
            <h2 class="title-xxlarge mb-4 mt-5 mb-lg-10">
                Elenco <?= $descrizione ?>
            </h2>
            <div class="cmp-input-search">
                <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                    <div class="input-group">
                        <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                        <input
                            type="search"
                            class="autocomplete form-control"
                            placeholder="Cerca una parola chiave"
                            id="autocomplete-two"
                            name="search"
                            value="<?php echo $query; ?>"
                            data-bs-autocomplete="[]"
                        />
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit" id="button-3">
                                Invio
                            </button>
                        </div>
                        <span class="autocomplete-icon" aria-hidden="true">
                            <svg class="icon icon-sm icon-primary" role="img" aria-labelledby="autocomplete-label">
                                <use href="#it-search"></use>
                            </svg>
                        </span>
                    </div>
                </div>
                <p id="autocomplete-label" class="mb-4">
                    <strong><?php echo $the_query->found_posts; ?> </strong>risultati in ordine alfabetico
                </p>
            </div>
            <div class="row g-2" id="load-more">
                <?php
                    // Visualizza ogni incarico per ogni persona
                    foreach ($persone_incarichi as $assoc) {
                        // Trova la persona
                        foreach ($persone as $post) {
                            if ($post->ID == $assoc['persona_id']) {
                                setup_postdata($post);
                                get_template_part('template-parts/politici/cards-list');
                                break;
                            }
                        }
                    }
                    wp_reset_postdata();
                ?>
            </div>
            <?php
                $load_card_type = 'persona_pubblica';
                get_template_part("template-parts/search/more-results");
            ?>       
        </div>
    </form>
</div>



