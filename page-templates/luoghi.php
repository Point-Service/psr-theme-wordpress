<?php
/* Template Name: luoghi
 *
 * luoghi template file
 *
 * @package Design_Comuni_Italia
 */
global $post;
get_header();

?>
	<main>
		<?php
		while ( have_posts() ) :
			the_post();
			
			$img = dci_get_option('immagine', 'luoghi');
			$didascalia = dci_get_option('didascalia', 'luoghi');
		
			$args = array(
				'posts_per_page' => -1,
				'post_type' => 'luogo',
				'post_status'    => 'publish'
			);
			$schede_luoghi = get_posts($args);

		?>
		<?php 
			$with_shadow = true;
			get_template_part("template-parts/hero/hero"); 
		?>		
			<?php get_template_part("template-parts/vivere-comune/tutti-luoghi"); ?>
			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
							
		<?php 
			endwhile; // End of the loop.
		?>
	</main>

<?php
get_footer();
