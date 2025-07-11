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





// 3. Aggiunge colonna "Ordinamento" nella lista dei termini
add_filter('manage_edit-tipi_cat_amm_trasp_columns', 'dci_add_ordinamento_column');
function dci_add_ordinamento_column($columns) {
    $columns['ordinamento'] = __('Ordinamento', 'design_comuni_italia');
    return $columns;
}

add_filter('manage_tipi_cat_amm_trasp_custom_column', 'dci_show_ordinamento_column', 10, 3);
function dci_show_ordinamento_column($out, $column, $term_id) {
    if ($column === 'ordinamento') {
        $val = get_term_meta($term_id, '_dci_ordinamento', true);
        return $val !== '' ? esc_html($val) : '&mdash;';
    }
    return $out;
}



add_action('pre_get_terms', 'dci_order_terms_by_ordinamento');
function dci_order_terms_by_ordinamento($query) {
    global $pagenow;

    if (
        is_admin() &&
        $pagenow === 'edit-tags.php' &&
        isset($_GET['taxonomy']) &&
        $_GET['taxonomy'] === 'tipi_cat_amm_trasp' &&
        !isset($_GET['orderby'])
    ) {
        $query->meta_key = '_dci_ordinamento';
        $query->orderby  = 'meta_value_num';
        $query->order    = 'ASC';
    }
}


?>

