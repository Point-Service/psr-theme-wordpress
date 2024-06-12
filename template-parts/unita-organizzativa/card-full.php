<?php
    global $uo_id, $with_border;
    $ufficio = get_post( $uo_id );

    $prefix = '_dci_unita_organizzativa_';
    $img = dci_get_meta('immagine', $prefix, $uo_id);
    $punti_contatto = dci_get_meta('contatti', $prefix, $uo_id);
    $sede_principale = dci_get_meta("sede_principale");
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

<div class="card card-teaser shadow mt-3 rounded">
    <svg class="icon">
        <use xlink:href="#it-pa"></use>
    </svg>
    <div class="card-body">
        <h3 class="card-title h5">
            <a class="text-decoration-none" href="<?php echo get_permalink($ufficio->ID); ?>" data-element="service-area">
                <?php echo $ufficio->post_title; ?>
            </a>
        </h5>
        <div class="card-text">
            <?php foreach ($contatti as $full_contatto) { ?>
                <div class="card-text mb-3">
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
                                    <?php echo $value; ?>
                                </a>
                            </p>
                    <?php }
                    } ?>
                    <?php if ( isset($full_contatto['email']) && is_array($full_contatto['email']) && count ($full_contatto['email']) ) {
                        foreach ($full_contatto['email'] as $value) { ?>
                            <p><div class="field--name-field-ita-mail">                                  
                                <a  
                                target="_blank" 
                                aria-label="invia un'email a <?php echo $value; ?>"
                                href="mailto:<?php echo $value; ?>">
                                     <?php echo $value; ?>
                                </a>
                            </div></p>
                    <?php }
                    } ?>
                    <?php if ( isset($full_contatto['pec']) && is_array($full_contatto['pec']) && count ($full_contatto['pec']) ) {
                        foreach ($full_contatto['pec'] as $value) { ?>
                            <p><div class="field--name-field-ita-mail">
                                <a  
                                target="_blank" 
                                aria-label="invia un'email a <?php echo $value; ?>"
                                href="mailto:<?php echo $value; ?>">
                                <?php echo $value; ?>
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
    <div class="card-wrapper rounded shadow-sm h-auto my-5">
        <div class="card card-teaser card-teaser-info rounded shadow-sm p-4">
            <svg class="icon">
                <use xlink:href="#it-pa"></use>
            </svg>
            <div class="card-body pe-3">
                <h3 class="card-title h5">
                    <a href="<?php echo get_permalink($ufficio->ID); ?>">
                        <?php echo $ufficio->post_title; ?>
                    </a>
                </h5>

                        
                            <section class="it-page-section">
                                <h2 class="mb-3" id="contacts">Sede principale</h2>
                                <div class="row">
                                    <div class="col-12 col-md-8 col-lg-6 mb-30">
                                        <div class="card-wrapper rounded h-auto mt-10">
                                            <div class="card card-teaser card-teaser-info rounded shadow-sm p-3">
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
                        
                
                <div class="card-text">
                    <?php foreach ($contatti as $full_contatto) { ?>
                        <div class="card-text mb-3">

                            <?php if (array_key_exists('indirizzo', $full_contatto) && is_array($full_contatto['indirizzo']) && count($full_contatto['indirizzo'])) {
                                echo '<div class="mb-3">';
                                foreach ($full_contatto['indirizzo'] as $dati) {
                                    echo '<p>' . $dati['valore'];
                                    if ($dati['dettagli']) {
                                        echo $dati['dettagli'];
                                    }
                                    echo '</p>';
                                }
                                echo '</div>';
                            } ?>
        
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
                                 <?php echo $value; ?>
                                </a>
                            </p>
                    <?php }
                    } ?>
                    <?php if ( isset($full_contatto['email']) && is_array($full_contatto['email']) && count ($full_contatto['email']) ) {
                        foreach ($full_contatto['email'] as $value) { ?>
                            <p><div class="field--name-field-ita-mail">                                    
                                <a  
                                target="_blank" 
                                aria-label="invia un'email a <?php echo $value; ?>"
                                href="mailto:<?php echo $value; ?>">
                                   <?php echo $value; ?>
                                </a>
                            </div></p>
                    <?php }
                    } ?>
                    <?php if ( isset($full_contatto['pec']) && is_array($full_contatto['pec']) && count ($full_contatto['pec']) ) {
                        foreach ($full_contatto['pec'] as $value) { ?>
                            <p><div class="field--name-field-ita-mail">                                
                                <a  
                                target="_blank" 
                                aria-label="invia un'email a <?php echo $value; ?>"
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
    </div>
<?php } 
$with_border = false;
?>
