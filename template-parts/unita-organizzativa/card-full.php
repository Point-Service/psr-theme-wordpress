<?php
    global $uo_id, $with_border;
    $ufficio = get_post( $uo_id );

    $prefix = '_dci_unita_organizzativa_';
    $img = dci_get_meta('immagine', $prefix, $uo_id);
    $punti_contatto = dci_get_meta('contatti', $prefix, $uo_id);
    $sede_principale = dci_get_meta("sede_principale", $prefix, $uo_id);
    $prefix = '_dci_punto_contatto_';
    $contatti = array();
    foreach ($punti_contatto as $pc_id) {
        $contatto = dci_get_full_punto_contatto($pc_id);
        array_push($contatti, $contatto);
    }
    $other_contacts = array(
        'linkedin',
        'skype',
        'telegram',
        'twitter',
        'whatsapp'
    );
    
    if(!$with_border) {
?>

<div class="card card-teaser card-teaser-info rounded shadow-sm p-4 me-3">
    <svg class="icon">
        <use xlink:href="#it-pa"></use>
    </svg>
    <div class="card-body">
	    
        <h5 class="card-title">
            <a class="text-decoration-none" href="<?php echo get_permalink($ufficio->ID); ?>" data-element="service-area">
                <?php echo $ufficio->post_title; ?>
            </a>
        </h5>
	    
        <div class="card-text">
            
                    <?php if ($sede_principale) { ?>
                            <section class="it-page-section">
                              <div class="field--name-field-ita-indirizzo">                                  
                                <p>
                                    <a target="_blank" 
                                    aria-label="Apri la mappa  <?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>"
                                    href="https://www.google.com/maps/search/?api=1&amp;query=<?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>">
					<svg class="icon" viewBox="0 0 24 24"><g>
					<g>
						<path d="M17.7,5.3C16,2.2,12,1.1,8.9,2.8s-4.3,5.7-2.5,8.8L12,22l5.7-10.4c0.5-1,0.8-2,0.8-3.1S18.2,6.3,17.7,5.3z M16.8,11.1
							L12,19.9l-4.8-8.8c-0.5-0.8-0.7-1.7-0.7-2.7C6.5,5.4,9,3,12,3s5.5,2.5,5.5,5.5C17.5,9.4,17.3,10.3,16.8,11.1z"></path>
						<path d="M12,5c-1.9,0-3.5,1.6-3.5,3.5S10.1,12,12,12s3.5-1.6,3.5-3.5S13.9,5,12,5z M12,11c-1.4,0-2.5-1.1-2.5-2.5S10.6,6,12,6
							s2.5,1.1,2.5,2.5S13.4,11,12,11z"></path>
					</g>
					</g></svg><?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>
                                    </a>
                              </p> 
                              </div>
                            </section>
                    <?php } ?> 
            <?php foreach ($contatti as $full_contatto) { ?>
                 <div class="card-text mb-3">
                    <?php if ( isset($full_contatto['indirizzo']) && is_array($full_contatto['indirizzo']) && count ($full_contatto['indirizzo']) ) {
                        foreach ($full_contatto['indirizzo'] as $value) {
                            echo '<p>'.$value.'</p>';
                        } 
                    } ?>                            
                    <?php if ( isset($full_contatto['telefono']) && is_array($full_contatto['telefono']) && count ($full_contatto['telefono']) ) {
                        foreach ($full_contatto['telefono'] as $value) {
                            echo '<p><svg class="icon">
                            <use xlink:href="#it-telephone"></use>
                        </svg> '.$value.'</p>';
                        }
                    } ?>
                    <?php if ( isset($full_contatto['url']) && is_array($full_contatto['url']) && count ($full_contatto['url']) ) {
                        foreach ($full_contatto['url'] as $value) { ?>
                            <p>
                                <a 
                                target="_blank" 
                                aria-label="scopri di più su <?php echo $value; ?> - link esterno - apertura nuova scheda" 
				title="vai sul sito <?php echo $value; ?>" 
                                href="<?php echo $value; ?>">
                                 <?php echo $value; ?>
                                </a>
                            </p>
                    <?php }
                    } ?>
                    <?php if ( isset($full_contatto['email']) && is_array($full_contatto['email']) && count ($full_contatto['email']) ) {
                        foreach ($full_contatto['email'] as $value) { ?>
                            <p><div class="field--name-field-ita-mail">                                    
                                    <svg class="icon">
                            <use xlink:href="#it-mail"></use>
                              </svg> <a  
                                target="_blank" 
                                aria-label="invia un'email a <?php echo $value; ?>"
                                title="invia un'email a <?php echo $value; ?>" 
                                href="mailto:<?php echo $value; ?>">
                              <?php echo $value; ?>
                                </a>
                            </div></p>
                    <?php }
                    } ?>
                    <?php if ( isset($full_contatto['pec']) && is_array($full_contatto['pec']) && count ($full_contatto['pec']) ) {
                        foreach ($full_contatto['pec'] as $value) { ?>
                            <p><div class="field--name-field-ita-mail"> 
				 <svg class="icon">
	                            <use xlink:href="#it-mail"></use>
	                        </svg> 			   
                                <a  
                                target="_blank" 
                                aria-label="invia un'email a <?php echo $value; ?>"
				title="invia un'email a <?php echo $value; ?>" 
                                href="mailto:<?php echo $value; ?>">
                                  <?php echo ''.$value.''; ?>                                                                   
                                </a>
                            </div></p>
                    <?php }
                    } ?>

                    
                    <?php foreach ($other_contacts as $type) {
                        if ( isset($full_contatto[$type]) && is_array($full_contatto[$type]) && count ($full_contatto[$type]) ) {
                            foreach ($full_contatto[$type] as $value) {
                                echo '<p>'.$type.': '.$value.'</p>';
                            }
                        } 
                    } ?>
                        </div>
                    <?php } ?>
        </div>
    </div>
</div>
<?php } else { ?>
<div class="card card-teaser card-teaser-info rounded shadow-sm p-4 me-3">

            <svg class="icon">
                <use xlink:href="#it-pa"></use>
            </svg>
            <div class="card-body pe-3">
                <h3 class="card-title h5">
                    <a href="<?php echo get_permalink($ufficio->ID); ?>">
                        <?php echo $ufficio->post_title; ?>
                    </a>
                </h5>
                <div class="card-text">

                    <?php if ($sede_principale) { ?>
                            <section class="it-page-section">
                              <div class="field--name-field-ita-indirizzo">                                  
                                <p>
                                    <a target="_blank" 
                                    aria-label="Apri la mappa  <?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>"
                                    href="https://www.google.com/maps/search/?api=1&amp;query=<?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>">
					<svg class="icon" viewBox="0 0 24 24"><g>
					<g>
						<path d="M17.7,5.3C16,2.2,12,1.1,8.9,2.8s-4.3,5.7-2.5,8.8L12,22l5.7-10.4c0.5-1,0.8-2,0.8-3.1S18.2,6.3,17.7,5.3z M16.8,11.1
							L12,19.9l-4.8-8.8c-0.5-0.8-0.7-1.7-0.7-2.7C6.5,5.4,9,3,12,3s5.5,2.5,5.5,5.5C17.5,9.4,17.3,10.3,16.8,11.1z"></path>
						<path d="M12,5c-1.9,0-3.5,1.6-3.5,3.5S10.1,12,12,12s3.5-1.6,3.5-3.5S13.9,5,12,5z M12,11c-1.4,0-2.5-1.1-2.5-2.5S10.6,6,12,6
							s2.5,1.1,2.5,2.5S13.4,11,12,11z"></path>
					</g>
					</g></svg><?php echo dci_get_meta("indirizzo", '_dci_luogo_', $sede_principale); ?>
                                    </a>
                              </p> 
                              </div>
                            </section>
                    <?php } ?>       
                    <?php foreach ($contatti as $full_contatto) { ?>
                        <div class="card-text mb-3">

                    <?php if ( isset($full_contatto['indirizzo']) && is_array($full_contatto['indirizzo']) && count ($full_contatto['indirizzo']) ) {
                        foreach ($full_contatto['indirizzo'] as $value) {
                            echo '<p>'.$value.'</p>';
                        } 
                    } ?>                            
                    <?php if ( isset($full_contatto['telefono']) && is_array($full_contatto['telefono']) && count ($full_contatto['telefono']) ) {
                        foreach ($full_contatto['telefono'] as $value) {
                            echo '<p><svg class="icon">
                            <use xlink:href="#it-telephone"></use>
                        </svg> '.$value.'</p>';
                        }
                    } ?>
                    <?php if ( isset($full_contatto['url']) && is_array($full_contatto['url']) && count ($full_contatto['url']) ) {
                        foreach ($full_contatto['url'] as $value) { ?>
                            <p>
                                <a 
                                target="_blank" 
                                aria-label="scopri di più su <?php echo $value; ?> - link esterno - apertura nuova scheda" 
				title="invia un'email a <?php echo $value; ?>" 
                                href="<?php echo $value; ?>">
                                 <?php echo $value; ?>
                                </a>
                            </p>
                    <?php }
                    } ?>
                    <?php if ( isset($full_contatto['email']) && is_array($full_contatto['email']) && count ($full_contatto['email']) ) {
                        foreach ($full_contatto['email'] as $value) { ?>
                            <p><div class="field--name-field-ita-mail">                                    
                                    <svg class="icon">
                            <use xlink:href="#it-mail"></use>
                              </svg> <a  
                                target="_blank" 
                                aria-label="invia un'email a <?php echo $value; ?>"
				title="invia un'email a <?php echo $value; ?>" 
                                href="mailto:<?php echo $value; ?>">
                              <?php echo $value; ?>
                                </a>
                            </div></p>
                    <?php }
                    } ?>
                    <?php if ( isset($full_contatto['pec']) && is_array($full_contatto['pec']) && count ($full_contatto['pec']) ) {
                        foreach ($full_contatto['pec'] as $value) { ?>
                            <p><div class="field--name-field-ita-mail"> 
				 <svg class="icon">
	                            <use xlink:href="#it-mail"></use>
	                        </svg> 			   
                                <a  
                                target="_blank" 
                                aria-label="invia un'email a <?php echo $value; ?>"
				title="invia un'email a <?php echo $value; ?>" 
                                href="mailto:<?php echo $value; ?>">
                                  <?php echo ''.$value.''; ?>                                                                   
                                </a>
                            </div></p>
                    <?php }
                    } ?>

                    
                    <?php foreach ($other_contacts as $type) {
                        if ( isset($full_contatto[$type]) && is_array($full_contatto[$type]) && count ($full_contatto[$type]) ) {
                            foreach ($full_contatto[$type] as $value) {
                                echo '<p>'.$type.': '.$value.'</p>';
                            }
                        } 
                    } ?>
                        </div>
                    <?php } ?>
                </div>
            </div>

    </div>
<?php } 
$with_border = false;
?>
