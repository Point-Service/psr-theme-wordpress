<?php
global $posts, $the_query, $load_posts, $servizio, $load_card_type, $should_have_grey_background;
$max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 9;
$load_posts = 12;
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
                
                <?php if (is_array($servizi_evidenza) && count($servizi_evidenza)) { ?>
                       <div class="col-12">
                         <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
                            <div class="link-list-wrap">
                                <h2 class="title-xsmall-semi-bold">
                                    <span>SERVIZI IN EVIDENZA</span>
                                </h2>
                                <ul class="link-list t-primary">
                                    <?php foreach ($servizi_evidenza as $servizio_id) { 
                                        $post = get_post($servizio_id);    
                                    ?>
                                    <li class="mb-3 mt-3">
                                        <a class="list-item ps-0 title-medium underline" href="<?php echo get_permalink($post->ID); ?>">
                                            <span><?php echo $post->post_title; ?></span>
                                        </a>
                                    </li>
                                    <?php } ?>
                                </ul>
                            </div>
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
                            <strong><?php echo $the_query->found_posts; ?> </strong>servizi trovati in ordine alfabetico
                        </p>
                    </div>
                    
                    <div class="row g-4" id="load-more">
                        <?php foreach ($posts as $servizio) {
                            $load_card_type = "servizio";
                            ?>
                            <div class="col-12 col-lg-6">
                            <?php
                            get_template_part("template-parts/servizio/card");
                            ?>
                            </div>
                            <?php
                        } ?>

                        <?php
                        // Recupera il valore dell'opzione
                        $servizi_maggioli_url = dci_get_option('servizi_maggioli_url', 'servizi');
                        
                        if (strlen($servizi_maggioli_url) > 1) {
                        
                            // Funzione per ottenere i dati dal servizio web
                            function get_procedures_data()
                            {
                                $url =  dci_get_option('servizi_maggioli_url', 'servizi');
                                $response = wp_remote_get($url);
                                $total_services = 0; // Inizializza il contatore
                        
                                if (is_array($response) && !is_wp_error($response)) {
                                    $body = wp_remote_retrieve_body($response);
                                    $data = json_decode($body, true);
                        
                                    if ($data) {
                                        foreach ($data as $procedure) {
                                            $name = $procedure['nome'];
                                            $description = $procedure['descrizione_breve'];
                                            $category = is_array($procedure['categoria']) ? implode(', ', $procedure['categoria']) : $procedure['categoria'];
                                            $arguments = is_array($procedure['argomenti']) ? implode(', ', $procedure['argomenti']) : $procedure['argomenti'];
                                            $url = $procedure['url'];
                        
                                            // Incrementa il contatore ad ogni iterazione
                                            $total_services++;
                        
                                            // Output dei dati nel template con la stessa struttura grafica
                                            ?>
                                            <div class="cmp-card-latest-messages card-wrapper" data-bs-toggle="modal" data-bs-target="#">
                                                <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
                                                    <span class="visually-hidden">Categoria:</span>
                                                    <div class="card-header border-0 p-0">
                                                        <?php if ($category) {
                                                            echo '<a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase" href="#">' . $category . '</a>';
                                                        } ?>
                                                    </div>
                                                    <div class="card-body p-0 my-2">
                                                        <h3 class="green-title-big t-primary mb-8">
                                                            <a class="text-decoration-none" href="<?php echo esc_url($url); ?>" data-element="service-link"><?php echo $name; ?></a>
                                                        </h3>
                                                        <p class="text-paragraph">
                                                            <?php echo $description; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div><p></p>
                                    <?php
                                        }
                                    }
                                } else {
                                    echo "Non riesco a leggere i servizi aggiuntivi.";
                                }
                        
                                // Restituisci il totale dei servizi caricati
                                return $total_services;
                            }
                        
                            // Aggiungi il codice HTML/PHP nel tuo template dove desideri visualizzare i dati
                            ?>
                            <div class="row g-4" id="load-more">
                                <div class="procedures-list">
                                <div class="col-12">
                                    <h2 class="title-xxlarge mb-4 mt-5 mb-lg-10">
                                    </a> Servizi Aggiuntivi
                                    </h2>
                                </div>
                                    
                                    <?php
                                    // Chiamata alla funzione per ottenere i dati e salvare il totale dei servizi
                                    $total_services_loaded = get_procedures_data();
                                    echo "<p>Servizi aggiuntivi: $total_services_loaded</p>";
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                                    
                    <?php get_template_part("template-parts/search/more-results"); ?>
                </div>
            </div>
        </div>
    </form>
</div>


