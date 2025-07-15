<?php

// 1. Registra la tassonomia
add_action('init', 'dci_register_taxonomy_tipi_cat_amm_trasp', -10);
function dci_register_taxonomy_tipi_cat_amm_trasp() {

    $labels = array(
        'name'              => _x('Tipi categoria Amministrazione Trasparente', 'taxonomy general name', 'design_comuni_italia'),
        'singular_name'     => _x('Tipo di categoria Amministrazione Trasparente', 'taxonomy singular name', 'design_comuni_italia'),
        'search_items'      => __('Cerca Tipo categoria Amministrazione Trasparente', 'design_comuni_italia'),
        'all_items'         => __('Tutti i Tipi di categoria Amministrazione Trasparente', 'design_comuni_italia'),
        'edit_item'         => __('Modifica il Tipo di categoria Amministrazione Trasparente', 'design_comuni_italia'),
        'update_item'       => __('Aggiorna il Tipo di categoria Amministrazione Trasparente', 'design_comuni_italia'),
        'add_new_item'      => __('Aggiungi un Tipo di categoria Amministrazione Trasparente', 'design_comuni_italia'),
        'new_item_name'     => __('Nuovo Tipo di categoria Amministrazione Trasparente', 'design_comuni_italia'),
        'menu_name'         => __('Tipi di categoria Amministrazione Trasparente', 'design_comuni_italia'),
    );

    $args = array(
        'hierarchical'      => true,
        'labels'            => $labels,
        'public'            => true,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'has_archive'       => true,
        'rewrite'           => array('slug' => 'tipi_cat_amm_trasp'),
        'capabilities'      => array(
            'manage_terms'  => 'manage_tipi_cat_amm_trasp',
            'edit_terms'    => 'edit_tipi_cat_amm_trasp',
            'delete_terms'  => 'delete_tipi_cat_amm_trasp',
            'assign_terms'  => 'assign_tipi_cat_amm_trasp'
        )
    );

    register_taxonomy('tipi_cat_amm_trasp', array('elemento_trasparenza'), $args);
}

// 2. Aggiungi campo "Ordinamento" al form di modifica/aggiunta termini
add_action('tipi_cat_amm_trasp_add_form_fields', 'dci_add_ordinamento_field', 10, 2);
add_action('tipi_cat_amm_trasp_edit_form_fields', 'dci_edit_ordinamento_field', 10, 2);

function dci_add_ordinamento_field($taxonomy) {
    ?>
    <div class="form-field term-ordinamento-wrap">
        <label for="ordinamento"><?php _e('Ordinamento', 'design_comuni_italia'); ?></label>
        <input name="ordinamento" id="ordinamento" type="number" min="0" step="1" value="0" />
        <p class="description"><?php _e('Numero per definire l’ordine di visualizzazione della categoria.', 'design_comuni_italia'); ?></p>
    </div>
    <?php
}

function dci_edit_ordinamento_field($term, $taxonomy) {
    $ordinamento = get_term_meta($term->term_id, 'ordinamento', true);
    ?>
    <tr class="form-field term-ordinamento-wrap">
        <th scope="row"><label for="ordinamento"><?php _e('Ordinamento', 'design_comuni_italia'); ?></label></th>
        <td>
            <input name="ordinamento" id="ordinamento" type="number" min="0" step="1" value="<?php echo esc_attr($ordinamento) ?: 0; ?>" />
            <p class="description"><?php _e('Numero per definire l’ordine di visualizzazione della categoria.', 'design_comuni_italia'); ?></p>
        </td>
    </tr>
    <?php
}

// 2.b Aggiungi campo "Visualizza elemento" al form di aggiunta/modifica termini
add_action('tipi_cat_amm_trasp_add_form_fields', 'dci_add_visualizza_elemento_field', 20, 2);
add_action('tipi_cat_amm_trasp_edit_form_fields', 'dci_edit_visualizza_elemento_field', 20, 2);

function dci_add_visualizza_elemento_field($taxonomy) {
    ?>
    <div class="form-field term-visualizza-elemento-wrap">
        <label for="visualizza_elemento">
            <input name="visualizza_elemento" id="visualizza_elemento" type="checkbox" value="1" checked />
            <?php _e('Visualizza elemento', 'design_comuni_italia'); ?>
        </label>
        <p class="description"><?php _e('Visualizza elemento nella lista degli elementi da poter aggiungere nella trasparenza.', 'design_comuni_italia'); ?></p>
    </div>
    <?php
}

function dci_edit_visualizza_elemento_field($term, $taxonomy) {
    $visualizza = get_term_meta($term->term_id, 'visualizza_elemento', true);
    ?>
    <tr class="form-field term-visualizza-elemento-wrap">
        <th scope="row"><?php _e('Visualizza elemento', 'design_comuni_italia'); ?></th>
        <td>
            <label for="visualizza_elemento">
                <input name="visualizza_elemento" id="visualizza_elemento" type="checkbox" value="1" <?php checked($visualizza, '1'); ?> />
                <?php _e('Visualizza elemento nella lista degli elementi da poter aggiungere nella trasparenza.', 'design_comuni_italia'); ?>
            </label>
        </td>
    </tr>
    <?php
}

// 3. Salva il valore del campo ordinamento
add_action('created_tipi_cat_amm_trasp', 'dci_save_ordinamento_meta', 10, 2);
add_action('edited_tipi_cat_amm_trasp', 'dci_save_ordinamento_meta', 10, 2);

function dci_save_ordinamento_meta($term_id, $tt_id) {
    if (isset($_POST['ordinamento'])) {
        $ordinamento = intval($_POST['ordinamento']);
        update_term_meta($term_id, 'ordinamento', $ordinamento);
    }
}

// 3.b Salva il valore del campo visualizza_elemento
add_action('created_tipi_cat_amm_trasp', 'dci_save_visualizza_elemento_meta', 20, 2);
add_action('edited_tipi_cat_amm_trasp', 'dci_save_visualizza_elemento_meta', 20, 2);

function dci_save_visualizza_elemento_meta($term_id, $tt_id) {
    // Salva come '1' se il checkbox è presente, altrimenti '0'
    $value = (isset($_POST['visualizza_elemento']) && $_POST['visualizza_elemento'] == '1') ? '1' : '0';
    update_term_meta($term_id, 'visualizza_elemento', $value);
}

// 4. Mostra la colonna Ordinamento nella lista termini
add_filter('manage_edit-tipi_cat_amm_trasp_columns', 'dci_add_ordinamento_column');
function dci_add_ordinamento_column($columns) {
    $columns['ordinamento'] = __('Ordinamento', 'design_comuni_italia');
    $columns['visualizza_elemento'] = __('Visualizza elemento', 'design_comuni_italia');
    return $columns;
}

add_filter('manage_tipi_cat_amm_trasp_custom_column', 'dci_show_custom_columns', 10, 3);
function dci_show_custom_columns($out, $column, $term_id) {
    if ($column === 'ordinamento') {
        $val = get_term_meta($term_id, 'ordinamento', true);
        return $val !== '' ? esc_html($val) : '&mdash;';
    }
    if ($column === 'visualizza_elemento') {
        $val = get_term_meta($term_id, 'visualizza_elemento', true);
        return ($val === '1') ? __('Sì', 'design_comuni_italia') : __('No', 'design_comuni_italia');
    }
    return $out;
}

?>

