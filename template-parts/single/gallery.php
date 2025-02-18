<?php
global $gallery;
?>
 <head> 
  <!-- Include il CSS di Tobii -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@midzer/tobii/dist/tobii.min.css">
  
  <style>
    /* Stile per la griglia della gallery */
    .gallery {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 20px;
      justify-content: center;
      background-color: #f7f7f7;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .image-container {
      position: relative;
      padding-bottom: 100%;
      border: 1px solid #ccc;
      overflow: hidden;
      border-radius: 10px;
      box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
    }

    .image-container img {
      position: absolute;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease-in-out;
    }

    .image-container img:hover {
      transform: scale(1.05);
    }
  </style>
</head>


<div class="gallery">
  <?php foreach ($gallery as $photo) { ?>
    <div class="image-container">
      <?php 
        // Recupera l'ID dell'allegato dalla URL dell'immagine
        $attachment_id = attachment_url_to_postid($photo);
        $full_image_url = wp_get_attachment_image_src($attachment_id, 'full')[0];
        $image_title = get_the_title(get_post($attachment_id)->ID);
      ?>
      <!-- Aggiungi il data-tobii per il lightbox -->
      <a href="<?= esc_url($full_image_url) ?>" data-caption="<?= esc_attr($image_title) ?>" data-tobii>
        <img title="<?= esc_attr($image_title) ?>" src="<?= esc_url($full_image_url) ?>" alt="<?= esc_attr($image_title) ?>" width="100%" />
      </a>
    </div>
  <?php } ?>
</div>

<!-- Include il JS di Tobii -->
<script src="https://cdn.jsdelivr.net/npm/@midzer/tobii/dist/tobii.min.js"></script>
<script>
  // Inizializza Tobii
  document.addEventListener("DOMContentLoaded", () => {
    const tobii = new Tobii({
      captions: true, // Mostra le didascalie
      captionAttribute: 'data-caption', // Usa il valore dell'attributo data-caption
      zoom: true, // Abilita l'effetto zoom
    });
  });
</script>

