<?php

function dci_register_pagina_multimedia_options(){
    $prefix = '';

       /**
     * Registers options page "Alerts".
     */

    $args = array(
        'id'           => 'dci_quick_messages',
        'title'        => esc_html__( 'Multimedia', 'design_comuni_italia' ),
        'object_types' => array( 'options-page' ),
        'option_key'   => 'multimedia',
        'capability'    => 'manage_options',
        'parent_slug'  => 'dci_options',
        'tab_group'    => 'dci_options',
        'tab_title'    => __('Multimedia', "design_comuni_italia"),	);

    // 'tab_group' property is supported in > 2.4.0.
    if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
        $args['display_cb'] = 'dci_options_display_with_tabs';
    }

    $boxes_options = new_cmb2_box( $args );

    $boxes_options->add_field( array(
        'id' => $prefix . 'messages_istruzioni',
        'name'        => __( 'Box in evidenza su Multimedia', 'design_comuni_italia' ),
        'desc' => __( 'Inserisci i video che verrano visualizzati nella pagina multimedia.' , 'design_comuni_italia' ),
        'type' => 'title',
    ) );

    $boxes_group_id = $boxes_options->add_field( array(
        'id'           => $prefix . 'quickboxes',
        'type'        => 'group',
        'desc' => __( 'Inserisci il nome e il link' , 'design_comuni_italia' ),
        'repeatable'  => true,
        'options'     => array(
            'group_title'   => __( 'Box {#}', 'design_comuni_italia' ),
            'add_button'    => __( 'Aggiungi un video', 'design_comuni_italia' ),
            'remove_button' => __( 'Rimuovi il video', 'design_comuni_italia' ),
            'sortable'      => true,  // Allow changing the order of repeated groups.
        ),
    ) );


    $boxes_options->add_group_field( $boxes_group_id, array(
        'id' => $prefix . 'titolo_video',
        'name'        => __( 'Titolo', 'design_comuni_italia' ),
        'desc' => __( 'Massimo 100 caratteri' , 'design_comuni_italia' ),
        'type' => 'textarea_small',
        'attributes'    => array(
            'rows'  => 1,
            'maxlength'  => '100',
        ),
    ) );

    $boxes_options->add_group_field( $boxes_group_id, array(
        'id' => $prefix . 'link_video',
        'name'        => __( 'Indirizzo video youtube', 'design_comuni_italia' ),
        'desc' => __( 'Link al video Youtube' , 'design_comuni_italia' ),
        'type' => 'text_url',
    ) );

}
