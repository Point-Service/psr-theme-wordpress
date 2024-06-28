<?php
/**
 * Definisce la tassonomia Categorie di Servizio
 */
add_action( 'init', 'dci_register_taxonomy_categorie_servizio', -10 );

// Assumiamo che questo sia l'array delle categorie predefinite
$my_categories = array(
    'Categoria 1',
    'Categoria 2',
    'Categoria 3',
    // Aggiungi altre categorie secondo necessità
);

function dci_register_taxonomy_categorie_servizio() {
    $labels = array(
        'name'              => _x( 'Categorie di Servizio', 'taxonomy general name', 'design_comuni_italia' ),
        'singular_name'     => _x( 'Categoria di Servizio', 'taxonomy singular name', 'design_comuni_italia' ),
        'search_items'      => __( 'Cerca Categoria di Servizio', 'design_comuni_italia' ),
        'all_items'         => __( 'Tutti le Categorie di Servizio', 'design_comuni_italia' ),
        'edit_item'         => __( 'Modifica la Categoria di Servizio', 'design_comuni_italia' ),
        'update_item'       => __( 'Aggiorna la Categoria di Servizio', 'design_comuni_italia' ),
        'add_new_item'      => __( 'Aggiungi una Categoria di Servizio', 'design_comuni_italia' ),
        'new_item_name'     => __( 'Nuovo Tipo di Categoria di Servizio', 'design_comuni_italia' ),
        'menu_name'         => __( 'Categorie di Servizio', 'design_comuni_italia' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array( 'slug' => 'servizi-categoria' ),
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

    // Aggiungo il pulsante per eliminare tutte le Categporie   
    if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'categorie_servizio') {
        add_action( 'admin_footer', 'add_empty_categories_button' );
    }
}

function add_empty_categories_button() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Trova il form per aggiungere una nuova categoria di servizio
            var addTermForm = $('.form-field.term-parent-wrap').closest('form');

            // Crea un nuovo elemento per il pulsante "Cancella tutte le categorie di servizio"
            var deleteButtonHtml = '<div style="margin-top: 40px;"><button id="delete-all-categories" class="button">Cancella tutte le categorie di servizio</button></div>';

            // Aggiungi il pulsante sotto il form per aggiungere una nuova categoria di servizio
            addTermForm.after(deleteButtonHtml);

            // Gestisci il clic del pulsante
            $(document).on('click', '#delete-all-categories', function(e) {
                e.preventDefault();
                var confirmDelete = confirm("Sei sicuro di voler cancellare tutte le categorie di servizio?");
                if (confirmDelete) {
                    $.ajax({
                        url: ajaxurl,
                        type: 'POST',
                        data: {
                            action: 'empty_all_categories',
                            nonce: '<?php echo wp_create_nonce( "empty-categories-nonce" ); ?>'
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
add_action( 'wp_ajax_empty_all_categories', 'empty_all_categories_callback' );
function empty_all_categories_callback() {
    check_ajax_referer( 'empty-categories-nonce', 'nonce' );

    $terms = get_terms( array(
        'taxonomy'   => 'categorie_servizio',
        'hide_empty' => false,
    ) );

    if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
        foreach ( $terms as $term ) {
            wp_delete_term( $term->term_id, 'categorie_servizio' );
        }
        echo 'success';
    } else {
        echo 'error';
    }

    wp_die();
}

// Funzione per confrontare e stampare le categorie non trovate
function confronta_categorie($data, $my_categories) {
    $categorie_trovate = array();

    foreach ($data as $procedure) {
        if (is_array($procedure['categoria'])) {
            foreach ($procedure['categoria'] as $categoria) {
                $categorie_trovate[] = $categoria;
            }
        } else {
            $categorie_trovate[] = $procedure['categoria'];
        }
    }

    $categorie_non_trovate = array_diff($my_categories, $categorie_trovate);

    $output = "<h4>Categorie non trovate:</h4>";
    if (!empty($categorie_non_trovate)) {
        $output .= "<ul>";
        foreach ($categorie_non_trovate as $categoria) {
            $output .= "<li>$categoria</li>";
        }
        $output .= "</ul>";
    } else {
        $output .= "<p>Tutte le categorie sono state trovate.</p>";
    }

    return $output;
}

// Funzione principale per mostrare i servizi e il pulsante
function mostra_servizi() {
    $url = dci_get_option('servizi_maggioli_url', 'servizi');
    $response = wp_remote_get($url);
    $total_services = 0; // Inizializza il contatore

    if (is_array($response) && !is_wp_error($response)) {
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($data) {
            // Pulsante per confrontare categorie
            echo '<button id="confronta-categorie">Confronta categorie</button>';
            echo '<div id="categorie-output"></div>';

            // Inizializza array per servizi in evidenza e non in evidenza
            $in_evidenza_services = [];
            $other_services = [];

            foreach ($data as $procedure) {
                // Verifica se il termine di ricerca è presente nel nome del servizio
                if (isset($_GET['search_term']) && stripos($procedure['nome'], $_GET['search_term']) === false) {
                    continue; // Ignora questo servizio se il termine di ricerca non è presente
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
    $url = dci_get_option('servizi_maggioli_url', 'servizi');
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

    wp_die(); // Termina l'esecuzione dello script
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

// Funzione per mostrare i servizi (inserisci questa funzione dove vuoi mostrare i servizi)
mostra_servizi();
?>


