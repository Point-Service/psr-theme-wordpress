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
            $colore_sfondo = $box['colore'] ?? false;
            $sfondo_scuro = $colore_sfondo ? is_this_dark_hex($colore_sfondo) : true;

            // ✂️ Titolo e descrizione con trim + taglio
            $titolo = trim($box['titolo_message']);
            if (strlen($titolo) > 50) {
                $titolo = substr($titolo, 0, 47) . '...';
            }

            $descrizione = trim($box['desc_message']);
            if (strlen($descrizione) > 100) {
                $descrizione = substr($descrizione, 0, 97) . '...';
            }
        ?>
            <div class="col-md-6 col-xl-4">
                <a href="<?php echo esc_url($box['link_message']); ?>" style="<?= ($colore_sfondo) ? 'background-color:' . $colore_sfondo : '' ?>" class="card card-teaser <?= $colore_sfondo ? '' : 'bg-primary' ?> rounded mt-0 p-3" target="_blank">
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
                                        <?php echo esc_html($titolo); ?>
                                    </h3>
                                    <?php if (!empty($descrizione)) { ?>
                                        <p class="card-text text-sans-serif mb-0 description text-dark <?= $sfondo_scuro ? '' : 'text-dark' ?>" style="font-size: 1rem; line-height: 1.5;">
                                            <?php echo esc_html($descrizione); ?>
                                        </p>
                                    <?php } ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </a>
                <!-- Rimosso il tasto "Scopri" -->
            </div>
        <?php } ?>
    </div>
</div>
<?php } ?>




    
<style>
.custom-styles .card {
    background-color: #fff !important; /* Sfondo bianco per la card */
    border: 1px solid #ddd; /* Bordi leggeri per separare visivamente */
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1); /* Ombra leggera */
}

.custom-styles .card-body {
    padding: 15px; /* Ridotto padding per contenuto più compatto */
}

.custom-styles .card-text {
    font-size: 0.95rem;
    line-height: 1.4;
    margin-top: 0.5rem;
    color: #333; /* Colore testo scuro per buona visibilità su sfondo bianco */
}

/* Titolo più contrastato */
.custom-styles .card-title {
    margin-bottom: 0;
    font-size: 1.2rem;
    color: #333; /* Colore del titolo più scuro per contrastare il bianco */
}

/* Stile per il tasto (se ne avessi uno) */
.custom-styles .btn {
    width: 150px; /* Ristretto il bottone */
    padding: 10px 15px; /* Più stretto */
    background-color: #007bff; /* Colore blu per il tasto */
    color: white;
    border: none;
    border-radius: 30px; /* Tasto arrotondato */
    text-transform: uppercase;
    font-size: 0.875rem;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1); /* Ombra per evidenziare */
}

.custom-styles .btn:hover {
    background-color: #0056b3; /* Colore hover più scuro */
}

.custom-styles .btn:focus {
    outline: none;
}

/* Per le icone, un colore contrastante */
.custom-styles .avatar i {
    color: #333; /* Colore icona più scuro */
}


</style>

