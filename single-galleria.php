<?php
/**
 * Template single – Galleria moderna con skeleton loader
 *
 * @package Design_Comuni_Italia
 */

get_header();

while ( have_posts() ) :
    the_post();

    $prefix        = '_dci_galleria_';
    $id            = get_the_ID();
    $descrizione   = get_post_meta( $id, $prefix . 'descrizione_breve', true );
    $foto_array    = get_post_meta( $id, $prefix . 'foto_gallery', true );
    $url_video_group = get_post_meta( $id, $prefix . 'url_video_group', true );
    $video_array   = get_post_meta( $id, $prefix . 'video', true );

    // --- PAGINAZIONE ---
    $paged     = ( get_query_var('page') ) ? get_query_var('page') : 1;
    $per_page  = -1;

    // Unisco foto e video in un unico array
    $all_items = [];
    if ( !empty($foto_array) ) {
        foreach ( $foto_array as $foto ) {
            $all_items[] = ['type' => 'foto', 'data' => $foto];
        }
    }
    if ( !empty($url_video_group) ) {
        foreach ( $url_video_group as $video ) {
            $all_items[] = ['type' => 'video_embed', 'data' => $video];
        }
    }
    if ( !empty($video_array) ) {
        foreach ( $video_array as $video ) {
            $all_items[] = ['type' => 'video_file', 'data' => $video];
        }
    }

    $total_items = count($all_items);
    $paged_items = $all_items;
?>
<div class="container" id="main-container">
    <div class="row">
        <div class="col px-lg-4">
            <?php get_template_part("template-parts/common/breadcrumb"); ?>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 px-lg-4 py-lg-2">
            <h1><?php the_title(); ?></h1>
            <h3 class="visually-hidden">Dettagli galleria</h3>
            <p><?= esc_html($descrizione); ?></p>
        </div>
        <div class="col-lg-3 offset-lg-1">
            <?php
            $inline = true;
            get_template_part('template-parts/single/actions');
            ?>
        </div>
    </div>
</div>

<main class="gallery-page">
    <br>
    <div class="container mb-5">
        <div class="gallery-grid">
            <?php foreach ( $paged_items as $item ): ?>
                <?php if ( $item['type'] === 'foto' ):
                    $foto = $item['data'];
                    $attachment_id = attachment_url_to_postid($foto);
                    $image_title   = $attachment_id ? get_the_title($attachment_id) : "Immagine della galleria";
                    $image_alt     = $attachment_id ? get_post_meta($attachment_id, '_wp_attachment_image_alt', true) : "Immagine della galleria";
                ?>
                    <div class="gallery-item image">
                        <div class="skeleton"></div>
                        <a href="<?= esc_url($foto); ?>" class="glightbox" data-gallery="galleria" data-title="<?= esc_attr($image_title); ?>">
                            <img src="<?= esc_url($foto); ?>" alt="<?= esc_attr($image_alt); ?>" loading="lazy">
                            <div class="gallery-info">
                                <i class="fas fa-search-plus"></i>
                                <p class="gallery-title"><?= esc_html($image_title); ?></p>
                            </div>
                            <span class="badge">Foto</span>
                        </a>
                    </div>
                <?php elseif ( $item['type'] === 'video_embed' ):
                    $video = $item['data']; ?>
                    <div class="gallery-item video">
                        <div class="video-container">
                            <iframe src="<?= esc_url($video['url_video']); ?>" title="<?= esc_attr($video['titolo']); ?>" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                        </div>
                        <span class="badge badge-video">Video</span>
                    </div>
                <?php elseif ( $item['type'] === 'video_file' ):
                    $video = $item['data']; ?>
                    <div class="gallery-item video">
                        <div class="video-container">
                            <video controls>
                                <source src="<?= esc_url($video); ?>" type="video/mp4">
                            </video>
                        </div>
                        <span class="badge badge-video">Video</span>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const lightbox = GLightbox({
        selector: '.glightbox',
        touchNavigation: true,
        zoomable: true,
        draggable: true,
        openEffect: 'zoom',
        closeEffect: 'fade',
        slideEffect: 'slide',
        loop: true,
        height: '100vh',
        showTitle: true
    });

    // Fade-in immagini al caricamento
    const images = document.querySelectorAll('.gallery-item img');
    images.forEach(img => {
        img.onload = () => img.classList.add('loaded');
    });
});
</script>

<?php endwhile; get_footer(); ?>

<style>
/* Skeleton loader */
.skeleton {
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
    position: absolute;
    top: 0;
    left: 0;
    border-radius: 15px;
    z-index: 2;
}
@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}

/* Immagine nascosta fino al caricamento */
.gallery-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    position: absolute;
    top: 0;
    left: 0;
    opacity: 0;
    transition: opacity 0.5s ease-in;
    border-radius: 15px;
}
.gallery-item img.loaded {
    opacity: 1;
}

/* Layout griglia */
.gallery-page {
    background: #f8f9fa;
    padding: 40px 20px;
}
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}
.gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 15px;
    background: #000;
    aspect-ratio: 16 / 9;
}

/* Video container */
.video-container {
    position: relative;
    width: 100%;
    padding-top: 56.25%;
    border-radius: 15px;
    overflow: hidden;
}
.gallery-item iframe,
.gallery-item video {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    object-fit: cover;
    border: none;
}

/* Hover info per immagini */
.gallery-info {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.6);
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    opacity: 0;
    transition: opacity 0.4s ease;
    z-index: 5;
    pointer-events: none;
}
.gallery-item:hover .gallery-info {
    opacity: 1;
}
.gallery-info .fas {
    color: #fff;
    font-size: 2.5rem;
    margin-bottom: 10px;
    transform: translateY(20px);
    transition: transform 0.4s ease;
}
.gallery-info .gallery-title {
    color: #fff;
    font-size: 1.2rem;
    font-weight: bold;
    text-align: center;
    padding: 0 20px;
    transform: translateY(20px);
    transition: transform 0.4s ease;
}
.gallery-item:hover .gallery-info .fas,
.gallery-item:hover .gallery-info .gallery-title {
    transform: translateY(0);
}

/* Badge */
.badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #007bff;
    color: #fff;
    font-size: 0.8rem;
    padding: 5px 10px;
    border-radius: 10px;
    font-weight: 600;
    z-index: 10;
}
.badge-video {
    background: #e63946;
}
</style>

