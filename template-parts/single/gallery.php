<?php
global $gallery;
?>
<div class="gallery">
  <?php foreach ($gallery as $photo) { ?>
    <div class="image-container">
      <a href="<?= wp_get_attachment_image_src(attachment_url_to_postid($photo), 'full')[0] ?>" target="_blank" rel="noopener">
        <img title="<?= get_the_title(get_post(attachment_url_to_postid($photo))->ID) ?>" src="<?= wp_get_attachment_image_src(attachment_url_to_postid($photo), 'full')[0] ?>" alt="<?= get_the_title(get_post(attachment_url_to_postid($photo))->ID) ?>" width="100%" />
      </a>
    </div>
  <?php } ?>
</div>

<script>
  const tobii = new Tobii({
    captionAttribute: 'title'
  })
</script>

<style>
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
