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

                $name = $procedure['nome'];
                $description = $procedure['descrizione_breve'];
                
                // Controlla se 'categoria' è un array o una stringa e separa correttamente
                $categories = is_array($procedure['categoria']) ? $procedure['categoria'] : array_map('trim', explode(', ', $procedure['categoria']));
                
                // Separa gli URL se necessario (assumendo che gli URL siano distinti per le categorie)
                $urls = is_array($procedure['url']) ? $procedure['url'] : array_map('trim', explode(', ', $procedure['url']));

                // Aggiungi il servizio all'array corretto con categorie e URL separati
                $service = [
                    'name' => $name,
                    'description' => $description,
                    'categories' => $categories,
                    'urls' => $urls
                ];

                if ($in_evidenza) {
                    $in_evidenza_services[] = $service;
                } else {
                    $other_services[] = $service;
                }

                // Incrementa il contatore ad ogni iterazione
                $total_services++;
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
        // Output del servizio con categorie e URL separati
        echo '<div class="cmp-card-latest-messages card-wrapper" data-bs-toggle="modal" data-bs-target="#">';
        echo '<div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">';
        echo '<span class="visually-hidden">Categoria:</span>';
        echo '<div class="card-header border-0 p-0">';

        // Iterare su ciascuna categoria e creare un link separato con l'URL corrispondente
        foreach ($service['categories'] as $index => $category) {
            // Crea lo slug per la categoria
            $category_slug = sanitize_title($category);

            // Associa l'URL alla categoria correttamente, usando l'indice o il primo URL come fallback
            $url = isset($service['urls'][$index]) ? $service['urls'][$index] : (count($service['urls']) > 0 ? $service['urls'][0] : '#');

            // Genera il link per ciascuna categoria
            $category_link = "/servizi-categoria/$category_slug";
            echo '<a href="' . esc_url($category_link) . '" class="text-decoration-none">';
            echo '<div class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase">' . esc_html($category) . '</div>';
            echo '</a>';
        }

        echo '</div>';
        echo '<div class="card-body p-0 my-2">';
        echo '<h3 class="green-title-big t-primary mb-8">';
        // Usa solo il primo URL come link al nome del servizio, se esiste una lista di URL
        echo '<a class="text-decoration-none" href="' . esc_url($service['urls'][0]) . '" data-element="service-link">' . esc_html($service['name']) . '</a>';
        echo '</h3>';
        echo '<p class="text-paragraph">' . esc_html($service['description']) . '</p>';
        echo '</div>';
        echo '</div>';
        echo '</div>';
        echo '<p></p>';
    }
}

// Chiamata alla funzione per ottenere i dati e salvare il totale dei servizi
$search_term = isset($_GET['search']) ? $_GET['search'] : null;
$total_services_loaded = get_procedures_data($search_term);
echo "<p>Servizi aggiuntivi: $total_services_loaded</p>";
?>


