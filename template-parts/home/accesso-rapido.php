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
        ?>
            <div class="col-md-6 col-xl-4 d-flex">
                <a href="<?php echo $box['link_message']; ?>" 
                   style="<?= ($colore_sfondo) ? 'background-color:' . $colore_sfondo : '' ?>" 
                   class="card card-teaser <?= $colore_sfondo ? '' : 'bg-neutral' ?> rounded mt-0 p-3 flex-fill" 
                   target="_blank">
                    <div class="cmp-card-simple card-wrapper pb-0 rounded">
                        <div style="border: none;">
                            <div class="card-body d-flex align-items-center">
                                <?php if (!empty($box['icon'])) { ?>
                                    <div class="avatar size-lg me-3">
                                        <i class="fas fa-<?php echo htmlspecialchars($box['icon']); ?>"></i>
                                    </div>
                                <?php } ?>
                                <div class="flex-grow-1 content-wrapper">
                                    <h3 class="card-title t-primary title-xlarge text-dark">
                                        <?php echo $box['titolo_message']; ?>
                                        <svg class="icon icon-white">
                                            <use href="#it-external-link"></use>
                                        </svg>
                                    </h3>
                                    <?php if (!empty($box['desc_message'])) { ?>
                                        <p class="card-text description text-muted">
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
/* Layout griglia */
.custom-styles .row {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 20px;
}

/* Card base */
.custom-styles .card {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 120px;
    background-color: #f9f9f9;
    border: 1px solid #e0e0e0;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    position: relative;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.custom-styles .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

/* Card body */
.custom-styles .card-body {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 15px;
    flex-grow: 1;
}

/* Titolo e descrizione */
.custom-styles .card-title {
    margin-bottom: 6px;
    font-size: 1.2rem;
    color: #333;
    position: relative;
    margin-right: 40px; /* spazio per SVG */
}

.custom-styles .description {
    font-size: 0.9rem;
    line-height: 1.4;
    color: #777;
    margin-top: 8px;
}

/* Avatar icona */
.custom-styles .avatar {
    background-color: #f0f0f0;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    display: flex;
    justify-content: center;
    align-items: center;
    margin-right: 15px;
}

.custom-styles .avatar i {
    font-size: 24px;
    color: #555;
}

/* Pulsanti neutri */
.custom-styles .card.bg-neutral {
    background-color: #fafafa;
    color: #333;
}

.custom-styles .card.bg-neutral:hover {
    background-color: #e0e0e0;
}

.custom-styles .card.bg-neutral:hover .card-title {
    color: #0056b3;
}

/* SVG esterno */
.custom-styles .card-title svg.icon-white {
    fill: #000 !important;
    position: absolute;
    top: 10px;
    right: 10px;
    width: 20px;
    height: 20px;
    z-index: 2;
    transition: transform 0.3s ease, fill 0.3s ease;
}

.custom-styles .card-title svg.icon-white:hover {
    transform: scale(1.1);
    fill: #f0f0f0 !important;
}

/* Allineamento colonne */
.custom-styles .col-md-6, .custom-styles .col-xl-4 {
    display: flex;
    align-items: stretch;
}

/* Mobile */
@media (max-width: 768px) {
    .custom-styles .card-body {
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }

    .custom-styles .content-wrapper {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    .custom-styles .avatar {
        margin-bottom: 10px;
        margin-right: 0;
    }

    .custom-styles .card-title {
        font-size: 1.1rem;
        margin-right: 0;
        margin-bottom: 5px;
    }

    .custom-styles .description {
        margin-top: 5px;
    }

    .custom-styles .card-title svg.icon-white {
        top: auto;
        right: auto;
        position: relative;
        margin-left: 5px;
    }
}
</style>
