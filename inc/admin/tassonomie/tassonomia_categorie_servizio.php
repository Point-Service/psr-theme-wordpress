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

    // Aggiungi il pulsante "Aggiungi URL"
    add_action( 'admin_footer', 'add_remote_url_button' );
}

function add_empty_categories_button() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Trova il form per aggiungere una nuova categoria di servizio
            var addTermForm = $('.form-field.term-parent-wrap').closest('form');

            // Crea un nuovo elemento per il pulsante "Cancella tutte le categorie di servizio"
            var deleteButtonHtml = '<div style="margin-top: 10px;"><button id="delete-all-categories" class="button">Cancella tutte le categorie di servizio</button></div>';

            // Aggiungi il pulsante sotto il form per aggiungere una nuova categoria di servizio
            addTermForm.after(deleteButtonHtml);

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
                        },
                        error: function(xhr, status, error) {
                            alert('Errore durante l\'eliminazione delle categorie: ' + error);
                            console.error(xhr.responseText); // Mostra la risposta completa dell'errore nella console
                        }
                    });
                }
            });
        });
    </script>
    <?php
}

function add_remote_url_button() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Trova il form per aggiungere una nuova categoria di servizio
            var addTermForm = $('.form-field.term-parent-wrap').closest('form');

            // Crea un nuovo elemento per il pulsante "Aggiungi URL"
            var addUrlButtonHtml = '<div style="margin-top: 10px;"><input type="text" id="remote-url" placeholder="Inserisci l\'URL"><button id="add-url" class="button">Carica Categorie da Maggioli</button></div>';
            addTermForm.after(addUrlButtonHtml);

     // Gestisci il clic del pulsante "Aggiungi URL"

            $(document).on('click', '#add-url', function(e) {
                e.preventDefault();
                var remoteUrl = $('#remote-url').val();
                if (remoteUrl) {
                    $.ajax({
                        url: remoteUrl,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            if (response && response.length > 0) {
                                var categoriesAdded = 0;
            
                                response.forEach(function(item) {
                                    if (item.categoria) {
                                        addCategoryIfNeeded(item.categoria, '', function(success) {
                                            if (success) {
                                                categoriesAdded++;
                                                if (categoriesAdded === response.length) {
                                                    // Quando tutte le categorie sono state aggiunte con successo, ricarica la pagina
                                                    location.reload();
                                                }
                                            }
                                        });
                                    }
                                });
            
                                alert('Richiesta di aggiunta categorie completata.');
                            } else {
                                alert('Nessuna categoria trovata nella risposta.');
                            }
                        },
                        error: function(xhr, status, error) {
                            alert('Errore durante il recupero delle categorie: ' + error);
                            console.error(xhr.responseText); // Mostra la risposta completa dell'errore nella console
                        }
                    });
                } else {
                    alert('Inserisci un URL valido.');
                }
            });
            
            // Funzione per aggiungere una categoria se non esiste
            function addCategoryIfNeeded(name, description, callback) {
                var data = {
                    action: 'add_category_if_not_exists',
                    name: name,
                    description: description,
                    nonce: '<?php echo wp_create_nonce( "add-category-nonce" ); ?>'
                };
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        if (response && response.success) {
                            console.log('Categoria aggiunta con successo:', name);
                            if (typeof callback === 'function') {
                                callback(true); // Chiamata al callback indicando successo
                            }
                        } else {
                            console.error('Errore durante l\'aggiunta della categoria:', name);
                            if (typeof callback === 'function') {
                                callback(false); // Chiamata al callback indicando errore
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Errore durante l\'aggiunta della categoria:', name, error);
                        if (typeof callback === 'function') {
                            callback(false); // Chiamata al callback indicando errore
                        }
                    }
                });
            }
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

// Funzione per aggiungere una categoria se non esiste
add_action( 'wp_ajax_add_category_if_not_exists', 'add_category_if_not_exists_callback' );
function add_category_if_not_exists_callback() {
    check_ajax_referer( 'add-category-nonce', 'nonce' );

    $name = sanitize_text_field( $_POST['name'] );
    $description = sanitize_text_field( $_POST['description'] );

    $existing_term = term_exists( $name, 'categorie_servizio' );

    if ( $existing_term === 0 || $existing_term === null ) {
        $term_args = array(
            'description' => $description,
            'slug' => sanitize_title( $name ),
        );
        $result = wp_insert_term( $name, 'categorie_servizio', $term_args );
        if ( ! is_wp_error( $result ) ) {
            echo json_encode( array( 'success' => true ) );
        } else {
            echo json_encode( array( 'success' => false ) );
        }
    } else {
        echo json_encode( array( 'success' => true ) );
    }

    wp_die();
}
?>
