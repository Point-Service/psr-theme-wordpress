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

    // Rimuoviamo la parte che disabilita l'editor, il titolo e la descrizione.
    // Non c'è più nessun controllo per disabilitare questi campi, così rimarranno sempre modificabili.
    global $pagenow;
    if (( $pagenow == 'post.php' ) || ($pagenow == 'post-new.php')) {

        if ( isset($_GET['post']) ) {
            $curr_page_id = $_GET['post'];
        } elseif ( isset($_POST['post_ID']) ) {
            $curr_page_id = $_POST['post_ID'];
        } else {
            return;
        }

        // A questo punto, non abbiamo più bisogno di controllare il template del "Sito dei Comuni"
        // e non rimuoviamo più il supporto per il titolo e l'editor.
        
        // Ripristiniamo il supporto per il titolo e l'editor se era stato rimosso
        add_post_type_support( 'page', 'editor' );
        add_post_type_support( 'page', 'title' );
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

