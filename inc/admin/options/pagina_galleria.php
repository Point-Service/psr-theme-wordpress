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

   






}
