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

            // prefix: _dci_unita_persona_pubblica_
            
           
            // $motivo_stato = dci_get_meta("motivo_stato");
            $nome = dci_get_meta("nome");
            $nome = dci_get_meta("cognome");
            $descrizione_breve = dci_get_meta("descrizione_breve");
            $competenze = dci_get_wysiwyg_field("competenze");

            $foto_id = dci_get_meta("foto_id");
            
            $img = wp_get_attachment_image_src($foto_id, "item-gallery");

            $data_insediamento = dci_get_meta("data_inizio_incarico");

            $responsabili = dci_get_meta("responsabile");

            $responsabile = $responsabili[0];

            $incarichi = dci_get_meta("incarichi", '_dci_persona_pubblica_', $responsabile);

            $incarico = get_the_title($incarichi[0]);

            $tipo_incarico = (get_the_terms(get_post($incarichi[0]), 'tipi_incarico'))[0]->name;

            $nome_incarico = $incarico;

            $compensi = dci_get_meta("compensi", '_dci_incarico_', $incarichi[0]);

            $organizzazioni = dci_get_meta("organizzazioni");

            $biografia = dci_get_meta("biografia");

            $curriculum_vitae = dci_get_meta("curriculum_vitae");

            $situazione_patrimoniale = dci_get_meta("situazione_patrimoniale");

            $situazione_patrimoniale_id = dci_get_meta("situazione_patrimoniale_id");

            $dichiarazione_redditi = dci_get_meta("dichiarazione_redditi");

            $spese_elettorali = dci_get_meta("spese_elettorali");

            $descrizione = dci_get_wysiwyg_field("descrizione_estesa");

            $punti_contatto = dci_get_meta("punti_contatto");

            $prefix = '_dci_punto_contatto_';
            $contatti = array();
            foreach ($punti_contatto as $pc_id) {
                $contatto = dci_get_full_punto_contatto($pc_id);
                array_push($contatti, $contatto);
            }

            $altre_cariche = dci_get_meta("altre_cariche");
            

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
                                                        
                                                        <?php if($img) { ?>
                                                            <section class="hero-img mb-20 mb-lg-50">
                                                              <section class="it-hero-wrapper it-hero-small-size cmp-hero-img-small">
                                                                 <div class="img-responsive-wrapper">

                                                                       <div class="img-wrapper"><img width="200" src="<?php echo $img[0]; ?>" title="titolo immagine" alt="descrizione immagine"></div>
                                                             
                                                                 </div>
                                                              </section>
                                                            </section>
                                                        <?php } ?>
                                        
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
                                                                <?php if ($incarico ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#who-needs">
                                                                        <span class="title-medium">Incarico</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $tipo_incarico ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#description">
                                                                        <span class="title-medium">Tipo di incarico</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if($compensi) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#how-to">
                                                                        <span class="title-medium">Compensi</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#needed">
                                                                        <span class="title-medium">Data di <?php if($tipo_incarico == "politico") { echo 'Insediamento'; } else { echo 'inizio incarico'; } ?></span>
                                                                    </a>
                                                                </li>
                                                                <?php if ( $organizzazioni ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#obtain">
                                                                        <span class="title-medium">Organizzazione</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $competenze ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#obtain">
                                                                        <span class="title-medium">Competenze</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $biografia ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#deadlines">
                                                                        <span class="title-medium">Biografia</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $contatti ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#deadlines">
                                                                        <span class="title-medium">Contatti</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $curriculum_vitae ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#costs">
                                                                        <span class="title-medium">Curriculum Vitae</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $situazione_patrimoniale ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#situazione-patrimoniale">
                                                                        <span class="title-medium">Situazione patrimoniale</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $dichiarazione_redditi ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#dichiarazione-redditi">
                                                                        <span class="title-medium">Dichiarazione dei redditi</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $spese_elettorali ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#spese-elettorali">
                                                                        <span class="title-medium">Spese elettorali</span>
                                                                    </a>
                                                                </li>
                                                                <?php } ?>
                                                                <?php if ( $allegati ) { ?>
                                                                <li class="nav-item">
                                                                    <a class="nav-link" href="#altre-cariche">
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
           
 
                             <?php if ($incarico) { ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="who-needs">Incarico</h2>
                                <div class="richtext-wrapper lora">
                                    <?php echo $incarico ?>
                                </div>
                            </section>
                            <?php } ?>
                            <?php if ($tipo_incarico) { ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="description">Tipo di Incarico</h2>
                                <div class="richtext-wrapper lora"><?php echo $tipo_incarico ?></div>
                            </section>
                            <?php } ?>
                            <?php if ($compensi) { ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="how-to">Compensi</h2>
                                <div class="richtext-wrapper lora">
                                    <?php echo $compensi ?>
                                </div>
                            </section>
                            <?php } ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="description">Data di <?php if($tipo_incarico == "politico") { echo 'Insediamento'; } else { echo 'inizio incarico'; } ?></h2>
                                <div class="richtext-wrapper lora"><?php echo $data_insediamento; ?></div>
                            </section>
                            <?php if ( $organizzazioni ) { ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="costs">Organizzazione</h2>
                                <div class="richtext-wrapper lora">
                                    <?php foreach ($organizzazioni as $uo_id) {
                                        get_template_part("template-parts/unita-organizzativa/card-full");
                                    } ?>
                                </div>
                            </section>
                            <?php } ?>
                            <?php if ($competenze) { ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="how-to">Competenze</h2>
                                <div class="richtext-wrapper lora">
                                    <?php echo $competenze ?>
                                </div>
                            </section>
                            <?php } ?>
                            <?php if ( $biografia ) {  ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="more-info">Biografia</h2>
                                <div class="richtext-wrapper lora">
                                    <?php echo $biografia ?>
                                </div>
                            </section>
                            <?php } ?>
                            <?php if ( $punti_contatto ) { ?>
                            <section class="it-page-section mb-30">
                                <h2 class="title-xxlarge mb-3" id="costs">Contatti</h2>
                                <div class="richtext-wrapper lora">
                                    <?php foreach ($punti_contatto as $pc_id) {
                                        get_template_part('template-parts/single/punto-contatto');
                                    } ?>
                                </div>
                            </section>
                            <?php if( $curriculum_vitae ) { ?>
                            <article id="documenti" class="it-page-section anchor-offset mt-5">
                            <h3>Curriculum Vitae</h3>
                            <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <?php  
                                if ( $curriculum_vitae ) {
                                    $documento_id = attachment_url_to_postid($curriculum_vitae);
                                    $documento = get_post($documento_id);
                                ?>
                            <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                <svg class="icon" aria-hidden="true">
                                <use
                                    xlink:href="#it-clip"
                                ></use>
                                </svg>
                                <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo $curriculum_vitae; ?>" aria-label="Visualizza il documento <?php echo $documento->post_title; ?>" title="Scarica il documento <?php echo $documento->post_title; ?>">
                                        <?php echo $documento->post_title; ?>
                                    </a>
                                </h5>
                                </div>
                            </div>
                            <?php } ?>
                            </div>
                    </article>
                    <?php  if ( $situazione_patrimoniale ) {?>
                    <article id="situazione-patrimoniale" class="it-page-section anchor-offset mt-5">
                            <h3>Situazione patrimoniale</h3>
                            <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <?php  
                                if ( $situazione_patrimoniale ) {
                                    $documento_id = attachment_url_to_postid($situazione_patrimoniale);
                                    $documento = get_post($documento_id);
                                ?>
                            <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                <svg class="icon" aria-hidden="true">
                                <use
                                    xlink:href="#it-clip"
                                ></use>
                                </svg>
                                <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo $situazione_patrimoniale; ?>" aria-label="Visualizza il documento <?php echo $documento->post_title; ?>" title="Scarica il documento <?php echo $documento->post_title; ?>">
                                        <?php echo $documento->post_title; ?>
                                    </a>
                                </h5>
                                </div>
                            </div>
                            <?php } ?>
                            </div>
                    </article>
                    <?php } ?>
                    <?php  if ( $dichiarazione_redditi ) {?>
                    <article id="dichiarazione-redditi" class="it-page-section anchor-offset mt-5">
                            <h3>Dichiarazione dei redditi</h3>
                            <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            <?php  
                                if ( $dichiarazione_redditi ) {
                                    foreach ($dichiarazione_redditi as $dichiarazione) {
                                        $documento_id = attachment_url_to_postid($dichiarazione);
                                        $documento = get_post($documento_id);
                                ?>
                            <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                <svg class="icon" aria-hidden="true">
                                <use
                                    xlink:href="#it-clip"
                                ></use>
                                </svg>
                                <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo $dichiarazione; ?>" aria-label="Visualizza il documento <?php echo $documento->post_title; ?>" title="Scarica il documento <?php echo $documento->post_title; ?>">
                                        <?php echo $documento->post_title; ?>
                                    </a>
                                </h5>
                                </div>
                            </div>
                            <?php } ?>
                            <?php } } ?>
                            
                            </div>
                    </article>
                    <?php  
                                if ( $spese_elettorali ) {
                                    foreach ($spese_elettorali as $spesa) {
                                    $documento_id = attachment_url_to_postid($spesa);
                                    $documento = get_post($documento_id);
                                ?>
                    <article id="spese-elettorali" class="it-page-section anchor-offset mt-5">
                            <h3>Spese elettorali</h3>
                            <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                            
                            <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                <svg class="icon" aria-hidden="true">
                                <use
                                    xlink:href="#it-clip"
                                ></use>
                                </svg>
                                <div class="card-body">
                                <h5 class="card-title">
                                    <a class="text-decoration-none" href="<?php echo $spesa; ?>" aria-label="Visualizza il documento <?php echo $documento->post_title; ?>" title="Scarica il documento <?php echo $documento->post_title; ?>">
                                        <?php echo $documento->post_title; ?>
                                    </a>
                                </h5>
                                </div>
                            </div>
                            <?php } ?>
                            </div>
                            <?php } ?>
                    </article>
                            <?php if ( $altre_cariche ) { ?>
                            <article id="altre-cariche" class="it-page-section anchor-offset mt-5">
                                <h3>Altre cariche</h3>
                                <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                                    <div class="richtext-wrapper lora">
                                        <?php foreach ($altre_cariche as $documento) {
                                            $documento_id = attachment_url_to_postid($documento);
                                            $documento = get_post($documento_id);
                                        ?>
                                        <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                            <svg class="icon" aria-hidden="true">
                                            <use
                                                xlink:href="#it-clip"
                                            ></use>
                                            </svg>
                                            <div class="card-body">
                                                <h5 class="card-title">
                                                    <a class="text-decoration-none" href="<?php echo $curriculum_vitae; ?>" aria-label="Visualizza il documento <?php echo $documento->post_title; ?>" title="Scarica il documento <?php echo $documento->post_title; ?>">
                                                        <?php echo $documento->post_title; ?>
                                                    </a>
                                                </h5>
                                            </div>
                                        </div>
                                    
                                        <?php } ?>
                                        </div>
                            </article>
                            <?php } ?>
                            
                            <?php } ?>
                   <?php } ?>
                   
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

