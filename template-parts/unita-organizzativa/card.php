<?php
    global $uo_id, $with_border, $data_element;
    $ufficio = get_post( $uo_id );

    $prefix = '_dci_unita_organizzativa_';
    $img = dci_get_meta('immagine', $prefix, $uo_id);
    $contatti = dci_get_meta('contatti', $prefix, $uo_id);
    $descrizione_breve = dci_get_meta("descrizione_breve", $prefix, $ufficio->ID);

    $prefix = '_dci_punto_contatto_';
    $contatti_full = array();


    foreach ($contatti as $punto_contatto_id) {
	$contatto = dci_get_full_punto_contatto($punto_contatto_id);
        array_push($contatti_full, $contatto);
      }
    $other_contacts = array(
        'linkedin',
        'skype',
        'telegram',
        'twitter',
        'whatsapp'
    );
    if($with_border) {
?>

<div class="card card-teaser card-teaser-info rounded shadow-sm p-3">
  <div data-element="service-area">
    <div class="block" id="it-">
     <div class="card-body pe-3">
        <p class="card-title text-paragraph-regular-medium-semi mb-3">
            <a class="text-decoration-none" href="<?php echo get_permalink($ufficio->ID); ?>" data-element="<?php echo $data_element != null ? $data_element : 'service_area'; ?>">
            <?php echo $ufficio->post_title; ?>
            </a>
        </p>
        <div class="card-text">
            <?php foreach ($indirizzi as $indirizzo) {
                echo '<p class="u-main-black">'.$indirizzo.'</p>';
            }?>
        </div>


	  <?php if ($descrizione_breve) {
	     echo '<div class="card-text"><p class="u-main-black">'.$descrizione_breve.'</p></div>';
	   } ?>      
	
   <?php foreach ($contatti_full as $full_contatto) { ?>	


	        <?php if ( isset($full_contatto['indirizzo']) && is_array($full_contatto['indirizzo']) && count ($full_contatto['indirizzo']) ) {
                        foreach ($full_contatto['indirizzo'] as $value) {
                            echo '<p>'.$value.'</p>';
                        } 
                    } ?>
                    <?php if ( isset($full_contatto['telefono']) && is_array($full_contatto['telefono']) && count ($full_contatto['telefono']) ) {
                        foreach ($full_contatto['telefono'] as $value) {
                            echo '<p>'.$value.'</p>';
                        }
                    } ?>
                    <?php if ( isset($full_contatto['url']) && is_array($full_contatto['url']) && count ($full_contatto['url']) ) {
                        foreach ($full_contatto['url'] as $value) { ?>
                            <p>
                                <a 
                                target="_blank" 
                                aria-label="scopri di più su <?php echo $value; ?> - link esterno - apertura nuova scheda" 
                                href="<?php echo $value; ?>">
                                    Vai sul Sito
                                </a>
                            </p>
                    <?php }
                    } ?>
                    <?php if ( isset($full_contatto['email']) && is_array($full_contatto['email']) && count ($full_contatto['email']) ) {
                        foreach ($full_contatto['email'] as $value) { ?>								     
                            <p>
                                <a  
                                target="_blank" 
                                aria-label="invia un'email a <?php echo $value; ?>"
				title="invia un'email a <?php echo $value; ?>" 
                                href="mailto:<?php echo $value; ?>">
                                    <?php echo $value; ?>
                                </a>
                            </p>
                    <?php }
                    } ?>

                    <?php if ( isset($full_contatto['pec']) && is_array($full_contatto['pec']) && count ($full_contatto['pec']) ) {
                        foreach ($full_contatto['pec'] as $value) { ?>								     
                            <p>
                                <a  
                                target="_blank" 
                                aria-label="invia un'email a <?php echo $value; ?>"
				title="invia un'email a <?php echo $value; ?>" 
                                href="mailto:<?php echo $value; ?>">
                                    <?php echo $value; ?>
                                </a>
                            </p>
                    <?php }
                    } ?>	     
                    <?php foreach ($other_contacts as $type) {
                        if ( isset($full_contatto[$type]) && is_array($full_contatto[$type]) && count ($full_contatto[$type]) ) {
                            foreach ($full_contatto[$type] as $value) {
                                echo '<p>'.$type.': '.$value.'</p>';
                            }
                        } 
                    } ?>

    <?php } ?>	  
 
    </div>
    <?php if ($img) { ?>
        <div class="avatar size-xl">
            <?php dci_get_img($img); ?>
        </div>
    <?php } ?>
  </div>
 </div>
</div>
<?php } else { ?>
<div class="card card-teaser border rounded shadow p-4">
 <div data-element="service-area">
  <div class="block" id="it-">
    <div class="card-body pe-3">
        <h4 class="u-main-black mb-1 title-small-semi-bold-medium">
            <a class="text-decoration-none" href="<?php echo get_permalink($ufficio->ID); ?>">
            <?php echo $ufficio->post_title; ?>
            </a>
        </h4>
        <div class="card-text">
            <?php foreach ($indirizzi as $indirizzo) {
                echo '<p>'.$indirizzo.'</p>';
            }?>
        </div> 

	    
	  <?php if ($descrizione_breve) {
	     echo '<div class="card-text"><p class="u-main-black">'.$descrizione_breve.'</p></div>';
	   } ?>      
        <?php foreach ($contatti1 as $full_contatto) { ?>	


	                        <?php if ( isset($full_contatto['indirizzo']) && is_array($full_contatto['indirizzo']) && count ($full_contatto['indirizzo']) ) {
                        foreach ($full_contatto['indirizzo'] as $value) {
                            echo '<p>'.$value.'</p>';
                        } 
                    } ?>
                    <?php if ( isset($full_contatto['telefono']) && is_array($full_contatto['telefono']) && count ($full_contatto['telefono']) ) {
                        foreach ($full_contatto['telefono'] as $value) {
                            echo '<p>T '.$value.'</p>';
                        }
                    } ?>
                    <?php if ( isset($full_contatto['url']) && is_array($full_contatto['url']) && count ($full_contatto['url']) ) {
                        foreach ($full_contatto['url'] as $value) { ?>
                            <p>
                                <a 
                                target="_blank" 
                                aria-label="scopri di più su <?php echo $value; ?> - link esterno - apertura nuova scheda" 
                                href="<?php echo $value; ?>">
                                    <?php echo $value; ?>
                                </a>
                            </p>
                    <?php }
                    } ?>
                    <?php if ( isset($full_contatto['email']) && is_array($full_contatto['email']) && count ($full_contatto['email']) ) {
                        foreach ($full_contatto['email'] as $value) { ?>
                            <p>
                                <a  
                                target="_blank" 
                                aria-label="invia un'email a <?php echo $value; ?>"
                                href="mailto:<?php echo $value; ?>">
                                    <?php echo $value; ?>
                                </a>
                            </p>
                    <?php }
                    } ?>
                    <?php foreach ($other_contacts as $type) {
                        if ( isset($full_contatto[$type]) && is_array($full_contatto[$type]) && count ($full_contatto[$type]) ) {
                            foreach ($full_contatto[$type] as $value) {
                                echo '<p>'.$type.': '.$value.'</p>';
                            }
                        } 
                    } ?>


    <?php } ?>	  
	      
    <?php if ($img) { ?>
    <div class="avatar size-xl">
        <?php dci_get_img($img); ?>
    </div>
    <?php } ?>
  </div>
 </div>
</div>
<?php } 
$with_border = false;
?>
