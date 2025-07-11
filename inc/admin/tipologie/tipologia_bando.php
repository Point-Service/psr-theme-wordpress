<?php
/**
 * Registra il custom post type "Bando"
 */

add_action('init', 'dci_register_post_type_bando', 21);
function dci_register_post_type_bando()
{
    $labels = array(
        'name'               => _x('Bandi di Gara', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name'      => _x('Bando di Gara', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'            => _x('Aggiungi un Bando', 'Post Type', 'design_comuni_italia'),
        'add_new_item'       => __('Aggiungi un nuovo Bando di Gara', 'design_comuni_italia'),
        'edit_item'          => __('Modifica Bando di Gara', 'design_comuni_italia'),
        'featured_image'     => __('Immagine di riferimento', 'design_comuni_italia'),
    );

    $args = array(
        'label'               => __('Bando di Gara', 'design_comuni_italia'),
        'labels'              => $labels,
        'supports'            => array('title', 'author'),
        'hierarchical'        => false,
        'public'              => true,
        'show_in_menu'        => 'edit.php?post_type=elemento_trasparenza',
        'menu_position'       => 21,
        'menu_icon'           => 'dashicons-media-interactive',
        'has_archive'         => false,
        'rewrite'             => array('slug' => 'bandi', 'with_front' => false),
        'map_meta_cap'        => true,
        'capabilities' => array(
            'edit_post'             => 'edit_bando',
            'read_post'             => 'read_bando',
            'delete_post'           => 'delete_bando',
            'edit_posts'            => 'edit_bandi',
            'edit_others_posts'     => 'edit_others_bandi',
            'publish_posts'         => 'publish_bandi',
            'read_private_posts'    => 'read_private_bandi',
            'delete_posts'          => 'delete_bandi',
            'delete_private_posts'  => 'delete_private_bandi',
            'delete_published_posts'=> 'delete_published_bandi',
            'delete_others_posts'   => 'delete_others_bandi',
            'edit_private_posts'    => 'edit_private_bandi',
            'edit_published_posts'  => 'edit_published_bandi',
            'create_posts'          => 'create_bandi'
        ),
        'description'         => __("Tipologia personalizzata per la pubblicazione dei bandi di gara del Comune.", 'design_comuni_italia'),
    );

    register_post_type('bando', $args);

    // Rimuove il supporto all'editor classico (content)
    remove_post_type_support('bando', 'editor');
}

/**
 * Pulsanti extra nella schermata elenco Bandi di Gara:
 */
add_action( 'admin_head-edit.php', 'dci_bando_extra_buttons' );
function dci_bando_extra_buttons() {

    $screen = get_current_screen();
    if ( $screen->post_type !== 'bando' || $screen->base !== 'edit' ) {
        return;
    }

    $extra_buttons = [
        [
            'id'   => 'dci-extra-tax-stato',
            'text' => __( 'Tipi stato bandi', 'design_comuni_italia' ),
            'href' => admin_url( 'edit-tags.php?taxonomy=tipi_stato_bando&post_type=bando' ),
        ],
        [
            'id'   => 'dci-extra-tax-procedura',
            'text' => __( 'Tipi procedura contraente', 'design_comuni_italia' ),
            'href' => admin_url( 'edit-tags.php?taxonomy=tipi_procedura_contraente&post_type=bando' ),
        ],
    ];
    ?>
    <style>
        .wrap .page-title-action {
            margin-right: 8px;
        }
        .dci-extra-btn {
            margin-left: 8px;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const stdBtn = document.querySelector('.wrap .page-title-action');
            if (!stdBtn) return;

            stdBtn.classList.add('button', 'button-primary', 'button-large');

            <?php foreach ( $extra_buttons as $btn ) : ?>
                (function() {
                    const link = document.createElement('a');
                    link.id        = '<?php echo esc_js( $btn['id'] ); ?>';
                    link.className = 'button button-primary button-large dci-extra-btn';
                    link.href      = '<?php echo esc_url( $btn['href'] ); ?>';
                    link.textContent = '<?php echo esc_js( $btn['text'] ); ?>';
                    stdBtn.after(link);
                })();
            <?php endforeach; ?>
        });
    </script>
    <?php
}

/**
 * Messaggio informativo sotto il titolo nel backend
 */
add_action('edit_form_after_title', 'dci_bando_add_content_after_title');
function dci_bando_add_content_after_title($post)
{
    if ($post->post_type === 'bando') {
        echo '<span><i>Il <strong>titolo</strong> corrisponde al <strong>titolo del bando di gara</strong>.</i></span><br><br>';
    }
}

/**
 * CMB2 Metaboxes per il CPT "Bando"
 */
add_action('cmb2_init', 'dci_add_bando_metaboxes');
function dci_add_bando_metaboxes()
{
    $prefix = '_dci_bando_';

    // Metabox: Apertura
    $cmb_apertura = new_cmb2_box(array(
        'id'           => $prefix . 'box_apertura',
        'title'        => __('Informazioni sul Bando', 'design_comuni_italia'),
        'object_types' => array('bando'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));
    
    $cmb_apertura->add_field(array(
        'id'                => $prefix . 'tipo_stato_bando',
        'name'              => __('Stato del Bando *', 'design_comuni_italia'),
        'desc'              => __('Selezionare la stato del bando.', 'design_comuni_italia'),
        'type'              => 'taxonomy_radio_hierarchical',
        'taxonomy'          => 'tipi_stato_bando',
        'show_option_none'  => false,
        'remove_default'    => true,
        'attributes'  => array('required' => 'required'),
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'data_inizio',
        'name'        => __('Data di Pubblicazione', 'design_comuni_italia'),
        'desc'        => __('Seleziona la data in cui il bando è stato pubblicato.', 'design_comuni_italia'),
        'type'        => 'text_date_timestamp',
        'date_format' => 'd-m-Y',
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'data_fine',
        'name'        => __('Data di Scadenza', 'design_comuni_italia'),
        'desc'        => __('Seleziona la data in cui scade il bando.', 'design_comuni_italia'),
        'type'        => 'text_date_timestamp',
        'date_format' => 'd-m-Y',
    ));

    $cmb_apertura->add_field(array(
        'id'          => $prefix . 'oggetto',
        'name'        => __('Oggetto del Bando *', 'design_comuni_italia'),
        'desc'        => __("Inserisci una descrizione sintetica dell'oggetto del bando.", 'design_comuni_italia'),
        'type'        => 'wysiwyg',
        'attributes'  => array('required' => 'required'),
        'options'     => array(
            'textarea_rows' => 8,
            'teeny'         => false,
        ),
    ));

    // Metabox: Dettagli Economici
    $cmb_dettagli = new_cmb2_box(array(
        'id'           => $prefix . 'box_dettagli',
        'title'        => __('Dettagli Economici', 'design_comuni_italia'),
        'object_types' => array('bando'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_dettagli->add_field(array(
        'id'          => $prefix . 'importo_aggiudicazione',
        'name'        => __('Importo di Aggiudicazione *', 'design_comuni_italia'),
        'desc'        => __('Indica l’importo finale con cui è stato aggiudicato il bando.', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'id'          => $prefix . 'importo_somme_liquidate',
        'name'        => __('Importo delle Somme Liquidate *', 'design_comuni_italia'),
        'desc'        => __('Indica l’importo delle somme effettivamente liquidate.', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'id'          => $prefix . 'struttura_proponente',
        'name'        => __('Struttura Proponente *', 'design_comuni_italia'),
        'desc'        => __('Indica la struttura o l’ufficio proponente.', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'id'          => $prefix . 'cig',
        'name'        => __('CIG *', 'design_comuni_italia'),
        'desc'        => __('Indica la CIG', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'id'          => $prefix . 'cf_sa',
        'name'        => __('Codice fiscale SA *', 'design_comuni_italia'),
        'desc'        => __('Indica il Codice fiscale della stazione appaltante', 'design_comuni_italia'),
        'type'        => 'text',
        'attributes'  => array('required' => 'required'),
    ));

    $cmb_dettagli->add_field(array(
        'id'                => $prefix . 'tipo_sceleta_contraente',
        'name'              => __('Scelta del Contraente *', 'design_comuni_italia'),
        'desc'              => __('Seleziona la tipologia di scelta del contraente', 'design_comuni_italia'),
        'type'              => 'taxonomy_radio_hierarchical',
        'taxonomy'          => 'tipi_procedura_contraente',
        'show_option_none'  => false,
        'remove_default'    => true,
        'attributes'        => array('required' => 'required'),
    ));

    // Metabox: Operatori Economici
    $cmb_operatore = new_cmb2_box(array(
        'id'           => $prefix . 'box_operatore',
        'title'        => __('Operatori Economici', 'design_comuni_italia'),
        'object_types' => array('bando'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $group_operatore = $cmb_operatore->add_field(array(
        'id'          => $prefix . 'gruppo_operatori',
        'type'        => 'group',
        'repeatable'  => true,
        'options'     => array(
            'group_title'   => __('Operatore {#}', 'design_comuni_italia'),
            'add_button'    => __('Aggiungi Operatore', 'design_comuni_italia'),
            'remove_button' => __('Rimuovi Operatore', 'design_comuni_italia'),
            'sortable'      => true,
        ),
    ));

    $cmb_operatore->add_group_field($group_operatore, array(
        'id'          => 'operatore_nome',
        'name'        => __('Nome Operatore', 'design_comuni_italia'),
        'type'        => 'text',
    ));

    $cmb_operatore->add_group_field($group_operatore, array(
        'id'          => 'operatore_cf',
        'name'        => __('Codice Fiscale', 'design_comuni_italia'),
        'type'        => 'text',
    ));

    $cmb_operatore->add_group_field($group_operatore, array(
        'id'          => 'operatore_ruolo',
        'name'        => __('Ruolo', 'design_comuni_italia'),
        'type'        => 'text',
    ));

    // Metabox: Aggiudicatari
    $cmb_aggiudicatari = new_cmb2_box(array(
        'id'           => $prefix . 'box_aggiudicatari',
        'title'        => __('Aggiudicatari', 'design_comuni_italia'),
        'object_types' => array('bando'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $group_aggiudicatari = $cmb_aggiudicatari->add_field(array(
        'id'          => $prefix . 'gruppo_aggiudicatari',
        'type'        => 'group',
        'repeatable'  => true,
        'options'     => array(
            'group_title'   => __('Aggiudicatario {#}', 'design_comuni_italia'),
            'add_button'    => __('Aggiungi Aggiudicatario', 'design_comuni_italia'),
            'remove_button' => __('Rimuovi Aggiudicatario', 'design_comuni_italia'),
            'sortable'      => true,
        ),
    ));

    $cmb_aggiudicatari->add_group_field($group_aggiudicatari, array(
        'id'          => 'aggiudicatario_nome',
        'name'        => __('Nome Aggiudicatario', 'design_comuni_italia'),
        'type'        => 'text',
    ));

    $cmb_aggiudicatari->add_group_field($group_aggiudicatari, array(
        'id'          => 'aggiudicatario_cf',
        'name'        => __('Codice Fiscale', 'design_comuni_italia'),
        'type'        => 'text',
    ));

    // Metabox: Documenti Allegati
    $cmb_allegati = new_cmb2_box(array(
        'id'           => $prefix . 'box_allegati',
        'title'        => __('Documenti Allegati', 'design_comuni_italia'),
        'object_types' => array('bando'),
        'context'      => 'normal',
        'priority'     => 'high',
    ));

    $cmb_allegati->add_field(array(
        'id'          => $prefix . 'documento_bando',
        'name'        => __('Documento del Bando', 'design_comuni_italia'),
        'desc'        => __('Carica il documento relativo al bando di gara.', 'design_comuni_italia'),
        'type'        => 'file',
        'options'     => array(
            'url' => false,
        ),
    ));
}

/**
 * Aggiunge uno script JS specifico per il CPT "bando" in admin
 */
add_action('admin_enqueue_scripts', 'dci_bando_scripts');
function dci_bando_scripts($hook)
{
    global $post_type;
    if ($post_type === 'bando') {
        wp_enqueue_script('dci_bando_js', plugin_dir_url(__FILE__) . 'js/bando.js', array('jquery'), '1.0', true);
    }
}

/**
 * Validazione lato server su salvataggio post per i campi obbligatori
 */
add_action('save_post_bando', 'dci_bando_validate_save', 10, 3);
function dci_bando_validate_save($post_id, $post, $update)
{
    // Non fare nulla se autosave o revisione
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (wp_is_post_revision($post_id)) return;

    // Controlla capability
    if (!current_user_can('edit_post', $post_id)) return;

    $prefix = '_dci_bando_';

    $required_fields = [
        $prefix . 'tipo_stato_bando',
        $prefix . 'oggetto',
        $prefix . 'importo_aggiudicazione',
        $prefix . 'importo_somme_liquidate',
        $prefix . 'struttura_proponente',
        $prefix . 'cig',
        $prefix . 'cf_sa',
        $prefix . 'tipo_sceleta_contraente',
    ];

    $errors = [];

    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $errors[] = sprintf(__('Il campo %s è obbligatorio.', 'design_comuni_italia'), $field);
        }
    }

    if (!empty($errors)) {
        // Aggiungi un admin notice (usiamo transient come workaround)
        set_transient('dci_bando_save_errors_' . $post_id, $errors, 30);

        // Ferma il salvataggio? WordPress non permette annullare facilmente il salvataggio qui,
        // ma puoi considerare rimuovere i meta o altre logiche.

        // Oppure fare un redirect con errore, ma qui lasciamo solo il messaggio.
    }

    // Sanitizza e salva i campi obbligatori per sicurezza
    foreach ($required_fields as $field) {
        if (isset($_POST[$field])) {
            if (is_array($_POST[$field])) {
                $sanitized = array_map('sanitize_text_field', $_POST[$field]);
            } else {
                $sanitized = sanitize_text_field(wp_unslash($_POST[$field]));
            }
            update_post_meta($post_id, $field, $sanitized);
        }
    }
}

/**
 * Mostra errori di salvataggio nel backend se presenti
 */
add_action('admin_notices', 'dci_bando_admin_notices');
function dci_bando_admin_notices()
{
    global $post;

    if (!$post || $post->post_type !== 'bando') {
        return;
    }

    $errors = get_transient('dci_bando_save_errors_' . $post->ID);
    if ($errors && is_array($errors)) {
        foreach ($errors as $error) {
            echo '<div class="notice notice-error is-dismissible"><p>' . esc_html($error) . '</p></div>';
        }
        delete_transient('dci_bando_save_errors_' . $post->ID);
    }
}

/**
 * Aggiorna il contenuto del post con due campi personalizzati concatenati
 */
add_filter('wp_insert_post_data', 'dci_bando_custom_content', 10, 2);
function dci_bando_custom_content($data, $postarr)
{
    if ($data['post_type'] === 'bando') {
        $prefix = '_dci_bando_';

        $importo = get_post_meta($postarr['ID'], $prefix . 'importo_aggiudicazione', true);
        $oggetto = get_post_meta($postarr['ID'], $prefix . 'oggetto', true);

        $importo = sanitize_text_field($importo);
        $oggetto = sanitize_text_field(wp_strip_all_tags($oggetto));

        $data['post_content'] = $importo . ' - ' . $oggetto;
    }
    return $data;
}

