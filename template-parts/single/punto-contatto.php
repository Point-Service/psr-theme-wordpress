<?php
global $pc_id;
$prefix = '_dci_punto_contatto_';

// Recupera il contatto
$contatto = get_post($pc_id); 
if (!$contatto) {
    // Se il contatto non esiste, mostra un messaggio
    echo "Contatto non trovato.";
    return; // Ferma l'esecuzione se il contatto non esiste
}

// Recupera i dettagli del contatto
$full_contatto = dci_get_full_punto_contatto($pc_id);

// Recupera altre informazioni legate al contatto
$voci = dci_get_meta('voci', $prefix, $pc_id); 

// Array di altri contatti
$other_contacts = array(
    'linkedin',
    'pec',
    'skype',
    'telegram',
    'twitter',
    'whatsapp'
);

// Verifica se ci sono dati di contatto disponibili
$contatti_presenti = false;

function check_if_contact_present($contact_data) {
    return isset($contact_data) && is_array($contact_data) && count($contact_data) > 0;
}

// Verifica se ci sono email, telefono, indirizzo, URL
if (check_if_contact_present($full_contatto['email']) || 
    check_if_contact_present($full_contatto['telefono']) ||
    check_if_contact_present($full_contatto['indirizzo']) ||
    check_if_contact_present($full_contatto['url'])) {
    $contatti_presenti = true;
}

// Verifica se ci sono contatti aggiuntivi (LinkedIn, Skype, ecc.)
foreach ($other_contacts as $type) {
    if (check_if_contact_present($full_contatto[$type])) {
        $contatti_presenti = true;
        break;
    }
}

?>

<div class="card card-teaser shadow mt-3 rounded">
    <?php if (check_if_contact_present($full_contatto['email'])): ?>
        <svg class="icon" aria-hidden="true">
            <use xlink:href="#it-mail"></use>
        </svg>
    <?php elseif (check_if_contact_present($full_contatto['telefono'])): ?>
        <svg class="icon" aria-hidden="true">
            <use xlink:href="#it-telephone"></use>
        </svg>
    <?php else: ?>
        <svg class="icon" aria-hidden="true">
            <use xlink:href="#it-map-marker-circle"></use>
        </svg>
    <?php endif; ?>

    <div class="card-body">
        <h3 class="card-title h5">
            <span>
                <?php echo $contatto->post_title; ?>
            </span>
        </h3>
        <div class="card-text">
            <!-- Se non ci sono indirizzi, non mostrare nulla, altrimenti visualizza i dettagli -->
            <?php if (check_if_contact_present($full_contatto['indirizzo'])): ?>
                <?php foreach ($full_contatto['indirizzo'] as $value): ?>
                    <a href="https://www.google.com/maps/place/<?php echo $value; ?>" target="_blank"><?php echo $value; ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Se non ci sono numeri di telefono, non mostrare nulla, altrimenti visualizza i dettagli -->
            <?php if (check_if_contact_present($full_contatto['telefono'])): ?>
                <?php foreach ($full_contatto['telefono'] as $value): ?>
                    <a href="tel:<?php echo $value;?>"><?php echo $value; ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Se non ci sono URL, non mostrare nulla, altrimenti visualizza i dettagli -->
            <?php if (check_if_contact_present($full_contatto['url'])): ?>
                <?php foreach ($full_contatto['url'] as $value): ?>
                    <p>
                        <a 
                            target="_blank" 
                            aria-label="scopri di più su <?php echo $value; ?> - link esterno - apertura nuova scheda" 
                            title="vai sul sito <?php echo $value; ?>" 
                            href="<?php echo $value; ?>">
                            <?php echo $value; ?>
                        </a>
                    </p>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Se non ci sono email, non mostrare nulla, altrimenti visualizza i dettagli -->
            <?php if (check_if_contact_present($full_contatto['email'])): ?>
                <?php foreach ($full_contatto['email'] as $value): ?>
                    <a  
                        target="_blank" 
                        aria-label="invia un'email a <?php echo $value; ?>"
                        title="invia un'email a <?php echo $value; ?>" 
                        href="mailto:<?php echo $value; ?>">
                        <?php echo $value; ?>
                    </a>
                <?php endforeach; ?>
            <?php endif; ?>

            <!-- Altri contatti (LinkedIn, Skype, ecc.) -->
            <?php foreach ($other_contacts as $type): ?>
                <?php if (check_if_contact_present($full_contatto[$type])): ?>
                    <?php foreach ($full_contatto[$type] as $value): ?>
                        <p><?php echo $type; ?>: <?php echo $value; ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>

            <?php if (!$contatti_presenti): ?>
                <!-- Se non ci sono dati, mostra un messaggio -->
                <p>Nessun contatto disponibile per questo ID.</p>
            <?php endif; ?>
        </div>
    </div>
</div>



