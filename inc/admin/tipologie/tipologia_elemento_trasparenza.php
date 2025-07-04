<?php
// CODICE ORIGINALE (NON MODIFICATO)

add_action('init', 'dci_register_post_type_elemento_trasparenza');
function dci_register_post_type_elemento_trasparenza()
{
    $labels = array(
        'name'                  => _x('Amministrazione Trasparente', 'Post Type General Name', 'design_comuni_italia'),
        'singular_name'         => _x('Amministrazione Trasparente', 'Post Type Singular Name', 'design_comuni_italia'),
        'add_new'               => _x('Aggiungi un Elemento Trasparenza', 'design_comuni_italia'),
        'add_new_item'          => _x('Aggiungi un Elemento Trasparenza', 'design_comuni_italia'), 
        'edit_item'             => _x('Modifica l\'Elemento Trasparenza', 'design_comuni_italia'),
        'new_item'              => __('Nuovo Elemento Trasparenza', 'design_comuni_italia'),
        'menu_name'             => __('Amministrazione Trasparente', 'design_comuni_italia'),
    );

    $args = array(
        'label'                 => __('Elemento Trasparenza', 'design_comuni_italia'),
        'labels'                => $labels,
        'supports'              => array('title', 'author'),
        'taxonomies'            => array('tipologia'),
        'hierarchical'          => false,
        'public'                => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-archive',
        'has_archive'           => false,
        'capability_type'       => array('elemento_trasparenza', 'elementi_trasparenza'),
        'map_meta_cap'          => true,
        'capabilities' => array(
            'edit_post'             => 'edit_elemento_trasparenza',
            'read_post'             => 'read_elemento_trasparenza',
            'delete_post'           => 'delete_elemento_trasparenza',
            'edit_posts'            => 'edit_elementi_trasparenza',
            'edit_others_posts'     => 'edit_others_elementi_trasparenza',
            'publish_posts'         => 'publish_elementi_trasparenza',
            'read_private_posts'    => 'read_private_elementi_trasparenza',
            'delete_posts'          => 'delete_elementi_trasparenza',
            'delete_private_posts'  => 'delete_private_elementi_trasparenza',
            'delete_published_posts'=> 'delete_published_elementi_trasparenza',
            'delete_others_posts'   => 'delete_others_elementi_trasparenza',
            'edit_private_posts'    => 'edit_private_elementi_trasparenza',
            'edit_published_posts'  => 'edit_published_elementi_trasparenza',
            'create_posts'          => 'create_elementi_trasparenza'
        ),
        'description'           => __('Struttura delle informazioni relative utili a presentare un Elemento Trasparenza', 'design_comuni_italia'),
    );

    register_post_type('elemento_trasparenza', $args);

    remove_post_type_support('elemento_trasparenza', 'editor');
}

add_action('edit_form_after_title', 'dci_elemento_trasparenza_add_content_after_title');
function dci_elemento_trasparenza_add_content_after_title($post)
{
    if ($post->post_type === 'elemento_trasparenza') {
        echo "<span><i>Il <b>Titolo</b> è il <b>Nome del elemento dell'amministrazione trasparente</b>.</i></span><br><br>";
    }
}

add_action('pre_get_posts', 'dci_limita_elementi_trasparenza_per_categoria_e_ruolo');
function dci_limita_elementi_trasparenza_per_categoria_e_ruolo($query) {
    if (!is_admin() || !$query->is_main_query()) return;

    if ($query->get('post_type') !== 'elemento_trasparenza') return;

    $user = wp_get_current_user();

    // Esempio ruolo -> categoria ID (modifica secondo i tuoi ruoli e categorie)
    if (in_array('redattore_bilanci', $user->roles)) {
        $query->set('tax_query', array(
            array(
                'taxonomy' => 'tipi_cat_amm_trasp',
                'field'    => 'term_id',
                'terms'    => array(123), // cambia con ID categoria corretta
            ),
        ));
    }

    if (in_array('redattore_bandigare', $user->roles)) {
        $query->set('tax_query', array(
            array(
                'taxonomy' => 'tipi_cat_amm_trasp',
                'field'    => 'term_id',
                'terms'    => array(456), // cambia con ID categoria corretta
            ),
        ));
    }
}

// --------------
// AGGIUNTA CARICAMENTO MULTIPLO - NON MODIFICARE IL CODICE SOPRA
// --------------

add_action('admin_menu', 'dci_add_transparency_multipost_page');
function dci_add_transparency_multipost_page() {
    add_submenu_page(
        'edit.php?post_type=elemento_trasparenza',
        __('Aggiungi Multi-Elemento Trasparenza', 'design_comuni_italia'), 
        __('Multi-Elemento', 'design_comuni_italia'),
        'create_elementi_trasparenza',              
        'dci_transparency_multipost_page',              
        'dci_render_transparency_multipost_page'
    );
}

function dci_render_transparency_multipost_page() {
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <p><?php _e('Questa pagina ti permette di creare rapidamente più Elementi di Amministrazione Trasparente.', 'design_comuni_italia'); ?></p>

        <h2><?php _e('Opzioni di Inserimento Multiplo', 'design_comuni_italia'); ?></h2>

        <form method="post" enctype="multipart/form-data">
            <?php wp_nonce_field('dci_multipost_transparency_action', 'dci_multipost_transparency_nonce'); ?>

            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><label for="dci_multi_files"><?php _e('Carica Documenti Multipli:', 'design_comuni_italia'); ?></label></th>
                        <td>
                            <input type="file" id="dci_multi_files" name="dci_multi_files[]" multiple accept=".pdf,.doc,.docx,.xls,.xlsx,.zip">
                            <p class="description"><?php _e('Seleziona più documenti da caricare. Verrà creato un Elemento Trasparenza per ogni file.', 'design_comuni_italia'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dci_default_category"><?php _e('Categoria Predefinita per i Nuovi Elementi:', 'design_comuni_italia'); ?></label></th>
                        <td>
                            <?php
                            wp_dropdown_categories( array(
                                'taxonomy'            => 'tipi_cat_amm_trasp',
                                'name'                => 'dci_default_category',
                                'id'                  => 'dci_default_category',
                                'show_option_none'    => __('Seleziona una categoria', 'design_comuni_italia'),
                                'hide_empty'          => 0,
                                'orderby'             => 'name',
                                'order'               => 'ASC',
                            ) );
                            ?>
                            <p class="description"><?php _e('Questa categoria verrà assegnata a tutti i nuovi elementi creati da questa pagina.', 'design_comuni_italia'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dci_default_open_new_tab"><?php _e('Apri in nuova finestra predefinito:', 'design_comuni_italia'); ?></label></th>
                        <td>
                            <input type="checkbox" id="dci_default_open_new_tab" name="dci_default_open_new_tab" value="1">
                            <p class="description"><?php _e('Spunta per impostare "Apri in una nuova finestra" per tutti i nuovi elementi.', 'design_comuni_italia'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="dci_default_open_direct"><?php _e('Apri link in modo diretto:', 'design_comuni_italia'); ?></label></th>
                        <td>
                            <input type="checkbox" id="dci_default_open_direct" name="dci_default_open_direct" value="1">
                            <p class="description"><?php _e('Spunta per impostare "Apri direttamente il file" per tutti i nuovi elementi.', 'design_comuni_italia'); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>

            <?php submit_button(__('Crea Elementi Trasparenza', 'design_comuni_italia')); ?>
        </form>

    <?php
    // Gestione del form dopo submit
    if ( isset( $_POST['submit'] ) && check_admin_referer('dci_multipost_transparency_action', 'dci_multipost_transparency_nonce') ) {

        $default_category = isset( $_POST['dci_default_category'] ) ? absint( $_POST['dci_default_category'] ) : 0;
        $open_new_tab     = isset( $_POST['dci_default_open_new_tab'] ) ? 1 : 0;
        $open_direct      = isset( $_POST['dci_default_open_direct'] ) ? 1 : 0;

        if ( $default_category === 0 ) {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Seleziona una categoria predefinita per gli elementi.', 'design_comuni_italia') . '</p></div>';
            return;
        }

        if ( empty( $_FILES['dci_multi_files'] ) || empty( $_FILES['dci_multi_files']['name'][0] ) ) {
            echo '<div class="notice notice-error is-dismissible"><p>' . __('Nessun file selezionato.', 'design_comuni_italia') . '</p></div>';
            return;
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';

        $files = $_FILES['dci_multi_files'];

        $created_count = 0;
        $errors = array();

        for ( $i = 0; $i < count( $files['name'] ); $i++ ) {
            if ( $files['error'][ $i ] !== UPLOAD_ERR_OK ) {
                $errors[] = sprintf(__('Errore durante il caricamento del file %s', 'design_comuni_italia'), esc_html($files['name'][$i]));
                continue;
            }

            $file_array = array(
                'name'     => $files['name'][$i],
                'type'     => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error'    => $files['error'][$i],
                'size'     => $files['size'][$i]
            );

            // Carica il file nella libreria media
            $attachment_id = media_handle_sideload( $file_array, 0 );

            if ( is_wp_error( $attachment_id ) ) {
                $errors[] = sprintf(__('Errore durante il caricamento del file %s: %s', 'design_comuni_italia'), esc_html($files['name'][$i]), $attachment_id->get_error_message());
                continue;
            }

            // Crea il post elemento_trasparenza
            $post_title = sanitize_text_field( pathinfo( $files['name'][$i], PATHINFO_FILENAME ) );

            $post_id = wp_insert_post( array(
                'post_title'   => $post_title,
                'post_type'    => 'elemento_trasparenza',
                'post_status'  => 'publish',
                'post_author'  => get_current_user_id(),
            ));

            if ( is_wp_error( $post_id ) || $post_id == 0 ) {
                $errors[] = sprintf(__('Errore durante la creazione dell\'Elemento Trasparenza per il file %s', 'design_comuni_italia'), esc_html($files['name'][$i]));
                // Cancella attachment
                wp_delete_attachment( $attachment_id, true );
                continue;
            }

            // Assegna la categoria personalizzata
            wp_set_object_terms( $post_id, intval($default_category), 'tipi_cat_amm_trasp' );

            // Salva il meta file (qui esempio con update_post_meta)
            update_post_meta( $post_id, '_dci_trasparenza_file', $attachment_id );

            // Salva i flag di apertura link
            update_post_meta( $post_id, '_dci_trasparenza_open_new_tab', $open_new_tab );
            update_post_meta( $post_id, '_dci_trasparenza_open_direct', $open_direct );

            $created_count++;
        }

        if ( $created_count > 0 ) {
            echo '<div class="notice notice-success is-dismissible"><p>' . sprintf( _n('%d Elemento Trasparenza creato con successo.', '%d Elementi Trasparenza creati con successo.', $created_count, 'design_comuni_italia'), $created_count ) . '</p></div>';
        }

        if ( ! empty( $errors ) ) {
            foreach ( $errors as $error ) {
                echo '<div class="notice notice-error is-dismissible"><p>' . esc_html( $error ) . '</p></div>';
            }
        }
    }
    ?>
    </div>
    <?php
}
