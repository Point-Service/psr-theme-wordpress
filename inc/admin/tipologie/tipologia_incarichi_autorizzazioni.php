<?php

/**
 * Registra il custom post type "incarichi_autorizzazioni"
 */
add_action('init', 'dci_register_post_type_incarichi_autorizzazioni');
function dci_register_post_type_incarichi_autorizzazioni()
{
    $labels = array(
        'name'               => _x('Incarichi e Autorizzazioni', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name'      => _x('Incarichi e Autorizzazioni', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'            => _x('Aggiungi un Incarichi e Autorizzazione', 'Post Type', 'design_comuni_italia'),
        'add_new_item'       => __('Aggiungi un nuovo Incarico e Autorizzazione', 'design_comuni_italia'),
        'edit_item'          => __('Modifica un Incarico e Autorizzazione', 'design_comuni_italia'),
        'featured_image'     => __('Immagine di riferimento', 'design_comuni_italia'),
    );

    $args = array(
        'label'               => __('Incarico e Autorizzazione', 'design_comuni_italia'),
        'labels'              => $labels,
        'supports'            => array('title', 'author'),
        'hierarchical'        => true,
        'public'              => true,
        'show_in_menu'        => 'edit.php?post_type=elemento_trasparenza', // <‑‑ cambio qui
        //'menu_position'       => 5,
        'menu_icon'           => 'dashicons-media-interactive',
        'has_archive'         => false,
        //'rewrite'             => array('slug' => 'atto-concessione', 'with_front' => false),
        'map_meta_cap'        => true,
        'capabilities' => array(
            'edit_post'             => 'edit_incarichi_autorizzazioni',
            'read_post'             => 'read_incarichi_autorizzazioni',
            'delete_post'           => 'delete_incarichi_autorizzazioni',
            'edit_posts'            => 'edit_incarichi_autorizzazioni',
            'edit_others_posts'     => 'edit_others_incarichi_autorizzazioni',
            'publish_posts'         => 'publish_incarichi_autorizzazioni',
            'read_private_posts'    => 'read_private_incarichi_autorizzazioni',
            'delete_posts'          => 'delete_incarichi_autorizzazioni',
            'delete_private_posts'  => 'delete_private_incarichi_autorizzazioni',
            'delete_published_posts' => 'delete_published_incarichi_autorizzazioni',
            'delete_others_posts' => 'delete_others_incarichi_autorizzazioni',
            'edit_private_posts' => 'edit_private_incarichi_autorizzazioni',
            'edit_published_posts' => 'edit_published_incarichi_autorizzazioni',
            'create_posts'          => 'create_incarichi_autorizzazioni'
        ),
        'description'         => __("Tipologia personalizzata per la pubblicazione degli incarichi e autorizzazioni del Comune.", 'design_comuni_italia'),
    );

    register_post_type('incarichi_autorizzazioni', $args);

    // Rimuove il supporto all'editor
    remove_post_type_support('incarichi_autorizzazioni', 'editor');
}

/**
 * Messaggio informativo sotto il titolo nel backend
 */
add_action('edit_form_after_title', 'dci_incarichi_autorizzazioni_add_content_after_title');
function dci_incarichi_autorizzazioni_add_content_after_title($post)
{
    if ($post->post_type == 'incarichi_autorizzazioni') {
        echo '<span><i>Il <strong>titolo/norma</strong> corrisponde al <strong>titolo dell incarico e autorizzazione</strong>.</i></span><br><br>';
    }
}


add_action('cmb2_init', 'dci_add_incarichi_autorizzazioni_metaboxes');
function dci_add_incarichi_autorizzazioni_metaboxes()
{

   
    $prefix = '_dci_incarichi_autorizzazioni_';

    // Metabox: Apertura
    $cmb_apertura = new_cmb2_box(array(
        'id'           => $prefix . 'box_apertura',
        'title'        => __('Informazioni sul incarichi_autorizzazioni', 'design_comuni_italia'),
        'object_types' => array('incarichi_autorizzazioni'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

     $cmb_apertura->add_field(array(
        'id'                => $prefix . 'tipo_stato',
        'name'              => __('Stato dell\'atto *', 'design_comuni_italia'),
        'desc'              => __('Selezionare la stato dell\'atto', 'design_comuni_italia'),
        'type'              => 'taxonomy_radio_hierarchical',
        'taxonomy'          => 'tipi_stato_bando',
        'show_option_none'  => false,
        'remove_default'    => true,
        //'attributes'  => array('required' => 'required'),
    ));
    
    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'anno_beneficio',
        'name'        => __('Anno Beneficio *', 'design_comuni_italia'),
        'desc'        => __("Seleziona l'anno di beneficio dell'atto", 'design_comuni_italia'),
        'type'        => 'text_date_timestamp',
        'date_format' => 'Y',
        //'attributes'  => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'rag_incarico',
        'name'        => __("Ragione dell'incarico *", 'design_comuni_italia'),
        'desc'        => __("Spoecificare la ragione dell'incarico dell'atto", 'design_comuni_italia'),
        'type'        => 'text',
        //'attributes'  => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'importo',
        'name'        => __("Importo *", 'design_comuni_italia'),
        'desc'        => __('Indica l’importo dell\'atto', 'design_comuni_italia'),
        'type'        => 'text',
        //'attributes'  => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'responsabile',
        'name'        => __("Responsabile *", 'design_comuni_italia'),
        'desc'        => __('Indica il Responsabile dell\'atto', 'design_comuni_italia'),
        'type'        => 'text',
        //'attributes'  => array('required' => 'required'),
    ));

    // Metabox: Dettagli
    $cmb_dettagli = new_cmb2_box(array(
        'id'           => $prefix . 'box_dettagli',
        'title'        => __('Dettagli  Beneficiario', 'design_comuni_italia'),
        'object_types' => array('atto_concessione'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

     $cmb_dettagli->add_field( array(
        'id' => $prefix . 'descrizione_breve',
        'name'        => __( 'Descrizione breve', 'design_comuni_italia' ),
        'desc' => __( 'Descrizione sintentica dell\'atto, inferiore a 255 caratteri' , 'design_comuni_italia' ),
        'type' => 'textarea',
        //'attributes'    => array('maxlength'  => '255'),
    ) );

        $cmb_dettagli->add_field(array(
        'name' => __('Ragione sociale *', 'design_comuni_italia'),
        'id'   => $prefix . 'ragione_sociale', // Ho aggiunto il prefix anche qui per coerenza
        'type' => 'text',
        //'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'name' => __('Codice fiscale/P.Iva *', 'design_comuni_italia'),
        'id'   => $prefix . 'codice_fiscale', // Ho aggiunto il prefix anche qui per coerenza
        'type' => 'text',
       // 'attributes'  => array('required' => 'required'),
));

    //DOCUMENTI
    $cmb_documenti = new_cmb2_box( array(
        'id'           => $prefix . 'box_documenti',
        'title'        => __( 'Documenti', 'design_comuni_italia' ),
        'object_types' => array('incarichi_autorizzazioni'),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

     $cmb_documenti->add_field( array(
        'id' => $prefix . 'allegati',
        'name'        => __( 'Allegati', 'design_comuni_italia' ),
        'desc' => __( 'Elenco di documenti allegati al Atto di Concessione' , 'design_comuni_italia' ),
        'type' => 'file_list',
    ) );
}



add_filter('wp_insert_post_data', 'dci_incarichi_autorizzazioni_set_post_content', 99, 1);
function dci_incarichi_autorizzazioni_set_post_content($data)
{
    if ($data['post_type'] == 'incarichi_autorizzazioni') {
        $descrizione_breve = isset($_POST['_dci_incarichi_autorizzazioni_descrizione_breve']) ? $_POST['_dci_incarichi_autorizzazioni_descrizione_breve'] : '';
        $testo_completo    = isset($_POST['_dci_incarichi_autorizzazioni_testo_completo']) ? $_POST['_dci_incarichi_autorizzazioni_testo_completo'] : '';

        $data['post_content'] = $descrizione_breve . '<br>' . $testo_completo;
    }

    return $data;
}
