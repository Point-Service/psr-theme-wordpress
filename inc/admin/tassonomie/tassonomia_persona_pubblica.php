<?php
/**
 * Definisce la tassonomia "Ruolo Persona Pubblica"
 */
add_action( 'init', 'dci_register_taxonomy_ruolo_persona_pubblica', -10 );
function dci_register_taxonomy_ruolo_persona_pubblica() {

    $labels = array(
        'name'              => _x( 'Ruoli di Persona Pubblica', 'taxonomy general name', 'textdomain' ),
        'singular_name'     => _x( 'Ruolo di Persona Pubblica', 'taxonomy singular name', 'textdomain' ),
        'search_items'      => __( 'Cerca Ruolo di Persona Pubblica', 'textdomain' ),
        'all_items'         => __( 'Tutti i Ruoli di Persona Pubblica', 'textdomain' ),
        'edit_item'         => __( 'Modifica il Ruolo di Persona Pubblica', 'textdomain' ),
        'update_item'       => __( 'Aggiorna il Ruolo di Persona Pubblica', 'textdomain' ),
        'add_new_item'      => __( 'Aggiungi un Ruolo di Persona Pubblica', 'textdomain' ),
        'new_item_name'     => __( 'Nuovo Ruolo di Persona Pubblica', 'textdomain' ),
        'menu_name'         => __( 'Ruolo Persona Pubblica', 'textdomain' ),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'public'            => true, // abilita la visibilità della tassonomia
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'has_archive'       => true,    // pagina archivio
        'rewrite'           => array( 'slug' => 'persona-pubblica/ruolo' ), // modifichiamo lo slug per evitare conflitti
        'capabilities'      => array(
            'manage_terms'  => 'manage_ruolo_persona_pubblica',
            'edit_terms'    => 'edit_ruolo_persona_pubblica',
            'delete_terms'  => 'delete_ruolo_persona_pubblica',
            'assign_terms'  => 'assign_ruolo_persona_pubblica'
        ),
    );

    register_taxonomy( 'ruolo_persona_pubblica', array( 'persona_pubblica' ), $args );
}

