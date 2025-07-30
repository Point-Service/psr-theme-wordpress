<?php global $gallery; ?>
<?php if (!empty($gallery)) : ?>
    <div class="it-carousel-wrapper it-full-carousel it-standard-image splide" id="gallery-carousel">
        <div class="splide__track">
            <ul class="splide__list">
                <?php foreach ($gallery as $photo) : ?>
                    <li class="splide__slide">
                        <div class="it-single-slide-wrapper">
                            <div class="it-grid-item-wrapper">
                                <a href="<?= esc_url(wp_get_attachment_image_src(attachment_url_to_postid($photo), 'full')[0]); ?>" class="lightbox">
                                    <div class="bg-image">
                                        <?php dci_get_img($photo, 'img-gallery-custom'); ?>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <div class="splide__pagination"></div>
    </div>
<?php else : ?>
    <p>Nessuna immagine disponibile nella galleria.</p>
<?php endif; ?>

<style>
.bg-image {
    position: relative;
    width: 100%;
    height: auto;
    max-height: 450px;
    overflow: hidden;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #f0f0f0; /* fallback */
}

.bg-image img.img-gallery-custom {
    max-height: 450px;
    width: auto;
    height: auto;
    object-fit: contain;
    object-position: center;
    display: block;
}

    /* Rimuovi margini e padding dalle slide */
    .splide__slide {
        margin: 0; /* Rimuovi margini tra le slide */
        padding: 0; /* Rimuovi padding se presente */
    }
</style>

<!-- Splide CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/css/splide.min.css" />

<!-- Splide JS -->
<script src="https://cdn.jsdelivr.net/npm/@splidejs/splide@4.1.4/dist/js/splide.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        new Splide('#gallery-carousel', {
            type: 'loop',
            autoplay: true,
            interval: 2000, // 2 secondi
            pauseOnHover: false,
            pauseOnFocus: false,
        }).mount();
    });
</script>
