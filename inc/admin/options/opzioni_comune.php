<?php

function dci_register_comune_options(){
    $prefix = '';

    /**
     * Opzioni di base
     * nome Comune, Regione, informazioni essenziali
     */
    $args = array(
        'id'           => 'dci_options_configurazione',
        'title'        => esc_html__( 'Configurazione', 'design_comuni_italia' ),
        'object_types' => array( 'options-page' ),
        'option_key'   => 'dci_options',
        'tab_group'    => 'dci_options',
        'tab_title'    => __('Configurazione Comune', "design_comuni_italia"),
        'capability'   => 'manage_options',
        'position'     => 2, // Menu position. Only applicable if 'parent_slug' is left empty.
        'icon_url'     => 'dashicons-admin-tools', // Menu icon. Only applicable if 'parent_slug' is left empty.
    );

    // 'tab_group' property is supported in > 2.4.0.
    if ( version_compare( CMB2_VERSION, '2.4.0' ) ) {
        $args['display_cb'] = 'dci_options_display_with_tabs';
    }

    $header_options = new_cmb2_box( $args );

    // Configurazione delle informazioni di base
    $header_options->add_field( array(
        'id' => $prefix . 'home_istruzioni',
        'name'        => __( 'Configurazione Comune', 'design_comuni_italia' ),
        'desc' => __( 'Area di configurazione delle informazioni di base' , 'design_comuni_italia' ),
        'type' => 'title',
    ));

    // Altri campi (quelli che hai già nel codice)
    $header_options->add_field( array(
        'id'    => $prefix . 'area_riservata',
        'name'  => __('Area Riservata', 'design_comuni_italia' ),
        'desc'  => __( 'Url per agganciare il tasto ad uno sportello esterno di autenticazione con CNS o SPID (Se aggiunto un url il tasto verrà automaticamente indirizzato al link da te inserito.)'),
        'type'  => 'text'
    ));   

    $header_options->add_field( array(
        'id'    => $prefix . 'nome_comune',
        'name'  => __( 'Nome del Comune *', 'design_comuni_italia' ),
        'desc'  => __( 'Il Nome del Comune' , 'design_comuni_italia' ),
        'type'  => 'text',
        'attributes' => array(
            'required' => 'required'
        ),
    ));

    // Altri campi... (salta la parte già presente)

    // Aggiungi il campo per visualizzare il percorso attuale del template
    $header_options->add_field( array(
        'id'    => $prefix . 'percorso_template',
        'name'  => __( 'Percorso Template Attuale', 'design_comuni_italia' ),
        'desc'  => __( 'Visualizza il percorso corrente del template', 'design_comuni_italia' ),
        'type'  => 'text',
        'default' => get_template_directory_uri(), // Visualizza il percorso del template
        'attributes' => array(
            'readonly' => 'readonly', // Rendi il campo di sola lettura
        ),
    ));

    // Aggiungi il pulsante per ricalcolare il percorso
    $header_options->add_field( array(
        'id'   => $prefix . 'ricalcola_percorso',
        'name' => __('Ricalcola Percorso Template', 'design_comuni_italia'),
        'desc' => __('Clicca per ricalcolare e aggiornare il percorso del template.', 'design_comuni_italia'),
        'type' => 'button',
        'attributes' => array(
            'class' => 'button ricalcola-percorso-button',
        ),
    ));

    // Inserisci il JavaScript per il pulsante
    add_action('admin_footer', 'dci_ricalcola_percorso_script');
    function dci_ricalcola_percorso_script() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Aggiungi un gestore dell'evento clic sul pulsante
                $('.ricalcola-percorso-button').on('click', function() {
                    var percorsoTemplate = '<?php echo get_template_directory_uri(); ?>'; // Calcola il percorso

                    // Imposta il campo con il nuovo percorso
                    $('#<?php echo $prefix; ?>percorso_template').val(percorsoTemplate);
                });
            });
        </script>
        <?php
    }

    // Altri campi (per i link, email e ecc.)

    $header_options->add_field( array(
        'id'    => $prefix . 'dichiarazioneaccessibilita',
        'name'  => __( 'Dichiarazione Accessibilità', 'design_comuni_italia' ),
        'desc'  => __( 'Inserisci qui il link della dichiarazione di accessibilità', 'design_comuni_italia' ),
        'type'  => 'text'
    ));

    $header_options->add_field(array(
        'id'    => $prefix . 'firma_nostra',
        'name'  => __('Nascondi la nostra firma.', 'design_comuni_italia'),
        'desc'  => __('Opzione per nascondere dal footer la nostra firma.', 'design_comuni_italia'),
        'type'  => 'radio_inline',
        'default' => 'false',
        'options' => array(
            'true' => __('Si', 'design_comuni_italia'),
            'false' => __('No', 'design_comuni_italia'),
        ),
        'attributes' => array(
            'data-conditional-value' => "false",
        ),
    ));
}

add_action('cmb2_admin_init', 'dci_register_comune_options');

