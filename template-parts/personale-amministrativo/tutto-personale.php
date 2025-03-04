<?php
global $the_query, $load_posts, $load_card_type;

$max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 6;
$load_posts = 6;

$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;

// Query per recuperare i post da visualizzare (con il limite di $max_posts)
$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type'      => 'persona_pubblica',
    'orderby'        => 'post_title',
    'order'          => 'ASC',
);

$the_query = new WP_Query($args);
$posts = $the_query->posts;

// Variabile per il conteggio totale dei record senza il tipo "politico"
$total_records = 0;

if ($the_query->have_posts()) {
    while ($the_query->have_posts()) {
        $the_query->the_post();

        // Ottieni l'ID del post corrente
        $post_id = get_the_ID();

        // Recupera gli incarichi associati al post
        $incarichi = dci_get_meta('incarichi') ?? [];  // Recupera gli incarichi associati

        // Se ci sono incarichi
        if (!empty($incarichi)) {
            foreach ($incarichi as $incarico_id) {
                // Recupera il tipo di incarico associato
                $tipo_incarico_terms = get_the_terms($incarico_id, 'tipi_incarico');
                
                // Verifica se ci sono termini di tipo incarico
                if (!empty($tipo_incarico_terms) && !is_wp_error($tipo_incarico_terms)) {
                    $tipo_incarico = $tipo_incarico_terms[0]->name;  // Prende il nome del tipo di incarico

                    // Se il tipo di incarico è "politico", salta questo elemento
                    if (esc_html($tipo_incarico) === 'politico') {
                        continue;  // Salta questo ciclo del while e passa al prossimo post
                    }

                    // Incrementa il conteggio totale dei record senza "politico"
                    $total_records++;

                } else {
                     $total_records++; // Se non c'è incarico, conta comunque il post
                }
            }
        } else {
             $total_records++; // Se non ci sono incarichi, conta comunque il post
        }
    }

    wp_reset_postdata();
}

// Esegui una query separata per contare **tutti i post** senza il limite di max_posts e senza incarico "politico"
$count_all_posts_args = array(
    's' => $query,  // Mantieni la stessa ricerca
    'post_type' => 'persona_pubblica',
    'posts_per_page' => -1,  // Recupera tutti i post (senza limitazione)
    'post_status' => 'publish',
    'tax_query' => array(
        array(
            'taxonomy' => 'tipi_incarico',
            'field' => 'name',
            'terms' => 'politico',
            'operator' => 'NOT IN', // Escludi i post con incarico "politico"
        ),
    ),
);

$total_count_query = new WP_Query($count_all_posts_args);

// Conta i post senza incarico politico (totale)
$total_records_without_politico = $total_count_query->found_posts;

echo "<p><strong>Totale record senza incarico politico: </strong>" . $total_records_without_politico . "</p>";

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
