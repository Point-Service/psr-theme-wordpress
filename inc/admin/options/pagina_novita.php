<?php

function dci_register_pagina_novita_options() {
    $prefix = '';

    /**
     * Opzioni Novità
     */
    $args = array(
        'id'           => 'dci_options_novita',
        'title'        => esc_html__( 'Novità', 'design_comuni_italia' ),
        'object_types' => array( 'options-page' ),
        'option_key'   => 'novita',
        'tab_title'    => __('Novità', "design_comuni_italia"),
        'parent_slug'  => 'dci_options',
        'tab_group'    => 'dci_options',
        'capability'    => 'manage_options',
    );

    // 'tab_group' property is supported in > 2.4.0.
    if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
        $args['display_cb'] = 'dci_options_display_with_tabs';
    }

    $novita_options = new_cmb2_box( $args );

    // Aggiungi il campo per la sezione "Novità"
    $novita_options->add_field( array(
        'id' => $prefix . 'novita_options',
        'name'        => __( 'Novità', 'design_comuni_italia' ),
        'desc' => __( 'Configurazione della pagina Novità' , 'design_comuni_italia' ),
        'type' => 'title',
    ));

    // Sezione "Contenuti in evidenza"
    $novita_options->add_field(array(
        'name' => __('Contenuti in evidenza', 'design_comuni_italia'),
        'desc' => __('Seleziona le notizie da mostrare nella sezione In Evidenza.', 'design_comuni_italia'),
        'id' => $prefix . 'contenuti_evidenziati',
        'type'    => 'custom_attached_posts',
        'column'  => true, // Output in the admin post-listing as a custom column.
        'options' => array(
            'show_thumbnails' => false, // Show thumbnails on the left
            'filter_boxes'    => true, // Show a text box for filtering the results
            'query_args'      => array(
                'posts_per_page' => -1,
                'post_type'      => array(
                    'notizia',
                )
            ),
        ),
        'attributes' => array(
            'data-max-items' => 3, //max 3 items
        ),
    ));

    // Sezione "Argomenti"
    $novita_options->add_field( array(
        'id' => $prefix . 'novita_argomenti',
        'name'        => __( 'Sezione Argomenti', 'design_comuni_italia' ),
        'desc' => __( 'Configurazione della Sezione Argomenti' , 'design_comuni_italia' ),
        'type' => 'title',
    ));

    // Aggiungi il campo per "Argomenti"
    $novita_options->add_field( array(
        'id' => $prefix . 'argomenti',
        'name'        => __( 'Argomenti ', 'design_comuni_italia' ),
        'desc' => __( 'Seleziona e ordina gli argomenti.' , 'design_comuni_italia' ),
        'type'    => 'pw_multiselect',
        'options' => dci_get_terms_options('argomenti'),
    ));

    // Aggiungiamo la funzione che interviene sul salvataggio dei campi
    add_action('cmb2_save_field', 'dci_save_empty_custom_attached_posts', 10, 3);
}

function dci_save_empty_custom_attached_posts($object_id, $field_id, $args) {
    // Verifica che il campo sia 'custom_attached_posts'
    if ('custom_attached_posts' === $args['type']) {
        $field_value = get_post_meta($object_id, $args['id'], true);

        // Se il campo è vuoto, forziamo il salvataggio di un array vuoto
        if (empty($field_value)) {
            update_post_meta($object_id, $args['id'], array());
        }
    }
}

// Registrazione della funzione nell'init di WordPress
add_action('cmb2_admin_init', 'dci_register_pagina_novita_options');

