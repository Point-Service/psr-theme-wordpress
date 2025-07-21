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
	);

	$args = array(
		'hierarchical'      => true,
		'labels'            => $labels,
		'public'            => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'query_var'         => true,
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

/**
 * Campi personalizzati aggiunta/modifica termine
 */
add_action( 'tipi_cat_amm_trasp_add_form_fields', 'dci_tassonomia_add_fields' );
add_action( 'tipi_cat_amm_trasp_edit_form_fields', 'dci_tassonomia_edit_fields' );

function dci_get_role_boxes_html( $selected_roles = [] ) {
	global $wp_roles;

	$all_roles = $wp_roles->roles;
	$available_roles = array_diff( array_keys( $all_roles ), $selected_roles );

	ob_start();
	?>
	<style>
		.dci-dual-select {
			display: flex;
			gap: 20px;
			margin-top: 10px;
		}
		.dci-dual-select select {
			width: 220px;
			height: 160px;
			padding: 5px;
			border: 1px solid #ccc;
			border-radius: 4px;
			font-size: 14px;
		}

		/* Miglioriamo i campi generali del form */
		.form-field {
			display: flex;
			align-items: center;
			margin-bottom: 15px;
			gap: 10px;
		}
		.form-field label {
			flex: 0 0 180px;
			font-weight: 600;
			font-size: 14px;
			color: #333;
		}
		.form-field input[type="url"],
		.form-field input[type="number"],
		.form-field select {
			flex: 1;
			padding: 6px 8px;
			font-size: 14px;
			border: 1px solid #ccc;
			border-radius: 4px;
		}

		.form-field input[type="checkbox"] {
			margin-left: 0;
			margin-right: 6px;
			transform: scale(1.1);
		}

		/* Checkbox label più compatto */
		.form-field label input[type="checkbox"] {
			vertical-align: middle;
		}

		.form-field p.description {
			margin-top: 4px;
			font-size: 12px;
			color: #666;
			flex-basis: 100%;
		}
	</style>
	<div class="form-field term-excluded_roles-wrap">
		<label><?php _e( 'Ruoli da escludere', 'design_comuni_italia' ); ?></label>
		<div class="dci-dual-select">
			<div>
				<strong><?php _e( 'Disponibili', 'design_comuni_italia' ); ?></strong><br />
				<select id="roles-available" multiple>
					<?php foreach ( $available_roles as $key ): ?>
						<option value="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( translate_user_role( $all_roles[ $key ]['name'] ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>

			<div>
				<strong><?php _e( 'Esclusi', 'design_comuni_italia' ); ?></strong><br />
				<select name="excluded_roles[]" id="roles-selected" multiple>
					<?php foreach ( $selected_roles as $key ): ?>
						<option value="<?php echo esc_attr( $key ); ?>">
							<?php echo esc_html( translate_user_role( $all_roles[ $key ]['name'] ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<p class="description"><?php _e( 'Trascina i ruoli che NON devono vedere questa categoria.', 'design_comuni_italia' ); ?></p>
	</div>
	<script>
		document.addEventListener('DOMContentLoaded', () => {
			const available = document.getElementById('roles-available');
			const selected = document.getElementById('roles-selected');

			function moveSelected(from, to) {
				Array.from(from.selectedOptions).forEach(opt => {
					to.appendChild(opt);
				});
			}

			available.ondblclick = () => moveSelected(available, selected);
			selected.ondblclick = () => moveSelected(selected, available);
		});
	</script>
	<?php
	return ob_get_clean();
}

function dci_tassonomia_add_fields() {
	echo dci_get_role_boxes_html();
	?>
	<div class="form-field">
		<label for="term_url"><?php _e('URL personalizzato', 'design_comuni_italia'); ?></label>
		<input id="term_url" name="term_url" type="url" placeholder="https://..." />
	</div>
	<div class="form-field">
		<label>
			<input type="checkbox" name="open_new_window" value="1" />
			<?php _e('Apri in nuova finestra', 'design_comuni_italia'); ?>
		</label>
	</div>
	<div class="form-field">
		<label for="ordinamento"><?php _e('Ordinamento', 'design_comuni_italia'); ?></label>
		<input id="ordinamento" type="number" name="ordinamento" value="0" />
	</div>
	<div class="form-field">
		<label>
			<input type="checkbox" name="visualizza_elemento" value="1" checked />
			<?php _e('Visualizza elemento', 'design_comuni_italia'); ?>
		</label>
	</div>
	<?php
}

function dci_tassonomia_edit_fields( $term ) {
	$excluded_roles = get_term_meta( $term->term_id, 'excluded_roles', true );
	$excluded_roles = is_array( $excluded_roles ) ? $excluded_roles : [];

	echo '<tr class="form-field" style="vertical-align: top;"><th colspan="2">' . dci_get_role_boxes_html($excluded_roles) . '</th></tr>';
	?>
	<tr class="form-field">
		<th><label for="term_url_edit"><?php _e('URL', 'design_comuni_italia'); ?></label></th>
		<td><input id="term_url_edit" type="url" name="term_url" value="<?php echo esc_attr(get_term_meta($term->term_id, 'term_url', true)); ?>" style="width: 100%; max-width: 400px;" /></td>
	</tr>
	<tr class="form-field">
		<th><?php _e('Nuova finestra', 'design_comuni_italia'); ?></th>
		<td><label><input type="checkbox" name="open_new_window" value="1" <?php checked(get_term_meta($term->term_id, 'open_new_window', true), '1'); ?> /> <?php _e('Apri in nuova finestra', 'design_comuni_italia'); ?></label></td>
	</tr>
	<tr class="form-field">
		<th><label for="ordinamento_edit"><?php _e('Ordinamento', 'design_comuni_italia'); ?></label></th>
		<td><input id="ordinamento_edit" name="ordinamento" type="number" value="<?php echo esc_attr(get_term_meta($term->term_id, 'ordinamento', true)); ?>" style="width: 100px;" /></td>
	</tr>
	<tr class="form-field">
		<th><?php _e('Visualizza', 'design_comuni_italia'); ?></th>
		<td><label><input name="visualizza_elemento" type="checkbox" value="1" <?php checked(get_term_meta($term->term_id, 'visualizza_elemento', true), '1'); ?> /> <?php _e('Visualizza elemento', 'design_comuni_italia'); ?></label></td>
	</tr>
	<?php
}

/**
 * 3. Salva metadati personalizzati
 */
add_action( 'created_tipi_cat_amm_trasp', 'dci_save_term_meta', 10, 2 );
add_action( 'edited_tipi_cat_amm_trasp',  'dci_save_term_meta', 10, 2 );
function dci_save_term_meta( $term_id ) {
	update_term_meta( $term_id, 'ordinamento', isset( $_POST['ordinamento'] ) ? intval( $_POST['ordinamento'] ) : 0 );
	update_term_meta( $term_id, 'visualizza_elemento', isset( $_POST['visualizza_elemento'] ) ? '1' : '0' );
	update_term_meta( $term_id, 'term_url', isset( $_POST['term_url'] ) ? esc_url_raw( $_POST['term_url'] ) : '' );
	update_term_meta( $term_id, 'open_new_window', isset( $_POST['open_new_window'] ) ? '1' : '0' );

	if ( isset( $_POST['excluded_roles'] ) && is_array( $_POST['excluded_roles'] ) ) {
		update_term_meta( $term_id, 'excluded_roles', array_map( 'sanitize_text_field', $_POST['excluded_roles'] ) );
	} else {
		delete_term_meta( $term_id, 'excluded_roles' );
	}
}

/**
 * 4. Colonne personalizzate
 */
add_filter('manage_edit-tipi_cat_amm_trasp_columns', 'dci_custom_column_order');
function dci_custom_column_order($columns) {
    $new_columns = [];

    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;
        if ($key === 'name') {
            $new_columns['ordinamento'] = __('Ordinamento', 'design_comuni_italia');
            $new_columns['visualizza_item'] = __('Visualizza', 'design_comuni_italia');
            $new_columns['term_url'] = __('URL', 'design_comuni_italia');
        }
    }

    return $new_columns;
}

add_filter( 'manage_tipi_cat_amm_trasp_custom_column', 'dci_custom_column_content', 10, 3 );
function dci_custom_column_content( $content, $column_name, $term_id ) {
    switch ( $column_name ) {
        case 'ordinamento':
            $content = get_term_meta( $term_id, 'ordinamento', true );
            break;
        case 'visualizza_item':
            $content = get_term_meta( $term_id, 'visualizza_elemento', true ) === '1' ? __('Sì', 'design_comuni_italia') : __('No', 'design_comuni_italia');
            break;
        case 'term_url':
            $url = get_term_meta( $term_id, 'term_url', true );
            $content = $url ? '<a href="'.esc_url($url).'" target="_blank">'.esc_html($url).'</a>' : '';
            break;
    }
    return $content;
}
?>

