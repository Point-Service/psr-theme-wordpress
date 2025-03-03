<?php

/**
 * Definisce la tassonomia Ruolo Persona Pubblica
 */
add_action( 'init', 'dci_register_taxonomy_ruolo_persona_pubblica', -10 );
function dci_register_taxonomy_ruolo_persona_pubblica() {

    $labels = array(
        'name'              => _x( 'Ruoli Persona Pubblica', 'taxonomy general name', 'design_comuni_italia' ),
        'singular_name'     => _x( 'Ruolo Persona Pubblica', 'taxonomy singular name', 'design_comuni_italia' ),
        'search_items'      => __( 'Cerca Ruolo Persona Pubblica', 'design_comuni_italia' ),
        'all_items'         => __( 'Tutti i Ruoli della Persona Pubblica', 'design_comuni_italia' ),
        'edit_item'         => __( 'Modifica il Ruolo Persona Pubblica', 'design_comuni_italia' ),
        'update_item'       => __( 'Aggiorna il Ruolo Persona Pubblica', 'design_comuni_italia' ),
        'add_new_item'      => __( 'Aggiungi un Ruolo Persona Pubblica', 'design_comuni_italia' ),
        'new_item_name'     => __( 'Nuovo Ruolo Persona Pubblica', 'design_comuni_italia' ),
        'menu_name'         => __( 'Ruoli Persona Pubblica', 'design_comuni_italia' ),
    );

    $args = array(
        'hierarchical'      => true, // Imposta la tassonomia come gerarchica (come le categorie)
        'labels'            => $labels,
        'public'            => true, // Abilita la visualizzazione sul front-end
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'has_archive'       => true, // Abilita la pagina di archivio
        'rewrite'           => array( 'slug' => 'persona-pubblica/ruolo' ), // URL personalizzato
        'capabilities'      => array(
            'manage_terms'  => 'manage_ruolo_persona_pubblica',
            'edit_terms'    => 'edit_ruolo_persona_pubblica',
            'delete_terms'  => 'delete_ruolo_persona_pubblica',
            'assign_terms'  => 'assign_ruolo_persona_pubblica'
        ),
    );

    // Associa la tassonomia al CPT persona_pubblica
  //  register_taxonomy( 'ruolo_persona_pubblica', array( 'persona_pubblica' ), $args );
}
