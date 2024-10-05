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

   
    $gallery_options = new_cmb2_box( $args );

    $gallery_options->add_field( array(
        'id' => $prefix . 'messages_istruzioni_g',
        'name'        => __( 'Box in evidenza su Multimedia', 'design_comuni_italia' ),
        'desc' => __( 'Inserisci i video che verrano visualizzati nella pagina multimedia.' , 'design_comuni_italia' ),
        'type' => 'title',
    ) );


   $gallery_options->add_field( array(
        'id' => $prefix . 'mostra_gallery_vivereilcomune',
        'name' => 'Mostra la Gallery',
        'desc' => 'Mostra la galleria Foto su Vivere il Comune',
        'type' => 'checkbox',
    ) ); 

     $gallery_options->add_field(array(
        'name' => __('Gallery', 'design_comuni_italia'),
        'desc' => __('Seleziona le foto da mostrare in fondo alla pagina', 'design_comuni_italia'),
        'id' => $prefix . 'gallery_items',
        'type' => 'file_list',
        'preview_size' => array( 100, 100 ), // Default: array( 50, 50 )
        'query_args' => array( 'type' => 'image' ), // Only images attachment
    )
  );

    $gallery_options->add_field( array(
        'id' => $prefix . 'gallery_title',
        'name' => 'Nome gallery',
        'desc' => 'Scegli il nome della sezione con la galleria.',
        'type' => 'text',
        'default' => 'Le nostre foto'
      ) 
    );
    
}
