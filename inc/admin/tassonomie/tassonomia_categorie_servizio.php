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
        'all_items'         => __( 'Tutte le Categorie di Servizio', 'design_comuni_italia' ),
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
}

/**
 * Aggiungi un pulsante per svuotare le categorie nella dashboard
 */
add_action( 'admin_menu', 'dci_add_empty_categories_button' );
function dci_add_empty_categories_button() {
    add_submenu_page(
        'edit.php?post_type=servizio',
        __( 'Svuota Categorie', 'design_comuni_italia' ),
        __( 'Svuota Categorie', 'design_comuni_italia' ),
        'manage_options',
        'svuota_categorie_servizio',
        'dci_empty_categories_page'
    );
}

/**
 * Visualizza il contenuto della pagina Svuota Categorie
 */
function dci_empty_categories_page() {
    ?>
    <div class="wrap">
        <h1><?php _e( 'Svuota tutte le categorie di servizio', 'design_comuni_italia' ); ?></h1>
        <form method="post" action="">
            <?php wp_nonce_field( 'dci_empty_categories_nonce', 'dci_empty_categories_nonce_field' ); ?>
            <p><?php _e( 'Questa azione rimuoverà tutte le categorie di servizio. Sei sicuro?', 'design_comuni_italia' ); ?></p>
            <input type="submit" name="dci_empty_categories_submit" class="button button-primary" value="<?php _e( 'Svuota Categorie', 'design_comuni_italia' ); ?>" />
        </form>
    </div>
    <?php
}

/**
 * Gestisci la richiesta di svuotamento delle categorie
 */
add_action( 'admin_init', 'dci_handle_empty_categories' );
function dci_handle_empty_categories() {
    if ( isset( $_POST['dci_empty_categories_submit'] ) && check_admin_referer( 'dci_empty_categories_nonce', 'dci_empty_categories_nonce_field' ) ) {
        $terms = get_terms( array(
            'taxonomy' => 'categorie_servizio',
            'hide_empty' => false,
        ) );

        foreach ( $terms as $term ) {
            wp_delete_term( $term->term_id, 'categorie_servizio' );
        }

        wp_redirect( admin_url( 'edit.php?post_type=servizio&page=svuota_categorie_servizio&success=true' ) );
        exit;
    }

    if ( isset( $_GET['success'] ) ) {
        add_action( 'admin_notices', 'dci_empty_categories_success_notice' );
    }
}

/**
 * Mostra un messaggio di successo dopo aver svuotato le categorie
 */
function dci_empty_categories_success_notice() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p><?php _e( 'Tutte le categorie di servizio sono state rimosse con successo.', 'design_comuni_italia' ); ?></p>
    </div>
    <?php
}
?>
