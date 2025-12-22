<?php
/**
 * Template single – Galleria moderna con loader
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
            <p><?= esc_html($descrizione); ?></p>
        </div>
        <div class="col-lg-3 offset-lg-1">
            <?php get_template_part('template-parts/single/actions'); ?>
        </div>
    </div>
</div>

<main class="gallery-page">
    <div class="container mb-5">
        <div class="gallery-grid">
            <?php foreach ( $all_items as $item ): ?>
                <?php if ( $item['type'] === 'foto' ):
                    $foto = $item['data'];
                    $attachment_id = attachment_url_to_postid($foto);
                    $image_title   = $attachment_id ? get_the_title($attachment_id) : "Immagine della galleria";
                    $image_alt     = $attachment_id ? get_post_meta($attachment_id, '_wp_attachment_image_alt', true) : "Immagine della galleria";
                ?>
                    <div class="gallery-item image loading">
                        <div class="skeleton"></div>
                        <a href="<?= esc_url($foto); ?>" class="glightbox" data-gallery="galleria" data-title="<?= esc_attr($image_title); ?>">
                            <div class="gallery-info">
                                <i class="fas fa-search-plus"></i>
                                <p class="gallery-title"><?= esc_html($image_title); ?></p>
                            </div>
                            <img src="<?= esc_url($foto); ?>" alt="<?= esc_attr($image_alt); ?>" loading="lazy">
                            <span class="badge">Foto</span>
                        </a>
                    </div>
                <?php elseif ( $item['type'] === 'video_embed' ):
                    $video = $item['data']; ?>
                    <div class="gallery-item video">
                        <div class="video-container">
                            <iframe src="<?= esc_url($video['url_video']); ?>" title="<?= esc_attr($video['titolo']); ?>" frameborder="0" allowfullscreen></iframe>
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
    GLightbox({ selector: '.glightbox', loop: true });

    document.querySelectorAll('.gallery-item.image img').forEach(img => {
        if (img.complete) {
            img.closest('.gallery-item').classList.add('loaded');
        } else {
            img.addEventListener('load', () => {
                img.closest('.gallery-item').classList.add('loaded');
            });
        }
    });
});
</script>

<?php endwhile; get_footer(); ?>



<style>
.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
}

.gallery-item {
    position: relative;
    overflow: hidden;
    border-radius: 15px;
    cursor: pointer;
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}

.gallery-item.image {
    aspect-ratio: 16 / 9;
    background: #ddd;
}

.gallery-item.image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    opacity: 0;
    transition: opacity 0.5s ease;
    position: relative;
    z-index: 2;
}

.gallery-item .skeleton {
    position: absolute;
    inset: 0;
    background: linear-gradient(90deg, #e0e0e0 25%, #f5f5f5 37%, #e0e0e0 63%);
    background-size: 400% 100%;
    animation: shimmer 1.4s infinite;
    z-index: 1;
}

.gallery-item.loaded img { opacity: 1; }
.gallery-item.loaded .skeleton { display: none; }

@keyframes shimmer {
    0% { background-position: 100% 0; }
    100% { background-position: -100% 0; }
}

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
    top: 0; left: 0;
    width: 100%; height: 100%;
    object-fit: cover;
    border: none;
}

.badge {
    position: absolute;
    top: 12px;
    left: 12px;
    background: #007bff;
    color: #fff;
    padding: 5px 10px;
    border-radius: 10px;
    font-size: 0.8rem;
    z-index: 3;
}

.badge-video { background: #e63946; }

.gallery-info {
    position: absolute;
    top: 0; left: 0;
    width: 100%; height: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    flex-direction: column;
    background-color: rgba(0,0,0,0.6);
    opacity: 0;
    transition: opacity 0.4s ease;
    pointer-events: none;
}

.gallery-item:hover .gallery-info { opacity: 1; }

.gallery-info .fas {
    color: #fff;
    font-size: 2.5rem;
    margin-bottom: 10px;
}

.gallery-info .gallery-title {
    color: #fff;
    font-size: 1.2rem;
    font-weight: bold;
    text-align: center;
    padding: 0 20px;
}

</style>



