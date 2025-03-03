<?php
function register_taxonomy_persona_pubblica() {
    register_taxonomy(
        'ruolo_persona_pubblica',
        'persona_pubblica',
        array(
            'label'             => __('Ruoli Persona Pubblica', 'textdomain'),
            'rewrite'           => array('slug' => 'ruolo-persona-pubblica'),
            'hierarchical'      => true,
            'show_admin_column' => true,
            'public'            => true,
            'show_ui'           => true
        )
    );
}
add_action('init', 'register_taxonomy_persona_pubblica');

