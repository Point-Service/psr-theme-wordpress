<?php
global $boxes;
$box_accesso_rapido = $boxes;
?>

<?php if (!empty($boxes)) { ?>
<div class="container py-4 accesso-rapido-section">
    <h2 class="title-large mb-3 text-uppercase border-bottom pb-2">Accesso rapido</h2>

    <div class="row g-3">
        <?php foreach ($boxes as $box) {
            $colore_sfondo = $box['colore'] ?? false;
            $sfondo_scuro = $colore_sfondo ? is_this_dark_hex($colore_sfondo) : true;

            // Titolo con trim e limite caratteri
            $titolo = trim($box['titolo_message']);
            if (strlen($titolo) > 50) {
                $titolo = substr($titolo, 0, 47) . '...';
            }

            // Descrizione con limite opzionale
            $descrizione = trim($box['desc_message']);
            if (strlen($descrizione) > 100) {
                $descrizione = substr($descrizione, 0, 97) . '...';
            }
        ?>
            <div class="col-12 col-sm-6 col-lg-4">
                <a href="<?php echo esc_url($box['link_message']); ?>" target="_blank"
                   class="card accesso-box border-0 shadow-sm text-decoration-none h-100 p-3 <?php echo $sfondo_scuro ? 'text-white' : 'text-dark'; ?>"
                   style="background-color: <?php echo $colore_sfondo ?: '#f8f9fa'; ?>;">

                    <div class="d-flex align-items-center mb-2">
                        <?php if (!empty($box['icon'])) { ?>
                            <div class="accesso-icon me-2 flex-shrink-0">
                                <i class="fas fa-<?php echo esc_attr($box['icon']); ?>"></i>
                            </div>
                        <?php } ?>
                        <h3 class="h6 mb-0 flex-grow-1">
                            <?php echo esc_html($titolo); ?>
                        </h3>
                    </div>

                    <?php if (!empty($descrizione)) { ?>
                        <p class="small mb-2 lh-sm"><?php echo esc_html($descrizione); ?></p>
                    <?php } ?>

                    <div class="text-end mt-auto">
                        <span class="small">Scopri</span>
                        <svg class="icon icon-xs ms-1">
                            <use href="#it-arrow-right"></use>
                        </svg>
                    </div>

                </a>
            </div>
        <?php } ?>
    </div>
</div>
<?php } ?>


    
<style>
 .accesso-rapido-section .accesso-box {
    border-radius: 0.5rem;
    min-height: 160px;
    transition: all 0.2s ease-in-out;
    font-size: 0.95rem;
}

.accesso-rapido-section .accesso-box:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}

.accesso-rapido-section .accesso-icon {
    width: 36px;
    height: 36px;
    background-color: rgba(255,255,255,0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}
</style>

