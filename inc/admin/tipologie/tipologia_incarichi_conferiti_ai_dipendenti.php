<?php
/**
 * Registra il custom post type "incarichi_conferiti_ai_dipendenti"
 * (sigla: ICAD)
 */
add_action( 'init', 'dci_register_post_type_icad' );
function dci_register_post_type_icad() {

	$labels = array(
		'name'           => _x( 'Incarichi conferiti e autorizzati', 'Post Type General Name', 'design_comuni_italia' ),
		'singular_name'  => _x( 'Incarico conferito',           'Post Type Singular Name', 'design_comuni_italia' ),
		'add_new'        => _x( 'Aggiungi un Incarico conferito', 'Post Type',             'design_comuni_italia' ),
		'add_new_item'   => __( 'Aggiungi un nuovo Incarico conferito', 'design_comuni_italia' ),
		'edit_item'      => __( 'Modifica Incarico conferito',           'design_comuni_italia' ),
		'featured_image' => __( 'Immagine di riferimento',               'design_comuni_italia' ),
	);

	$args = array(
		'label'        => __( 'Incarico conferito', 'design_comuni_italia' ),
		'labels'       => $labels,
		'supports'     => array( 'title', 'author' ),
		'hierarchical' => true,
		'public'       => true,
		'show_in_menu' => 'edit.php?post_type=elemento_trasparenza',
		'menu_icon'    => 'dashicons-media-interactive',
		'has_archive'  => false,
		'map_meta_cap' => true,
		'capabilities' => array(
			'edit_post'               => 'edit_incarico_conferito_ai_dipendente',
			'read_post'               => 'read_incarico_conferito_ai_dipendente',
			'delete_post'             => 'delete_incarico_conferito_ai_dipendente',
			'edit_posts'              => 'edit_incarichi_conferiti_ai_dipendenti',
			'edit_others_posts'       => 'edit_others_incarichi_conferiti_ai_dipendenti',
			'publish_posts'           => 'publish_incarichi_conferiti_ai_dipendenti',
			'read_private_posts'      => 'read_private_incarichi_conferiti_ai_dipendenti',
			'delete_posts'            => 'delete_incarichi_conferiti_ai_dipendenti',
			'delete_private_posts'    => 'delete_private_incarichi_conferiti_ai_dipendenti',
			'delete_published_posts'  => 'delete_published_incarichi_conferiti_ai_dipendenti',
			'delete_others_posts'     => 'delete_others_incarichi_conferiti_ai_dipendenti',
			'edit_private_posts'      => 'edit_private_incarichi_conferiti_ai_dipendenti',
			'edit_published_posts'    => 'edit_published_incarichi_conferiti_ai_dipendenti',
			'create_posts'            => 'create_incarichi_conferiti_ai_dipendenti',
		),
		'description'   => __( 'Tipologia personalizzata per la pubblicazione degli incarichi conferiti ai dipendenti del Comune.', 'design_comuni_italia' ),
	);

	register_post_type( 'incarichi_conferiti_ai_dipendenti', $args );

	// Rimuove l'editor di WordPress
	remove_post_type_support( 'incarichi_conferiti_ai_dipendenti', 'editor' );
}

/* Messaggio informativo sotto al titolo */
add_action( 'edit_form_after_title', 'dci_icad_add_content_after_title' );
function dci_icad_add_content_after_title( $post ) {
	if ( $post->post_type === 'incarichi_conferiti_ai_dipendenti' ) {
		echo '<span><i>Il <strong>titolo/norma</strong> corrisponde al <strong>titolo dell\'incarico conferito</strong>.</i></span><br><br>';
	}
}

/* ---------------- CMB2 Metaboxes ---------------- */
add_action( 'cmb2_init', 'dci_add_icad_metaboxes' );
function dci_add_icad_metaboxes() {

	$prefix = '_dci_icad_';   // prefisso univoco

	/* — Metabox: Apertura — */
	$cmb_apertura = new_cmb2_box( array(
		'id'           => $prefix . 'box_apertura',
		'title'        => __( 'Informazioni sull\'incarico conferito', 'design_comuni_italia' ),
		'object_types' => array( 'incarichi_conferiti_ai_dipendenti' ),
		'context'      => 'normal',
		'priority'     => 'high',
	) );

	$cmb_apertura->add_field( array(
		'id'               => $prefix . 'tipo_stato',
		'name'             => __( 'Stato dell\'incarico *', 'design_comuni_italia' ),
		'desc'             => __( 'Selezionare lo stato dell\'incarico', 'design_comuni_italia' ),
		'type'             => 'taxonomy_radio_hierarchical',
		'taxonomy'         => 'tipi_stato_bando',
		'show_option_none' => false,
		'remove_default'   => true,
	) );

	$cmb_apertura->add_field( array(
		'id'          => $prefix . 'anno_beneficio',
		'name'        => __( 'Anno di beneficio *', 'design_comuni_italia' ),
		'desc'        => __( 'Seleziona l\'anno di beneficio', 'design_comuni_italia' ),
		'type'        => 'text_date_timestamp',
		'date_format' => 'Y',
	) );

	$cmb_apertura->add_field( array(
		'id'   => $prefix . 'rag_incarico',
		'name' => __( 'Ragione dell\'incarico *', 'design_comuni_italia' ),
		'desc' => __( 'Specificare la ragione dell\'incarico', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	$cmb_apertura->add_field( array(
		'id'   => $prefix . 'importo',
		'name' => __( 'Importo *', 'design_comuni_italia' ),
		'desc' => __( 'Indica l’importo', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	$cmb_apertura->add_field( array(
		'id'   => $prefix . 'responsabile',
		'name' => __( 'Responsabile *', 'design_comuni_italia' ),
		'desc' => __( 'Indica il responsabile', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	/* — Metabox: Dettagli — */
	$cmb_dettagli = new_cmb2_box( array(
		'id'           => $prefix . 'box_dettagli',
		'title'        => __( 'Dettagli beneficiario', 'design_comuni_italia' ),
		'object_types' => array( 'incarichi_conferiti_ai_dipendenti' ),
		'context'      => 'normal',
		'priority'     => 'high',
	) );

	$cmb_dettagli->add_field( array(
		'id'   => $prefix . 'descrizione_breve',
		'name' => __( 'Descrizione breve', 'design_comuni_italia' ),
		'desc' => __( 'Descrizione sintetica (max 255 caratteri)', 'design_comuni_italia' ),
		'type' => 'textarea',
	) );

	$cmb_dettagli->add_field( array(
		'id'   => $prefix . 'ragione_sociale',
		'name' => __( 'Ragione sociale *', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	$cmb_dettagli->add_field( array(
		'id'   => $prefix . 'codice_fiscale',
		'name' => __( 'Codice fiscale / P. IVA *', 'design_comuni_italia' ),
		'type' => 'text',
	) );

	/* — Metabox: Documenti — */
	$cmb_documenti = new_cmb2_box( array(
		'id'           => $prefix . 'box_documenti',
		'title'        => __( 'Documenti', 'design_comuni_italia' ),
		'object_types' => array( 'incarichi_conferiti_ai_dipendenti' ),
		'context'      => 'normal',
		'priority'     => 'high',
	) );

	$cmb_documenti->add_field( array(
		'id'   => $prefix . 'allegati',
		'name' => __( 'Allegati', 'design_comuni_italia' ),
		'desc' => __( 'Elenco di documenti allegati all\'incarico', 'design_comuni_italia' ),
		'type' => 'file_list',
	) );
}

/* ---------------- JS Admin ---------------- */
add_action( 'admin_print_scripts-post-new.php', 'dci_icad_admin_script', 11 );
add_action( 'admin_print_scripts-post.php',      'dci_icad_admin_script', 11 );
function dci_icad_admin_script() {
	if ( get_current_screen()->post_type === 'incarichi_conferiti_ai_dipendenti' ) {
		wp_enqueue_script(
			'icad-admin-script',
			get_template_directory_uri() . '/inc/admin-js/incarichi_conferiti_ai_dipendenti.js',
			array(), null, true
		);
	}
}

/* ---------------- Popola automaticamente post_content ---------------- */
add_filter( 'wp_insert_post_data', 'dci_icad_set_post_content', 99, 1 );
function dci_icad_set_post_content( $data ) {

	if ( $data['post_type'] === 'incarichi_conferiti_ai_dipendenti' ) {

		$descrizione_breve = isset( $_POST['_dci_icad_descrizione_breve'] )
			? $_POST['_dci_icad_descrizione_breve']
			: '';

		$testo_completo    = isset( $_POST['_dci_icad_testo_completo'] )
			? $_POST['_dci_icad_testo_completo']
			: '';

		$data['post_content'] = $descrizione_breve . '<br>' . $testo_completo;
	}

	return $data;
}
