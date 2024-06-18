<?php
/**
 * Nav Menu API: Footer_Menu_Walker class
 *
 * @package WordPress
 * @subpackage Nav_Menus
 * @since 4.6.0
 */

/**
 * Custom class used to implement an HTML list of nav menu items.
 *
 * @since 3.0.0
 *
 * @see Walker
 */


class Footer_Menu_Walker extends Walker_Nav_Menu {
	function start_el(&$output, $item, $depth=0, $args=[], $id=0) {
		$output .= "<li>";
		if ( !$item->url ) $item['url'] = '#';
		
		
	       // Sovrascrivi l'URL per "Luoghi" se è vuoto
	        if ($item->title == 'Luoghi' && $item->url == '/vivere-il-comune') {
	            $item->url = $item->url . '/luoghi';
	        }
	
	        // Sovrascrivi l'URL per "Eventi" se è vuoto
	        if ($item->title == 'Eventi' && $item->url == '/vivere-il-comune') {
	            $item->url = $item->url . '/eventi';
	        }

               // Sovrascrivi l'URL per "Avvisi" se è vuoto
	        if ($item->title == 'Avvisi' && $item->url == '/novita') {
	            $item->url = '/tipi_notizia/avvisi';
	        }
	
               // Sovrascrivi l'URL per "Comunicati" se è vuoto
	        if ($item->title == 'Comunicati' && $item->url == '/novita') {
	            $item->url = '/tipi_notizia/comunicato-stampa';
	        }

               // Sovrascrivi l'URL per "Notizie" se è vuoto
	        if ($item->title == 'Notizie' && $item->url == '/novita') {
	            $item->url = '/tipi_notizia/notizie';
	        }
		
		
		$data_element = '';
		   // Imposta data-elements
		        if ($item->title == 'Leggi le FAQ') {
		            $data_element = "data-element='faq'";
		        }
		        if ($item->title == 'Segnalazione disservizio') {
		            $data_element = "data-element='report-inefficiency'";
		        }
		        if ($item->title == 'Informativa privacy') {
		            $data_element = "data-element='privacy-policy-link'";
		        }
		        if ($item->title == 'Dichiarazione di accessibilità') {
		            $data_element = "data-element='accessibility-link'";
		        }
		        if ($item->title == 'Note legali') {
		            $data_element = "data-element='legal-notes'";
		        }
		        if ($item->title == 'Luoghi') {
		            $data_element = "data-element='LUOGHI'";
		        }


		$output .= '<a href="' . $item->url . '" '.$data_element.'>';
		$output .= $item->title;		
		$output .= '</a>';
	}
}
