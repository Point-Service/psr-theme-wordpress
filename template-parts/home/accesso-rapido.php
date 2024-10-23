<?php
global $boxes;
$box_accesso_rapido = $boxes;
?>
<head>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
</head>

<div class="container py-5">
    <?php if (!empty($boxes)) { ?>
        <h2 class="title-xxlarge mb-4">Accesso rapido</h2>
    <?php } ?>
    <div class="row g-4">
        <?php foreach($boxes as $box) { ?>
        <div class="col-md-6 col-xl-4">
            <a href="<?php echo htmlspecialchars($box['link_message']); ?>" class="card card-teaser bg-primary rounded mt-0 p-3 shadow-sm border border-light d-flex flex-column" target="_blank" data-focus-mouse="false">
                <?php 
                if (array_key_exists('icona_message', $box) && array_key_exists('icon', $box) && !empty($box['icon'])) { ?>
                    <div class="avatar size-lg me-3" style="min-width: 50px; width: 50px; height: 50px; display: flex; justify-content: center; align-items: center; background-color: #f0f0f0; border-radius: 50%;">
                        <i class="fas fa-<?php echo htmlspecialchars($box['icon']); ?>" style="color: #007bff; font-size: 24px;"></i>
                    </div>
                <?php } ?>
                <div class="card-body flex-grow-1"> <!-- Aggiunta la classe flex-grow-1 -->
                    <h3 class="card-title sito-tematico titolo-sito-tematico text-white">
                        <?php echo htmlspecialchars($box['titolo_message']); ?>
                    </h3>
                    <?php if (isset($box['desc_message']) && $box['desc_message']) { ?>
                        <p class="card-text text-sans-serif text-white">
                            <?php echo htmlspecialchars($box['desc_message']); ?>            
                        </p>
                    <?php } ?>
                </div>
            </a>
        </div>
        <?php } ?>
    </div>
</div>

<style>
    .custom-styles .row {
        display: flex; /* Cambiato in flex per gestire l'allineamento */
        flex-wrap: wrap; /* Permette la rottura su più righe */
        gap: 20px;
    }

    .custom-styles .card {
        height: 100%; /* Assicura che il card occupi tutta l'altezza disponibile */
    }

    .custom-styles .card-body {
        flex: 1; /* Permette al card-body di occupare spazio disponibile */
    }
</style>
