     <section id="servizi">
            <div class="pb-40 pt-40 pt-lg-80">
                <div class="container">
                    <div class="row row-title">
                        <div class="col-12">
                            <h3 class="u-grey-light border-bottom border-semi-dark pb-2 pb-lg-3 title-large-semi-bold">
                                Servizi
                            </h3>
                        </div>
                    </div>
                    <div class="row mx-0">
                        
    <?php

    // Ottieni l'URL della pagina corrente
    $current_url = home_url(add_query_arg(array(), $wp->request));

    // Estrai il segmento desiderato dall'URL
    $segments = explode('/', $current_url);
    $argomento_segment = end($segments); // Prendi l'ultimo segmento dell'URL

    // Funzione per ottenere i dati dal servizio web
    function get_procedures_data($search_term = null, $argomento_segment = null)
    {
        $url = dci_get_option('servizi_maggioli_url', 'servizi');
        $response = wp_remote_get($url);
        $total_services = 0; // Inizializza il contatore

        if (is_array($response) && !is_wp_error($response)) {
            $body = wp_remote_retrieve_body($response);
            $data = json_decode($body, true);

            if ($data) {
                // Inizializza array per servizi filtrati
                $filtered_services = [];

                foreach ($data as $procedure) {
                    // Verifica se il termine di ricerca è presente nel nome del servizio
                    if ($search_term && stripos($procedure['nome'], $search_term) === false) {
                        continue; // Ignora questo servizio se il termine di ricerca non è presente
                    }

                    $name = $procedure['nome'];
                    $description = $procedure['descrizione_breve'];
                    $category = is_array($procedure['argomenti']) ? implode(', ', $procedure['argomenti']) : $procedure['argomenti'];
                    $url = $procedure['url'];

                  
                    // Stampa il titolo e la categoria per debug
                    echo 'TITOLO  : ' . strtolower($argomento_segment);
                   // echo '<br>';
                   // echo strtolower($category);
                   // echo '<br>';
                    
                    
                    // Verifica se la categoria contiene il segmento dell'URL, confrontando in modo case-insensitive
                        if ($argomento_segment && mb_stripos(mb_strtolower($category), mb_strtolower($argomento_segment)) === false) {
                            continue; // Ignora questo servizio se la categoria non contiene il segmento dell'URL
                        }
                      

                    // Aggiungi il servizio all'array filtrato
                    $service = [
                        'name' => $name,
                        'description' => $description,
                        'category' => $category,
                        'url' => $url
                    ];

                    $filtered_services[] = $service;
                    // Incrementa il contatore ad ogni iterazione
                    $total_services++;
                }

                // Output dei servizi filtrati
                echo "<h4>Servizi trovati nella categoria: $title)</h4>";
                output_services($filtered_services);
            }
        } else {
            echo "Non riesco a leggere i servizi aggiuntivi.";
        }

        // Restituisci il totale dei servizi caricati
        return $total_services;
    }

    // Funzione per stampare i servizi
    function output_services($services)
    {
        foreach ($services as $service) {
            // Genera il link alla categoria basato sul nome del servizio
            $category_slug = sanitize_title($service['category']);
            $category_link = "/servizi-categoria/$category_slug";
?>
   
                        <div class="card-wrapper px-0 card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
                        <?php if ($service['category']) {
                            echo '<a href="'. esc_url($category_link) .'" class="text-decoration-none"><div class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase">' . $service['category'] . '</a></div>';
                        } ?>
                            </div> <div class="card card-teaser card-teaser-image card-flex no-after rounded shadow-sm border border-light mb-0">
                                    <div class="card-image-wrapper with-read-more">
                                        <div class="card-body p-3">
                                            <div class="category-top">
                                                <a class="title-xsmall-semi-bold fw-semibold text-decoration-none" href="<?php echo get_term_link($categoria_servizio->term_id); ?>"><?php echo $categoria_servizio->name; ?></a>
                                            </div>
                                                <h4 class="card-title text-paragraph-medium u-grey-light">
                                                   <a class="text-decoration-none" href="<?php echo esc_url($service['url']); ?>" data-element="service-link"><?php echo $service['name']; ?></a>
                                                </h4>
                                            <p class="text-paragraph-card u-grey-light m-0"> <?php echo $service['description']; ?></p>
                                        </div>
                                    </div>
                                </div>                       
                 
            <p></p>
<?php
        }
    }

    // Chiamata alla funzione per ottenere i dati e salvare il totale dei servizi
    $search_term = isset($_GET['search']) ? $_GET['search'] : null;
    $total_services_loaded = get_procedures_data($search_term, $category_segment);
?>


 
            <div class="row mt-4">
                <div class="col-12 col-lg-3 offset-lg-9">
                    <button 
                        type="button" 
                        class="btn btn-primary text-button w-100"
                        onclick="location.href='<?php echo dci_get_template_page_url('page-templates/servizi.php'); ?>'"
                    >
                        Tutti i servizi
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
