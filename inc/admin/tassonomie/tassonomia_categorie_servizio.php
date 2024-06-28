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

    // Aggiungi il pulsante per confrontare ed eliminare categorie non corrispondenti   
    if (isset($_GET['taxonomy']) && $_GET['taxonomy'] === 'categorie_servizio') {
        add_action( 'admin_footer', 'add_empty_and_sync_categories_button' );
    }
}

// Funzione per aggiungere il pulsante "Confronta ed elimina categorie non corrispondenti"
function add_empty_and_sync_categories_button() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#delete-non-matching-categories').on('click', function() {
                $.getJSON('https://sportellotelematico.comune.roccalumera.me.it/rest/pnrr/procedures', function(data) {
                    var remoteCategories = data.map(function(item) {
                        return item.procedure_category_name;
                    });

                    var localCategories = <?php echo json_encode(get_terms('categorie_servizio', array('fields' => 'names'))); ?>;

                    // Trova le categorie locali che non sono presenti nel dato remoto
                    var categoriesToDelete = localCategories.filter(function(category) {
                        return remoteCategories.indexOf(category) === -1;
                    });

                    // Elimina le categorie non corrispondenti
                    categoriesToDelete.forEach(function(category) {
                        $.ajax({
                            url: ajaxurl,
                            type: 'POST',
                            data: {
                                action: 'delete_category_by_name',
                                category_name: category
                            },
                            success: function(response) {
                                console.log('Categoria eliminata: ' + category);
                            },
                            error: function(error) {
                                console.error('Errore durante l\'eliminazione della categoria: ' + category);
                            }
                        });
                    });

                    alert('Operazione completata. Le categorie non corrispondenti sono state eliminate.');
                });
            });
        });
    </script>
    <button id="delete-non-matching-categories" class="button button-secondary">Confronta ed elimina categorie non corrispondenti</button>
    <?php
}

// Aggiungi il pulsante nella pagina delle categorie di servizio
add_action('admin_footer', 'add_empty_and_sync_categories_button');

// Funzione per eliminare una categoria per nome
add_action('wp_ajax_delete_category_by_name', 'delete_category_by_name');
function delete_category_by_name() {
    if (isset($_POST['category_name'])) {
        $category_name = sanitize_text_field($_POST['category_name']);
        $term = get_term_by('name', $category_name, 'categorie_servizio');

        if ($term && !is_wp_error($term)) {
            wp_delete_term($term->term_id, 'categorie_servizio');
        }
    }
}

?>
