<?php
global $the_query, $load_posts, $load_card_type;

    $max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 70;
    $load_posts = 70;

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
?>


<div class="bg-grey-card py-5">
    <form role="search" id="search-form" method="get" class="search-form">
    <button type="submit" class="d-none"></button>
        <div class="container">
            <h2 class="title-xxlarge mb-4">
                Esplora tutti gli uffici
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
                        <?php echo $the_query->found_posts; ?> uffici trovati in ordine alfabetico
                        </p>
                    </div>
                </div>
            </div>
            <div class="row g-4" id="load-more">
                <?php 
                    $load_card_type = 'unita_organizzativa';
                    foreach ($posts as $post) {get_template_part('template-parts/uffici/cards-list');
                }?>
            </div>

    <?php

              
        $ck_commissario_osl = dci_get_option('ck_commissario_osl', 'Amministrazione');

	
		if ( 'true' !== $ck_commissario_osl ) :
                 ?>
                <!-- Blocco HTML da visualizzare -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
                        <div class="card shadow-sm rounded">
                            <div class="card-body">
                                <a class="text-decoration-none" href="/dataset">
                                    <h3 class="card-title t-primary title-xlarge">Dataset</h3>
                                </a>
                                <p class="text-paragraph mb-0">
                                    "Dataset" fornisce l'accesso ai dati aperti pubblicati dall'Autorità Nazionale Anticorruzione (ANAC) riguardanti i contratti pubblici in Italia. Questi dataset, disponibili in formato aperto, comprendono informazioni dettagliate sulle procedure di appalto, le stazioni appaltanti e altri elementi chiave relativi ai contratti pubblici, permettendo un'analisi approfondita e promuovendo la trasparenza nel settore degli appalti pubblici.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            endif;
            ?>


            
            <?php get_template_part("template-parts/search/more-results"); ?>
        </div>
    </form>
</div>
