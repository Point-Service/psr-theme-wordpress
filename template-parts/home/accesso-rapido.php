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
                <a href="<?php echo $box['link_message']; ?>" style="<?= ($colore_sfondo) ? 'background-color:' . $colore_sfondo : '' ?>" class="card card-teaser <?= $colore_sfondo ? '' : 'bg-neutral' ?> rounded mt-0 p-3" target="_blank">
                    <div class="cmp-card-simple card-wrapper pb-0 rounded">
                        <div style="border: none;">
                            <div class="card-body d-flex align-items-center">
                                <?php if (isset($box['icona_message']) && $box['icona_message'] && array_key_exists('icon', $box) && !empty($box['icon'])) { ?>
                                    <div class="avatar size-lg me-3" style="min-width: 50px; width: 50px; height: 50px; display: flex; justify-content: center; align-items: center; background-color: #f0f0f0; border-radius: 50%;">
                                        <i class="fas fa-<?php echo htmlspecialchars($box['icon']); ?>" style="color: #555555; font-size: 24px;"></i>
                                    </div>
                                <?php } ?>
                                <div class="flex-grow-1">
                                    <h3 class="card-title t-primary title-xlarge text-dark" style="font-size: 1.5rem; line-height: 1.2;">
                                        <?php echo $box['titolo_message']; ?>
                                    </h3>
                                    <?php if (isset($box['desc_message']) && $box['desc_message']) { ?>
                                        <p class="card-text text-sans-serif mb-0 description text-muted" style="font-size: 1rem; line-height: 1.5;">
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
    /* Stile per la sezione dei siti tematici */
    .siti-tematici-section .card-wrapper {
        display: flex;
        align-items: stretch; /* Allineamento verticale per tutte le card */
        height: 100%;
    }

    /* Stile di base per le card */
    .siti-tematici-section .card {
        background-color: #f9f9f9; /* Sfondo neutro chiaro */
        border: 1px solid #e0e0e0; /* Bordo grigio chiaro */
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Ombra leggera */
        transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 200px; /* Altezza minima */
        border-radius: 10px; /* Bordo arrotondato */
    }

    /* Effetto hover per sollevare la card */
    .siti-tematici-section .card:hover {
        transform: translateY(-5px); /* Sollevamento della card */
        box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Ombra più intensa */
    }

    /* Colore e effetto hover sul titolo */
    .siti-tematici-section .card-title {
        font-size: 1.2rem;
        color: #333333; /* Colore scuro per il titolo */
        margin-bottom: 8px;
        transition: color 0.3s ease;
    }

    /* Colore del titolo su hover */
    .siti-tematici-section .card:hover .card-title {
        color: #0056b3; /* Blu scuro per il titolo quando si passa sopra */
    }

    /* Stile per la descrizione */
    .siti-tematici-section .description {
        font-size: 1rem;
        line-height: 1.5;
        color: #777777; /* Grigio per la descrizione */
        margin-top: 10px;
    }

    /* Icona: circolare, centrata e con sfondo chiaro */
    .siti-tematici-section .avatar {
        background-color: #f0f0f0;
        border-radius: 50%;
        padding: 12px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .siti-tematici-section .avatar i {
        color: #555555; /* Colore scuro per l'icona */
        font-size: 24px;
    }

    /* Hover per il background della card */
    .siti-tematici-section .card.bg-neutral:hover {
        background-color: #e0e0e0; /* Cambia colore su hover */
    }

    /* Layout e spaziatura */
    .siti-tematici-section .card-wrapper {
        width: 100%;
    }

    /* Layout responsive per schermi più piccoli */
    @media (max-width: 768px) {
        .siti-tematici-section .card-body {
            flex-direction: column;
            text-align: center;
        }
        .siti-tematici-section .card-title {
            font-size: 1.1rem;
        }
    }

    /* Colonna e card uniforme per altezza */
    .siti-tematici-section .col-md-6, .siti-tematici-section .col-lg-4 {
        display: flex;
        align-items: stretch;
    }

    .siti-tematici-section .card {
        display: flex;
        flex-direction: column;
        height: 100%;
        min-height: 200px; /* Altezza minima */
    }

    .siti-tematici-section .card-body {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        padding: 20px;
        flex-grow: 1;
    }
</style>


