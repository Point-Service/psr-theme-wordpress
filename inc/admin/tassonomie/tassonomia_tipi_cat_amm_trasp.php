<?php
/* -------------------------------------------------------------
 *  TASSONOMIA: tipi_cat_amm_trasp
 * ----------------------------------------------------------- */

/**
 * 1. Registrazione tassonomia
 */
add_action( 'init', 'dci_register_taxonomy_tipi_cat_amm_trasp', -10 );
function dci_register_taxonomy_tipi_cat_amm_trasp() {
	$labels = array(
		'name'              => _x( 'Tipi categoria Amministrazione Trasparente', 'taxonomy general name', 'design_comuni_italia' ),
		'singular_name'     => _x( 'Tipo di categoria Amministrazione Trasparente', 'taxonomy singular name', 'design_comuni_italia' ),
		'search_items'      => __( 'Cerca Tipo categoria Amministrazione Trasparente', 'design_comuni_italia' ),
		'all_items'         => __( 'Tutti i Tipi di categoria Amministrazione Trasparente', 'design_comuni_italia' ),
		'edit_item'         => __( 'Modifica il Tipo di categoria Amministrazione Trasparente', 'design_comuni_italia' ),
		'update_item'       => __( 'Aggiorna il Tipo di categoria Amministrazione Trasparente', 'design_comuni_italia' ),
		'add_new_item'      => __( 'Aggiungi un Tipo di categoria Amministrazione Trasparente', 'design_comuni_italia' ),
		'new_item_name'     => __( 'Nuovo Tipo di categoria Amministrazione Trasparente', 'design_comuni_italia' ),
		'menu_name'         => __( 'Tipi di categoria Amministrazione Trasparente', 'design_comuni_italia' ),
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
		'has_archive'       => true,
		'rewrite'           => array( 'slug' => 'tipi_cat_amm_trasp' ),
		'capabilities'      => array(
			'manage_terms' => 'manage_tipi_cat_amm_trasp',
			'edit_terms'   => 'edit_tipi_cat_amm_trasp',
			'delete_terms' => 'delete_tipi_cat_amm_trasp',
			'assign_terms' => 'assign_tipi_cat_amm_trasp',
		),
	);

	register_taxonomy( 'tipi_cat_amm_trasp', array( 'elemento_trasparenza' ), $args );
}

/* ----- Aggiungi (pagina “Aggiungi nuovo”) */
add_action( 'tipi_cat_amm_trasp_add_form_fields', 'dci_tassonomia_add_fields' );
function dci_tassonomia_add_fields() {
	$parent_term_id = isset($_GET['parent']) ? $_GET['parent'] : 0;

	// Campi standard (ordinamento, visualizza, ecc.) come già presenti...
	// ...

	// 🔸 Ruoli da escludere (modificato)
	?>
	<div class="form-field term-excluded_roles-wrap">
		<label for="excluded_roles"><?php _e( 'Ruoli da escludere', 'design_comuni_italia' ); ?></label>
		<?php
		$editable_roles = wp_roles()->roles;
		foreach ( $editable_roles as $role_key => $role_info ) {
			echo '<label><input type="checkbox" name="excluded_roles[]" value="' . esc_attr( $role_key ) . '"> ' . esc_html( $role_info['name'] ) . '</label><br>';
		}
		?>
		<p class="description"><?php _e( 'Se non selezioni nessun ruolo, la categoria sarà accessibile a tutti.', 'design_comuni_italia' ); ?></p>
	</div>
	<?php
}

/* ----- Modifica (pagina “Modifica termine”) */
add_action( 'tipi_cat_amm_trasp_edit_form_fields', 'dci_tassonomia_edit_fields' );
function dci_tassonomia_edit_fields( $term ) {
	// Campi standard già presenti...
	// ...

	$saved_roles = get_term_meta( $term->term_id, 'excluded_roles', true );
	$saved_roles = is_array($saved_roles) ? $saved_roles : [];

	// 🔸 Ruoli da escludere (modificato)
	?>
	<tr class="form-field term-excluded_roles-wrap">
		<th scope="row"><label for="excluded_roles"><?php _e( 'Ruoli da escludere', 'design_comuni_italia' ); ?></label></th>
		<td>
			<?php
			$editable_roles = wp_roles()->roles;
			foreach ( $editable_roles as $role_key => $role_info ) {
				$checked = in_array( $role_key, $saved_roles ) ? 'checked' : '';
				echo '<label><input type="checkbox" name="excluded_roles[]" value="' . esc_attr( $role_key ) . '" ' . $checked . '> ' . esc_html( $role_info['name'] ) . '</label><br>';
			}
			?>
			<p class="description"><?php _e( 'Gli utenti con i ruoli selezionati non potranno accedere a questa categoria.', 'design_comuni_italia' ); ?></p>
		</td>
	</tr>
	<?php
}

/* 3. Salva i metadati */
add_action( 'created_tipi_cat_amm_trasp', 'dci_save_term_meta', 10, 2 );
add_action( 'edited_tipi_cat_amm_trasp',  'dci_save_term_meta', 10, 2 );
function dci_save_term_meta( $term_id ) {
	// Altri campi già presenti...

	if ( isset( $_POST['excluded_roles'] ) && is_array( $_POST['excluded_roles'] ) ) {
		update_term_meta( $term_id, 'excluded_roles', array_map( 'sanitize_text_field', $_POST['excluded_roles'] ) );
	} else {
		delete_term_meta( $term_id, 'excluded_roles' );
	}
}

/* Colonne personalizzate (nessuna modifica richiesta qui) */
// Rimangono intatte come nel tuo codice

/* -----------------------------------------------
 * 5. Blocco accesso ai ruoli esclusi (da usare nel template)
 * ---------------------------------------------------------- */
function dci_user_has_access_to_term( $term_id ) {
	if ( is_admin() ) return true; // Sempre accesso in admin

	$excluded_roles = get_term_meta( $term_id, 'excluded_roles', true );
	if ( empty( $excluded_roles ) || ! is_array( $excluded_roles ) ) return true;

	$current_user = wp_get_current_user();
	if ( empty( $current_user->roles ) ) return true;

	foreach ( $current_user->roles as $role ) {
		if ( in_array( $role, $excluded_roles ) ) {
			return false;
		}
	}

	return true;
}
?>
