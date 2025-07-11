<?php

/**
 * Definisce la tassonomia Tipi stato bando
 */
add_action( 'init', 'dci_register_taxonomy_tipi_stato_bando', 20 );
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



/* ---------- 1. AGGIUNTA DEL SOTTOMENU ---------- */
add_action( 'admin_menu', 'dci_add_bandi_submenu', 21 );
function dci_add_bandi_submenu() {

    $parent_slug = 'edit.php?post_type=elemento_trasparenza';

 /* 0. ——— separatore ——— */
    add_submenu_page(
        $parent_slug,
        '',                      // page‑title (vuoto: non vogliamo una schermata)
        '────────────',          // menu‑title (caratteri orizzontali)
        'read',                  // capability minima
        'dci_dummy_separator',   // slug fantoccio
        '__return_null',         // callback nullo
        8                        // posizione (prima di 9)
    );

    
    // 1. Bandi di gara - elenco
    add_submenu_page(
        $parent_slug,
        __('Bandi di gara', 'design_comuni_italia'),
        __('Bandi di gara', 'design_comuni_italia'),
        'edit_bando',
        'edit.php?post_type=bando',
        '',
        9
    );


        // 2. Aggiungi nuovo Bando di gara
    add_submenu_page(
        $parent_slug,
        __('Aggiungi nuovo Bando di gara', 'design_comuni_italia'),
        __('Aggiungi nuovo Bando di gara', 'design_comuni_italia'),
        'edit_posts',
        'post-new.php?post_type=bando',
        '',
        10
    );

    // 3. Tipi stato bandi
    add_submenu_page(
        $parent_slug,
        __('Tipi stato bandi', 'design_comuni_italia'),
        __('Tipi stato bandi', 'design_comuni_italia'),
        'manage_tipi_stato_bando',
        'edit-tags.php?taxonomy=tipi_stato_bando&post_type=bando',
        '',
        15
    );

    // 4. Tipi procedura contraente
    add_submenu_page(
        $parent_slug,
        __('Tipi procedura contraente', 'design_comuni_italia'),
        __('Tipi procedura contraente', 'design_comuni_italia'),
        'manage_tipi_procedura_contraente',
        'edit-tags.php?taxonomy=tipi_procedura_contraente&post_type=bando',
        '',
        20
    );

}


    

