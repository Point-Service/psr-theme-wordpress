<?php
/**
 * Plugin Name: Gestione Servizi
 * Description: Un plugin per gestire e confrontare i servizi.
 */

// Assumiamo che questo sia l'array delle categorie predefinite
$my_categories = array(
    'Categoria 1',
    'Categoria 2',
    'Categoria 3',
    // Aggiungi altre categorie secondo necessità
);

// Registra la tassonomia Categorie di Servizio
add_action('init', 'dci_register_taxonomy_categorie_servizio', -10);

function dci_register_taxonomy_categorie_servizio() {
    $labels = array(
        'name'              => _x('Categorie di Servizio', 'taxonomy general name', 'design_comuni_italia'),
        'singular_name'     => _x('Categoria di Servizio', 'taxonomy singular name', 'design_comuni_italia'),
        'search_items'      => __('Cerca Categoria di Servizio', 'design_comuni_italia'),
        'all_items'         => __('Tutti le Categorie di Servizio', 'design_comuni_italia'),
        'edit_item'         => __('Modifica la Categoria di Servizio', 'design_comuni_italia'),
        'update_item'       => __('Aggiorna la Categoria di Servizio', 'design_comuni_italia'),
        'add_new_item'      => __('Aggiungi una Categoria di Servizio', 'design_comuni_italia'),
        'new_item_name'     => __('Nuovo Tipo di Categoria di Servizio', 'design_comuni_italia'),
        'menu_name'         => __('Categorie di Servizio', 'design_comuni_italia'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'servizi-categoria'),
        'capabilities'      => array(
            'manage_terms'  => 'manage_categorie_servizio',
            'edit_terms'    => 'edit_categorie_servizio',
            'delete_terms'  => 'delete_categorie_servizio',
            'assign_terms'  => 'assign_categorie_servizio'
        ),
        'show_in_rest'          => true,
        'rest_base'             => 'categorie_servizio',
        'rest_controller_class' => 'WP_REST_Terms_Controller',
    );

    register_taxonomy('categorie_servizio', array('servizio'), $args);

    // Aggiungo il pulsante per eliminare tutte le categorie
    if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'categorie_servizio') {
        add_action('admin_footer', 'add_empty_categories_button');
    }
}


// Verifica se il parametro 'taxonomy' nell'URL è uguale a 'categorie_servizio'
 if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'categorie_servizio') {


                        
                    // Funzione per aggiungere il pulsante di eliminazione categorie
                    function add_empty_categories_button() {
                        ?>
                        <script type="text/javascript">
                            jQuery(document).ready(function($) {
                                var addTermForm = $('.form-field.term-parent-wrap').closest('form');
                                var deleteButtonHtml = '<div style="margin-top: 40px;"><button id="delete-all-categories" class="button">Cancella tutte le categorie di servizio</button></div>';
                                addTermForm.after(deleteButtonHtml);
                    
                            
                                $(document).on('click', '#delete-all-categories', function(e) {
                                    e.preventDefault();
                                    var confirmDelete = confirm("Sei sicuro di voler cancellare tutte le categorie di servizio?");
                                    if (confirmDelete) {
                                        $.ajax({
                                            url: ajaxurl,
                                            type: 'POST',
                                            data: {
                                                action: 'empty_all_categories',
                                                nonce: '<?php echo wp_create_nonce("empty-categories-nonce"); ?>'
                                            },
                                            success: function(response) {
                                                if (response === 'success') {
                                                    alert('Tutte le categorie di servizio sono state cancellate.');
                                                    location.reload();
                                                } else {
                                                    alert('Si è verificato un errore durante la cancellazione delle categorie di servizio.');
                                                }
                                            },
                                            error: function(xhr, status, error) {
                                                console.error(error);
                                                alert('Si è verificato un errore durante la richiesta AJAX.');
                                            }
                                        });
                                    }
                                });
                            });
                        </script>
                        <?php
                    }
                    
                    // Funzione per svuotare tutte le categorie di servizio
                    add_action('wp_ajax_empty_all_categories', 'empty_all_categories_callback');
                    function empty_all_categories_callback() {
                        check_ajax_referer('empty-categories-nonce', 'nonce');
                    
                        $terms = get_terms(array(
                            'taxonomy'   => 'categorie_servizio',
                            'hide_empty' => false,
                        ));
                    
                        if (!empty($terms) && !is_wp_error($terms)) {
                            foreach ($terms as $term) {
                                wp_delete_term($term->term_id, 'categorie_servizio');
                            }
                            echo 'success';
                        } else {
                            echo 'error';
                        }
                    
                        wp_die();
                    }
                    

                    // Funzione per confrontare e stampare i nomi delle categorie non trovate
                    function confronta_categorie($data, $my_categories) {
                        $categorie_remote = array(); // Array per tenere traccia delle categorie remote
                    
                        // Popolo l'array con tutte le categorie remote trovate
                        foreach ($data as $procedure) {
                            if (is_array($procedure['categoria'])) {
                                foreach ($procedure['categoria'] as $categoria) {
                                    $categorie_remote[] = $categoria;
                                }
                            } else {
                                $categorie_remote[] = $procedure['categoria'];
                            }
                        }
                    
                        // Trovo le categorie remote non presenti nell'array locale $my_categories
                        $categorie_non_trovate = array_diff($categorie_remote, $my_categories);
                    
                        $output = "<h4>Categorie non trovate:</h4>";
                        if (!empty($categorie_non_trovate)) {
                            $output .= "<ul>";
                            foreach ($categorie_non_trovate as $categoria) {
                                $output .= "<li>" . htmlspecialchars($categoria) . "</li>"; // Utilizzo di htmlspecialchars per evitare problemi di sicurezza
                            }
                            $output .= "</ul>";
                        } else {
                            $output .= "<p>Tutte le categorie sono state trovate.</p>";
                        }
                    
                        return $output;
                    }
                    
                    // Funzione principale per mostrare i servizi e il pulsante
                    function mostra_servizi() {
                        $url =  dci_get_option('servizi_maggioli_url', 'servizi'); // Assicurati che questa sia l'URL corretta
                        $response = wp_remote_get($url);
                        $total_services = 0;
                    
                        if (is_array($response) && !is_wp_error($response)) {
                            $body = wp_remote_retrieve_body($response);
                            $data = json_decode($body, true);
                    
                            if ($data) {
                                echo '<button id="confronta-categorie">Confronta categorie</button>';
                                echo '<div id="categorie-output"></div>';
                    
                                $in_evidenza_services = [];
                                $other_services = [];
                    
                                foreach ($data as $procedure) {
                                    $name = $procedure['nome'];
                                    $description = $procedure['descrizione_breve'];
                                    $category = is_array($procedure['categoria']) ? implode(', ', $procedure['categoria']) : $procedure['categoria'];
                                    $in_evidenza = filter_var($procedure['in_evidenza'], FILTER_VALIDATE_BOOLEAN);
                                    $url = $procedure['url'];
                    
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
                    
                                    $total_services++;
                                }
                    
                                echo "<h2>Servizi Aggiuntivi ( $total_services )</h2>";
                                echo "<h4>Servizi in Evidenza</h4>";
                                output_services($in_evidenza_services);
                                echo "<h4>Altri Servizi</h4>";
                                output_services($other_services);
                                
                            }
                        } else {
                            echo "Non riesco a leggere i servizi aggiuntivi.";
                        }
                    
                        return $total_services;
                    }
                    
                    // Funzione di output dei servizi (dummy, sostituisci con la tua implementazione)
                    function output_services($services) {
                        if (!empty($services)) {
                            echo "<ul>";
                            foreach ($services as $service) {
                                echo "<li><a href='{$service['url']}'>{$service['name']}</a>: {$service['description']} ({$service['category']})</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<p>Nessun servizio disponibile.</p>";
                        }
                    }
                    
                    // Funzione AJAX per confrontare le categorie
                    function ajax_confronta_categorie() {
                        $url = dci_get_option('servizi_maggioli_url', 'servizi'); // Assicurati che questa sia l'URL corretta
                        $response = wp_remote_get($url);
                    
                        if (is_array($response) && !is_wp_error($response)) {
                            $body = wp_remote_retrieve_body($response);
                            $data = json_decode($body, true);
                    
                            if ($data) {
                                echo confronta_categorie($data, $GLOBALS['my_categories']);
                            } else {
                                echo "Errore nella decodifica dei dati.";
                            }
                        } else {
                            echo "Non riesco a leggere i servizi aggiuntivi.";
                        }
                    
                        wp_die();
                    }
                    add_action('wp_ajax_confronta_categorie', 'ajax_confronta_categorie');
                    add_action('wp_ajax_nopriv_confronta_categorie', 'ajax_confronta_categorie');
                    
                    
                    // Aggiungi il seguente script JavaScript per gestire l'evento click sul pulsante
                    function aggiungi_script_confronta_categorie() {
                        ?>
                        <script type="text/javascript">
                        jQuery(document).ready(function($) {
                            $('#confronta-categorie').click(function() {
                                $.ajax({
                                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                    type: 'POST',
                                    data: {
                                        action: 'confronta_categorie'
                                    },
                                    success: function(response) {
                                        $('#categorie-output').html(response);
                                    },
                                    error: function() {
                                        $('#categorie-output').html('<p>Errore nella richiesta AJAX.</p>');
                                    }
                                });
                            });
                        });
                        </script>
                        <?php
                    }
                    add_action('wp_footer', 'aggiungi_script_confronta_categorie');
                    
                    // Mostra i servizi nella pagina desiderata (puoi chiamare questa funzione nel template corretto)
                    // Funzione per mostrare i servizi in un textarea scrollabile
function mostra_servizi_with_textarea() {
    $url = 'https://sportellotelematico.comune.roccalumera.me.it/rest/pnrr/procedures'; // Assicurati che questa sia l'URL corretta
    $response = wp_remote_get($url);
    $total_services = 0;

    if (is_wp_error($response)) {
        echo "Errore nella richiesta HTTP: " . $response->get_error_message();
        return $total_services;
    }

    $response_code = wp_remote_retrieve_response_code($response);
    if ($response_code !== 200) {
        echo "Errore nella risposta HTTP. Codice: " . $response_code;
        return $total_services;
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        echo "Errore nella decodifica dei dati JSON.";
        return $total_services;
    }

    // Inizializza l'array delle categorie remote
    $categorie_remote = array();

    // Recupero delle categorie remote
    foreach ($data as $procedure) {
        if (isset($procedure['categoria'])) {
            if (is_array($procedure['categoria'])) {
                foreach ($procedure['categoria'] as $categoria) {
                    $categorie_remote[] = $categoria;
                }
            } else {
                $categorie_remote[] = $procedure['categoria'];
            }
        }
    }

    // Trova le categorie remote non presenti nell'array locale $my_categories
    $categorie_non_presenti = array_diff($categorie_remote, $GLOBALS['my_categories']);

    // Output delle categorie non presenti
    if (!empty($categorie_non_presenti)) {
        echo "<h4>Categorie non presenti nell'array locale:</h4>";
        echo "<ul>";
        foreach ($categorie_non_presenti as $categoria) {
            echo "<li>" . htmlspecialchars($categoria) . "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>Tutte le categorie sono presenti nell'array locale.</p>";
    }

    // Output dei servizi in un textarea scrollabile
    ob_start();
    echo "<textarea id='services-textarea' style='width: 100%; height: 300px;' readonly>";
    echo "Servizi Aggiuntivi ( " . count($data) . " )\n\n";

    foreach ($data as $procedure) {
        $name = $procedure['nome'];
        $description = $procedure['descrizione_breve'];
        $category = is_array($procedure['categoria']) ? implode(', ', $procedure['categoria']) : $procedure['categoria'];

        echo "{$name}: {$description} ({$category})\n";
    }

    echo "</textarea>";
    $textarea_content = ob_get_clean();

    // Stampa il textarea
    echo $textarea_content;

    return $total_services;
}
                    


}
    
?>

