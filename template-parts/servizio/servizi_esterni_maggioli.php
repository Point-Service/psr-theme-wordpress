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
                // Inizializza array per servizi in evidenza e non in evidenza
                $in_evidenza_services = [];
                $other_services = [];

                foreach ($data as $procedure) {
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
                echo "</a>ii";
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

    // Funzione per stampare i servizi
    function output_services($services)
    {
        foreach ($services as $service) {
            ?>
            <div class="cmp-card-latest-messages card-wrapper" data-bs-toggle="modal" data-bs-target="#">
                <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
                    <span class="visually-hidden">Categoria:</span>
                    <div class="card-header border-0 p-0">
                        <?php if ($service['category']) {
                            echo '<div class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase">' . $service['category'] . '</div>';
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
            </div><p></p>
        <?php
        }
    }

    // Aggiungi il codice HTML/PHP nel tuo template dove desideri visualizzare i dati
    ?>
    <div class="row g-4" id="load-more">
        <div class="procedures-list">
            <?php
                // Chiamata alla funzione per ottenere i dati e salvare il totale dei servizi
                $total_services_loaded = get_procedures_data();
                echo "<p>Servizi aggiuntivi: $total_services_loaded</p>";
            ?>
        </div>
    </div>
<?php } ?>
