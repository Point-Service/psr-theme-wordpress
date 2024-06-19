<?php
/* Template Name: note-legali
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
			
			<div class="container">
    <div class="row mt-lg-50">
        <div class="col-12 col-lg-3 d-lg-block mb-4 d-none">
            <div class="cmp-navscroll sticky-top" aria-labelledby="accordion-title-one">
                <nav class="navbar it-navscroll-wrapper navbar-expand-lg" data-bs-navscroll="">
                    <div class="navbar-custom" id="navbarPageIndexNavProgress">
                        <div class="menu-wrapper">
                            <div class="link-list-wrapper">
                                <div class="accordion">
                                    <div class="accordion-item">
                                        <span class="accordion-header" id="accordion-title-one">
                                            <button class="accordion-button pb-10 px-3 text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-one" aria-expanded="true" aria-controls="collapse-one">
                                                Indice della pagina
                                                <svg role="img" class="icon icon-xs right">
                                                    <use href="https://comune.sedriano.mi.it/wp-content/themes/si-agid-pa-theme/node_modules/bootstrap-italia/dist/svg/sprites.svg#it-expand"></use>
                                                </svg>
                                            </button>
                                        </span>
                                        <div class="progress">
                                            <div class="progress-bar it-navscroll-progressbar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%;"></div>
                                        </div>
                                        <div id="collapse-one" class="accordion-collapse collapse show" role="region" aria-labelledby="accordion-title-one">
                                            <div class="accordion-body">
                                                <ul class="link-list" data-element="page-index">
                                                    <li class="nav-item">
                                                        <a class="nav-link active" href="#licenza"><span>Licenza dei contenuti</span></a>
                                                    </li>
                                                       
						</ul>
                                            </div>						
                                        </div>	

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
        <div class="col-12 col-lg-8 offset-lg-1">
            <div class="it-page-sections-container">
            <article id="cos-e" class="it-page-section anchor-offset">
                        <h4 data-element="legal-notes-section">Licenza dei contenuti</h4>
<p><strong>Premessa</strong><br />Nel rispetto delle Linee guida per i siti web della PA (art. 4 della Direttiva 8/09 del Ministro per la pubblica amministrazione e l'innovazione) si forniscono le seguenti informazioni che regolano l'accesso al sito web e disciplinano i termini e le condizioni d'uso del sito stesso. Accedendo e consultando questo sito, il visitatore ne accetta i seguenti termini e condizioni d'uso.<br /><br /><strong>Copyright</strong><br />Tutti i contenuti, le immagini e le informazioni presenti all'interno del sito sono protetti ai sensi delle normative sul diritto d'autore, sui brevetti e su quelle relative alla propriet&agrave; intellettuale; pertanto nulla, neppure in parte, potr&agrave; essere copiato, modificato o rivenduto per fini di lucro o per trarne qualsivoglia utilit&agrave;.<br /><br /><strong>Utilizzo del sito</strong><br />In nessun caso l'Ente potr&agrave; essere ritenuto responsabile dei danni di qualsiasi natura causati direttamente o indirettamente dall'accesso al sito e dall'utilizzo che venga fatto delle informazioni in esso contenute; inoltre, l'Ente non ha alcuna responsabilit&agrave; per eventuali problemi causati dall'utilizzazione del presente sito o di siti ad esso collegati.<br />Sebbene i contenuti forniti si ritengano adeguati ed aggiornati, l'Ente proprietario, titolare e gestore del sito non garantisce che il sito web stesso sia privo di imprecisioni, errori e/o omissioni.<br />L'Ente si riserva il diritto di modificare i contenuti in ogni momento.<br /><br /><strong>Accesso a siti esterni collegati</strong><br />L'Ente non pu&ograve; essere ritenuto responsabile di quanto contenuto nei siti ai quali &egrave; possibile accedere tramite i collegamenti posti all'interno del sito stesso, declinando ogni responsabilit&agrave; per le informazioni, la grafica, gli aggiornamenti ed i riferimenti in essi contenuti;<br /><br /><strong>Download</strong><br />I contenuti e la documentazione scaricabile dal sito e reperibile via download sono liberamente e gratuitamente disponibili, alle condizioni stabilite al punto Copyright, salvo diversa indicazione. Quanto reperibile via download in questo sito tramite link a siti esterni pu&ograve; essere coperto da copyright, diritti d&rsquo;uso e/o copia dei rispettivi proprietari; pertanto l'Ente invita a verificare condizioni di utilizzo e diritti e si ritiene esplicitamente sollevato da qualsiasi responsabilit&agrave; in merito.<br /><br /><strong data-element="legal-notes-section">Licenza dei contenuti</strong></p><br />
                
                
                
                
                
                
                <article id="contatti" class="it-page-section anchor-offset mt-5">
                                                        </article>

                <section class="it-page-section">
                <div class="row variable-gutters">
                            <div class="col-lg-12">
                                <?php get_template_part( "template-parts/single/bottom" ); ?>
                            </div><!-- /col-lg-9 -->
                        </div><!
                </section>
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

<?php
get_footer();



