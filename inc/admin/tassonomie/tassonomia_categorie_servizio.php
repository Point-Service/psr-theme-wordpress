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

    register_taxonomy( 'categorie_servizio', array( 'servizio' ), $args );

    // Aggiungi il pulsante per eliminare tutte le Categporie
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

            // Aggiungi un'area per visualizzare le categorie non trovate
            var outputHtml = '<div style="margin-top: 20px;"><h3>Output delle categorie non trovate:</h3><textarea id="categorie-output" style="width: 100%; height: 200px;" readonly></textarea></div>';
            addTermForm.after(outputHtml);

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
                                // Ricarica l'output delle categorie non trovate dopo aver cancellato le categorie
                                confrontaCategorie();
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

            // Funzione per confrontare le categorie e aggiornare l'output nella textarea
            function confrontaCategorie() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'confronta_categorie'
                    },
                    success: function(response) {
                        $('#categorie-output').val(response);
                    },
                    error: function() {
                        $('#categorie-output').val('Errore nella richiesta AJAX.');
                    }
                });
            }

            // Avvia il confronto delle categorie al caricamento della pagina
            confrontaCategorie();
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

// Funzione per confrontare le categorie e ritornare l'output delle categorie non trovate
add_action( 'wp_ajax_confronta_categorie', 'ajax_confronta_categorie' );
add_action( 'wp_ajax_nopriv_confronta_categorie', 'ajax_confronta_categorie' );
function ajax_confronta_categorie() {
    global $my_categories;

    $url = 'https://sportellotelematico.comune.roccalumera.me.it/rest/pnrr/procedures';
    $response = wp_remote_get( $url );

    if ( is_array( $response ) && ! is_wp_error( $response ) ) {
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        $output = confronta_categorie( $data, $my_categories );

        echo $output;
    } else {
        echo "Non riesco a leggere i servizi aggiuntivi.";
    }

    wp_die();
}

// Funzione per confrontare le categorie e ritornare l'output delle categorie non trovate
function confronta_categorie( $data, $my_categories ) {
    $categorie_trovate = array();

    foreach ( $data as $procedure ) {
        if ( is_array( $procedure['categoria'] ) ) {
            foreach ( $procedure['categoria'] as $categoria ) {
                $categorie_trovate[] = $categoria;
            }
        } else {
            $categorie_trovate[] = $procedure['categoria'];
        }
    }

    $categorie_non_trovate = array_diff( $my_categories, $categorie_trovate );

    $output = '';

    foreach ( $categorie_non_trovate as $categoria ) {
        $output .= "$categoria\n";
    }

    return $output;
}
?>



