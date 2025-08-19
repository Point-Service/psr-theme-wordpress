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
       class="sito-tematico-card" 
       target="_blank"
       style="<?= $colore_sfondo ? "background-color: $colore_sfondo;" : '' ?>">

        <div class="card-body">
            <?php if($st_img) { ?>
                <div class="avatar me-3" style="min-width:50px; width:50px; height:50px; display:flex; justify-content:center; align-items:center; background-color:#f0f0f0; border-radius:50%;">
                    <?php dci_get_img($st_img); ?>
                </div>
            <?php } ?>

            <div class="flex-grow-1">
                <h3 class="card-title" style="color: <?= $sfondo_scuro ? '#fff' : '#333' ?>;">
                    <?= $sito_tematico->post_title ?>
                    <svg class="icon" style="width:20px; height:20px; margin-left:8px;">
                        <use href="#it-external-link"></use>
                    </svg>
                </h3>
                <p class="card-text mb-0" style="font-size:1rem; line-height:1.5; color: <?= $sfondo_scuro ? '#ddd' : '#555' ?>;">
                    <?= $st_descrizione ?>
                </p>
            </div>
        </div>
    </a>
</div>

<style>
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
    padding: 12px;
}

.sito-tematico-card-wrapper .sito-tematico-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}

.sito-tematico-card-wrapper .sito-tematico-card .card-body {
    display: flex;
    align-items: center;
}

.sito-tematico-card-wrapper .sito-tematico-card .avatar i,
.sito-tematico-card-wrapper .sito-tematico-card .avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

/* Titolo con icona inline */
.sito-tematico-card-wrapper .sito-tematico-card .card-title {
    display: flex;
    align-items: center;
    margin: 0;
}

.sito-tematico-card-wrapper .sito-tematico-card .icon {
    transition: transform 0.3s ease, fill 0.3s ease;
}

.sito-tematico-card-wrapper .sito-tematico-card:hover .icon {
    transform: scale(1.1);
    fill: #777777;
}
</style>

