<?php
global $argomento;

$posts = dci_get_grouped_posts_by_term('servizi', 'argomenti', $argomento->slug, 3);
$argomento_name = $argomento->name;
?>

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
                          <div class="card-wrapper px-0 card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">    
                    
                        <?php
                        // Funzione per ottenere i dati dal servizio web
                        function get_procedures_data($search_term = null, $argomento_name = null)
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
                                        $argomento = is_array($procedure['argomenti']) ? implode(', ', $procedure['argomenti']) : $procedure['argomenti'];
                                        $category = is_array($procedure['categoria']) ? implode(', ', $procedure['categoria']) : $procedure['categoria'];
                                        $url = $procedure['url'];
        
                                        // Verifica se l'argomento contiene il segmento dell'URL, confrontando in modo case-insensitive
                                        if ($argomento_name && mb_stripos(mb_strtolower($argomento), mb_strtolower($argomento_name)) === false) {
                                            continue;
                                        }
        
                                        // Aggiungi l'argomento all'array filtrato
                                        $filtered_services[] = [
                                            'name' => $name,
                                            'description' => $description,
                                            'category' => $category,
                                            'url' => $url
                                        ];
        
                                        // Incrementa il contatore ad ogni iterazione
                                        $total_services++;
                                    }
        
                                    // Output dei servizi filtrati
                                   // echo "<h4>Argomento: $argomento_name</h4>";
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
                  
                                    <div class="card card-teaser card-teaser-image card-flex no-after rounded shadow-sm border border-light mb-0">
                                        <div class="card-image-wrapper with-read-more">    
                                            <div class="card-body p-3">
                                                <div class="argomento-top">
                                                    <a class="title-xsmall-semi-bold fw-semibold text-decoration-none" href="<?= esc_url($service['$category_link']); ?>"><?= $service['category']; ?></a>
                                                </div>
                                                <h4 class="card-title text-paragraph-medium u-grey-light">
                                                    <a class="text-decoration-none" href="<?= esc_url($service['url']); ?>" data-element="service-link"><?= $service['name']; ?></a>
                                                </h4>
                                                <p class="text-paragraph-card u-grey-light m-0"><?= $service['description']; ?></p>
                                            </div>
                                        </div>
                                    </div>
  
                        <?php
                            }
                        }
        
                        // Chiamata alla funzione per ottenere i dati e salvare il totale dei servizi
                        $search_term = isset($_GET['search']) ? $_GET['search'] : null;
                        $total_services_loaded = get_procedures_data($search_term, $argomento_name);
        
                        // Verifica se ci sono errori
                        if ($total_services_loaded === 0) {
                            echo "<p>Nessun servizio trovato.</p>";
                        }
                        ?>
        
                        </div>
                          </div>
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
