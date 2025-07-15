
// --- Funzioni CMB2 esistenti (rimangono invariate) ---
add_action('cmb2_init', 'dci_add_elemento_trasparenza_metaboxes');
function dci_add_elemento_trasparenza_metaboxes()
{
    $prefix = '_dci_elemento_trasparenza_';

    $cmb_apertura = new_cmb2_box(array(
        'id'            => $prefix . 'box_apertura',
        'title'         => __('Apertura', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'high',
        'closed'        => false,
    ));

    // $cmb_apertura->add_field(array(
    //     'id'            => $prefix . 'data_pubblicazione',
    //     'name'          => __('Data di pubblicazione', 'design_comuni_italia'),
    //     'desc'          => __('Data in cui il post sarà reso visibile pubblicamente.', 'design_comuni_italia'),
    //     'type'          => 'text_date_timestamp',
    //     'date_format'   => 'd-m-Y',
    // ));


    $cmb_apertura->add_field(array(
        'id'            => $prefix . 'descrizione_breve',
        'name'          => __('Descrizione breve ', 'design_comuni_italia'),
        'desc'          => __('Indicare una sintetica descrizione (max 255 caratteri spazi inclusi)', 'design_comuni_italia'),
        'type'          => 'textarea',
        'attributes'    => array(
            'maxlength' => '255',
        ),
    ));

    $cmb_sezione = new_cmb2_box(array(
        'id'            => $prefix . 'box_sezione_post',
        'title'         => __('Seleziona la sezione', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'high',
    ));

    $cmb_sezione->add_field(array(
        'id'                => $prefix . 'tipo_cat_amm_trasp',
        'name'              => __('Categoria Trasparenza *', 'design_comuni_italia'),
        'desc'              => __('Selezionare una categoria per determinare la sezione dell’Amministrazione Trasparente in cui verrà posizionato l’elemento o il link.', 'design_comuni_italia'),
        'type'              => 'taxonomy_radio_hierarchical',
        'taxonomy'          => 'tipi_cat_amm_trasp',
        'show_option_none'  => false,
        'remove_default'    => true,
    ));

        $cmb_corpo = new_cmb2_box(array(
        'id'            => $prefix . 'box_corpo',
        'title'         => __('Corpo', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'low',
    ));

    $cmb_corpo->add_field( array(
        'id' => $prefix . 'descrizione',
        'name'          => __( 'Descrizione', 'design_comuni_italia' ),
        'desc' => __( 'Testo principale del post' , 'design_comuni_italia' ),
        'type' => 'wysiwyg',
        'options' => array(
            'textarea_rows' => 10, 
            'teeny' => false, 
        ),
    ) );

    $cmb_documento = new_cmb2_box(array(
        'id'            => $prefix . 'box_documento',
        'title'         => __('Documento/Link *', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'high',
    ));

    $cmb_documento->add_field(array(
        'id'            => $prefix . 'url',
        'name'          => __('URL', 'design_comuni_italia'),
        'desc'          => __('Link ad una pagina interna o esterna al sito', 'design_comuni_italia'),
        'type'          => 'text_url',
    ));

      // Gruppo per URL multipli
    $cmb_documento->add_field(array(
        'id'            => $prefix . 'url_documento_group',
        'type'          => 'group',
        'description' => __('Aggiungi uno o più link al documento', 'design_comuni_italia'),
        'options'     => array(
            'group_title'   => __('Link Documento {#}', 'design_comuni_italia'),
            'add_button'    => __('Aggiungi link', 'design_comuni_italia'),
            'remove_button' => __('Rimuovi link', 'design_comuni_italia'),
            'sortable'      => true,
            'closed'        => true,
        ),
    ));
    
    // URL del documento
    $cmb_documento->add_group_field($prefix . 'url_documento_group', array(
        'name' => __('URL del documento', 'design_comuni_italia'),
        'id'   => 'url_documento',
        'type' => 'text_url',
    ));
    
    // Titolo del documento
    $cmb_documento->add_group_field($prefix . 'url_documento_group', array(
        'name' => __('Titolo del link', 'design_comuni_italia'),
        'id'   => 'titolo',
        'type' => 'text',
    ));
    
    // Checkbox: apri in nuova scheda
    $cmb_documento->add_group_field($prefix . 'url_documento_group', array(
        'name' => __('Apri in nuova scheda', 'design_comuni_italia'),
        'id'   => 'target_blank',
        'type' => 'checkbox',
    ));


    $cmb_documento->add_field(array(
        'id'            => $prefix . 'file',
        'name'          => __('Documento: Carica più file', 'design_comuni_italia'),
        'desc'          => __('Carica uno o più documenti. Devono essere scaricabili e stampabili.', 'design_comuni_italia'),
        'type'          => 'file_list',
        'preview_size' => array(100, 100),
        'text'          => array(
            'add_upload_files_text' => __('Aggiungi allegati', 'design_comuni_italia'),
            'remove_image_text'     => __('Rimuovi', 'design_comuni_italia'),
            'remove_text'           => __('Rimuovi', 'design_comuni_italia'),
        ),
    ));

      $cmb_extra = new_cmb2_box(array(
        'id'            => $prefix . 'box_extra',
        'title'         => __('Extra', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'side',
        'priority'      => 'high',
    ));

    $cmb_extra->add_field(array(
        'id'            => $prefix . 'open_in_new_tab',
        'name'          => __('Apri in una nuova finestra', 'design_comuni_italia'),
        'desc'          => __('Spuntare per aprire il documento in una nuova finestra del browser', 'design_comuni_italia'),
        'type'          => 'checkbox',
    ));
    $cmb_extra->add_field(array(
        'id'            => $prefix . 'open_direct',
        'name'          => __('Apri link in modo diretto', 'design_comuni_italia'),
        'desc'          => __('Link diretto al link senza visualizzare alcuna pagina intermedia', 'design_comuni_italia'),
        'type'          => 'checkbox',
    ));

    $cmb_post_collegati = new_cmb2_box(array(
        'id'            => $prefix . 'box_postcollegati',
        'title'         => __('Documenti correlati', 'design_comuni_italia'),
        'object_types'  => array('elemento_trasparenza'),
        'context'       => 'normal',
        'priority'      => 'low',
    ));

    $cmb_post_collegati->add_field( array(
        'id' => $prefix . 'post_trasparenza',
        'name'          => __( 'Documenti correlati', 'design_comuni_italia' ),
        'desc' => __( 'Selezionare i documenti di trasparenza correlati a quello attualmente pubblicato.', 'design_comuni_italia' ),
        'type'          => 'pw_multiselect',
        'options' => dci_get_posts_options('elemento_trasparenza'),
        'attributes'    => array(
            'placeholder' =>  __( 'Seleziona i documenti correlati', 'design_comuni_italia' ),
        ),
    ) );

}

add_action('admin_print_scripts-post-new.php', 'dci_elemento_trasparenza_admin_script', 11);
add_action('admin_print_scripts-post.php', 'dci_elemento_trasparenza_admin_script', 11);
// Aggiungi l'hook per la tua pagina di amministrazione personalizzata
add_action('admin_enqueue_scripts', 'dci_enqueue_multipost_transparency_scripts');







function dci_elemento_trasparenza_admin_script()
{
    global $post_type;
    if ($post_type === 'elemento_trasparenza') {
        wp_enqueue_script('elemento-trasparenza-admin-script', get_template_directory_uri() . '/inc/admin-js/elemento_trasparenza.js', array('jquery'), null, true);
    }
}

function dci_enqueue_multipost_transparency_scripts($hook_suffix) {
    // Il $hook_suffix per le pagine di sottomenu è tipicamente 'post_type_page_YOUR_PAGE_SLUG'
    if ( 'elemento_trasparenza_page_dci_transparency_multipost_page' === $hook_suffix ) {
        wp_enqueue_script('multipost-transparency-validation-script', get_template_directory_uri() . '/inc/admin-js/elemento_trasparenza.js', array('jquery'), null, true);
    }
}

add_filter('wp_insert_post_data', 'dci_elemento_trasparenza_set_post_content', 99, 1);
function dci_elemento_trasparenza_set_post_content($data)
{
    if ($data['post_type'] === 'elemento_trasparenza') {
        // personalizzazione futura del content
    }
    return $data;
}

// Questa funzione è rimasta dalla logica precedente (pre-impostare campi in CPT con parametro)
// Puoi mantenerla se hai ancora un pulsante che aggiunge un "Tipo 2" di Elemento Trasparenza
// che NON è la pagina di caricamento multiplo. Altrimenti, puoi rimuoverla se non più necessaria.
add_action( 'load-post-new.php', 'dci_handle_specific_elemento_trasparenza_creation' );
function dci_handle_specific_elemento_trasparenza_creation() {
    if ( 'elemento_trasparenza' !== get_current_screen()->post_type ) {
        return;
    }

    if ( isset( $_GET['tipo_elemento'] ) && $_GET['tipo_elemento'] === '2' ) {
        add_filter( 'cmb2_override_meta_value', 'dci_set_default_cmb2_values_for_type_2', 10, 4 );
    }
}

// Funzione per impostare valori predefiniti per CMB2 (esempio)
function dci_set_default_cmb2_values_for_type_2( $value, $object_id, $field_args, $cmb ) {
    if ( $field_args['id'] === '_dci_elemento_trasparenza_tipo_cat_amm_trasp' ) {
        // Sostituisci 'ID_DELLA_CATEGORIA_PREDEFINITA' con l'ID reale del tuo termine di tassonomia
        $value = 'ID_DELLA_CATEGORIA_PREDEFINITA'; // Ricorda di mettere l'ID effettivo qui!
    }
    return $value;
}
