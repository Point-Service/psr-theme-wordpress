<?php
/* Template Name: piano_miglioramento
 *
 * note legali template file
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

<main>
  <div class="container">
    <div class="row mt-lg-50">

 <div class="container py-4">
            <h4>Metriche oggetto del piano di miglioramento</h4>
<div class="table-responsive"><table class="table  table-striped table-bordered table-hover"  class="Table Table--withBorder" style="border-collapse: collapse; width: 100%; height: 608px;">
<thead>
<tr style="background-color: #0059b3; color: #ffffff;">
<th style="width: 33.3333%; text-align: center; height: 24px;"><strong>Metrica</strong></th>
<th style="width: 33.3333%; text-align: center; height: 24px;"><strong>Azioni future di miglioramento</strong></th>
<th style="width: 33.3333%; text-align: center; height: 24px;"><strong>Tempistiche previste</strong></th>
</tr>
</thead>
<tbody>
<tr style="height: 176px;">
<td style="width: 33.3333%; height: 176px;"><strong>First Contentful Paint</strong></td>
<td style="width: 33.3333%; height: 176px;">
<div id="accordion-panel-1" class="Accordion-panel fr-accordion__panel js-fr-accordion__panel" tabindex="0" role="tabpanel" aria-labelledby="accordion-header-1" aria-hidden="false" data-focus-mouse="false">
<div class="Prose u-padding-top-xs">
<ul>
<li>ottimizzazione e minimizzazione di fogli di stile CSS e codice JS per ridurre le risorse caricate;</li>
<li>generale alleggerimento del peso delle pagine ottimizzando i contenuti;</li>
<li>configurazione del server web per abilitare la compressione delle risposte.</li>
</ul>
</div>
</div>
</td>
<td style="width: 33.3333%; height: 176px;">primo semestre 2025</td>
</tr>
<tr style="height: 176px;">
<td style="width: 33.3333%; height: 176px;"><strong>Largest Content Paint</strong></td>
<td style="width: 33.3333%; height: 176px;">
<ul>
<li>ottimizzazione delle immagini utilizzando tipi di file più leggeri e moderni (passare a webp, ad esempio);</li>
<li>eventuale utilizzo di CDN rapide per contenuti statici;</li>
<li>migliorare il tempo di risposta del server potenziando le risorse e implementando delle politiche di caching lato server.</li>
</ul>
</td>
<td style="width: 33.3333%; height: 176px;">primo semestre 2025</td>
</tr>
<tr style="height: 80px;">
<td style="width: 33.3333%; height: 80px;"><strong>Cumulative Layout Shift</strong></td>
<td style="width: 33.3333%; height: 80px;">
<ul>
<li>definire per ogni immagine le dimensioni della stessa, per evitare spostamenti di layout in fase di caricamento.</li>
</ul>
</td>
<td style="width: 33.3333%; height: 80px;">primo semestre 2025</td>
</tr>
<tr style="height: 152px;">
<td style="width: 33.3333%; height: 152px;"><strong>Speed Index</strong></td>
<td style="width: 33.3333%; height: 152px;">
<ul>
<li>ridurre / rimuove risorse bloccanti del render (ottimizzare caricamenti di js e CSS);</li>
<li>migliorare il tempo di risposta del server potenziando le risorse e implementando delle politiche di caching lato server.</li>
</ul>
</td>
<td style="width: 33.3333%; height: 152px;">primo semestre 2025</td>
</tr>
</tbody>
</table></div>
        </div>

      
            </div>
        </div>
    </div>
</div>


			<?php get_template_part("template-parts/common/valuta-servizio"); ?>
			<?php get_template_part("template-parts/common/assistenza-contatti"); ?>
							
		<?php 
			endwhile; // End of the loop.
		?>
	</main>
