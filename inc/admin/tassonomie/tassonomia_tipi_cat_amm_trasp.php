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

	// Aggiungi il campo di selezione per la categoria genitore
	?>
	<div class="form-field term-parent-wrap">
		<label for="parent"><?php _e( 'Categoria Padre', 'design_comuni_italia' ); ?></label>
		<?php
		wp_dropdown_categories( array(
			'taxonomy'         => 'tipi_cat_amm_trasp',
			'name'             => 'parent',
			'id'               => 'parent',
			'hide_empty'       => false,
			'show_option_none' => __( 'Seleziona una categoria principale', 'design_comuni_italia' ),
			'selected'         => $parent_term_id,
			'orderby'          => 'name',
			'option_none_value' => 0
		) );
		?>
	</div>
	<?php

	// Ordina per categoria principale o secondaria
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

    // Riorganizza le colonne
    $new_columns['cb'] = $columns['cb'];
    $new_columns['name'] = $columns['name'];
    $new_columns['ordinamento'] = __('Ordinamento', 'design_comuni_italia');
    $new_columns['visualizza_elemento'] = __('Visualizza', 'design_comuni_italia');
    $new_columns['term_url'] = __('URL', 'design_comuni_italia');
    $new_columns['taxonomy'] = $columns['taxonomy'];

    return $new_columns;
}

/* 4.2 – Aggiungi il contenuto nelle colonne personalizzate */
add_filter('manage_tipi_cat_amm_trasp_custom_column', 'dci_custom_column_content', 10, 3);
function dci_custom_column_content($content, $column_name, $term_id) {
    if ($column_name == 'ordinamento') {
        $content = get_term_meta($term_id, 'ordinamento', true);
    }

    if ($column_name == 'visualizza_elemento') {
        $content = get_term_meta($term_id, 'visualizza_elemento', true) ? 'Sì' : 'No';
    }

    if ($column_name == 'term_url') {
        $content = get_term_meta($term_id, 'term_url', true);
    }

    return $content;
}

/* ------------------------------------------------------------------
 * 5. Javascript per la gestione dinamica dei campi
 * ---------------------------------------------------------------- */
add_action('admin_footer', 'dci_admin_footer_script');
function dci_admin_footer_script() {
	// Solo nella pagina di creazione di nuovi termini per la tassonomia tipi_cat_amm_trasp
	if ( isset( $_GET['taxonomy'] ) && $_GET['taxonomy'] === 'tipi_cat_amm_trasp' ) :
	?>
	<script type="text/javascript">
		jQuery(document).ready(function($) {
			// Nascondi o mostra i campi in base alla selezione della categoria padre
			$('#parent').on('change', function() {
				var parentTerm = $(this).val();
				
				// Se la categoria è principale (ID == 0)
				if (parentTerm == 0) {
					// Nascondi i campi URL e "Apri in una nuova finestra"
					$('.term-url-wrap').hide();
					$('.term-open-new-window-wrap').hide();
				} else {
					// Mostra i campi URL e "Apri in una nuova finestra"
					$('.term-url-wrap').show();
					$('.term-open-new-window-wrap').show();
				}
			});

			// Esegui il controllo anche all'inizio in caso di caricamento
			$('#parent').trigger('change');
		});
	</script>
	<?php
	endif;
}
?>

