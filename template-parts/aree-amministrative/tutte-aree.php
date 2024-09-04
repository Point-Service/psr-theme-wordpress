<?php
global $the_query, $load_posts, $load_card_type;

    $max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 6;
    $load_posts = 9;

    $query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;
    $args = array(
        's' => $query,
        'posts_per_page' => $max_posts,
        'post_type'      => 'unita_organizzativa',
        'orderby'        => 'post_title',
        'order'          => 'ASC'
     );
     $the_query = new WP_Query($args);

     $posts = $the_query->posts;

     $posts = array_filter($posts, function($post, $key) {
        $tipo = get_the_terms($post, 'tipi_unita_organizzativa')[0];

        return $tipo->slug === "area";
    }, ARRAY_FILTER_USE_BOTH);
?>


<div class="bg-grey-card py-5">
    <form role="search" id="search-form" method="get" class="search-form">
    <button type="submit" class="d-none"></button>
        <div class="container">
            <h2 class="title-xxlarge mb-4">
                Esplora le aree amministrative
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
                        <?php echo count($posts); ?> aree amministrative trovate in ordine alfabetico
                        </p>
                    </div>
                </div>
            </div>
            <div class="row g-4" id="load-more">
                <?php 
                    $load_card_type = 'aree-amministrative';
                    foreach ($posts as $post) {get_template_part('template-parts/aree-amministrative/cards-list');
                }?>
            </div>
            <?php get_template_part("template-parts/search/more-results"); ?>
        </div>
    </form>
</div>
<?php
    // Per selezionare i contenuti in evidenza tramite flag
    // $post_types = dci_get_post_types_grouped('amministrazione');
    // $posts_evidenza = dci_get_highlighted_posts( $post_types, 3);

    //Per selezionare i contenuti in evidenza tramite configurazione
    $posts_evidenza = dci_get_option('notizia_evidenziata','amministrazione');

    if (is_array($posts_evidenza) && count($posts_evidenza)) {
?>

<div class="bg-grey-card py-5">
    <div class="container">
        <h2 class="title-xxlarge mb-4">
        In evidenza
        </h2>
        <div class="row g-4">
            <?php foreach ($posts_evidenza as $post_id) { 
                $post = get_post($post_id);
            ?>
            <div class="col-md-6 col-lg-4">
                <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
                    <div class="card shadow-sm rounded">
                        <div class="card-body">
                            <a class="text-decoration-none" href="<?php echo get_permalink($post->ID); ?>">
                                <h3 class="card-title t-primary">
                                    <?php echo $post->post_title; ?>
                                </h3>
                            </a>
                            <p class="text-paragraph mb-0">
                                <?php echo dci_get_meta('descrizione_breve'); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>
</div>
<?php } ?>
