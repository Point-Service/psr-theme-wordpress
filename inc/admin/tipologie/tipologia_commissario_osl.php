

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
