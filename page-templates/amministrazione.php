<?php
/* Template Name: amministrazione
 *
 * amministrazione template file
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
			?>
			<?php get_template_part("template-parts/hero/hero"); ?>
			<?php get_template_part("template-parts/amministrazione/evidenza"); ?>
			<?php get_template_part("template-parts/amministrazione/cards-list"); ?>
			    <a class="read-more pt-0" href="/politici">
			        <span class="list-item-title-icon-wrapper">
			            <span class="text">Visualizza Organo Politico</span>
			        </span>
			    </a>
			
			<?php get_template_part("template-parts/politici/cards-list"); ?>
			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
							
		<?php 
			endwhile; // End of the loop.
		?>
	</main>

<?php
get_footer();



