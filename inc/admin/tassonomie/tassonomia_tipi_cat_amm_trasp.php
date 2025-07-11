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

add_action( 'cmb2_admin_init', 'dci_register_taxonomy_metabox' );
/**
 * Hook in and add a metabox to add fields to taxonomy terms
 */
function dci_register_taxonomy_metabox() {
    $prefix = 'dci_term_';

    /**
     * Metabox to add fields to categories and tags
     */
    $cmb_term = new_cmb2_box( array(
        'id'               => $prefix . 'edit',
        'title'            => __( 'Personalizzazione <b>pagina Argomento</b>' , 'design_comuni_italia' ), // Doesn't output for term boxes
        'object_types'     => array( 'term' ), // Tells CMB2 to use term_meta vs post_meta
        'taxonomies'       => array( 'argomenti' ), // Tells CMB2 which taxonomies should have these fields
        // 'new_term_section' => true, // Will display in the "Add New Category" section
        'context' => 'normal',
        'priority' => 'high',
    ) );


}

?>

