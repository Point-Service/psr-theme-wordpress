<?php
global $sito_tematico_id;

$sito_tematico = get_post($sito_tematico_id);
$prefix = '_dci_sito_tematico_';
$st_descrizione = dci_get_meta('descrizione_breve', $prefix, $sito_tematico->ID);
$st_link = dci_get_meta('link', $prefix, $sito_tematico->ID);
$st_colore = dci_get_meta('colore', $prefix, $sito_tematico->ID);
$st_img = dci_get_meta('immagine', $prefix, $sito_tematico->ID);

$colore_sfondo = $st_colore ?: false;
$sfondo_scuro = $colore_sfondo ? is_this_dark_hex($colore_sfondo) : false;
?>

<div class="sito-tematico-card-wrapper col-md-6 col-xl-4">
    <a href="<?= $st_link ?>" 
       style="<?= $colore_sfondo ? "background-color:$colore_sfondo;" : '' ?>" 
       class="card card-teaser sito-tematico-card mt-0 p-2 shadow-sm" 
       target="_blank">

        <div class="card-body d-flex align-items-center position-relative" style="padding:10px;">

            <!-- Icona in alto a destra della card -->
            <svg class="icon icon-dark external-icon" style="width:18px; height:18px; fill:#555555; position:absolute; top:10px; right:10px;">
                <use href="#it-external-link"></use>
            </svg>

            <!-- Icona/avatar -->
            <?php if($st_img) { ?>
                <div class="avatar size-lg me-3" style="flex-shrink: 0; width:40px; height:40px; border-radius:50%; background-color:#f0f0f0; display:flex; align-items:center; justify-content:center;">
                    <?php dci_get_img($st_img); ?>
                </div>
            <?php } ?>

            <!-- Titolo e descrizione -->
            <div class="flex-grow-1">
                <h3 class="card-title mb-1" style="font-size:1.1rem; font-weight:600; color: <?= $sfondo_scuro ? '#fff' : '#333' ?>;">
                    <?= $sito_tematico->post_title ?>
                </h3>
                <p class="card-text mb-0" style="font-size:0.85rem; color: <?= $sfondo_scuro ? '#ddd' : '#555' ?>;">
                    <?= $st_descrizione ?>
                </p>
            </div>

        </div>
    </a>
</div>

<style>
/* Card principale */
.sito-tematico-card-wrapper .sito-tematico-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background-color: #fafafa;
    box-shadow: 0 2px 4px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    position: relative;
    text-decoration: none;
    min-height: 80px;
}

/* Hover card */
.sito-tematico-card-wrapper .sito-tematico-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}

/* Card body */
.sito-tematico-card-wrapper .sito-tematico-card .card-body {
    display: flex;
    align-items: center;
    padding: 8px 10px;
}

/* Avatar */
.sito-tematico-card-wrapper .sito-tematico-card .avatar i,
.sito-tematico-card-wrapper .sito-tematico-card .avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

/* Titolo */
.sito-tematico-card-wrapper .sito-tematico-card .card-title {
    margin: 0;
}

/* Icona esterna in alto a destra */
.sito-tematico-card-wrapper .sito-tematico-card .external-icon {
    transition: transform 0.3s ease, fill 0.3s ease;
}

.sito-tematico-card-wrapper .sito-tematico-card:hover .external-icon {
    transform: scale(1.2);
    fill: #777777;
}
</style>
