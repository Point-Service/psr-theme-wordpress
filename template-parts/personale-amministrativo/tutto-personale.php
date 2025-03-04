<?php
global $the_query, $load_posts, $load_card_type, $tax_query, $additional_filter, $filter_ids;


$query = $_GET['search'] ?? null;

switch ($post->post_name){
	case 'politici': $tipo_incarico = 'politico'; $descrizione = 'del personale'; break;
	case 'personale-amministrativo': $tipo_incarico = 'amministrativo'; $descrizione = 'del personale'; break;
	case '': $tipo_incarico = ''; $descrizione = 'del personale'; break;
	case 'personale-sanitario': $tipo_incarico = 'sanitario'; $descrizione = 'del personale'; break;
	case 'personale-socio-assistenziale': $tipo_incarico = 'socio-assistenziale'; $descrizione = 'del personale'; break;
	case 'altro': $tipo_incarico = 'altro'; $descrizione = 'del personale'; break;
}

$tax_query = array(
	array (
		'taxonomy' => 'tipi_incarico',
		'field' => 'slug',
		'terms' => $tipo_incarico
	));

$args_incarichi = array(
	'post_type' => 'incarico',
	'tax_query' => $tax_query,
    'posts_per_page' => -1
);

$incarichi = get_posts($args_incarichi);
$persone_ids = array();

foreach($incarichi as $incarico) {
	$persone = get_post_meta($incarico->ID, '_dci_incarico_persona');
	foreach($persone as $persona) {
		$persone_ids[] = $persona;
	}
}

$filter_ids = array_unique($persone_ids);

$search_value = isset($_GET['search']) ? $_GET['search'] : null;
$args = array(
	's'         => $search_value,
	'posts_per_page'    => -1,
	'post_type' => 'persona_pubblica',
	'post_status' => 'publish',
	'orderby'        => 'post_title',
	'order'          => 'ASC',
    'post__in' => empty($persone_ids) ? [0] : $filter_ids,
);

$the_query = new WP_Query( $args );
$persone = $the_query->posts;
?>




<div class="bg-grey-card py-5">
    <form role="search" id="search-form" method="get" class="search-form">
    <button type="submit" class="d-none"></button>
        <div class="container">
            <h2 class="title-xxlarge mb-4">
                Esplora il personale amministrativo
            </h2>
            <div>
                <div class="cmp-input-search">
                    <div class="form-group autocomplete-wrapper mb-0">
                        <div class="input-group">
                        <label for="autocomplete-two" class="visually-hidden"
                        >Cerca</label
                        >
                        <input
                        type="search"
                        class="autocomplete form-control"
                        placeholder="Cerca per parola chiave"
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
                        <span class="autocomplete-icon" aria-hidden="true"
                        ><svg
                            class="icon icon-sm icon-primary"
                            role="img"
                            aria-labelledby="autocomplete-label"
                        >
                            <use
                            href="#it-search"
                            ></use></svg></span>
                        </div>
                        <p
                        id="autocomplete-label"
                        class="u-grey-light text-paragraph-card mt-2 mb-4 mt-lg-3 mb-lg-40"
                        >
                        <?php echo $the_query->found_posts; ?> amministratori trovati in ordine alfabetico
                        </p>
                    </div>
                </div>
            </div>
            <div class="row g-4" id="load-more">
                <?php 
                    $load_card_type = 'personale-amministrativo';
                    foreach ($posts as $post) {get_template_part('template-parts/personale-amministrativo/cards-list');
                }?>
            </div>
            <?php get_template_part("template-parts/search/more-results"); ?>
        </div>
    </form>
</div>
