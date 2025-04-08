<?php
global $posts, $the_query, $load_posts, $servizio, $load_card_type, $should_have_grey_background;
$max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 9;
$load_posts = 30;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;

$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type'      => 'servizio',
    'orderby'        => 'post_title',
    'order'          => 'ASC'
);
$the_query = new WP_Query($args);
$posts = $the_query->posts;
// Per selezionare i contenuti in evidenza tramite flag
// $post_types = dci_get_post_types_grouped('servizi');
// $servizi_evidenza = dci_get_highlighted_posts( $post_types, 10);
//Per selezionare i contenuti in evidenza tramite configurazione
$servizi_evidenza = dci_get_option('servizi_evidenziati', 'servizi');
?> 
 <div id="tutti-servizi" class="<?= !($should_have_grey_background=(!$should_have_grey_background)) ? 'bg-grey-dsk':'' ?>">
    <form role="search" id="search-form" method="get" class="search-form">
        <button type="submit" class="d-none"></button>
        <div class="container">
            <div class="row">                
                <?php 

               if (is_array($servizi_evidenza) && count($servizi_evidenza) > 0) { ?>
                    <div class="col-12">
                        <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
                            <div class="link-list-wrap">
                                <h2 class="title-xsmall-semi-bold">
                                    <span><b>SERVIZI IN EVIDENZA</b></span>
                                </h2>
                                <ul class="link-list t-primary">
                                    <?php foreach ($servizi_evidenza as $servizio_id) { 
                                        $post = get_post($servizio_id);    
                                    ?>
                                    <li class="mb-3 mt-3">
                                        <a class="list-item ps-0 title-medium underline" href="<?php echo get_permalink($post->ID); ?>">
                                           <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 640 512"><!--!Font Awesome Free 6.7.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2025 Fonticons, Inc.--><path d="M54.2 202.9C123.2 136.7 216.8 96 320 96s196.8 40.7 265.8 106.9c12.8 12.2 33 11.8 45.2-.9s11.8-33-.9-45.2C549.7 79.5 440.4 32 320 32S90.3 79.5 9.8 156.7C-2.9 169-3.3 189.2 8.9 202s32.5 13.2 45.2 .9zM320 256c56.8 0 108.6 21.1 148.2 56c13.3 11.7 33.5 10.4 45.2-2.8s10.4-33.5-2.8-45.2C459.8 219.2 393 192 320 192s-139.8 27.2-190.5 72c-13.3 11.7-14.5 31.9-2.8 45.2s31.9 14.5 45.2 2.8c39.5-34.9 91.3-56 148.2-56zm64 160a64 64 0 1 0 -128 0 64 64 0 1 0 128 0z"/></svg>
                                            <span><?php echo $post->post_title; ?></span>             
                                        </a>
                                            <p class="text-paragraph">

                                            </p>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                <?php } ?>
                
                <div class="col-12">
                    <h2 class="title-xxlarge mb-4 mt-5 mb-lg-10">
                        Esplora tutti i servizi
                    </h2>
                </div>
                <div class="pt-lg-50 pb-lg-50">
                    <div class="cmp-input-search">
                        <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                            <div class="input-group">
                                <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                                <input type="search" class="autocomplete form-control" placeholder="Cerca una parola chiave" id="autocomplete-two" name="search" value="<?php echo $query; ?>" data-bs-autocomplete="[]" />
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="submit" id="button-3">
                                        Invio
                                    </button>
                                </div>
                                <span class="autocomplete-icon" aria-hidden="true"><svg class="icon icon-sm icon-primary" role="img" aria-labelledby="autocomplete-label">
                                        <use href="#it-search"></use>
                                    </svg></span>
                            </div>
                        </div>
                        <p id="autocomplete-label" class="mb-4">
                            <?php 
                               if (strlen(dci_get_option('servizi_maggioli_url', 'servizi')) < 5) { 
                                 if ($the_query->found_posts > 0) : ?>
                                <strong><?php echo $the_query->found_posts; ?></strong> servizi trovati in ordine alfabetico
                            <?php endif;  } ?>
                        </p>
                    </div>
                                   


                    <div class="row g-4" id="load-more">
                        <?php foreach ($posts as $servizio) {
                            $load_card_type = "servizio";
                            ?>
                                <div class="col-12 col-lg-6">
                                    <?php if (strlen(dci_get_option('servizi_maggioli_url', 'servizi')) < 5) { 
                                        get_template_part("template-parts/servizio/card");
                                         }
                                    ?>
                                </div>
                            <?php
                        } ?>
                      </div>
                       </a>

                        <?php     
                        if (strlen(dci_get_option('servizi_maggioli_url', 'servizi')) < 5) { ?>
                            <?php get_template_part("template-parts/search/more-results"); ?>
                        <?php } else { ?>
                             <?php get_template_part("template-parts/servizio/servizi_esterni_maggioli"); ?>
                        <?php } ?>
                            
            </div>
        </div>
    </form>
 </div>
