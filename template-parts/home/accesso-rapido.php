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
                            <div class="card-body d-flex align-items-center bg-white shadow-sm rounded"> <!-- Aggiunta bg-white e shadow-sm -->
                                <?php if (isset($box['icona_message']) && $box['icona_message'] && array_key_exists('icon', $box) && !empty($box['icon'])) { ?>
                                    <div class="avatar size-lg me-3" style="min-width: 50px; width: 50px; height: 50px; display: flex; justify-content: center; align-items: center; background-color: #f0f0f0; border-radius: 50%;">
                                        <i class="fas fa-<?php echo htmlspecialchars($box['icon']); ?>" style="color: #007bff; font-size: 24px;"></i>
                                    </div>
                                <?php } ?>                
                                <div class="flex-grow-1"> <!-- Contenitore per il titolo e la descrizione -->
                                    <h3 class="card-title t-primary title-xlarge text-dark <?= $sfondo_scuro ? 'text-white' : 'text-dark' ?>" style="font-size: 1.5rem; line-height: 1.2;">
                                        <?php echo $box['titolo_message']; ?>
                                    </h3>
                                    <?php if (isset($box['desc_message']) && $box['desc_message']) { ?>
                                        <p class="card-text text-sans-serif mb-0 description <?= $sfondo_scuro ? 'text-white' : 'text-dark' ?>" style="font-size: 1rem; line-height: 1.5;">
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
    .custom-styles .row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .custom-styles .card-wrapper {
        width: 100%;
    }

    .custom-styles .card {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .custom-styles .card-body {
        flex: 1;
        display: flex; /* Flex per allineare l'icona e il contenuto */
        background-color: white; /* Imposto il background bianco */
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Aggiunta ombra */
        border-radius: 10px; /* Aggiunta bordatura arrotondata */
    }

    .custom-styles .card-title {
        margin-bottom: 0;
    }

    .custom-styles .btn {
        width: max-content;
    }
</style>



