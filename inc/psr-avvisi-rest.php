<?php
/**
 * PSR - Endpoint REST di sola lettura per gli Avvisi.
 *
 * Riutilizza il controller della tipologia "notizia" per mantenere identici
 * campi, permessi di lettura, paginazione e intestazioni HTTP dell'API Notizie.
 * Non modifica le route o le query delle API esistenti.
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('rest_api_init', static function () {
    if (!post_type_exists('notizia') || !taxonomy_exists('tipi_notizia')) {
        return;
    }

    $controller = new WP_REST_Posts_Controller('notizia');

    register_rest_route('wp/v2', '/avvisi', array(
        array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => static function (WP_REST_Request $request) use ($controller) {
                // Il filtro agisce soltanto durante questa richiesta, non sulle Notizie.
                $only_avvisi = static function ($args, $current_request) use ($request) {
                    if ($current_request !== $request) {
                        return $args;
                    }

                    $avvisi_clause = array(
                        'taxonomy' => 'tipi_notizia',
                        'field' => 'slug',
                        // "avvisi" e' lo slug standard; "avviso" copre vecchi siti.
                        'terms' => array('avvisi', 'avviso'),
                        'include_children' => true,
                        'operator' => 'IN',
                    );

                    $existing = isset($args['tax_query']) && is_array($args['tax_query'])
                        ? $args['tax_query']
                        : array();

                    $args['tax_query'] = $existing
                        ? array('relation' => 'AND', $existing, $avvisi_clause)
                        : array($avvisi_clause);

                    return $args;
                };

                add_filter('rest_notizia_query', $only_avvisi, PHP_INT_MAX, 2);
                try {
                    return $controller->get_items($request);
                } finally {
                    remove_filter('rest_notizia_query', $only_avvisi, PHP_INT_MAX);
                }
            },
            'permission_callback' => array($controller, 'get_items_permissions_check'),
            'args' => $controller->get_collection_params(),
        ),
    ));
});
