<!-- Aggiungi il modulo di ricerca -->
<div class="row g-4">
    <div class="search-container">
        <form method="GET">
            <input type="text" name="search" placeholder="Cerca nei servizi...">
            <button type="submit">Cerca</button>
        </form>
    </div>
</div>

<?php
// Modifica la funzione per ottenere i dati dal servizio web
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
                $category = is_array($procedure['categoria']) ? implode(', ', $procedure['categoria']) : $procedure['categoria'];
                $arguments = is_array($procedure['argomenti']) ? implode(', ', $procedure['argomenti']) : $procedure['argomenti'];
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

                // Incrementa il contatore ad ogni iterazione
                $total_services++;
            }

            // Output del totale
            echo "<h2>Servizi Aggiuntivi ( $total_services )</h2>";
            echo "<p></p>";

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

// Chiamata alla funzione per ottenere i dati e salvare il totale dei servizi
$search_term = isset($_GET['search']) ? $_GET['search'] : null;
$total_services_loaded = get_procedures_data($search_term);
echo "<p>Servizi aggiuntivi: $total_services_loaded</p>";
?>
