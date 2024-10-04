<?php

function dci_register_pagina_multimedia_options(){
    $prefix = '';

       /**
     * Registers options page "Alerts".
     */

    $args = array(
        'id'           => 'dci_options_multimedia',
        'title'        => esc_html__( 'Multimedia', 'design_comuni_italia' ),
        'object_types' => array( 'options-page' ),
        'option_key'   => 'multimedia',
        'capability'    => 'manage_options',
        'parent_slug'  => 'dci_options',
        'tab_group'    => 'dci_options',
        'tab_title'    => __('Multimedia', "design_comuni_italia"),	);



}
