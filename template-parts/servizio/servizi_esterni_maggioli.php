<?php
// Funzione per ottenere i dati dal servizio web
function get_procedures_data($search_term = null)
{
    $url = dci_get_option('servizi_maggioli_url', 'servizi');
    $response = wp_remote_get($url);
    $total_services = 0; // Inizializza il contatore

    if (is_array($response) && !is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($data) {
            $in_evidenza_services = [];
            $other_services = [];

            foreach ($data as $procedure) {
                // Verifica se il termine di ricerca è presente nel nome del servizio
                if ($search_term && stripos($procedure['nome'], $search_term) === false) {
                    continue; // Ignora questo servizio se il termine di ricerca non è presente
                }

                $name = $procedure['nome'];
                $description = $procedure['descrizione_breve'];
                
                // Gestisci le categorie e gli URL delle categorie
                if (isset($procedure['categoria']) && isset($procedure['url_categoria'])) {
                    $categories_raw = $procedure['categoria'];
                    $urls_categoria_raw = $procedure['url_categoria'];

                    $categories = array_map('trim', explode(',', $categories_raw));
                    $urls_categoria = array_map('trim', explode(',', $urls_categoria_raw));

                    $num_categories = count($categories);
                    $num_urls = count($urls_categoria);
                    $urls_categoria = array_pad($urls_categoria, $num_categories, end($urls_categoria));

                    for ($i = 0; $i < $num_categories; $i++) {
                        $category = $categories[$i];
                        $category_url = $urls_categoria[$i];

                        $service = [
                            'name' => $name,
                            'description' => $description,
                            'category' => $category,
                            'category_url' => $category_url
                        ];

                        if (filter_var($procedure['in_evidenza'], FILTER_VALIDATE_BOOLEAN)) {
                            $in_evidenza_services[] = $service;
                        } else {
                            $other_services[] = $service;
                        }

                        $total_services++;
                    }
                }
            }

            // Output del totale
            echo "<h2>Servizi Aggiuntivi ( $total_services )</h2>";

            // Output dei servizi in evidenza
            echo "<h4>Servizi in Evidenza</h4>";
            output_services($in_evidenza_services);

            // Output degli altri servizi
            echo "<h4>Altri Servizi</h4>";
            output_services($other_services);
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
        $category_link = esc_url($service['category_url']);
?>
        <div class="cmp-card-latest-messages card-wrapper" data-bs-toggle="modal" data-bs-target="#">
            <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
                <span class="visually-hidden">Categoria:</span>
                <div class="card-header border-0 p-0">
                    <?php if ($service['category']) {
                        echo '<a href="'. $category_link .'" class="text-decoration-none"><div class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase">' . $service['category'] . '</div></a>';
                    } ?>
                </div>
                <div class="card-body p-0 my-2">
                    <h3 class="green-title-big t-primary mb-8">
                        <a class="text-decoration-none" href="<?php echo esc_url($service['url']); ?>" data-element="service-link"><?php echo $service['name']; ?></a>
                    </h3>
                    <p class="text-paragraph">
                        <?php echo $service['description']; ?>
                    </p>
                </div>
            </div>
        </div>
        <p></p>
<?php
    }
}

// Chiamata alla funzione per ottenere i dati e salvare il totale dei servizi
$search_term = isset($_GET['search']) ? $_GET['search'] : null;
$total_services_loaded = get_procedures_data($search_term);
echo "<p>Servizi aggiuntivi: $total_services_loaded</p>";
?>
