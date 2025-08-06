<?php
global $boxes;
$box_accesso_rapido = $boxes;
?>

<?php if (!empty($boxes)) { ?>
<div class="container py-5 accesso-rapido-section">
    <h2 class="title-xxlarge mb-4 text-uppercase border-bottom border-2 pb-2">Accesso rapido</h2>

    <div class="row g-4">
        <?php foreach ($boxes as $box) {
            $colore_sfondo = $box['colore'] ?? false;
            $sfondo_scuro = $colore_sfondo ? is_this_dark_hex($colore_sfondo) : true;
            ?>
            <div class="col-sm-6 col-lg-4">
                <a href="<?php echo esc_url($box['link_message']); ?>" target="_blank" class="card accesso-box border-0 shadow-sm h-100 text-decoration-none <?php echo $sfondo_scuro ? 'text-white' : 'text-dark'; ?>" style="<?php echo $colore_sfondo ? 'background-color:' . $colore_sfondo : 'background-color: #f5f5f5'; ?>">
                    <div class="card-body d-flex flex-column justify-content-between h-100">
                        <div class="d-flex align-items-center mb-3">
                            <?php if (!empty($box['icon'])) { ?>
                                <div class="accesso-icon me-3 flex-shrink-0">
                                    <i class="fas fa-<?php echo esc_attr($box['icon']); ?>"></i>
                                </div>
                            <?php } ?>
                            <h3 class="card-title fs-5 m-0 flex-grow-1">
                                <?php echo esc_html($box['titolo_message']); ?>
                            </h3>
                        </div>
                        <?php if (!empty($box['desc_message'])) { ?>
                            <p class="card-text small lh-sm">
                                <?php echo esc_html($box['desc_message']); ?>
                            </p>
                        <?php } ?>
                        <div class="mt-auto d-flex align-items-center justify-content-between pt-2 border-top" style="border-color: rgba(255,255,255,0.3);">
                            <span class="small fw-semibold">Scopri</span>
                            <svg class="icon icon-sm">
                                <use href="#it-arrow-right"></use>
                            </svg>
                        </div>
                    </div>
                </a>
            </div>
        <?php } ?>
    </div>
</div>
<?php } ?>

    
<style>
 .accesso-rapido-section .accesso-box {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 0.75rem;
}

.accesso-rapido-section .accesso-box:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

.accesso-rapido-section .accesso-icon {
    width: 48px;
    height: 48px;
    background-color: rgba(255,255,255,0.15);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.accesso-rapido-section .card-title {
    font-size: 1.1rem;
    font-weight: 600;
}

.accesso-rapido-section .card-text {
    font-size: 0.95rem;
    opacity: 0.9;
}

</style>

