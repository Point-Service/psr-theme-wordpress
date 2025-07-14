<?php
/**
 * Custom Post Type: incarichi_dip
 * (Incarichi conferiti ai dipendenti)
 */

/* -------------------------------------------------
   Registrazione CPT
--------------------------------------------------*/
add_action( 'init', 'dci_register_post_type_icad' );
function dci_register_post_type_icad() {

	$labels = array(
		'name'           => _x( 'Incarichi conferiti e autorizzati', 'Post Type General Name', 'design_comuni_italia' ),
		'singular_name'  => _x( 'Incarico conferito', 'Post Type Singular Name', 'design_comuni_italia' ),
		'add_new'        => _x( 'Aggiungi un Incarico conferito', 'Post Type', 'design_comuni_italia' ),
		'add_new_item'   => __( 'Aggiungi un nuovo Incarico conferito', 'design_comuni_italia' ),
		'edit_item'      => __( 'Modifica Incarico conferito', 'design_comuni_italia' ),
		'featured_image' => __( 'Immagine di riferimento incarico', 'design_comuni_italia' ),
	);

	$args = array(
		'label'         => __( 'Incarico conferito', 'design_comuni_italia' ),
		'labels'        => $labels,
		'supports'      => array( 'title', 'author' ),
		'hierarchical'  => true,
		'public'        => true,
		'show_in_menu'  => 'edit.php?post_type=elemento_trasparenza',
		'menu_icon'     => 'dashicons-media-interactive',
		'has_archive'   => false,
		'map_meta_cap'  => true,
		// usa capability standard "post" per evitare problemi di permessi
		'capability_type' => 'post',
		'description'   => __( 'Incarichi conferiti ai dipendenti del Comune.', 'design_comuni_italia' ),
	);

	register_post_type( 'incarichi_dip', $args );
	remove_post_type_support( 'incarichi_dip', 'editor' );
}

/* -------------------------------------------------
   Messaggio informativo nel backend
--------------------------------------------------*/
add_action( 'edit_form_after_title', 'dci_icad_notice_after_title' );
function dci_icad_notice_after_title( $post ) {
	if ( $post->post_type === 'incarichi_dip' ) {
		echo '<span><i>Il <strong>titolo/norma</strong> corrisponde al <strong>titolo dell\'incarico conferito</strong>.</i></span><br><br>';
	}
}

/* -------------------------------------------------
   CMB2 Metaboxes
--------------------------------------------------*/
add_action( 'cmb2_init', 'dci_icad_metaboxes' );
function dci_icad_metaboxes() {

	$prefix = '_dci_icad_';

	/* — Apertura — */
	$cmb_apertura = new_cmb2_box( array(
		'id'           => $prefix . 'box_apertura',
		'title'        => __( 'Informazioni sull\'incarico conferito', 'design_comuni_italia' ),
		'object_types' => array( 'incarichi_dip' ),
	) );

	$cmb_apertura->add_field( array(
		'id'      => $prefix . 'tipo_stato',
		'name'    => __( 'Stato dell\'incarico *', 'design_comuni_italia' ),
		'type'    => 'taxonomy_radio_hierarchical',
		'taxonomy'=> 'tipi_stato_bando',
	) );

	$cmb_apertura->add_field( array(
		'id'          => $prefix . 'anno_beneficio',
		'name'        => __( 'Anno di beneficio *', 'design_comuni_italia' ),
		'type'        => 'text_date_timestamp',
		'date_format' => 'Y',
	) );

	$cmb_apertura->add_field( array(
		'id'   => $prefix . 'rag_incarico',
		'name' => __( 'Ragione dell\'incarico *', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	$cmb_apertura->add_field( array(
		'id'   => $prefix . 'importo',
		'name' => __( 'Importo *', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	$cmb_apertura->add_field( array(
		'id'   => $prefix . 'responsabile',
		'name' => __( 'Responsabile *', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	/* — Dettagli beneficiario — */
	$cmb_dettagli = new_cmb2_box( array(
		'id'           => $prefix . 'box_dettagli',
		'title'        => __( 'Dettagli beneficiario', 'design_comuni_italia' ),
		'object_types' => array( 'incarichi_dip' ),
	) );

	$cmb_dettagli->add_field( array(
		'id'   => $prefix . 'descrizione_breve',
		'name' => __( 'Descrizione breve', 'design_comuni_italia' ),
		'type' => 'textarea',
	) );

	$cmb_dettagli->add_field( array(
		'id'   => $prefix . 'ragione_sociale',
		'name' => __( 'Ragione sociale *', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	$cmb_dettagli->add_field( array(
		'id'   => $prefix . 'codice_fiscale',
		'name' => __( 'Codice fiscale / P. IVA *', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	/* — Documenti — */
	$cmb_documenti = new_cmb2_box( array(
		'id'           => $prefix . 'box_documenti',
		'title'        => __( 'Documenti', 'design_comuni_italia' ),
		'object_types' => array( 'incarichi_dip' ),
	) );

	$cmb_documenti->add_field( array(
		'id'   => $prefix . 'allegati',
		'name' => __( 'Allegati', 'design_comuni_italia' ),
		'type' => 'file_list',
	) );
}

/* -------------------------------------------------
   JS backend (opzionale)
--------------------------------------------------*/
add_action( 'admin_print_scripts-post-new.php', 'dci_icad_admin_script', 11 );
add_action( 'admin_print_scripts-post.php',      'dci_icad_admin_script', 11 );
function dci_icad_admin_script() {
	if ( get_current_screen()->post_type === 'incarichi_dip' ) {
		wp_enqueue_script(
			'icad-admin-script',
			get_template_directory_uri() . '/inc/admin-js/incarichi_dip.js',
			array(), null, true
		);
	}
}

/* -------------------------------------------------
   Popola automaticamente post_content
--------------------------------------------------*/
add_filter( 'wp_insert_post_data', 'dci_icad_set_post_content', 99, 1 );
function dci_icad_set_post_content( $data ) {

	if ( $data['post_type'] === 'incarichi_dip' ) {

		$descrizione_breve = isset( $_POST['_dci_icad_descrizione_breve'] )
			? $_POST['_dci_icad_descrizione_breve']
			: '';

		$testo_completo    = isset( $_POST['_dci_icad_testo_completo'] )
			? $_POST['_dci_icad_testo_completo']
			: '';

		$data['post_content'] = $descrizione_breve . "\n\n" . $testo_completo;
	}

	return $data;
}

