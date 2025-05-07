<?php


add_action( 'wp_enqueue_scripts', 'load_more_script' );
function load_more_script() {
	global $wp_query, $the_query, $wp_the_query;

    wp_enqueue_script( 'dci-load_more', get_template_directory_uri() . '/assets/js/load_more.js', array('jquery'), null, true );
    $variables = array(
        'ajaxurl' => admin_url( 'admin-ajax.php' ),
        'posts' => json_encode( $wp_query->query_vars ), 
		'current_page' => get_query_var( 'paged' ) ? get_query_var('paged') : 1,
		'max_page' => $wp_query->max_num_pages,
    );
    wp_localize_script('dci-load_more', "data", $variables);
}

function load_template_part($template_name, $part_name=null) {
    ob_start();
    get_template_part($template_name, $part_name);
    $var = ob_get_contents();
    ob_end_clean();
    return $var;
}

// load more posts
add_action("wp_ajax_load_more" , "load_more");
add_action("wp_ajax_nopriv_load_more" , "load_more");
function load_more(){
	global $servizio, $i, $hide_categories;

	$load_card_type = $_POST['load_card_type'];
	$post_types = json_decode(stripslashes($_POST['post_types']), true);
	$url_query_params = json_decode(stripslashes($_POST['query_params']), true);
	$additional_filter = json_decode(stripslashes($_POST['additional_filter']), true);

	$args = array(
		's' => sanitize_text_field($_POST['search']),
		'posts_per_page' => intval($_POST['post_count']) + intval($_POST['load_posts']),
		'post_type' => $post_types,
		'post_status' => 'publish',
		'order' => 'DESC',
		'meta_query' => array(
			array(
				'key' => '_dci_notizia_data_pubblicazione',
			)
		),
		'meta_type' => 'text_date_timestamp',
		'orderby' => 'meta_value_num',
	);

	// Sovrascrivi argomenti se presenti
	if (isset($url_query_params["post_terms"])) {
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'argomenti',
				'field' => 'id',
				'terms' => array_map('intval', $url_query_params["post_terms"]),
			)
		);
	}

	if (isset($url_query_params["post_types"])) {
		$args['post_type'] = $url_query_params["post_types"];
	}
	if (isset($url_query_params["s"])) {
		$args['s'] = sanitize_text_field($url_query_params["s"]);
	}
	if (!empty($additional_filter)) {
		$args = array_merge($args, $additional_filter);
	}

	// Usa WP_Query
	$new_query = new WP_Query($args);
	$out = '';
	$i = 0;

	if ($new_query->have_posts()) :
		while ($new_query->have_posts()) : $new_query->the_post();
			$post = get_post();
			++$i;

			switch ($load_card_type) {
				case 'servizio':
				case 'categoria_servizio':
					$servizio = $post;
					if ($load_card_type === 'categoria_servizio') $hide_categories = true;
					$out .= load_template_part("template-parts/servizio/card");
					break;
				case 'notizia':
					$out .= load_template_part("template-parts/novita/cards-list");
					break;
				case 'documento':
					$out .= load_template_part("template-parts/documento/cards-list");
					break;
				case 'global-search':
					$out .= load_template_part("template-parts/search/item");
					break;
				case 'commissario':
					$out .= load_template_part("template-parts/commissario_osl/cards-list");
					break;
				case 'progetto':
					$out .= load_template_part("template-parts/progetti/cards-list");
					break;
				case 'personale-amministrativo':
					$out .= load_template_part("template-parts/personale-amministrativo/cards-list");
					break;
				case 'domanda-frequente':
					$out .= load_template_part("template-parts/domanda-frequente/item");
					break;
				case 'luogo':
					$out .= load_template_part("template-parts/luogo/card-full");
					break;
			}
		endwhile;
	endif;

	wp_reset_postdata();

	wp_send_json(array(
		'response' => $out,
		'post_count' => $new_query->post_count,
		'all_results' => ($new_query->found_posts <= $new_query->post_count),
	));
}

