<?php
/**
 * Definisce la tassonomia Categorie di Servizio
 */
add_action( 'init', 'dci_register_taxonomy_categorie_servizio', -10 );
function dci_register_taxonomy_categorie_servizio() {
    $labels = array(
        'name'              => _x( 'Categorie di Servizio', 'taxonomy general name', 'design_comuni_italia' ),
        'singular_name'     => _x( 'Categoria di Servizio', 'taxonomy singular name', 'design_comuni_italia' ),
        'search_items'      => __( 'Cerca Categoria di Servizio', 'design_comuni_italia' ),
        'all_items'         => __( 'Tutte le Categorie di Servizio ', 'design_comuni_italia' ),
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

    register_taxonomy( 'categorie_servizio', array( 'servizio' ), $args );

    // Aggiungi il pulsante per svuotare le categorie di servizio
    add_action( 'admin_footer', 'add_empty_categories_button' );
}

function add_empty_categories_button() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Trova il form per aggiungere una nuova categoria di servizio
            var addTermForm = $('.form-field.term-parent-wrap').closest('form');

            // Crea un nuovo elemento per il pulsante "Cancella tutte le categorie di servizio"
            var deleteButtonHtml = '<div style="margin-top: 20px;"><button id="delete-all-categories" class="button">Cancella tutte le categorie di servizio</button></div>';
            var loadCategoriesButtonHtml = '<div style="margin-top: 10px;"><button id="load-categories" class="button">Carica Categorie1</button></div>';

            // Aggiungi i pulsanti sotto il form per aggiungere una nuova categoria di servizio
            addTermForm.after(deleteButtonHtml);
            addTermForm.after(loadCategoriesButtonHtml);

            // Gestisci il clic del pulsante "Cancella tutte le categorie di servizio"
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
                            alert('Tutte le categorie di servizio sono state cancellate.');
                            location.reload();
                        }
                    });
                }
            });

            // Gestisci il clic del pulsante "Carica Categorie"
            $(document).on('click', '#load-categories', function(e) {
                e.preventDefault();
                $.ajax({
                    url: '<?php echo admin_url( "admin-ajax.php" ); ?>',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        action: 'load_categories_from_external_api',
                        nonce: '<?php echo wp_create_nonce( "load-categories-nonce" ); ?>'
                    },
                    success: function(response) {
                        if (response.success && response.data) {
                            var categories = [];

                            if (Array.isArray(response.data)) {
                                // Loop attraverso le categorie restituite dall'API
                                response.data.forEach(function(item) {
                                    if (item.categoria) {
                                        categories.push(item.categoria.nome);
                                    }
                                });

                                // Aggiungi le categorie alla lista delle opzioni
                                $.each(categories, function(key, value) {
                                    $('#categorie_servizio').append($('<option></option>').val(value).html(value));
                                });

                                // Mostra il debug a video
                                $('#debug-output').html('<pre>' + JSON.stringify(categories, null, 2) + '</pre>');

                                alert('Categorie caricate correttamente.');
                            } else {
                                console.error('Errore: dati delle categorie non validi.');
                                alert('Errore nel caricamento delle categorie.');
                            }
                        } else {
                            console.error('Errore nella risposta API:', response);
                            alert('Errore nel caricamento delle categorie.');
                        }
                    },
                    error: function(error) {
                        console.error('Errore nella chiamata AJAX:', error);
                        alert('Errore nel caricamento delle categorie.');
                    }
                });
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

// Funzione per caricare le categorie da un API esterno
add_action( 'wp_ajax_load_categories_from_external_api', 'load_categories_from_external_api_callback' );
function load_categories_from_external_api_callback() {
    check_ajax_referer( 'load-categories-nonce', 'nonce' );

    // Esegui la richiesta all'API remoto
    $response = wp_remote_get( 'https://sportellotelematico.comune.roccalumera.me.it/rest/pnrr/procedures' );

    if ( is_wp_error( $response ) ) {
        wp_send_json_error( array( 'message' => 'Errore nella richiesta API remoto.' ) );
    }

    $body = wp_remote_retrieve_body( $response );
    $data = json_decode( $body );

    if ( ! empty( $data ) ) {
        // Mostra il debug a video
        echo '<div id="debug-output"><pre>' . json_encode( $data, JSON_PRETTY_PRINT ) . '</pre></div>';

        wp_send_json_success( $data );
    } else {
        wp_send_json_error( array( 'message' => 'Nessun dato ricevuto dall\'API remoto.' ) );
    }
}


?>


