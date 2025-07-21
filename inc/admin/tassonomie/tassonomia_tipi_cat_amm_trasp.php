<?php
/**
 * Tassonomia: tipi_cat_amm_trasp
 */

// 1. REGISTRAZIONE TASSONOMIA
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


// 2. Aggiunta campi personalizzati
add_action( 'tipi_cat_amm_trasp_add_form_fields', 'dci_tassonomia_add_fields' );
function dci_tassonomia_add_fields() {
	?>

	<!-- URL -->
	<div class="form-field term-url-wrap">
		<label for="url"><?php _e( 'URL personalizzato (facoltativo)', 'design_comuni_italia' ); ?></label>
		<input type="text" name="url" id="url" value="" />
		<p class="description"><?php _e( 'Se inserito, questo URL sarà usato al posto del link alla categoria.', 'design_comuni_italia' ); ?></p>
	</div>

	<!-- Ruoli da escludere -->
	<div class="form-field term-excluded_roles-wrap">
		<label for="excluded_roles"><?php _e( 'Ruoli da escludere', 'design_comuni_italia' ); ?></label>
		<?php
		$editable_roles = wp_roles()->roles;
		foreach ( $editable_roles as $role_key => $role_info ) {
			echo '<label><input type="checkbox" name="excluded_roles[]" value="' . esc_attr( $role_key ) . '"> ' . esc_html( $role_info['name'] ) . '</label><br>';
		}
		?>
		<p class="description"><?php _e( 'Se selezioni uno o più ruoli, questi NON potranno visualizzare la categoria.', 'design_comuni_italia' ); ?></p>
	</div>

	<?php
}

// 3. Modifica campi personalizzati
add_action( 'tipi_cat_amm_trasp_edit_form_fields', 'dci_tassonomia_edit_fields' );
function dci_tassonomia_edit_fields( $term ) {
	$url = get_term_meta( $term->term_id, 'url', true );
	$saved_roles = get_term_meta( $term->term_id, 'excluded_roles', true );
	$saved_roles = is_array($saved_roles) ? $saved_roles : [];
	?>

	<!-- URL -->
	<tr class="form-field term-url-wrap">
		<th scope="row"><label for="url"><?php _e( 'URL personalizzato (facoltativo)', 'design_comuni_italia' ); ?></label></th>
		<td>
			<input type="text" name="url" id="url" value="<?php echo esc_attr( $url ); ?>" />
			<p class="description"><?php _e( 'Se inserito, questo URL sarà usato al posto del link alla categoria.', 'design_comuni_italia' ); ?></p>
		</td>
	</tr>

	<!-- Ruoli da escludere -->
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

// 4. Salvataggio dei metadati
add_action( 'created_tipi_cat_amm_trasp', 'dci_save_term_meta', 10, 2 );
add_action( 'edited_tipi_cat_amm_trasp',  'dci_save_term_meta', 10, 2 );
function dci_save_term_meta( $term_id ) {
	if ( isset( $_POST['url'] ) ) {
		update_term_meta( $term_id, 'url', sanitize_text_field( $_POST['url'] ) );
	}

	if ( isset( $_POST['excluded_roles'] ) && is_array( $_POST['excluded_roles'] ) ) {
		update_term_meta( $term_id, 'excluded_roles', array_map( 'sanitize_text_field', $_POST['excluded_roles'] ) );
	} else {
		delete_term_meta( $term_id, 'excluded_roles' );
	}
}
?>
