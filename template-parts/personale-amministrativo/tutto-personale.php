<?php
global $the_query, $load_posts, $load_card_type;

    $max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 6;
    $load_posts = 6;

    $query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
    $args = array(
        's' => $query,
        'posts_per_page' => $max_posts,
        'post_type'      => 'persona_pubblica',
        'orderby'        => 'post_title',
        'order'          => 'ASC'        
     );
     $the_query = new WP_Query($args);
     $posts = $the_query->posts;

if ($the_query->have_posts()) {
    while ($the_query->have_posts()) {
        $the_query->the_post();
        
        // Ottieni l'ID del post corrente
        $post_id = get_the_ID();

        // Ottieni le tassonomie associate al post
        $taxonomies = get_object_incarico('persona_pubblica');  // Ottieni tutte le tassonomie per il post type

        echo "<h3>Tassonomie per il post: " . get_the_title() . " (ID: $post_id)</h3>";
        echo "<pre>";

        // Itera su ogni tassonomia
        foreach ($taxonomies as $taxonomy) {
            $terms = get_the_terms($post_id, $taxonomy);  // Ottieni i termini per questa tassonomia

            if (!empty($terms) && !is_wp_error($terms)) {
                echo "<strong>" . $taxonomy . ":</strong> ";
                foreach ($terms as $term) {
                    echo $term->name . " ";  // Stampa il nome del termine
                }
                echo "<br>";
            }
        }
        echo "</pre>";
    }
    wp_reset_postdata();
} else {
    echo "Nessun risultato trovato.";
}
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
                        <?php echo $total_posts; ?> amministratori trovati in ordine alfabetico
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
