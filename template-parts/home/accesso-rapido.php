<?php
global $boxes;
$box_accesso_rapido = $boxes;
?>
<div class="container py-5">
    <?php if (!empty($boxes)) { ?>
        <h2 class="title-xxlarge mb-4">Accesso rapido</h2>
    <?php } ?>
    <div class="row g-4 custom-styles">
        <?php foreach ($boxes as $box) {
            // Recupero delle variabili dal box
            $colore_sfondo = $box['colore'] ?? false; // Aggiungi colore se disponibile
            $sfondo_scuro = $colore_sfondo ? is_this_dark_hex($colore_sfondo) : true; // Controlla se il colore è scuro
        ?>
            <div class="col-md-6 col-xl-4">
                <a href="<?php echo $box['link_message']; ?>" style="<?= ($colore_sfondo) ? 'background-color:' . $colore_sfondo : '' ?>" class="card card-teaser <?= $colore_sfondo ? '' : 'bg-primary' ?> rounded mt-0 p-3 shadow-sm border border-light" target="_blank">
                    <div class="cmp-card-simple card-wrapper pb-0 rounded border-none">
                        <div class="card shadow-sm rounded">
                            <div class="card-body d-flex align-items-center"> <!-- Usato align-items-center per centrare verticalmente -->
                                <?php if (array_key_exists('icon', $box) && !empty($box['icon'])) { ?>
                                    <div class="avatar size-lg me-3" style="min-width: 50px; width: 50px; height: 50px; display: flex; justify-content: center; align-items: center; background-color: #f0f0f0; border-radius: 50%;">
                                        <i class="fas fa-<?php echo htmlspecialchars($box['icon']); ?>" style="color: #007bff; font-size: 24px;"></i>
                                    </div>
                                <?php } ?>
                                <div class="flex-grow-1"> <!-- Contenitore per il titolo e la descrizione -->
                                    <h3 class="card-title t-primary title-xlarge text-white <?= $sfondo_scuro ? 'text-white' : 'text-dark' ?>">
                                        <?php echo $box['titolo_message']; ?>
                                    </h3>
                                    <?php if (isset($box['desc_message']) && $box['desc_message']) { ?>
                                        <p class="card-text text-sans-serif mb-0 description text-white <?= $sfondo_scuro ? 'text-white' : '' ?>">
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
    }

    .custom-styles .card-title {
        margin-bottom: 0; /* Rimosso margine per mantenere il testo più compatto */
    }

    .custom-styles .btn {
        width: max-content;
    }
</style>


