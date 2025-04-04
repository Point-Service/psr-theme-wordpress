<?php

/**
 * Aggiungo label sotto il titolo
 */
add_action( 'edit_form_after_title', 'dci_page_add_content_after_title' );
function dci_page_add_content_after_title($post) {
    if($post->post_type == "page")
        _e('<span><i>il <b>Titolo</b> è il <b>Titolo della Pagina</b>.</i></span><br><br><br> ', 'design_comuni_italia' );
}

/**
 * Crea i metabox del post type page
 */
add_action( 'cmb2_init', 'dci_add_page_metaboxes' );
function dci_add_page_metaboxes() {
    $prefix = '_dci_page_';

    $cmb_descrizione = new_cmb2_box( array(
        'id'           => $prefix . 'box_descrizione',
        'object_types' => array( 'page' ),
        'context'      => 'after_title',
        'priority'     => 'high',
    ) );

    $args =  array(
        'id' => $prefix . 'descrizione',
        'name'        => __( 'Descrizione *', 'design_comuni_italia' ),
        'desc'        => __( 'Una breve descrizione compare anche nella card di presentazione della pagina', 'design_comuni_italia' ),
        'type'        => 'textarea',
        'attributes'  => array(
            'required'   => 'required',
            'maxlength'  => 255
        ),
    );

    /**
     * Disabilito editor body e title per le pagine del Sito dei Comuni
     * Rendo il campo descrizione_breve readonly
     */
    global $pagenow;
    if (( $pagenow == 'post.php' ) || ($pagenow == 'post-new.php')) {

        if ( isset($_GET['post']) ) {
            $curr_page_id = $_GET['post'];
        } elseif ( isset($_POST['post_ID']) ) {
            $curr_page_id = $_POST['post_ID'];
        } else {
            return;
        }

        $slug = get_post_field( 'post_name', $curr_page_id );

        // Get the name of the Page Template file.
        $template_file = get_post_meta( $curr_page_id, '_wp_page_template', true );
        $template_name = basename($template_file, ".php");

        // Se la pagina utilizza un template del Sito dei Comuni
        if ( in_array($template_name, dci_get_pagine_template_names()) ) {

            // Aggiungi il controllo per non disabilitare il titolo se la pagina è già pubblicata
            $post_status = get_post_status( $curr_page_id );
            if ( 'publish' !== $post_status ) {
                remove_post_type_support( 'page', 'title' );
            }

            remove_post_type_support( 'page', 'editor' );

            // Rendo readonly il campo descrizione_breve
            $args['attributes'] = array(
                'required' => 'required',
                'maxlength' => 255,
                'readonly' => true
            );
        }
    }

    // Aggiungi il campo "Descrizione" al metabox
    $cmb_descrizione->add_field($args);

}

/**
 * Disabilito quick edit del titolo per le pagine del Sito dei Comuni
 * @param $actions
 * @param $post
 * @return mixed
 */
function dci_page_row_actions( $actions, $post ) {

    // Se la pagina ha slug tra le pagine create all'attivazione del tema
    if ( 'page' === $post->post_type && in_array ($post->post_name, dci_get_pagine_slugs())) {

        // Rimuove l'azione "Quick Edit"
        unset( $actions['inline hide-if-no-js'] );
    }
    return $actions;
}
add_filter( 'page_row_actions', 'dci_page_row_actions', 10, 2 );

?>
