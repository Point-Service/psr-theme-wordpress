<?php

/**
 * Definisce il post type Notizia
 */
add_action( 'init', 'dci_register_post_type_notizia');
function dci_register_post_type_notizia() {

    $labels = array(
        'name'          => _x( 'Notizie', 'Post Type General Name', 'design_comuni_italia' ),
        'singular_name' => _x( 'Notizia', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new'       => _x( 'Aggiungi una Notizia', 'Post Type Singular Name', 'design_comuni_italia' ),
        'add_new_item'  => _x( 'Aggiungi una nuova Notizia', 'Post Type Singular Name', 'design_comuni_italia' ),
        'edit_item'       => _x( 'Modifica la Notizia', 'Post Type Singular Name', 'design_comuni_italia' ),
        'featured_image' => __( 'Immagine di riferimento', 'design_comuni_italia' ),
    );
    $args   = array(
        'label'         => __( 'Notizia', 'design_comuni_italia' ),
        'labels'        => $labels,
        'supports'      => array( 'title', 'editor', 'author', 'thumbnail'),
        'hierarchical'  => false,
        'public'        => true,
        'menu_position' => 5,
        'menu_icon'     => 'dashicons-media-interactive',
        'has_archive'   => false,
        'rewrite' => array('slug' => 'tipi_notizia/notizie','with_front' => false),
        'capability_type' => array('notizia', 'notizie'),
        'map_meta_cap'    => true,
        'description'    => __( "Tipologia che struttura le informazioni relative a agli aggiornamenti d un comune", 'design_comuni_italia' ),
    );
    register_post_type('notizia', $args );

    remove_post_type_support( 'notizia', 'editor');
}

/**
 * Aggiungo label sotto il titolo
 */
add_action( 'edit_form_after_title', 'dci_notizia_add_content_after_title' );
function dci_notizia_add_content_after_title($post) {
    if($post->post_type == "notizia")
        _e('<span><i>il <b>Titolo</b> è il <b>Titolo della News o del Comunicato</b>.</i></span><br><br>', 'design_comuni_italia' );
}

add_action( 'cmb2_init', 'dci_add_notizia_metaboxes' );
function dci_add_notizia_metaboxes() {
    $prefix = '_dci_notizia_';

    //argomenti
    $cmb_argomenti = new_cmb2_box( array(
        'id'           => $prefix . 'box_argomenti',
        'title'        => __( 'Argomenti *', 'design_comuni_italia' ),
        'object_types' => array( 'notizia' ),
        'context'      => 'side',
        'priority'     => 'high',
    ) );

    $cmb_argomenti->add_field( array(
        'id' => $prefix . 'argomenti',
        'type'             => 'taxonomy_multicheck_hierarchical',
        'taxonomy'       => 'argomenti',
        'show_option_none' => false,
        'remove_default' => 'true',
    ) );

    // APERTURA
    $cmb_apertura = new_cmb2_box( array(
        'id'           => $prefix . 'box_apertura',
        'title'        => __( 'Apertura', 'design_comuni_italia' ),
        'object_types' => array( 'notizia' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );

    $cmb_apertura->add_field( array(
        'id' => $prefix . 'tipo_notizia',
        'name'        => __( 'Tipo di notizia *', 'design_comuni_italia' ),
        'type'             => 'taxonomy_radio_hierarchical',
        'taxonomy'       => 'tipi_notizia',
        'show_option_none' => false,
        'remove_default' => 'true',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );

    // Campo Descrizione Breve
    $cmb_apertura->add_field( array(
        'id' => $prefix . 'descrizione_breve',
        'name'        => __( 'Descrizione breve *', 'design_comuni_italia' ),
        'desc' => __( 'Descrizione sintentica della notizia, inferiore a 255 caratteri' , 'design_comuni_italia' ),
        'type' => 'textarea',
        'attributes'    => array(
            'maxlength'  => '255',
            'required'    => 'required'
        ),
    ) );

    // Corso
    $cmb_corpo = new_cmb2_box( array(
        'id'           => $prefix . 'box_corpo',
        'title'        => __( 'Corpo', 'design_comuni_italia' ),
        'object_types' => array( 'notizia' ),
        'context'      => 'normal',
        'priority'     => 'high',
    ) );
    $cmb_corpo->add_field( array(
        'id' => $prefix . 'testo_completo',
        'name'        => __( 'Testo completo della notizia *', 'design_comuni_italia' ),
        'desc' => __( 'Testo principale della notizia' , 'design_comuni_italia' ),
        'type' => 'wysiwyg',
        'attributes'    => array(
            'required'    => 'required'
        ),
    ) );
}

/**
 * Aggiungi script JS per il controllo in tempo reale
 */
add_action( 'admin_enqueue_scripts', 'dci_enqueue_admin_scripts' );
function dci_enqueue_admin_scripts($hook) {
    if ('post.php' !== $hook && 'post-new.php' !== $hook) return;
    global $post;
    if ($post && $post->post_type === 'notizia') {
        wp_enqueue_script('notizia-admin-validation', get_template_directory_uri() . '/js/notizia-validation.js', array('jquery'), null, true);
    }
}

/**
 * JavaScript per il controllo delle lettere maiuscole consecutive
 */
function dci_notizia_admin_script() {
    ?>
    <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Funzione per verificare se ci sono due lettere maiuscole consecutive
            function hasConsecutiveUppercase(text) {
                return /[A-Z]{2,}/.test(text);
            }

            // Messaggio di errore per la validazione
            var errorMessage = $('<p style="color: red; font-weight: bold;">Errore: non possono esserci due lettere maiuscole consecutive.</p>');

            // Controlla il campo titolo
            $('#title').on('input', function() {
                var title = $(this).val();
                if (hasConsecutiveUppercase(title)) {
                    if ($('#title-error').length === 0) {
                        $(this).after(errorMessage.clone().attr('id', 'title-error'));
                    }
                } else {
                    $('#title-error').remove();
                }
            });

            // Controlla il campo descrizione breve
            $('#_dci_notizia_descrizione_breve').on('input', function() {
                var description = $(this).val();
                if (hasConsecutiveUppercase(description)) {
                    if ($('#description-error').length === 0) {
                        $(this).after(errorMessage.clone().attr('id', 'description-error'));
                    }
                } else {
                    $('#description-error').remove();
                }
            });
        });
    </script>
    <?php
}

/**
 * Valorizzo il post content in base al contenuto dei campi custom
 * @param $data
 * @return mixed
 */
function dci_notizia_set_post_content( $data ) {

    if($data['post_type'] == 'notizia') {

        $descrizione_breve = '';
        if (isset($_POST['_dci_notizia_descrizione_breve'])) {
            $descrizione_breve = $_POST['_dci_notizia_descrizione_breve'];
        }

        $testo_completo = '';
        if (isset($_POST['_dci_notizia_testo_completo'])) {
            $testo_completo = $_POST['_dci_notizia_testo_completo'];
        }

        $content = $descrizione_breve.'<br>'.$testo_completo;

        $data['post_content'] = $content;
    }

    return $data;
}
add_filter( 'wp_insert_post_data' , 'dci_notizia_set_post_content' , '99', 1 );
?>
