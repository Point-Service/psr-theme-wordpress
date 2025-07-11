<?php
/**
 * TASSONOMIA: Tipi categoria Amministrazione Trasparente
 * ------------------------------------------------------
 * - slug:  tipi_cat_amm_trasp
 * - post:  elemento_trasparenza
 * - meta:  ordine_personalizzato (int)  → controlla la posizione
 */

/*--------------------------------------------------------------
# 1. REGISTRAZIONE TASSONOMIA
--------------------------------------------------------------*/
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
		'hierarchical'          => true,
		'labels'                => $labels,
		'public'                => true,
		'show_ui'               => true,
		'show_admin_column'     => true,
		'query_var'             => true,
		'has_archive'           => true,
		'rewrite'               => array( 'slug' => 'tipi_cat_amm_trasp' ),
		'capabilities'          => array(
			'manage_terms'  => 'manage_tipi_cat_amm_trasp',
			'edit_terms'    => 'edit_tipi_cat_amm_trasp',
			'delete_terms'  => 'delete_tipi_cat_amm_trasp',
			'assign_terms'  => 'assign_tipi_cat_amm_trasp',
		),
	);

	register_taxonomy( 'tipi_cat_amm_trasp', array( 'elemento_trasparenza' ), $args );
}

/*--------------------------------------------------------------
# 2. ORDINA I TERMINI IN BASE AL META "ordine_personalizzato"
--------------------------------------------------------------*/
add_filter( 'get_terms_args', 'dci_order_trasp_terms_by_meta', 10, 2 );
function dci_order_trasp_terms_by_meta( $args, $taxonomies ) {

	if ( in_array( 'tipi_cat_amm_trasp', (array) $taxonomies, true ) ) {
		$args['meta_key'] = 'ordine_personalizzato';
		$args['orderby']  = 'meta_value_num';
		$args['order']    = 'ASC';
	}
	return $args;
}




add_action( 'current_screen', function ( $screen ) {
    if ( $screen->taxonomy === 'tipi_cat_amm_trasp' ) {
        error_log( 'Sono nella schermata tipi_cat_amm_trasp – ID: ' . $screen->id );
    }
} );





/*--------------------------------------------------------------
# 3. COLONNA "Ordine" NELLA LISTA TERMINI
--------------------------------------------------------------*/
add_filter( 'manage_edit-tipi_cat_amm_trasp_columns', function ( $cols ) {
	$cols['ordine_personalizzato'] = __( 'Ordine', 'design_comuni_italia' );
	return $cols;
} );

add_filter( 'manage_tipi_cat_amm_trasp_custom_column', function ( $out, $column, $term_id ) {
	if ( 'ordine_personalizzato' === $column ) {
		$out = intval( get_term_meta( $term_id, 'ordine_personalizzato', true ) );
	}
	return $out;
}, 10, 3 );

/*--------------------------------------------------------------
# 4. QUICK EDIT + MODIFICA TERMINE – CAMPO “Ordine”
--------------------------------------------------------------*/
# 4.a – Aggiunge campo nel Quick Edit
add_action( 'quick_edit_custom_box', function( $column, $screen, $name ) {
	if ( 'ordine_personalizzato' !== $column || 'edit-tipi_cat_amm_trasp' !== $screen ) {
		return;
	} ?>
	<fieldset class="inline-edit-col-right">
		<div class="inline-edit-col">
			<label>
				<span class="title"><?php _e( 'Ordine', 'design_comuni_italia' ); ?></span>
				<span class="input-text-wrap">
					<input type="number" name="ordine_personalizzato" class="ordine-personalizzato" value="" min="0" style="width:80px;">
				</span>
			</label>
		</div>
	</fieldset>
<?php }, 10, 3 );

# 4.b – Popola Quick Edit con il valore esistente
add_action( 'admin_footer-edit-tags.php', function() {
	$screen = get_current_screen();
	if ( ! $screen || 'tipi_cat_amm_trasp' !== $screen->taxonomy ) {
		return;
	} ?>
	<script>
		jQuery(function($){
			$('a.editinline').on('click', function(){
				var row   = $(this).closest('tr');
				var id    = row.attr('id').replace('tag-', '');
				var ordine= row.find('.column-ordine_personalizzato').text().trim();
				setTimeout(function(){
					$('#edit-'+id).find('input.ordine-personalizzato').val( ordine );
				}, 50);
			});
		});
	</script>
<?php } );

# 4.c – Campo extra nella schermata “Aggiungi / Modifica termine”
add_action( 'tipi_cat_amm_trasp_add_form_fields', 'dci_add_order_field_on_add' );
add_action( 'tipi_cat_amm_trasp_edit_form_fields', 'dci_edit_order_field_on_edit' );

function dci_add_order_field_on_add() { ?>
	<div class="form-field term-order-wrap">
		<label for="ordine_personalizzato"><?php _e( 'Ordine', 'design_comuni_italia' ); ?></label>
		<input name="ordine_personalizzato" id="ordine_personalizzato" type="number" min="0" value="">
		<p class="description"><?php _e( 'Numero per definire l’ordine di visualizzazione.', 'design_comuni_italia' ); ?></p>
	</div>
<?php }

function dci_edit_order_field_on_edit( $term ) {
	$val = intval( get_term_meta( $term->term_id, 'ordine_personalizzato', true ) ); ?>
	<tr class="form-field term-order-wrap">
		<th scope="row"><label for="ordine_personalizzato"><?php _e( 'Ordine', 'design_comuni_italia' ); ?></label></th>
		<td>
			<input name="ordine_personalizzato" id="ordine_personalizzato" type="number" min="0" value="<?php echo esc_attr( $val ); ?>" style="width:80px;">
			<p class="description"><?php _e( 'Numero per definire l’ordine di visualizzazione.', 'design_comuni_italia' ); ?></p>
		</td>
	</tr>
<?php }

# 4.d – Salva il metadato da Quick Edit e da Add/Edit
add_action( 'create_tipi_cat_amm_trasp', 'dci_save_term_order_meta' );
add_action( 'edited_tipi_cat_amm_trasp', 'dci_save_term_order_meta' );

function dci_save_term_order_meta( $term_id ) {
	if ( isset( $_POST['ordine_personalizzato'] ) ) {
		update_term_meta( $term_id, 'ordine_personalizzato', intval( $_POST['ordine_personalizzato'] ) );
	}
}
