<?php
global $sito_tematico_id;

$sito_tematico = get_post($sito_tematico_id);
$prefix = '_dci_sito_tematico_';
$st_descrizione = dci_get_meta('descrizione_breve', $prefix, $sito_tematico->ID);
$st_link = dci_get_meta('link',$prefix, $sito_tematico->ID);
$st_colore = dci_get_meta('colore',$prefix, $sito_tematico->ID);
$st_img = dci_get_meta('immagine',$prefix, $sito_tematico->ID);

$colore_sfondo = $st_colore ?: false;
$sfondo_scuro = $colore_sfondo ? is_this_dark_hex($colore_sfondo) : false;
?>

<a href="<?php echo $st_link ?>" 
   style="<?= ($colore_sfondo) ? 'background-color:'.$colore_sfondo : '' ?>" 
   class="card card-teaser sito-tematico-card mt-0 p-3 shadow-sm" 
   target="_blank">

    <div class="card-body d-flex align-items-center" style="padding:12px; position: relative;">

        <!-- Icona -->
        <?php if($st_img) { ?>
            <div class="avatar size-lg me-3" style="flex-shrink: 0; width:50px; height:50px; border-radius:50%; background-color:#f0f0f0; display:flex; align-items:center; justify-content:center;">
                <?php dci_get_img($st_img); ?>
            </div>
        <?php } ?>

        <!-- Titolo e descrizione -->
        <div class="flex-grow-1">
            <h3 class="card-title mb-1" style="font-size:1.2rem; font-weight:600; color: <?= $sfondo_scuro ? '#fff' : '#333' ?>;">
                <?php echo $sito_tematico->post_title ?>
                <svg class="icon icon-white" style="width:20px; height:20px; margin-left:8px; position:absolute; top:12px; right:12px;">
                    <use href="#it-external-link"></use>
                </svg>
            </h3>
            <p class="card-text mb-0" style="font-size:0.9rem; color: <?= $sfondo_scuro ? '#ddd' : '#555' ?>;">
                <?php echo $st_descrizione; ?>
            </p>
        </div>

    </div>
</a>

<style>
/* Stile card coerente con Accesso Rapido */
.sito-tematico-card {
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background-color: #fafafa;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    position: relative;
    text-decoration: none;
}

.sito-tematico-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 10px rgba(0,0,0,0.2);
}

.sito-tematico-card .card-body {
    display: flex;
    align-items: center;
    padding: 12px;
}

.sito-tematico-card .avatar i,
.sito-tematico-card .avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.sito-tematico-card .card-title svg.icon-white {
    fill: #000;
    transition: transform 0.3s ease, fill 0.3s ease;
}

.sito-tematico-card:hover .card-title svg.icon-white {
    transform: scale(1.1);
    fill: #f0f0f0;
}

.sito-tematico-card .card-title {
    margin-right: 40px; /* spazio per icona */
}
</style>


