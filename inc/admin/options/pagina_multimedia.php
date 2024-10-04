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


}
