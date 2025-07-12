<?php
/* Template Name: bandi-di-gara
 *
 * Bandi di Gara template file
 *
 * @package Design_Comuni_Italia
 */
global $post, $with_shadow;
$search_url = esc_url( home_url( '/' ));

$tipi_documento = get_terms( array(
    'taxonomy' => 'tipi_documento',
    'hide_empty' => false,
) );

get_header();

?>


	<main>
		<?php
		while ( have_posts() ) :
			the_post();
			
			?>
			<?php 
				$with_shadow = true;
				get_template_part("template-parts/hero/hero"); 
			?>
			<?php
$titolo_bandi = get_field('titolo_bandi');
$descrizione_bandi = get_field('descrizione_bandi');

if ($titolo_bandi || $descrizione_bandi) : ?>
  <section class="section section--trasparenza">
    <div class="container">
      <?php if ($titolo_bandi): ?>
        <h2><?php echo esc_html($titolo_bandi); ?></h2>
      <?php endif; ?>
      <?php if ($descrizione_bandi): ?>
        <div class="descrizione-bandi">
          <?php echo wp_kses_post($descrizione_bandi); ?>
        </div>
      <?php endif; ?>
    </div>
  </section>
<?php endif; ?>
			
			<?php get_template_part("template-parts/bandi-di-gara/tutti-bandi"); ?>

			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
							
		<?php 
			endwhile; // End of the loop.
		?>
	</main>

<?php
get_footer();
