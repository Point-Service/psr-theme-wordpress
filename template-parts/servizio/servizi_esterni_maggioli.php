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
            // Inizializza array per servizi in evidenza e non in evidenza
            $in_evidenza_services = [];
            $other_services = [];

            foreach ($data as $procedure) {
                // Verifica se il termine di ricerca è presente nel nome del servizio
                if ($search_term && stripos($procedure['nome'], $search_term) === false) {
                    continue; // Ignora questo servizio se il termine di ricerca non è presente
                }

                $names = explode(',', $procedure['nome']); // Split dei nomi dei servizi
                $urls = explode(',', $procedure['url']); // Split degli URL

                // Assicurati che il numero di URL corrisponda al numero di nomi
                if (count($names) !== count($urls)) {
                    echo "Errore: Il numero di URL non corrisponde al numero di nomi dei servizi.";
                    continue; // Salta questa iterazione se non corrispondono
                }

                $description = $procedure['descrizione_breve'];
                $category = is_array($procedure['categoria']) ? implode(', ', $procedure['categoria']) : $procedure['categoria'];
                $in_evidenza = filter_var($procedure['in_evidenza'], FILTER_VALIDATE_BOOLEAN);

                // Itera su nomi e URL per creare servizi individuali
                foreach ($names as $index => $name) {
                    $name = trim($name); // Rimuovi eventuali spazi vuoti attorno al nome
                    $url = trim($urls[$index]); // Associa l'URL corrispondente

                    // Crea un nuovo servizio
                    $service = [
                        'name' => $name,
                        'description' => $description,
                        'category' => $category,
                        'url' => $url
                    ];

                    // Aggiungi il servizio all'array corretto
                    if ($in_evidenza) {
                        $in_evidenza_services[] = $service;
                    } else {
                        $other_services[] = $service;
                    }

                    // Incrementa il contatore per ogni servizio
                    $total_services++;
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
        // Genera il link alla categoria basato sul nome del servizio
        $category_slug = sanitize_title($service['category']);
        $category_link = "/servizi-categoria/$category_slug";
?>
        <div class="cmp-card-latest-messages card-wrapper" data-bs-toggle="modal" data-bs-target="#">
            <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
                <span class="visually-hidden">Categoria:</span>
                <div class="card-header border-0 p-0">
                    <?php if ($service['category']) {
                        echo '<a href="'. esc_url($category_link) .'" class="text-decoration-none"><div class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase">' . $service['category'] . '</a></div>';
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

