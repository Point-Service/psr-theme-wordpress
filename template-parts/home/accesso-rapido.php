<?php
global $boxes;
$box_accesso_rapido = $boxes;
?>

<?php if (!empty($boxes)) { ?>
<div class="container py-5">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
    <h2 class="title-xxlarge mb-4">Accesso rapido</h2>

    <div class="row g-4 custom-styles">
        <?php foreach ($boxes as $box) {
            // Recupero delle variabili dal box
            $colore_sfondo = $box['colore'] ?? false; // Aggiungi colore se disponibile
            $sfondo_scuro = $colore_sfondo ? is_this_dark_hex($colore_sfondo) : true; // Controlla se il colore è scuro
        ?>
            <div class="col-md-6 col-xl-4">
                <a href="<?php echo $box['link_message']; ?>" style="<?= ($colore_sfondo) ? 'background-color:' . $colore_sfondo : '' ?>" class="card card-teaser <?= $colore_sfondo ? '' : 'bg-primary' ?> rounded mt-0 p-3" target="_blank">
                    <div class="cmp-card-simple card-wrapper pb-0 rounded">
                        <div style="border: none;">
                            <div class="card-body d-flex align-items-center">
                                <?php if (isset($box['icona_message']) && $box['icona_message'] && array_key_exists('icon', $box) && !empty($box['icon'])) { ?>
                                    <div class="avatar size-lg me-3" style="min-width: 50px; width: 50px; height: 50px; display: flex; justify-content: center; align-items: center; background-color: #f0f0f0; border-radius: 50%;">
                                        <i class="fas fa-<?php echo htmlspecialchars($box['icon']); ?>" style="color: #007bff; font-size: 24px;"></i>
                                    </div>
                                <?php } ?>
                                <div class="flex-grow-1">
                                    <h3 class="card-title t-primary title-xlarge text-white <?= $sfondo_scuro ? 'text-white' : 'text-dark' ?>" style="font-size: 1.5rem; line-height: 1.2;">
                                        <?php echo $box['titolo_message']; ?>
                                    </h3>
                                    <?php if (isset($box['desc_message']) && $box['desc_message']) { ?>
                                        <p class="card-text text-sans-serif mb-0 description text-white <?= $sfondo_scuro ? 'text-white' : '' ?>" style="font-size: 1rem; line-height: 1.5;">
                                            <?php echo $box['desc_message']; ?>
                                        </p>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
            </div>
        <?php } ?>
    </div>

</div>
<?php } ?>



    
<style>
/* Stile per la lista dei pulsanti accesso rapido */
.custom-styles .row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

.custom-styles .card {
    background-color: #ffffff; /* Sfondo bianco per il pulsante */
    border: 1px solid #ddd; /* Bordo grigio per la separazione */
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Leggera ombra per dare profondità */
    transition: transform 0.2s ease, box-shadow 0.2s ease; /* Transizione per effetto hover */
}

.custom-styles .card:hover {
    transform: translateY(-5px); /* Lieve sollevamento al passaggio del mouse */
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Ombra più pronunciata durante l'hover */
}

.custom-styles .card-body {
    display: flex;
    align-items: center;
    padding: 15px; /* Distanza tra il bordo e il contenuto */
}

.custom-styles .card-title {
    margin-bottom: 0; /* Rimosso margine per una visualizzazione compatta */
    font-size: 1.2rem;
    color: #333; /* Colore scuro per il titolo */
}

.custom-styles .description {
    font-size: 1rem;
    line-height: 1.5;
    color: #666; /* Colore di descrizione grigio scuro */
    margin-top: 8px; /* Un po' di margine sopra la descrizione */
}

/* Per le icone, usa un colore contrastante */
.custom-styles .avatar i {
    color: #ffffff; /* Colore icona bianco */
    font-size: 1.5rem; /* Icona più grande */
}

/* Aggiungere un po' di padding e arrotondamento ai pulsanti */
.custom-styles .card.teaser {
    padding: 20px 15px; /* Più padding per un aspetto più spazioso */
    border-radius: 10px; /* Bordi arrotondati per i pulsanti */
    background-color: #f8f9fa; /* Sfondo più chiaro per i pulsanti */
}

/* Colori di sfondo specifici per i pulsanti */
.custom-styles .card.bg-primary {
    background-color: #007bff !important; /* Colore blu per pulsanti */
    color: white; /* Colore testo bianco */
}

.custom-styles .card.bg-primary .card-title,
.custom-styles .card.bg-primary .description {
    color: white; /* Colore bianco per il testo in pulsante blu */
}

/* Effetto hover per il pulsante */
.custom-styles .card.bg-primary:hover {
    background-color: #0056b3; /* Cambia il colore di sfondo al passaggio del mouse */
}

/* Icone più grandi */
.custom-styles .avatar {
    background-color: #ffffff;
    border-radius: 50%;
    padding: 10px;
}

</style>

