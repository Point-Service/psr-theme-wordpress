<?php

function dci_register_pagina_galleria_options(){
    $prefix = '';

 
    $args = array(
        'id'           => 'dci_options_galleria',
        'title'        => esc_html__( 'Galleria', 'design_comuni_italia' ),
        'object_types' => array( 'options-page' ),
        'option_key'   => 'Galleria',
        'capability'    => 'manage_options',
        'parent_slug'  => 'dci_options',
        'tab_group'    => 'dci_options',
        'tab_title'    => __('Galleria', "design_comuni_italia"),	);

   
    $media_options = new_cmb2_box( $args );

    $media_options->add_field( array(
        'id' => $prefix . 'messages_istruzioni_g',
        'name'        => __( 'Box in evidenza su Multimedia', 'design_comuni_italia' ),
        'desc' => __( 'Inserisci i video che verrano visualizzati nella pagina multimedia.' , 'design_comuni_italia' ),
        'type' => 'title',
    ) );


   $options_galleria->add_field( array(
        'id' => $prefix . 'mostra_gallery_vivereilcomune',
        'name' => 'Mostra la Gallery',
        'desc' => 'Mostra la galleria Foto su Vivere il Comune',
        'type' => 'checkbox',
    ) ); 

    
}
