<?php
 function dci_register_comune_options() {
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

    // Resto delle tue opzioni di configurazione già esistenti
    $header_options->add_field( array(
        'id' => $prefix . 'home_istruzioni',
        'name'        => __( 'Configurazione Comune', 'design_comuni_italia' ),
        'desc' => __( 'Area di configurazione delle informazioni di base' , 'design_comuni_italia' ),
        'type' => 'title',
    ) );


    // Aggiungi il campo per visualizzare il percorso attuale
    $header_options->add_field( array(
        'id'    => $prefix . 'percorso_template',
        'name'  => __( 'Percorso Template Attuale', 'design_comuni_italia' ),
        'desc'  => __( 'Visualizza il percorso corrente del template', 'design_comuni_italia' ),
        'type'  => 'text',
        'default' => get_template_directory_uri(), // Visualizza il percorso del template
        'attributes' => array(
            'readonly' => 'readonly', // Rendi il campo di sola lettura
        ),
    ) );

    // Aggiungi un campo per un pulsante che aggiorna il percorso
    $header_options->add_field( array(
        'id'   => $prefix . 'aggiorna_percorso_button',
        'name' => __('Aggiorna Percorso', 'design_comuni_italia'),
        'desc' => __('Clicca per aggiornare il percorso del template', 'design_comuni_italia'),
        'type' => 'button',
        'attributes' => array(
            'onclick' => 'updateTemplatePath();', // Funzione JavaScript da richiamare
        ),
    ) );

    // Funzione JavaScript da utilizzare per l'aggiornamento del percorso
    add_action( 'admin_footer', function() {
        ?>
        <script type="text/javascript">
            function updateTemplatePath() {
                // Aggiungi qui il percorso aggiornato
                var nuovoPercorso = '<?php echo get_template_directory_uri(); ?>';

                // Se il campo esiste, aggiornalo
                var campoPercorso = document.querySelector('[name="dci_options[percorso_template]"]');
                if (campoPercorso) {
                    campoPercorso.value = nuovoPercorso;
                }

                alert('Percorso template aggiornato a: ' + nuovoPercorso);
            }
        </script>
        <?php
    });
}
add_action('cmb2_admin_init', 'dci_register_comune_options');
