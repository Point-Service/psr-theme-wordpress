
<?php

/**
 * Definisce post type Commissario
 */
add_action( 'init', 'dci_register_post_type_commissario');
function dci_register_post_type_commissario() {

    $labels = array(
        'name'          => _x( 'Commissario OSL', 'Post Type General Name', 'design_comuni_italia' ),
        'singular_name' => _x( 'Commissario', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new'       => _x( 'Aggiungi un Documento', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new_item'  => _x( 'Aggiungi un nuovo Documento', 'Post Type Singular Name', 'design_comuni_italia' ),
        'edit_item'       => _x( 'Modifica un Documento', 'Post Type Singular Name', 'design_comuni_italia' ),
        'featured_image' => __( 'Immagine di riferimento', 'design_comuni_italia' ),
    );
    $args   = array(
        'label'         => __( 'commissario', 'design_comuni_italia'),
        'labels'        => $labels,
        'supports'      => array( 'title', 'editor', 'author', 'thumbnail'),
        'hierarchical'  => false,
        'public'        => true,
        'menu_position' => 5,
        'menu_icon'     => 'dashicons-media-interactive',
        'has_archive'   => false,
        'rewrite' => array('slug' => 'progetti','with_front' => false),
        'map_meta_cap'    => true,
        'description'    => __( "Tipologia che consente l'inserimento dei progetti PNRR del comune", 'design_comuni_italia' ),
    );
    register_post_type('commissario', $args );

    remove_post_type_support( 'commissario', 'editor');
}

/**
 * Aggiungo label sotto il titolo
 */
add_action( 'edit_form_after_title', 'dci_commissario_add_content_after_title' );
function dci_Progetto_add_content_after_title($post) {
    if($post->post_type == "commissario")
        _e('<span><i>il <b>Titolo</b> è il <b>Titolo di un documento per la sezione Commissario OSL</b>.</i></span><br><br>', 'design_comuni_italia' );
}

add_action( 'cmb2_init', 'dci_add_commissario_metaboxes' );
function dci_add_commissario_metaboxes() {
    $prefix = '_dci_commissario_';

    //APERTURA
    $cmb_apertura = new_cmb2_box( array(
        'id'           => $prefix . 'box_apertura',
        'title'        => __( 'Apertura', 'design_comuni_italia' ),
        'object_types' => array( 'commissario' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );


}


/**
 * aggiungo js per controllo compilazione campi
 */

add_action( 'admin_print_scripts-post-new.php', 'dci_commissario_admin_script', 11 );
add_action( 'admin_print_scripts-post.php', 'dci_commissario_admin_script', 11 );

function dci_commissario_admin_script() {
    global $post_type;
    if( 'commissario' == $post_type )
        // wp_enqueue_script( 'progetto-admin-script', get_template_directory_uri() . '/inc/admin-js/progetto.js' );
}

/**
 * Valorizzo il post content in base al contenuto dei campi custom
 * @param $data
 * @return mixed
 */

add_filter( 'wp_insert_post_data' , 'dci_commissario_set_post_content' , '99', 1 );
