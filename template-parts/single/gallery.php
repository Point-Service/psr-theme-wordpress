<?php
global $gallery;
?>

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

