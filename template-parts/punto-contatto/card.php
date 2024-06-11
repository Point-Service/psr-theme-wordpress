<?php
global $pc_id;
$prefix = '_dci_punto_contatto_';

$full_contatto = dci_get_full_punto_contatto($pc_id);
$contatto = get_post($pc_id);
$voci = dci_get_meta('voci', $prefix, $pc_id);

$other_contacts = array(
    'linkedin',
    'pec',
    'skype',
    'telegram',
    'twitter',
    'whatsapp'
);
?>

<div class="card card-teaser card-teaser-info rounded shadow-sm p-4 me-3">
    <div class="card-body pe-3">
        <h5 class="card-title">
            <?php echo $contatto->post_title; ?>
        </h5>
        <div class="card-text">
            <?php if ( is_array($full_contatto['indirizzo']) && count ($full_contatto['indirizzo']) ) {
                foreach ($full_contatto['indirizzo'] as $value) {
                    echo '<p>'.$value.'</p>';
                } 
                echo '<p class="mt-3"></p>';
            } ?>
            <?php if ( is_array($full_contatto['telefono']) && count ($full_contatto['telefono']) ) {
                foreach ($full_contatto['telefono'] as $value) {
                    echo '<p><b>Tel.</b> '.$value.'</p>';
                }
            } ?>
            <?php if ( is_array($full_contatto['url']) && count ($full_contatto['url']) ) {
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
            <?php if ( is_array($full_contatto['email']) && count ($full_contatto['email']) ) {
                foreach ($full_contatto['email'] as $value) { ?>
                    <p>
                       <svg class="icon me-2" viewBox="0 0 24 24"><g>
                        <g>
                        	<path d="M20.5,5h-17C2.7,5,2,5.7,2,6.5v11C2,18.3,2.7,19,3.5,19h17c0.8,0,1.5-0.7,1.5-1.5v-11C22,5.7,21.3,5,20.5,5z M20.2,6
                        		l-7.1,7.2c-0.6,0.6-1.6,0.6-2.2,0L3.8,6H20.2z M3,17.3V6.6L8.3,12L3,17.3z M3.7,18L9,12.7l1.2,1.2c1,0.9,2.6,0.9,3.6,0l1.2-1.2l0,0
                        		l5.3,5.3H3.7z M15.7,12L21,6.6v10.7L15.7,12z"></path>
                        </g>
                        </g></svg>
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
                if ( is_array($full_contatto[$type]) && count ($full_contatto[$type]) ) {
                    foreach ($full_contatto[$type] as $value) {
                        echo '<p>'.$type.': '.$value.'</p>';
                    }
                } 
            } ?>
        </div>
    </div>
</div>
