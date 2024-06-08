       <?php

// Recupera il valore dell'opzione
$servizi_maggioli_url = dci_get_option('servizi_maggioli_url', 'servizi');

if (strlen($servizi_maggioli_url) > 1) {

    // Funzione per ottenere i dati dal servizio web
    function get_procedures_data()
    {
        $url = dci_get_option('servizi_maggioli_url', 'servizi');
        $response = wp_remote_get($url);
        $total_services = 0; // Inizializza il contatore
        $highlighted_services = [];
        $regular_services = [];

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
                    $highlighted = isset($procedure['in_evidenza']) ? $procedure['in_evidenza'] : false;

                    // Incrementa il contatore ad ogni iterazione
                    $total_services++;

                    // Organizza i servizi in base al campo 'highlighted'
                    if ($highlighted) {
                        $highlighted_services[] = $procedure;
                    } else {
                        $regular_services[] = $procedure;
                    }
                }
            }
        } else {
            echo "Non riesco a leggere i servizi aggiuntivi.";
        }

        // Restituisci i servizi separati e il totale dei servizi caricati
        return [
            'highlighted_services' => $highlighted_services,
            'regular_services' => $regular_services,
            'total_services' => $total_services
        ];
    }

    // Aggiungi il codice HTML/PHP nel tuo template dove desideri visualizzare i dati
    ?>
    <div class="row g-4" id="load-more">
        <div class="procedures-list">
            <div class="col-12">
                <h2 class="title-xxlarge mb-4 mt-5 mb-lg-10">Servizi Aggiuntivi</h2>
            </div>

            <?php
            // Chiamata alla funzione per ottenere i dati e salvare il totale dei servizi
            $services_data = get_procedures_data();
            $highlighted_services = $services_data['highlighted_services'];
            $regular_services = $services_data['regular_services'];
            $total_services_loaded = $services_data['total_services'];

            // Visualizza i servizi in evidenza
            if (!empty($highlighted_services)) {
                echo '<div class="col-12"><h3 class="title-xlarge mb-4">Servizi in Evidenza</h3></div>';
                foreach ($highlighted_services as $procedure) {
                    display_service($procedure);
                }
            }

            // Visualizza i servizi regolari
            if (!empty($regular_services)) {
                echo '<div class="col-12"><h3 class="title-xlarge mb-4">Servizi</h3></div>';
                foreach ($regular_services as $procedure) {
                    display_service($procedure);
                }
            }

            echo "<p>Servizi aggiuntivi: $total_services_loaded</p>";
            ?>
        </div>
    </div>
<?php
}

function display_service($procedure)
{
    $name = $procedure['nome'];
    $description = $procedure['descrizione_breve'];
    $category = is_array($procedure['categoria']) ? implode(', ', $procedure['categoria']) : $procedure['categoria'];
    $url = $procedure['url'];
    ?>
    <div class="cmp-card-latest-messages card-wrapper" data-bs-toggle="modal" data-bs-target="#">
        <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
            <span class="visually-hidden">Categoria:</span>
            <div class="card-header border-0 p-0">
                <?php if ($category) {
                    echo '<div class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase">' . $category . '</div>';
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
?>
