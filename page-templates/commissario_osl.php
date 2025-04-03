<?php
/* Template Name: Commissario-OSL
 *
 * Commissario template file
 *
 * @package Design_Comuni_Italia
 */
global $post, $with_shadow;
$search_url = esc_url( home_url( '/' ));

function info(){?>
<section class="hero-img mb-20 mb-lg-50">
    <div class="container">
        <div class="row">
		<article class="commissario-osl">
		    <h1>Commissario OSL</h1>		    
		    <p>Il <strong>Commissario OSL (Organo Straordinario di Liquidazione)</strong> è una figura istituzionale nominata per gestire il risanamento finanziario degli enti locali in dissesto. Il suo compito principale è quello di amministrare la massa passiva dell’ente, provvedendo alla ricognizione dei debiti e alla loro estinzione secondo le normative vigenti.</p>

            </p>
                 <h3>Compiti e Funzioni</h3>
		    <ul>
		        <li>Verifica e certificazione della situazione economico-finanziaria dell’ente</li>
		        <li>Gestione della liquidazione dei debiti pregressi</li>
		        <li>Adozione di misure per il riequilibrio del bilancio</li>
		        <li>Interazione con creditori e soggetti coinvolti nel processo di risanamento</li>
		    </ul>
            </ul>
        </div>
    </div>
</section>


<?php }

get_header();
?>
	<main>
		<?php
		while ( have_posts() ) :
			the_post();
			
			$with_shadow = true;
			?>
			<?php  get_template_part("template-parts/commissario_osl/hero"); ?>
                        <?php info();?>
			<?php // get_template_part("template-parts/progetti/tutti"); ?>
			<?php // get_template_part("template-parts/progetti/categorie"); ?>
			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
		<?php 
			endwhile; // End of the loop.
		?>
	</main>

<?php
get_footer();
