<?php
function create_persona_pubblica_taxonomy() {
    $labels = array(
        'name'              => __('Ruoli', 'textdomain'),
        'singular_name'     => __('Ruolo', 'textdomain'),
        'search_items'      => __('Cerca Ruoli', 'textdomain'),
        'all_items'         => __('Tutti i Ruoli', 'textdomain'),
        'parent_item'       => __('Ruolo Genitore', 'textdomain'),
        'parent_item_colon' => __('Ruolo Genitore:', 'textdomain'),
        'edit_item'         => __('Modifica Ruolo', 'textdomain'),
        'update_item'       => __('Aggiorna Ruolo', 'textdomain'),
        'add_new_item'      => __('Aggiungi Nuovo Ruolo', 'textdomain'),
        'new_item_name'     => __('Nome Nuovo Ruolo', 'textdomain'),
        'menu_name'         => __('Ruoli', 'textdomain'),
    );

    $args = array(
        'labels'            => $labels,
        'hierarchical'      => true, // True se vuoi che sia gerarchica come le categorie
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => array('slug' => 'ruolo-persona-pubblica'),
        'show_in_rest'      => true, // Per Gutenberg
    );

    register_taxonomy('ruolo_persona_pubblica', array('persona_pubblica'), $args);
}
add_action('init', 'create_persona_pubblica_taxonomy');
