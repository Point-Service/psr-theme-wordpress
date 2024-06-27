<?php
    global $title, $description, $with_shadow, $data_element;
    if (!$title) $title = get_the_title();
    if (!$description && $post) $description = dci_get_meta('descrizione','_dci_page_',$post->ID);
    if (!$categorie && $post) $categorie = dci_get_meta('categorie_servizio','_dci_page_',$post->ID);


?>

 <?php if (is_array($categorie) && count($categorie)) {
                        $count = 1;
                        foreach ($categorie as $categoria) {
                            echo $count == 1 ? '' : ' - ';
                            echo '<a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase" href="'.get_term_link($categoria->term_id).'">';
                            echo $categoria->name ;                                    
                            echo '</a>';
                            ++$count;
                        }
                    }                        
                    ?>


<?php

// Ottieni l'URL della pagina corrente
$current_url = home_url(add_query_arg(array(), $wp->request));

// Estrai il segmento desiderato dall'URL
$segments = explode('/', $current_url);
$category_segment = end($segments); // Prendi l'ultimo segmento dell'URL


// Funzione per ottenere i dati dal servizio web
function get_procedures_data($search_term = null, $title = null)
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
                $category = is_array($procedure['categoria']) ? implode(', ', $procedure['categoria']) : $procedure['categoria'];
                $url = $procedure['url'];

                // Verifica se la categoria contiene il segmento dell'URL
                if ($title && stripos($category, $title) === false) {
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
            echo "<h4>Servizi trovati nella categoria: $category_segment</h4>";
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
$total_services_loaded = get_procedures_data($search_term, $category_segment);
echo "<p>Servizi aggiuntivi: $total_services_loaded</p>";
?>




