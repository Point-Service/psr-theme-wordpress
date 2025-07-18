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

/* ------------------------------------------------------------------
 * 2. Campi personalizzati nei form di Aggiungi / Modifica termine
 * ---------------------------------------------------------------- */

/* ----- Aggiungi (pagina “Aggiungi nuovo”) */
add_action( 'tipi_cat_amm_trasp_add_form_fields', 'dci_tassonomia_add_fields' );
function dci_tassonomia_add_fields() {
	// Ottieni il termine corrente (se esiste)
	$parent_term_id = isset($_GET['parent']) ? $_GET['parent'] : 0;  // Per aggiungere, controlla se è una categoria principale

	// Controlla se la categoria è principale
	if ( $parent_term_id == 0 ) {
		// Nascondi i campi URL e apri in una nuova finestra per le categorie principali
		?>
		<!-- Ordinamento -->
		<div class="form-field term-ordinamento-wrap">
			<label for="ordinamento"><?php _e( 'Ordinamento', 'design_comuni_italia' ); ?></label>
			<input name="ordinamento" id="ordinamento" type="number" min="0" step="1" value="0" />
			<p class="description"><?php _e( 'Numero per definire l’ordine di visualizzazione della categoria.', 'design_comuni_italia' ); ?></p>
		</div>

		<!-- Visualizza elemento -->
		<div class="form-field term-visualizza-elemento-wrap">
			<label for="visualizza_elemento">
				<input name="visualizza_elemento" id="visualizza_elemento" type="checkbox" value="1" checked />
				<?php _e( 'Visualizza elemento nella lista degli elementi da poter aggiungere nella trasparenza.', 'design_comuni_italia' ); ?>
			</label>
		</div>
		<?php
	} else {
		// Se è una categoria secondaria, mostra i campi URL
		?>
		<!-- URL personalizzato -->
		<div class="form-field term-url-wrap">
			<label for="term_url"><?php _e( 'URL personalizzato', 'design_comuni_italia' ); ?></label>
			<input name="term_url" id="term_url" type="url" value="" placeholder="https://..." />
			<p class="description"><?php _e( 'Inserisci un URL per indirizzare direttamente alla pagina. Se lasci vuoto, non verrà creato un link.', 'design_comuni_italia' ); ?></p>
		</div>

		<!-- Apri in una nuova finestra -->
		<div class="form-field term-open-new-window-wrap">
			<label for="open_new_window">
				<input name="open_new_window" id="open_new_window" type="checkbox" value="1" />
				<?php _e( 'Apri il link in una nuova finestra', 'design_comuni_italia' ); ?>
			</label>
		</div>

		<!-- Ordinamento -->
		<div class="form-field term-ordinamento-wrap">
			<label for="ordinamento"><?php _e( 'Ordinamento', 'design_comuni_italia' ); ?></label>
			<input name="ordinamento" id="ordinamento" type="number" min="0" step="1" value="0" />
			<p class="description"><?php _e( 'Numero per definire l’ordine di visualizzazione della categoria.', 'design_comuni_italia' ); ?></p>
		</div>

		<!-- Visualizza elemento -->
		<div class="form-field term-visualizza-elemento-wrap">
			<label for="visualizza_elemento">
				<input name="visualizza_elemento" id="visualizza_elemento" type="checkbox" value="1" checked />
				<?php _e( 'Visualizza elemento nella lista degli elementi da poter aggiungere nella trasparenza.', 'design_comuni_italia' ); ?>
			</label>
		</div>
		<?php
	}
}

/* ----- Modifica (pagina “Modifica termine”) */
add_action( 'tipi_cat_amm_trasp_edit_form_fields', 'dci_tassonomia_edit_fields' );
function dci_tassonomia_edit_fields( $term ) {

	$ordinamento    = get_term_meta( $term->term_id, 'ordinamento', true );
	$visualizza     = get_term_meta( $term->term_id, 'visualizza_elemento', true );
	$term_url       = get_term_meta( $term->term_id, 'term_url', true ); // Prendi l'URL dal meta
	$open_new_window = get_term_meta( $term->term_id, 'open_new_window', true ); // Prendi il flag per "Apri in nuova finestra"

	// Ottieni il termine genitore
	$parent_term_id = $term->parent ? $term->parent : 0;

	// Se la categoria è principale (parent = 0), nascondi i campi URL e apri in una nuova finestra
	if ( $parent_term_id == 0 ) {
		?>
		<!-- Ordinamento -->
		<tr class="form-field term-ordinamento-wrap">
			<th scope="row"><label for="ordinamento"><?php _e( 'Ordinamento', 'design_comuni_italia' ); ?></label></th>
			<td>
				<input name="ordinamento" id="ordinamento" type="number" min="0" step="1" value="<?php echo esc_attr( $ordinamento ?: 0 ); ?>" />
				<p class="description"><?php _e( 'Numero per definire l’ordine di visualizzazione della categoria.', 'design_comuni_italia' ); ?></p>
			</td>
		</tr>

		<!-- Visualizza elemento -->
		<tr class="form-field term-visualizza-elemento-wrap">
			<th scope="row"><?php _e( 'Visualizza elemento', 'design_comuni_italia' ); ?></th>
			<td>
				<label for="visualizza_elemento">
					<input name="visualizza_elemento" id="visualizza_elemento" type="checkbox" value="1" <?php checked( $visualizza, '1' ); ?> />
					<?php _e( 'Visualizza elemento nella lista degli elementi da poter aggiungere nella trasparenza.', 'design_comuni_italia' ); ?>
				</label>
			</td>
		</tr>
		<?php
	} else {
		// Se è una categoria secondaria, mostra i campi URL
		?>
		<!-- URL personalizzato -->
		<tr class="form-field term-url-wrap">
			<th scope="row"><label for="term_url"><?php _e( 'URL personalizzato', 'design_comuni_italia' ); ?></label></th>
			<td>
				<input name="term_url" id="term_url" type="url" value="<?php echo esc_attr( $term_url ); ?>" placeholder="https://..." />
				<p class="description"><?php _e( 'Inserisci un URL per indirizzare direttamente alla pagina. Se lasci vuoto, non verrà creato un link.', 'design_comuni_italia' ); ?></p>
			</td>
		</tr>

		<!-- Apri in una nuova finestra -->
		<tr class="form-field term-open-new-window-wrap">
			<th scope="row"><?php _e( 'Apri in una nuova finestra', 'design_comuni_italia' ); ?></th>
			<td>
				<label for="open_new_window">
					<input name="open_new_window" id="open_new_window" type="checkbox" value="1" <?php checked( $open_new_window, '1' ); ?> />
					<?php _e( 'Apri il link in una nuova finestra', 'design_comuni_italia' ); ?>
				</label>
			</td>
		</tr>

		<!-- Ordinamento -->
		<tr class="form-field term-ordinamento-wrap">
			<th scope="row"><label for="ordinamento"><?php _e( 'Ordinamento', 'design_comuni_italia' ); ?></label></th>
			<td>
				<input name="ordinamento" id="ordinamento" type="number" min="0" step="1" value="<?php echo esc_attr( $ordinamento ?: 0 ); ?>" />
				<p class="description"><?php _e( 'Numero per definire l’ordine di visualizzazione della categoria.', 'design_comuni_italia' ); ?></p>
			</td>
		</tr>

		<!-- Visualizza elemento -->
		<tr class="form-field term-visualizza-elemento-wrap">
			<th scope="row"><?php _e( 'Visualizza elemento', 'design_comuni_italia' ); ?></th>
			<td>
				<label for="visualizza_elemento">
					<input name="visualizza_elemento" id="visualizza_elemento" type="checkbox" value="1" <?php checked( $visualizza, '1' ); ?> />
					<?php _e( 'Visualizza elemento nella lista degli elementi da poter aggiungere nella trasparenza.', 'design_comuni_italia' ); ?>
				</label>
			</td>
		</tr>
	<?php }
}

/* ------------------------------------------------------------------
 * 3. Salva i metadati
 * ---------------------------------------------------------------- */
add_action( 'created_tipi_cat_amm_trasp', 'dci_save_term_meta', 10, 2 );
add_action( 'edited_tipi_cat_amm_trasp',  'dci_save_term_meta', 10, 2 );
function dci_save_term_meta( $term_id ) {

	if ( isset( $_POST['ordinamento'] ) ) {
		update_term_meta( $term_id, 'ordinamento', intval( $_POST['ordinamento'] ) );
	}

	$visualizza = isset( $_POST['visualizza_elemento'] ) ? '1' : '0';
	update_term_meta( $term_id, 'visualizza_elemento', $visualizza );

	if ( isset( $_POST['term_url'] ) ) {
		update_term_meta( $term_id, 'term_url', esc_url( $_POST['term_url'] ) );
	}

	$open_new_window = isset( $_POST['open_new_window'] ) ? '1' : '0';
	update_term_meta( $term_id, 'open_new_window', $open_new_window );
}

/* ------------------------------------------------------------------
 * 4. Colonne personalizzate nella tabella dei termini
 * ---------------------------------------------------------------- */

/* 4.1 – Aggiunge “Ordinamento”, “Visualizza” e “URL” */
add_filter('manage_edit-tipi_cat_amm_trasp_columns', 'dci_custom_column_order');
function dci_custom_column_order($columns) {
    $new_columns = [];

    foreach ($columns as $key => $label) {
        $new_columns[$key] = $label;

        // Dopo "name" inserisci Ordinamento, Visualizza e URL
        if ($key === 'name') {
            $new_columns['ordinamento'] = __('Ordinamento', 'design_comuni_italia');
            $new_columns['visualizza_item'] = __('Visualizza', 'design_comuni_italia');
            $new_columns['term_url'] = __('URL', 'design_comuni_italia');
        }
    }

    return $new_columns;
}

/* 4.2 – Popola le colonne “Ordinamento”, “Visualizza” e “URL” */
add_filter( 'manage_tipi_cat_amm_trasp_custom_column', 'dci_show_custom_columns', 10, 3 );
function dci_show_custom_columns( $out, $column, $term_id ) {

	switch ( $column ) {

		case 'ordinamento':
			$val = get_term_meta( $term_id, 'ordinamento', true );
			return $val !== '' ? esc_html( $val ) : '&mdash;';

		case 'visualizza_item':
			$show = get_term_meta( $term_id, 'visualizza_elemento', true );
			$show = ( $show === '' ) ? '1' : $show; // default visibile
			return $show === '1'
				? __( 'Sì', 'design_comuni_italia' )
				: __( 'No', 'design_comuni_italia' );

		case 'term_url':
			$term_url = get_term_meta( $term_id, 'term_url', true );
			$open_new_window = get_term_meta( $term_id, 'open_new_window', true );
			if ( $term_url ) {
				$target = $open_new_window ? ' target="_blank"' : '';
				return '<a href="' . esc_url( $term_url ) . '"' . $target . '>' . esc_html( $term_url ) . '</a>';
			}
			return '&mdash;';
	}

	return $out;
}
?>


