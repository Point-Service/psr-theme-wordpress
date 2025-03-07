<?php
global $pc_id;
$prefix = '_dci_punto_contatto_';

$contatto = get_post($pc_id); // Recupera il post con l'ID
if (!$contatto) {
    // Se il contatto non esiste, mostra un messaggio
    echo "Contatto non trovato.";
    return; // Ferma l'esecuzione se il contatto non esiste
}

$full_contatto = dci_get_full_punto_contatto($pc_id); // Recupera i dettagli del contatto
if (empty($full_contatto)) {
    // Se i dettagli del contatto non esistono, mostra un messaggio
    echo "Dettagli del contatto non disponibili.";
    return; // Ferma l'esecuzione se i dettagli non esistono
}

$voci = dci_get_meta('voci', $prefix, $pc_id); // Recupera altre informazioni legate al contatto

$other_contacts = array(
    'linkedin',
    'pec',
    'skype',
    'telegram',
    'twitter',
    'whatsapp'
);

// Verifica se almeno uno dei contatti esiste
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

// Verifica se ci sono contatti aggiuntivi
foreach ($other_contacts as $type) {
    if (check_if_contact_present($full_contatto[$type])) {
        $contatti_presenti = true;
        break;
    }
}

// Se non ci sono dati da mostrare, non visualizzare la tabella
if (!$contatti_presenti) {
    echo "Nessun contatto disponibile.";
    return; // Ferma l'esecuzione se non ci sono contatti
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
            <!-- Controlla se ci sono indirizzi -->
            <?php if (check_if_contact_present($full_contatto['indirizzo'])): ?>
                <?php foreach ($full_contatto['indirizzo'] as $value): ?>
                    <a href="https://www.google.com/maps/place/<?php echo $value; ?>" target="_blank"><?php echo $value; ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Controlla se ci sono numeri di telefono -->
            <?php if (check_if_contact_present($full_contatto['telefono'])): ?>
                <?php foreach ($full_contatto['telefono'] as $value): ?>
                    <a href="tel:<?php echo $value;?>"><?php echo $value; ?></a>
                <?php endforeach; ?>
            <?php endif; ?>
            
            <!-- Controlla se ci sono URL -->
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

            <!-- Controlla se ci sono email -->
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

            <!-- Altri contatti (linkedin, skype, ecc.) -->
            <?php foreach ($other_contacts as $type): ?>
                <?php if (check_if_contact_present($full_contatto[$type])): ?>
                    <?php foreach ($full_contatto[$type] as $value): ?>
                        <p><?php echo $type; ?>: <?php echo $value; ?></p>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</div>



