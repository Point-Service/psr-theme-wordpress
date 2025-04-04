<?php

/**
 * Definisce post type commissario
 */
add_action( 'init', 'dci_register_post_type_commissario');
function dci_register_post_type_commissario() {

    $labels = array(
        'name'          => _x( 'Commissario OSL', 'Post Type General Name', 'design_comuni_italia' ),
        'singular_name' => _x( 'Documenti', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new'       => _x( 'Aggiungi un Documento', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new_item'  => _x( 'Aggiungi un nuovo Documento', 'Post Type Singular Name', 'design_comuni_italia' ),
        'edit_item'       => _x( 'Modifica il Documento', 'Post Type Singular Name', 'design_comuni_italia' ),
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
        'rewrite' => array('slug' => 'Commissario','with_front' => false),
        'map_meta_cap'    => true,
        'description'    => __( "Tipologia che consente l'inserimento dei Documenti per il Commissario OSL del comune", 'design_comuni_italia' ),
    );
    register_post_type('commissario', $args );

    remove_post_type_support( 'commissario', 'editor');
}

/**
 * Aggiungo label sotto il titolo
 */
add_action( 'edit_form_after_title', 'dci_commissario_add_content_after_title' );
function dci_commissario_add_content_after_title($post) {
    if($post->post_type == "commissario")
        _e('<span><i>il <b>Titolo</b> è il <b>Titolo del Documento del Commissario OSL</b>.</i></span><br><br>', 'design_comuni_italia' );
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

    $cmb_apertura->add_field( array(
        'name'       => __('Immagine', 'design_comuni_italia' ),
        'desc' => __( 'Immagine principale del commissario' , 'design_comuni_italia' ),
        'id'             => $prefix . 'immagine',
        'type' => 'file',
        'query_args' => array( 'type' => 'image' ),
    ) );

    $cmb_apertura->add_field( array(
        'id' => $prefix . 'data_pubblicazione',
        'name'    => __( 'Data della commissario', 'design_comuni_italia' ),
        'desc' => __( 'Data di pubblicazione del commissario.' , 'design_comuni_italia' ),
        'type'    => 'text_date_timestamp',
        'date_format' => 'd-m-Y',
    ) );

    $cmb_apertura->add_field( array(
        'id' => $prefix . 'tipo_commissario',
        'name'        => __( 'Tipo di commissario *', 'design_comuni_italia' ),
        'type'             => 'taxonomy_radio_hierarchical',
        'taxonomy'       => 'tipi_commissario',
        'show_option_none' => false,
        'remove_default' => 'true',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );

    $cmb_apertura->add_field( array(
        'id' => $prefix . 'a_cura_di',
        'name'    => __( 'A cura di *', 'design_comuni_italia' ),
        'desc' => __( 'Ufficio che ha curato il commissario PNRR' , 'design_comuni_italia' ),
        'type'    => 'pw_multiselect',
        'options' => dci_get_posts_options('unita_organizzativa'),
        'attributes'    => array(
            'required'    => 'required',
            'placeholder' =>  __( 'Seleziona le unità organizzative', 'design_comuni_italia' ),
        ),
    ) );

    $cmb_apertura->add_field( array(
        'id' => $prefix . 'descrizione_breve',
        'name'        => __( 'Descrizione breve *', 'design_comuni_italia' ),
        'desc' => __( 'Descrizione sintentica del commissario, inferiore a 255 caratteri' , 'design_comuni_italia' ),
        'type' => 'textarea',
        'attributes'    => array(
            'maxlength'  => '255',
            'required'    => 'required'
        ),
    ) );

    
    $cmb_apertura->add_field( array(
        'id' => $prefix . 'descrizione_scopo',
        'name'        => __( 'Descrizione scopo *', 'design_comuni_italia' ),
        'desc' => __( 'Descrizione e scopo del commissario' , 'design_comuni_italia' ),
        'type' => 'wysiwyg',
        'attributes'    => array(
            'required'    => 'required'
        ),
        'options' => array(
            'textarea_rows' => 10,
            'teeny' => false, 
        ),
    ) );



    // DETTAGLI
    $cmb_dettagli = new_cmb2_box( array(
        'id'           => $prefix . 'box_dettagli',
        'title'        => __( 'Dettagli', 'design_comuni_italia' ),
        'object_types' => array( 'commissario' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

    
    $cmb_dettagli->add_field( array(
        'id' => $prefix . 'componente',
        'name'        => __( 'Componente del commissario *', 'design_comuni_italia' ),
        'desc' => __( 'Testo della componente del commissario' , 'design_comuni_italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );

    $cmb_dettagli->add_field( array(
        'id' => $prefix . 'investimento',
        'name'        => __( 'investimento del commissario *', 'design_comuni_italia' ),
        'desc' => __( 'Testo del investimento del commissario' , 'design_comuni_italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );

    $cmb_dettagli->add_field( array(
        'id' => $prefix . 'intervento',
        'name'        => __( 'intervento del commissario *', 'design_comuni_italia' ),
        'desc' => __( 'Testo del intervento del commissario' , 'design_comuni_italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );
    
    $cmb_dettagli->add_field( array(
        'id' => $prefix . 'titolare',
        'name'        => __( 'Titolare del commissario *', 'design_comuni_italia' ),
        'desc' => __( 'inserire il titolare del commissario' , 'design_comuni_italia' ),
        'type' => 'text',
        'default' => 'PCM PRESIDENZA CONSIGLIO MINISTRI'
    ) );

    $cmb_dettagli->add_field( array(
        'id' => $prefix . 'cup',
        'name'        => __( 'CUP *', 'design_comuni_italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );
    
    $cmb_dettagli->add_field( array(
        'id' => $prefix . 'importo',
        'name'        => __( 'Importo Finanziato *', 'design_comuni_italia' ),
        'type' => 'text',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );

    $cmb_modalita= new_cmb2_box( array(
        'id'           => $prefix . 'box_modalita',
        'title'        => __( 'Modalità/ Attività', 'design_comuni_italia' ),
        'object_types' => array( 'commissario' ),
        'context'      => 'normal',
        'priority'     => 'low',
        
    ) );

    $cmb_modalita->add_field(array(
        'id' => $prefix . 'modalita',
        'name'        => __( 'Modalità di Accesso al Finanziamento *', 'design_comuni_italia' ),
        'type' => 'wysiwyg',
        'attributes'    => array(
            'required'    => 'required'
        ),
        'options' => array(
            'textarea_rows' => 10,
            'teeny' => false, 
        ),
    ));

    $cmb_modalita->add_field(array(
        'id' => $prefix . 'attivita',
        'name'        => __( 'Attività Finanziata *', 'design_comuni_italia' ),
        'type' => 'wysiwyg',
        'attributes'    => array(
            'required'    => 'required'
        ),
        'options' => array(
            'textarea_rows' => 10,
            'teeny' => false, 
        ),
    ));

   
    // Avanzamento del commissario

    $cmb_avanzamento= new_cmb2_box( array(
        'id'           => $prefix . 'box_avanzamento',
        'title'        => __( 'Avanzamento del commissario', 'design_comuni_italia' ),
        'object_types' => array( 'commissario' ),
        'context'      => 'normal',
        'priority'     => 'low',
    ) );

    $cmb_avanzamento->add_field(array(
        'id' => $prefix . 'avanzamento',
        'name'        => __( 'Avanzaqmento del commissario *', 'design_comuni_italia' ),
        'type' => 'text',
    ));

    $cmb_avanzamento->add_field( array(
        'id' => $prefix . 'avanzamento_allegati',
        'name'        => __( 'Allegati Avanzamento del ptogegtto', 'design_comuni_italia' ),
        'type' => 'file_list',
    ) );

    //Atti Legislsativi e Amministativi
    $cmb_allegati = new_cmb2_box( array(
        'id'           => $prefix . 'box_allegtati',
        'title'        => __( 'Atti Leggislativi e Amministrativi', 'design_comuni_italia' ),
        'object_types' => array( 'commissario' ),
        'context'      => 'normal',
        'priority'     => 'low',
    ) );


    $cmb_allegati->add_field( array(
        'id' => $prefix . 'atti',
        'name'        => __( 'Allegati Atti', 'design_comuni_italia' ),
        'desc' => __( 'Elenco di Atti allegati Legislativi e Amministrativi' , 'design_comuni_italia' ),
        'type' => 'file_list',
    ) );

    $cmb_allegati->add_field( array(
        'id' => $prefix . 'allegati',
        'name'        => __( 'Allegati', 'design_comuni_italia' ),
        'desc' => __( 'Elenco di altri allegati collegati con il commissario PNRR' , 'design_comuni_italia' ),
        'type' => 'file_list',
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
        wp_enqueue_script( 'commissario-admin-script', get_template_directory_uri() . '/inc/admin-js/commissario.js' );
}

/**
 * Valorizzo il post content in base al contenuto dei campi custom
 * @param $data
 * @return mixed
 */
function dci_commissario_set_post_content( $data ) {

    if($data['post_type'] == 'commissario') {

        $descrizione_scopo = '';
        if (isset($_POST['_dci_commissario_descrizione_scopo'])) {
            $descrizione_scopo = $_POST['_dci_commissario_descrizione_scopo'];
        }

        $testo_completo = '';
        if (isset($_POST['_dci_commissario_testo_completo'])) {
            $testo_completo = $_POST['_dci_commissario_testo_completo'];
        }

        $content = $descrizione_scopo.'<br>'.$testo_completo;

        $data['post_content'] = $content;
    }

    return $data;
}
add_filter( 'wp_insert_post_data' , 'dci_commissario_set_post_content' , '99', 1 );
