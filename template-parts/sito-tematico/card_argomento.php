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

<div class="sito-tematico-card-wrapper">
  <a href="<?php echo $st_link ?>" 
     style="<?= ($colore_sfondo) ? 'background-color:'.$colore_sfondo : '' ?>" 
     class="card card-teaser sito-tematico-card shadow-sm" 
     target="_blank">

    <!-- Icona in alto a destra della card -->
    <svg class="icon icon-dark external-icon">
      <use href="#it-external-link"></use>
    </svg>

    <div class="card-body d-flex align-items-center" style="padding:10px; position: relative;">

      <!-- Icona avatar -->
      <?php if($st_img) { ?>
        <div class="avatar size-lg me-3" style="flex-shrink: 0; width:36px; height:36px; border-radius:50%; background-color:#f0f0f0; display:flex; align-items:center; justify-content:center;">
          <?php dci_get_img($st_img); ?>
        </div>
      <?php } ?>

      <!-- Titolo e descrizione -->
      <div class="flex-grow-1 position-relative">
        <h3 class="card-title mb-1" style="font-size:1rem; font-weight:600; color: <?= $sfondo_scuro ? '#fff' : '#333' ?>;">
          <?php echo $sito_tematico->post_title ?>
        </h3>
        <p class="card-text mb-0" style="font-size:0.8rem; color: <?= $sfondo_scuro ? '#ddd' : '#555' ?>;">
          <?php echo $st_descrizione; ?>
        </p>
      </div>
    </div>
  </a>
</div>

<style>
.sito-tematico-card-wrapper {
  margin: 6px 8px; /* Spazio esterno contenuto */
}

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
  min-height: 65px;
  padding: 4px 6px;
}

.sito-tematico-card-wrapper .sito-tematico-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 3px 8px rgba(0,0,0,0.15);
}

.sito-tematico-card-wrapper .sito-tematico-card .card-body {
  display: flex;
  align-items: center;
  padding: 6px 8px;
}

.sito-tematico-card-wrapper .sito-tematico-card .avatar {
  width: 32px;
  height: 32px;
  margin-right: 6px;
  flex-shrink: 0;
  border-radius: 50%;
  background-color: #f0f0f0;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* TITOLO */
.sito-tematico-card-wrapper .sito-tematico-card .card-title {
  font-size: 0.95rem;
  font-weight: 600;
  line-height: 1.2;        /* Più compatto */
  margin: 0;               /* Nessun margine extra */
  padding: 0;
  color: <?= $sfondo_scuro ? '#fff' : '#333' ?>;
}

/* DESCRIZIONE */
.sito-tematico-card-wrapper .sito-tematico-card .card-text {
  font-size: 0.78rem;
  line-height: 1.2;        /* Più compatto */
  margin: 2px 0 0 0;       /* Ridotto margine sopra */
  padding: 0;
  color: <?= $sfondo_scuro ? '#ddd' : '#555' ?>;
}

/* ICONA */
.sito-tematico-card-wrapper .sito-tematico-card .external-icon {
  position: absolute;
  top: 6px;
  right: 6px;
  width: 18px;  /* Icona torna alla dimensione originale */
  height: 18px;
  fill: #555;
  transition: transform 0.3s ease, fill 0.3s ease;
}

.sito-tematico-card-wrapper .sito-tematico-card:hover .external-icon {
  transform: scale(1.1);
  fill: #777;
}


</style>
