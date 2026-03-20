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
 * 🔥 Limita slug
 */
add_filter('wp_insert_term_data', function($data, $taxonomy) {
	if ($taxonomy === 'tipi_cat_amm_trasp') {
		$data['slug'] = substr($data['slug'], 0, 80);
	}
	return $data;
}, 10, 2);


/**
 * Campi personalizzati
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
		.dci-dual-select { display:flex; gap:20px; margin-top:10px; }
		.dci-dual-select select { width:200px; height:150px; }
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

		<p class="description"><?php _e( 'Doppio click per spostare i ruoli', 'design_comuni_italia' ); ?></p>
	</div>

	<script>
	document.addEventListener('DOMContentLoaded', function () {

		var available = document.getElementById('roles-available');
		var selected = document.getElementById('roles-selected');

		function moveSelected(from, to) {
			if (!from || !to) return;
			var opts = from.selectedOptions;
			for (var i = 0; i < opts.length; i++) {
				to.appendChild(opts[i]);
			}
		}

		if (available && selected) {
			available.addEventListener('dblclick', function() {
				moveSelected(available, selected);
			});
			selected.addEventListener('dblclick', function() {
				moveSelected(selected, available);
			});
		}

		// 🔥 FIX submit
		var form = document.querySelector('form');
		if (form && selected) {
			form.addEventListener('submit', function () {
				var options = selected.options;
				for (var i = 0; i < options.length; i++) {
					options[i].selected = true;
				}
			});
		}
	});
	</script>

	<?php
	return ob_get_clean();
}

function dci_tassonomia_add_fields() {
	?>
	<div class="form-field">
		<?php echo dci_get_role_boxes_html(); ?>
	</div>

	<div id="url-wrapper">
		<div class="form-field"><label><?php _e('URL personalizzato', 'design_comuni_italia'); ?></label><input name="term_url" type="url" placeholder="https://..." /></div>
		<div class="form-field"><label><input type="checkbox" name="open_new_window" value="1" /> <?php _e('Apri in nuova finestra', 'design_comuni_italia'); ?></label></div>
	</div>

	<div class="form-field"><label><?php _e('Ordinamento', 'design_comuni_italia'); ?></label><input type="number" name="ordinamento" value="0" /></div>
	<div class="form-field"><label><input type="checkbox" name="visualizza_elemento" value="1" checked /> <?php _e('Visualizza elemento', 'design_comuni_italia'); ?></label></div>

	<script>
	document.addEventListener('DOMContentLoaded', function() {
		var parentSelect = document.getElementById('parent');
		var urlWrapper = document.getElementById('url-wrapper');

		function toggleUrlFields() {
			if (!parentSelect || !urlWrapper) return;
			urlWrapper.style.display = parentSelect.value === '0' ? 'none' : 'block';
		}

		if (parentSelect) {
			parentSelect.addEventListener('change', toggleUrlFields);
		}

		toggleUrlFields();
	});
	</script>
	<?php
}

function dci_tassonomia_edit_fields( $term ) {
	$excluded_roles = get_term_meta( $term->term_id, 'excluded_roles', true );
	$excluded_roles = is_array( $excluded_roles ) ? $excluded_roles : [];
	?>
	<tr class="form-field">
		<th scope="row"><?php _e('Ruoli da escludere', 'design_comuni_italia'); ?></th>
		<td><?php echo dci_get_role_boxes_html($excluded_roles); ?></td>
	</tr>
<?php }

/**
 * Salvataggio
 */
add_action( 'created_tipi_cat_amm_trasp', 'dci_save_term_meta', 10, 2 );
add_action( 'edited_tipi_cat_amm_trasp',  'dci_save_term_meta', 10, 2 );

function dci_save_term_meta( $term_id ) {
	update_term_meta( $term_id, 'ordinamento', isset($_POST['ordinamento']) ? intval($_POST['ordinamento']) : 0 );
	update_term_meta( $term_id, 'visualizza_elemento', isset($_POST['visualizza_elemento']) ? '1' : '0' );
	update_term_meta( $term_id, 'term_url', isset($_POST['term_url']) ? esc_url_raw($_POST['term_url']) : '' );
	update_term_meta( $term_id, 'open_new_window', isset($_POST['open_new_window']) ? '1' : '0' );

	if ( isset($_POST['excluded_roles']) && is_array($_POST['excluded_roles']) ) {
		update_term_meta( $term_id, 'excluded_roles', array_map('sanitize_text_field', $_POST['excluded_roles']) );
	} else {
		delete_term_meta( $term_id, 'excluded_roles' );
	}
}

/**
 * 🔥 FIX LOOP
 */
add_filter( 'terms_clauses', 'dci_hide_invisible_or_restricted_terms', 10, 3 );
function dci_hide_invisible_or_restricted_terms( $clauses, $taxonomies, $args ) {

	if ( ! in_array( 'tipi_cat_amm_trasp', (array) $taxonomies, true ) ) return $clauses;
	if ( ! is_admin() ) return $clauses;

	// blocca durante submit
	if (
		( defined('DOING_AJAX') && DOING_AJAX ) ||
		( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) ||
		! empty($_POST)
	) {
		return $clauses;
	}

	return $clauses;
}
