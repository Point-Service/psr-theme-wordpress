<?php
/**
 * Design Comuni Italia functions and definitions
 *
 * @link https://developer.wordpress.org/themes/basics/theme-functions/
 *
 * @package Design_Comuni_Italia
 */

/**
 * Funzionalità Trasversali
 */
require get_template_directory() . '/inc/funzionalita_trasversali.php';

/**
 * Load more posts
 */
require get_template_directory() . '/inc/load_more.php';

/**
 * Vocabolario
 */
require get_template_directory() . '/inc/vocabolario.php';

/**
 * Extend User Taxonomy
 */
require get_template_directory() . '/inc/extend-tax-to-user.php';

/**
 * Implement Plugin Activations Rules
 */
require get_template_directory() . '/inc/theme-dependencies.php';

/**
 * Implement CMB2 Custom Field Manager
 */
if ( ! function_exists ( 'dci_get_tipologia_articoli_options' ) ) {
	require get_template_directory() . '/inc/cmb2.php';
	require get_template_directory() . '/inc/backend-template.php';
}

/**
 * Utils functions
 */
require get_template_directory() . '/inc/utils.php';

/**
 * Breadcrumb class
 */
require get_template_directory() . '/inc/breadcrumb.php';

/**
 * Activation Hooks
 */
require get_template_directory() . '/inc/activation.php';

/**
 * Actions & Hooks
 */
require get_template_directory() . '/inc/actions.php';

/**
 * Gutenberg editor rules
 */
require get_template_directory() . '/inc/gutenberg.php';

/**
 * Welcome page
 */
require get_template_directory() . '/inc/welcome.php';

/**
 * main menu walker
 */
require get_template_directory() . '/walkers/main-menu.php';

/**
 * menu header right walker
 */
require get_template_directory() . '/walkers/menu-header-right.php';

/**
 * footer info walker
 */
require get_template_directory() . '/walkers/footer-info.php';



/**
 *  Caricamento dati Trasparenza
 */
require get_template_directory() . '/inc/activationTrasparenza.php';


/**
 * Filters
 */
require get_template_directory() . '/inc/filters.php';

if ( ! function_exists( 'dci_setup' ) ) :
	/**
	 * Sets up theme defaults and registers support for various WordPress features.
	 *
	 * Note that this function is hooked into the after_setup_theme hook, which
	 * runs before the init hook. The init hook is too late for some features, such
	 * as indicating support for post thumbnails.
	 */
	function dci_setup() {
		/*
		 * Make theme available for translation.
		 * Translations can be filed in the /languages/ directory.
		 * If you're building a theme based on Design Comuni Italia, use a find and replace
		 * to change 'design_comuni_italia' to the name of your theme in all the template files.
		 */
		load_theme_textdomain( 'design_comuni_italia', get_template_directory() . '/languages' );


        load_theme_textdomain( 'easy-appointments', get_template_directory() . '/languages' );

        // Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

        // image size
        if ( function_exists( 'add_image_size' ) ) {
            add_image_size( 'article-simple-thumb', 500, 384 , true);
            add_image_size( 'item-thumb', 280, 280 , true);
            add_image_size( 'item-gallery', 730, 485 , true);
            add_image_size( 'vertical-card', 190, 290 , true);

            add_image_size( 'banner', 600, 250 , false);
        }

	}
endif;
add_action( 'after_setup_theme', 'dci_setup' );

/**
 * Register widget area.
 *
 * @link https://developer.wordpress.org/themes/functionality/sidebars/#registering-a-sidebar
 */
function dci_widgets_init() {
}
add_action( 'widgets_init', 'dci_widgets_init' );

/**
 * Enqueue scripts and styles.
 */
function dci_scripts() {

    //wp_deregister_script('jquery');

	//load bootstrap-italia-comuni
    wp_enqueue_style( 'dci-comuni', get_template_directory_uri() . '/assets/css/bootstrap-italia-comuni.min.css');

    wp_enqueue_style( 'dci-font', get_template_directory_uri() . '/assets/css/fonts.css', array('dci-comuni'));
    wp_enqueue_style( 'dci-wp-style', get_template_directory_uri()."/style.css", array('dci-comuni'));


    wp_enqueue_script( 'dci-modernizr', get_template_directory_uri() . '/assets/js/modernizr.custom.js');

	// print css
    wp_enqueue_style('dci-print-style',get_template_directory_uri() . '/print.css', array(),'20190912','print' );



    // Aggiungi il codice JavaScript inline per passare il percorso del tema
    wp_add_inline_script( 'dci-comuni', '
        const theme_folder = "' . get_template_directory_uri() . '"; // URL del tema
    ', 'after');


	// footer
    //load Bootstrap Italia latest js if exists in node_modules
    if (file_exists(dirname(__FILE__) . DIRECTORY_SEPARATOR . '/node_modules/bootstrap-italia/dist/js/bootstrap-italia.bundle.min.js')) {
        wp_enqueue_script( 'dci-boostrap-italia-min-js', get_template_directory_uri() . '/node_modules/bootstrap-italia/dist/js/bootstrap-italia.bundle.min.js', array(), false, true);
    }
    else {
        wp_enqueue_script( 'dci-boostrap-italia-min-js', get_template_directory_uri() . '/assets/js/bootstrap-italia.bundle.min.js', array(), false, true);
    }
	wp_enqueue_script( 'dci-comuni', get_template_directory_uri() . '/assets/js/comuni.js', array(), false, true);
	wp_add_inline_script( 'dci-comuni', 'window.wpRestApi = "' . get_rest_url() . '"', 'before' );

	wp_enqueue_script( 'dci-jquery-easing', get_template_directory_uri() . '/assets/js/components/jquery-easing/jquery.easing.js', array(), false, true);
	wp_enqueue_script( 'dci-jquery-scrollto', get_template_directory_uri() . '/assets/js/components/jquery.scrollto/jquery.scrollTo.js', array(), false, true);
	wp_enqueue_script( 'dci-jquery-responsive-dom', get_template_directory_uri() . '/assets/js/components/ResponsiveDom/js/jquery.responsive-dom.js', array(), false, true);
	wp_enqueue_script( 'dci-jpushmenu', get_template_directory_uri() . '/assets/js/components/jPushMenu/jpushmenu.js', array(), false, true);
	wp_enqueue_script( 'dci-perfect-scrollbar', get_template_directory_uri() . '/assets/js/components/perfect-scrollbar-master/perfect-scrollbar/js/perfect-scrollbar.jquery.js', array(), false, true);
	wp_enqueue_script( 'dci-vallento', get_template_directory_uri() . '/assets/js/components/vallenato.js-master/vallenato.js', array(), false, true);
	wp_enqueue_script( 'dci-jquery-responsive-tabs', get_template_directory_uri() . '/assets/js/components/responsive-tabs/js/jquery.responsiveTabs.js', array(), false, true);
	wp_enqueue_script( 'dci-fitvids', get_template_directory_uri() . '/assets/js/components/fitvids/jquery.fitvids.js', array(), false, true);
	wp_enqueue_script( 'dci-sticky-kit', get_template_directory_uri() . '/assets/js/components/sticky-kit-master/dist/sticky-kit.js', array(), false, true);
	
	wp_enqueue_script( 'dci-jquery-match-height', get_template_directory_uri() . '/assets/js/components/jquery-match-height/dist/jquery.matchHeight.js', array(), false, true);

	if(is_singular(array("servizio", "struttura", "luogo", "evento", "scheda_progetto", "post", "circolare", "indirizzo")) || is_archive() || is_search() || is_post_type_archive("luogo")) {
		wp_enqueue_script( 'dci-leaflet-js', get_template_directory_uri() . '/assets/js/components/leaflet/leaflet.js', array(), false, true);
    }

	if(is_singular(array("evento","scheda_progetto")) || is_home() || is_archive() ){
		wp_enqueue_script( 'dci-clndr-json2', get_template_directory_uri() . '/assets/js/components/clndr/json2.js', array(), false, false);
		wp_enqueue_script( 'dci-clndr-moment', get_template_directory_uri() . '/assets/js/components/clndr/moment.js', array(), false, false);
		wp_enqueue_script( 'dci-clndr-underscore', get_template_directory_uri() . '/assets/js/components/clndr/underscore.js', array(), false, false);
		wp_enqueue_script( 'dci-clndr-clndr', get_template_directory_uri() . '/assets/js/components/clndr/clndr.js', array(), false, false);
		wp_enqueue_script( 'dci-clndr-it', get_template_directory_uri() . '/assets/js/components/clndr/it.js', array(), false, false);
	}

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'dci_scripts' );

function add_menu_link_class( $atts, $item, $args ) {
	if (property_exists($args, 'link_class')) {
	  $atts['class'] = $args->link_class;
	}
	return $atts;
  }
  add_filter( 'nav_menu_link_attributes', 'add_menu_link_class', 1, 3 );

  function add_menu_list_item_class($classes, $item, $args) {
	if (property_exists($args, 'list_item_class')) {
		$classes[] = $args->list_item_class;
	}
	return $classes;
  }
  add_filter('nav_menu_css_class', 'add_menu_list_item_class', 1, 3);

  function max_nav_items($sorted_menu_items, $args){
    if (property_exists($args, 'li_slice')) {
		$slice = $args->li_slice;
		$items = array();
		foreach($sorted_menu_items as $item){
			if($item->menu_item_parent != 0) continue;
			$items[] = $item;
		}
		$items = array_slice($items, $slice[0], $slice[1]);
		foreach($sorted_menu_items as $key=>$one_item){
			if($one_item->menu_item_parent == 0 && !in_array($one_item,$items)){
				unset($sorted_menu_items[$key]);
			}
		}
	}
    return $sorted_menu_items;
}
add_filter("wp_nav_menu_objects","max_nav_items",10,2);

function console_log ($output, $msg = "log") {
    echo '<script> console.log("'. $msg .'",'. json_encode($output) .')</script>';
};

function get_parent_template () {
	return basename( get_page_template_slug( wp_get_post_parent_id() ) );
}


 // Restituisce il formato e le dimensioni di un allegato
function getFileSizeAndFormat($url) {
    $percorso = parse_url($url);
    $percorso = isset($percorso["path"]) ? substr($percorso["path"], 0, -strlen(pathinfo($url, PATHINFO_BASENAME))) : '';
    $response = wp_remote_head($url);

    if (is_wp_error($response)) {
        return 'Errore nel recupero delle informazioni del file';
    }

    $headers = wp_remote_retrieve_headers($response);
    $content_length = isset($headers['content-length']) ? intval($headers['content-length']) : 0;

    $base = log($content_length, 1024);
    $suffixes = array('', 'Kb', 'Mb', 'Gb', 'Tb');
    $size_formatted = round(pow(1024, $base - floor($base)), 2) . ' ' . $suffixes[floor($base)];

    $info_file = pathinfo($url);
    $file_format = strtoupper(isset($info_file['extension']) ? $info_file['extension'] : '');

    return $file_format . ' ' . $size_formatted;
}






function my_custom_one_time_function() {
    // Controlla se l'opzione è già stata impostata
    if (!get_option('my_custom_function_executed')) {
        
		$args = array(
			'post_type' => 'notizia', 
			'post_status' => 'publish', 
			'posts_per_page'   => -1 
		);
		$posts = get_posts($args);
		foreach ( $posts as $post ) {
			
			$meta_valore = get_post_meta($post->ID, '_dci_notizia_data_pubblicazione', true);
			
			if (empty($meta_valore)) {
				// Recupera la data di pubblicazione del post
				$data_pubblicazione = strtotime(get_the_date('d-m-Y', $post->ID));
				
				// Aggiorna il campo personalizzato con la data di pubblicazione
				update_post_meta($post->ID, '_dci_notizia_data_pubblicazione', $data_pubblicazione);
			}
		}

        // Imposta l'opzione per segnare che la funzione è stata eseguita
        update_option('my_custom_function_executed', 1);
    }
}
add_action('init', 'my_custom_one_time_function');





add_action( 'admin_enqueue_scripts', 'dci_evidenzia_categorie_cmb2', 20 );
function dci_evidenzia_categorie_cmb2( $hook ) {

    // Applica solo su pagine nuovo/modifica post tipo 'elemento_trasparenza'
    $tipo_post = $_GET['post_type'] ?? get_post_type( $_GET['post'] ?? 0 );

    if ( ! in_array( $hook, ['post-new.php', 'post.php'] ) || $tipo_post !== 'elemento_trasparenza' ) {
        return;
    }

    // Aggiungo gli stili CSS inline
    wp_add_inline_style(
        'wp-admin',
        "
        .cmb2-categoria-principale {
            font-weight: 700 !important;
            color: #000000 !important;
            text-transform: uppercase !important; /* Maiuscolo */
        }
        .cmb2-sottocategoria {
            color: #343a40 !important;
        }
        .cmb2-radio-list input[type='checkbox']:disabled {
            cursor: not-allowed; /* Cambia il cursore per la checkbox disabilitata */
        }
        .cmb2-radio-list input[type='checkbox'] {
            display: inline-block;
        }
        .cmb2-radio-list input[type='checkbox'].disabled-checkbox {
            display: none;
        }
        "
    );

    // Aggiungo lo script JS inline per disabilitare la checkbox nella categoria principale
    wp_add_inline_script(
        'jquery-core',
        <<<JS
        (function($){
            $(function(){
                $('.cmb2-radio-list, .cmb2-checkbox-list').each(function(){
                    $(this).children('li').each(function(){
                        var label = $(this).children('label').first();
                        var checkbox = $(this).children('input[type="checkbox"]');
                        
                        if(label.length){
                            // Se è la categoria principale
                            if(label.html().indexOf('&nbsp;') === -1){
                                label.addClass('cmb2-categoria-principale');
                                
                                // Disabilito la checkbox e aggiungo la classe per nasconderla
                                checkbox.prop('disabled', true);  // Disabilita la checkbox
                                checkbox.addClass('disabled-checkbox');  // Nasconde la checkbox

                                // Se vuoi rimuovere la checkbox dal DOM, puoi farlo con il seguente codice:
                                // checkbox.closest('li').remove(); 
                            } else {
                                label.addClass('cmb2-sottocategoria');
                            }
                        }
                    });
                });
            });
        })(jQuery);
JS
    );
}


function crea_pagina_sitemap_personalizzata() {
    $slug = 'page-sitemap';
    $pagina = get_page_by_path($slug);

    if (!$pagina) {
        $pagina_id = wp_insert_post(array(
            'post_title'    => 'Mappa del Sito',
            'post_name'     => $slug,
            'post_content'  => '',
            'post_status'   => 'publish',
            'post_type'     => 'page',
            'page_template' => 'page-templates/page-sitemap.php', // ✅ Con cartella
        ));

        if (!is_wp_error($pagina_id)) {
            error_log('Pagina "Mappa del Sito" creata automaticamente.');
        }
    } else {
        // Se esiste già, aggiorna il template se non è corretto
        if (get_page_template_slug($pagina->ID) !== 'page-templates/page-sitemap.php') {
            wp_update_post(array(
                'ID'            => $pagina->ID,
                'page_template' => 'page-templates/page-sitemap.php',
            ));
        }
    }
}
add_action('after_setup_theme', 'crea_pagina_sitemap_personalizzata');

