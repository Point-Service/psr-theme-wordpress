<?php
/**
 * The template for displaying home
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/
 *
 * @package Design_Comuni_Italia
 */
	
//Se il portale gestisce solo la nostra Trasparenza in modo esterno, indirizza all'home del comune.
$portalesoloperusoesterno = dci_get_option("ck_portalesoloperusoesterno");
$is_external_only = in_array(strtolower((string) $portalesoloperusoesterno), array('1', 'true', 'yes', 'on'), true);


if ($is_external_only) {
    wp_redirect(dci_get_option("url_homesoloesterno"));
    exit;
}

if (function_exists('psr_home_tools_begin')) { psr_home_tools_begin(); }
get_header();

?>
	    
       <main id="main-container" class="main-container redbrown psr-home-restyle">


        <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('header'); } ?>
        <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('banner'); } ?>
        <?php get_template_part("template-parts/home/carosello2"); ?>

        <h1 class="visually-hidden">
            <?php echo dci_get_option("nome_comune"); ?>
        </h1>
        <section id="head-section">
            <h2 class="visually-hidden">Contenuti in evidenza</h2>
            <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('messages'); } ?>
            <?php
			$messages = dci_get_option( "messages", "home_messages" );
            if($messages && !empty($messages)) {
                get_template_part("template-parts/home/messages");
            }
		    ?>

            
            
	    <p></p>
            <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('news'); } ?>
            <?php dci_get_template_part_async("home-notizie"); ?>
            <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('events'); } ?>
            <?php dci_get_template_part_async("home-calendario"); ?>

        </section>


		   
        <section id="evidenza" class="evidence-section">
                <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('topics'); } ?>
                <?php dci_get_template_part_async("home-argomenti"); ?>
               <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('strip'); } ?>
               <?php  get_template_part("template-parts/home/strip"); ?>
                <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('services'); } ?>
                <?php dci_get_template_part_async("home-servizi"); ?>
            <div class="section py-5 pb-lg-80 px-lg-5 position-relative">
		        
                <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('thematic'); } ?>
                <?php  get_template_part("template-parts/home/siti","tematici"); ?>
            </div>
        </section>
		   
      
		   
        <section id="accesso-rapido" class="quick-access-section">
            <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('quick'); } ?>

            <?php 
                $boxes = dci_get_option( "quickboxes", "accesso_rapido" );
                get_template_part("template-parts/home/accesso-rapido"); 
            ?>
        </section>
        
		<?php 
		    // Controlla se mostrare la galleria
            if (function_exists('psr_home_tools_section')) { psr_home_tools_section('gallery'); }
            $mostra_gallery = dci_get_option('mostra_gallery', 'galleria');
            if ($mostra_gallery) {
                $stile_galleria = dci_get_option('stile_galleria','galleria') ?: null;
                if($stile_galleria === "solo-foto" || $stile_galleria ==="Solo foto"){
                     // Vecchia Galleria fotografica
                    dci_get_template_part_async("home-gallery-photo");
                }else if($stile_galleria === "foto-gallery" || $stile_galleria ==="foto-gallery"){
                    // Nuova galleria
                    dci_get_template_part_async("home-gallery");
                }
		    }
		?>


        <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('links'); } ?>
        <?php get_template_part("template-parts/home/ricerca"); ?>

         <?php 
            if (function_exists('psr_home_tools_section')) { psr_home_tools_section('map'); }
            $show_map = dci_get_option( "ck_show_map", "homepage" );
            if($show_map === 'true'){
              dci_get_template_part_async("home-map");
            }

        ?>
        <?php get_template_part("template-parts/common/valuta-servizio"); ?>
        <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('weather'); } ?>
        <?php dci_get_template_part_async("home-meteo"); ?>
        <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('assistance'); } ?>
        <?php get_template_part("template-parts/common/assistenza-contatti"); ?>
        <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('footer'); } ?>
        <?php if (function_exists('psr_home_tools_section')) { psr_home_tools_section('socials'); } ?>
    </main>
<?php
get_footer();
?>
