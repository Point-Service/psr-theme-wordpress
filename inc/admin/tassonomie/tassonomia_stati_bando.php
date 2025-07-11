<?php

/**
 * Definisce la tassonomia Tipi stato bando
 */
add_action( 'init', 'dci_register_taxonomy_tipi_stato_bando', -10 );
function dci_register_taxonomy_tipi_stato_bando() {

    $labels = array(
        'name'              => _x( 'Tipi stato bando', 'taxonomy general name', 'design_comuni_italia' ),
        'singular_name'     => _x( 'Tipo  stato bando', 'taxonomy singular name', 'design_comuni_italia' ),
        'search_items'      => __( 'Cerca Tipo stato bando', 'design_comuni_italia' ),
        'all_items'         => __( 'Tutti i Tipi stato bando ', 'design_comuni_italia' ),
        'edit_item'         => __( 'Modifica il Tipo stato bando', 'design_comuni_italia' ),
        'update_item'       => __( 'Aggiorna il Tipo stato bando', 'design_comuni_italia' ),
        'add_new_item'      => __( 'Aggiungi un Tipo stato bando', 'design_comuni_italia' ),
        'new_item_name'     => __( 'Nuovo Tipo  stato bando', 'design_comuni_italia' ),
        'menu_name'         => __( 'Tipi  stato bando', 'design_comuni_italia' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'public'            => true, 
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'has_archive'       => false,       
        'capabilities'      => array(
            'manage_terms'  => 'manage_tipi_stato_bando',
            'edit_terms'    => 'edit_tipi_stato_bando',
            'delete_terms'  => 'delete_tipi_stato_bando',
            'assign_terms'  => 'assign_tipi_stato_bando'
        )
    );

    register_taxonomy( 'tipi_stato_bando', array( 'bando' ), $args );
}


/* ---------- 2. AGGIUNTA DEL SOTTOMENU ---------- */
add_action( 'admin_menu', 'dci_add_tipi_stato_bando_submenu', 20 );
function dci_add_tipi_stato_bando_submenu() {

    // Slug del menu padre (“Amministrazione Trasparente”)
    $parent_slug = 'edit.php?post_type=elemento_trasparenza';

    
    // Crea la voce che apre la schermata gestione termini
    add_submenu_page(
        $parent_slug,                                                        // genitore
        __( 'Tipi stato bando', 'design_comuni_italia' ),                    // page‑title
        __( 'Tipi stato bando', 'design_comuni_italia' ),                    // menu‑title
        'manage_tipi_stato_bando',                                           // capability
        'edit-tags.php?taxonomy=tipi_stato_bando&post_type=bando'            // link alla tassonomia
    );

    add_submenu_page(
    'post-new.php?post_type=bando',
    'Aggiungi Nuovo Bando',
    'Aggiungi Nuovo Bando',
    'manage_tipi_procedura_contraente',
    'edit-tags.php?taxonomy=post-new.php?post_type=bando'
   );
    
    add_submenu_page(
    'edit.php?post_type=elemento_trasparenza',
    'Tipi procedura contraente',
    'Tipi procedura contraente',
    'manage_tipi_procedura_contraente',
    'edit-tags.php?taxonomy=tipi_procedura_contraente&post_type=bando'
   );

     add_submenu_page(
    'edit.php?post_type=elemento_trasparenza',
    'Tipi procedura contraente',
    'Tipi procedura contraente',
    'manage_tipi_procedura_contraente',
    'edit-tags.php?taxonomy=tipi_procedura_contraente&post_type=bando'
   );
    
}
