<?php
/**
 * Servizio template file
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Design_Comuni_Italia
 */
global $uo_id, $file_url, $hide_arguments;

get_header();
?>
    <main>
        <?php
        while ( have_posts() ) :
            the_post();
            $user_can_view_post = dci_members_can_user_view_post(get_current_user_id(), $post->ID);

        
             $prefix= '_dci_unita_organizzativa_';
             $documenti = dci_get_meta("documenti", $prefix, $post->ID);
            // $motivo_stato = dci_get_meta("motivo_stato");
            $sottotitolo = dci_get_meta("sottotitolo");
            $descrizione_breve = dci_get_meta("descrizione_breve");
            $competenze = dci_get_wysiwyg_field("competenze");
            // $destinatari_intro = dci_get_meta("destinatari_introduzione");
            // $destinatari_list = dci_get_meta("destinatari_list");


            $responsabili = dci_get_meta("responsabile");

            $responsabile = $responsabili[0];

            $incarichi = dci_get_meta("incarichi", '_dci_persona_pubblica_', $responsabile);

            $incarico = get_the_title($incarichi[0]);

  			$tipologie = get_the_terms($post, 'tipi_unita_organizzativa');

  			$tipologia = $tipologie[0]->name;

            $nome_incarico = $incarico;

            $area_riferimento = dci_get_meta("unita_organizzativa_genitore");

            $persone = dci_get_meta("persone_struttura");

            $allegati = dci_get_meta("allegati");

			$sede_principale = dci_get_meta("sede_principale");

            $servizi = dci_get_meta("elenco_servizi_offerti");

            $descrizione = dci_get_wysiwyg_field("descrizione_estesa");

            $punti_contatto = dci_get_meta("contatti");

            $prefix = '_dci_punto_contatto_';
            $contatti = array();
            foreach ($punti_contatto as $pc_id) {
                $contatto = dci_get_full_punto_contatto($pc_id);
                array_push($contatti, $contatto);
            }
           	

            $more_info = dci_get_wysiwyg_field("ulteriori_informazioni");
            $condizioni_servizio = dci_get_meta("condizioni_servizio");     
            $uo_id = intval(dci_get_meta("unita_responsabile"));
            $argomenti = get_the_terms($post, 'argomenti');

            // valori per metatag
            $categorie = get_the_terms($post, 'categorie_servizio');
            $categoria_servizio = $categorie[0]->name;
            $ipa = dci_get_meta('codice_ente_erogatore');
            $copertura_geografica = dci_get_wysiwyg_field("copertura_geografica");
            if ($canale_fisico_uffici[0]??null) {
                $ufficio = get_post($canale_fisico_uffici[0]);
                $luogo_id = dci_get_meta('sede_principale', '_dci_unita_organizzativa_', $ufficio->ID);
                $indirizzo = dci_get_meta('indirizzo', '_dci_luogo_', $luogo_id);
                $quartiere = dci_get_meta('quartiere', '_dci_luogo_', $luogo_id);
                $cap = dci_get_meta('cap', '_dci_luogo_', $luogo_id);
            }
            function convertToPlain($text) {
                $text = str_replace(array("\r", "\n"), '', $text);
                $text = str_replace('"', '\"', $text);
                $text = str_replace('&nbsp;', ' ', $text);

                return trim(strip_tags($text));
            };
            ?>
            <script type="application/ld+json" data-element="metatag">{
                    "name": "<?= $post->post_title; ?>",
                    "serviceType": "<?= $categoria_servizio; ?>",
                    "serviceOperator": {
                        "name": "<?= $ipa; ?>"
                    },
                    "areaServed": {
                        "name": "<?= convertToPlain($copertura_geografica); ?>"
                    },
                    "audience": {
                        "name": "<?= convertToPlain($destinatari); ?>"
                    },
                    "availableChannel": {
                       "serviceUrl": "<?= $canale_digitale_link; ?>",
                        "serviceLocation": {
                            "name": "<?= $ufficio->post_title; ?>",
                            "address": {
                            "streetAddress": "<?= $indirizzo; ?>",
                            "postalCode": "<?= $cap; ?>",
                            "addressLocality": "<?= $quartiere; ?>"
                            }
                        }
                    }
            }</script>
            <div class="container" id="main-container">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-10">
                        <?php get_template_part("template-parts/common/breadcrumb"); ?>
                    </div>
                </div>
            </div>
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-12 col-lg-10">
                        <div class="cmp-heading pb-3 pb-lg-4">
                            <div class="row">
                                <div class="col-lg-8">
                                    <h1 class="title-xxxlarge" data-element="service-title">
                                        <?php the_title(); ?>
                                    </h1>
                                    
                                    <p class="subtitle-small mb-3" data-element="service-description">
                                        <?php echo $descrizione_breve ?>
                                    </p>
                                    <?php if ($canale_digitale_link) { ?>
                                    <button type="button" class="btn btn-primary fw-bold" onclick="location.href='<?php echo $canale_digitale_link; ?>';">
                                        <span class=""><?php echo $canale_digitale_label; ?></span>
                                    </button>
                                    <?php } ?>
                                </div>
                                <div class="col-lg-3 offset-lg-1 mt-5 mt-lg-0">
                                    <?php
                                        $hide_arguments = true;
                                        get_template_part('template-parts/single/actions');
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="d-none d-lg-block mt-2"/>
                </div>
            </div>
            <div class="container">
                <div class="row row-column-menu-left mt-4 mt-lg-80 pb-lg-80 pb-40">
                    <div class="col-12 col-lg-3 mb-4 border-col">
                        <div class="cmp-navscroll sticky-top" aria-labelledby="accordion-title-one">
                            <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="Indice della pagina" data-bs-navscroll>
                                <div class="navbar-custom" id="navbarNavProgress">
                                    <div class="menu-wrapper">
                                        <div class="link-list-wrapper">
                                            <div class="accordion">
                                                <div class="accordion-item">
                                                    <span class="accordion-header" id="accordion-title-one">
                                                        <button class="accordion-button pb-10 px-3 text-uppercase" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-one" aria-expanded="true" aria-controls="collapse-one">
                                                            Indice della pagina
                                                            <svg class="icon icon-xs right">
                                                                <use href="#it-expand"></use>
                                                            </svg>
                                                        </button>
                                                    </span>
                                                    <div class="progress">
                                                        <div class="progress-bar it-navscroll-progressbar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <div id="collapse-one" class="accordion-collapse collapse show" role="region" aria-labelledby="accordion-title-one">
                                                        <div class="accordion-body">
                                                            <ul class="link-list" data-element="page-index">
                                                                <?php if ($competenze ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#who-needs">
                                                                        <span class="title-medium">Competenze</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $area_riferimento ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#description">
                                                                        <span class="title-medium">Area di Riferimento</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( is_array($responsabili)) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#how-to">
                                                                        <span class="title-medium">Responsabile</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( is_array($persone) ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#needed">
                                                                        <span class="title-medium">Persone</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( is_array($servizi) ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#obtain">
                                                                        <span class="title-medium">Servizi collegati</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $sede_principale ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#deadlines">
                                                                        <span class="title-medium">Sede principale</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $altre_sedi ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#costs">
                                                                        <span class="title-medium">Altre sedi</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $allegati ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#more-info">
                                                                        <span class="title-medium">Allegati</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
       
                                                                <?php if ( $uo_id ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#contacts">
                                                                        <span class="title-medium">Contatti</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
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
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="who-needs">Competenze</h2>
                                <div class="richtext-wrapper lora">
                                    <?php echo $competenze ?>
                                </div>
                            </section>
                            <?php if ($tipologia) { ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="description">Tipologia di Organizzazione</h2>
                                <div class="richtext-wrapper lora"><?php echo $tipologia; ?></div>
                            </section>
                            <?php } ?>
                            <?php if ( $area_riferimento ) { ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="costs">Area di Riferimento</h2>
                                <div class="richtext-wrapper lora">
                                    <?php foreach ($area_riferimento as $uo_id) {
                                        get_template_part("template-parts/unita-organizzativa/card-full");
                                    } ?>
                                </div>
                            </section>
                            <?php } ?>
                            <?php if ( $costi ) { ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="costs">Quanto costa</h2>
                                <div class="richtext-wrapper lora"><?php echo $costi ?></div>
                            </section>
                            <?php } ?>
                            
                            <?php if ( $more_info ) {  ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="more-info">Ulteriori informazioni</h2>
                                <h3 class="mb-3 subtitle-medium">Graduatorie di accesso</h3>
                                <div class="richtext-wrapper lora">
                                    <?php echo $more_info ?>
                                </div>
                            </section>
                            <?php }  ?>
                            <?php if ( $condizioni_servizio ) {
                                $file_url = $condizioni_servizio;
                            ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="conditions">Condizioni di servizio</h2>
                                <div class="richtext-wrapper lora">Per conoscere i dettagli di
                                    scadenze, requisiti e altre informazioni importanti, leggi i termini e le condizioni di servizio.
                                </div>
                                <?php get_template_part("template-parts/single/attachment"); ?>
                            </section>
                            <?php } ?>

                            <section class="it-page-section">
			      <?php if ( $responsabile ) {?>
                                <h2 class="mb-3" id="contacts">Responsabile</h2>                                
                                <div class="row">
                                    <div class="col-12 col-md-8 col-lg-6 mb-30">
                                        <div class="cmp-card-latest-messages mb-3 mb-30">
                                        	<div class="card card-bg px-4 pt-4 pb-4 rounded">
                                                    <div class="card-header border-0 p-0">
                                                             <a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase" href="#"><?php echo 
                                                             $nome_incarico; ?></a>
                                                        </div>
                                                   <div class="card-body p-0 my-2">
                                                      <div class="card-content">
                                                        
                                                         <h4 class="h5"><a href="<?php echo get_permalink($responsabile); ?>"><?php echo dci_get_meta('nome', '_dci_persona_pubblica_', $responsabile); ?> <?php echo dci_get_meta('cognome', '_dci_persona_pubblica_', $responsabile); ?></a></h4>
                                                         <p class="text-paragraph"><?php echo dci_get_meta('descrizione_breve', '_dci_persona_pubblica_', $responsabile); ?></p>
                                                      </div>
                                                   </div>
                                                   <!-- /card-body -->
                                               </div>
                                        </div>
                                    </div>
                                </div>
                                <?php } ?>
                            </section>
                            <section class="it-page-section">
                                <h2 class="mb-3" id="contacts">Persone</h2>
                                <div class="row">
                                      <?php 
                                    $with_border = true;
                                      get_template_part("template-parts/single/persone");
                                    ?>

                                    <br>
                                </div>
                            </section>
                            <?php if ($servizi && is_array($servizi) && count($servizi)>0 ) { ?>
			        <article id="servizi" class="it-page-section anchor-offset mt-5">
				    <h3>Servizi collegati</h3><p></p>
				    <?php foreach ($servizi as $servizio_id) { ?>
				        <div class="row">
				            <div class="col-12 col-sm-8">
				                <?php 
				                    $servizio = get_post($servizio_id);
				                    $with_border = true;
				                    get_template_part("template-parts/servizio/card");
				                ?>
				            </div>
				        </div><p></p>
				    <?php } ?>
				</article>
                            <?php } ?>
				
                            <?php if ($sede_principale) { ?>
			    <p></p>
                            <section class="it-page-section">
                                  <h3 class="mt-4" id="contacts">Sede principale</h3>
                                <div class="row">
                                    <div class="col-12 col-md-8 col-lg-6 mb-30">
                                        <div class="card-wrapper rounded h-auto mt-10">
                                            <div class="card card-teaser shadow-sm p-4s rounded border border-light flex-nowrap">
                                            <div class="card-body pe-3">
                                                <p class="card-title text-paragraph-regular-medium-semi mb-3">
                                                    <a href="<?php echo get_permalink($sede_principale); ?>"><?php echo dci_get_meta('nome_alternativo', '_dci_luogo_', $sede_principale); ?></a>
                                                </p>
                                                <div class="card-text">
                                                     <p><?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?></p>
                                                         <p><?php echo dci_get_meta("descrizione_breve", '_dci_luogo_', $sede_principale); ?></
                                                </div>
                                            </div>
                                        </div>
                                        </div>
                                    </div>                                    
                                </div>
                            </section>
                            <?php } ?>

   				
                      <?php if( is_array($allegati) && count($allegati) ) { ?>
                    <article class="it-page-section anchor-offset mt-5">
                        <h4 id="allegati">Allegati</h4>
                        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <?php foreach ($allegati as $all_url) {
                                $all_id = attachment_url_to_postid($all_url);
                                $allegato = get_post($all_id);
                            ?>
                            <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                <svg class="icon" aria-hidden="true">
                                <use
                                    xlink:href="#it-clip"
                                ></use>
                                </svg>
                                <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo get_the_guid($allegato); ?>" aria-label="Scarica l'allegato <?php echo $allegato->post_title; ?>" title="Scarica l'allegato <?php echo $allegato->post_title; ?>">
                                        <?php echo $allegato->post_title; ?>
                                    </a>
                                </h5>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </article>
                    <?php } ?>

				
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="costs">Contatti</h2>
                                <div class="richtext-wrapper lora">
                                    <?php foreach ($punti_contatto as $pc_id) {
                                        get_template_part('template-parts/punto-contatto/card');
                                    } ?>
                                </div>
                            </section>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php get_template_part("template-parts/common/valuta-servizio"); ?>
        <?php get_template_part('template-parts/single/more-posts', 'carousel'); ?>
        <?php get_template_part("template-parts/common/assistenza-contatti"); ?>

        <?php
        endwhile; // End of the loop.
        ?>
    </main>
<?php
get_footer();

 ?>
