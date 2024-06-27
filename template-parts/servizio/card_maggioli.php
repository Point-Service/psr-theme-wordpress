<?php
// Funzione per ottenere i dati dal servizio web
function get_procedures_data($search_term = null)
{
    // Ottieni l'URL dei servizi
    $url = dci_get_option('servizi_maggioli_url', 'servizi');
    
    // Esegui la richiesta remota
    $response = wp_remote_get($url);
    $total_services = 0; // Inizializza il contatore dei servizi

    if (is_array($response) && !is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($data) {
            // Inizializza array per servizi in evidenza e non in evidenza
            $in_evidenza_services = [];
            $other_services = [];

            foreach ($data as $procedure) {
                // Verifica se il termine di ricerca è presente nel nome del servizio
                if ($search_term && stripos($procedure['nome'], $search_term) === false) {
                    continue; // Ignora questo servizio se il termine di ricerca non è presente
                }

                // Verifica se il segmento della categoria corrisponde al segmento desiderato
                $url_segments = explode('/', $procedure['url']);
                $category_segment = end($url_segments);

                // Sostituisci gli spazi con i trattini nel segmento della categoria
                $category_segment = str_replace(' ', '-', $category_segment);

                // Confronta il segmento della categoria con il segmento desiderato
                if ($category_segment !== 'cultura-e-tempo-libero') {
                    continue; // Ignora questo servizio se la categoria non corrisponde
                }

                $name = $procedure['nome'];
                $description = $procedure['descrizione_breve'];
                $category = is_array($procedure['categoria']) ? implode(', ', $procedure['categoria']) : $procedure['categoria'];
                $in_evidenza = filter_var($procedure['in_evidenza'], FILTER_VALIDATE_BOOLEAN);
                $url = $procedure['url'];

                // Aggiungi il servizio all'array corretto
                $service = [
                    'name' => $name,
                    'description' => $description,
                    'category' => $category,
                    'url' => $url
                ];

                if ($in_evidenza) {
                    $in_evidenza_services[] = $service;
                } else {
                    $other_services[] = $service;
                }

                // Incrementa il contatore dei servizi
                $total_services++;
            }

            // Output del totale dei servizi caricati
            echo "<h2>Servizi Aggiuntivi ($total_services)</h2>";

            // Output dei servizi in evidenza
            if (!empty($in_evidenza_services)) {
                echo "<h4>Servizi in Evidenza</h4>";
                output_services($in_evidenza_services);
            }

            // Output degli altri servizi
            if (!empty($other_services)) {
                echo "<h4>Altri Servizi</h4>";
                output_services($other_services);
            }
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
        ?>
        <div class="cmp-card-latest-messages card-wrapper" data-bs-toggle="modal" data-bs-target="#">
            <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
                <span class="visually-hidden">Categoria:</span>
                <div class="card-header border-0 p-0">
                    <?php 
                    if (!empty($service['category'])) {
                        echo '<div class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase">';
                        $first = true;
                        foreach ($service['category'] as $index => $category_name) {
                            $category_url = '/servizi-categoria/' . urlencode($category_name);
                            if (!$first) {
                                echo ', ';
                            }
                            echo '<a href="' . $category_url . '">' . $category_name . '</a>';
                            $first = false;
                        }
                        echo '</div>';
                    }
                    ?>
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

// Esegui la funzione per ottenere i dati e salvare il totale dei servizi
$search_term = isset($_GET['search']) ? $_GET['search'] : null;
$total_services_loaded = get_procedures_data($search_term);
echo "<p>Servizi aggiuntivi: $total_services_loaded</p>";
?>
