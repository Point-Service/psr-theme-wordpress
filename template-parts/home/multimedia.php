<?php
global $medias;
$box_media = $medias;
?>
<div class="container py-5">
    <?php if (!empty($medias)) { ?>
        <h2 class="title-xxlarge mb-4">Multimedia</h2>
    <?php } ?>
    <div class="gallery">
        <?php foreach($medias as $box) { ?>
            <div class="video-container">
                <h4 class="blog-title-2">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512" width="20px" height="auto">
                        <path d="M0 128C0 92.7 28.7 64 64 64l256 0c35.3 0 64 28.7 64 64l0 256c0 35.3-28.7 64-64 64L64 448c-35.3 0-64-28.7-64-64L0 128zM559.1 99.8c10.4 5.6 16.9 16.4 16.9 28.2l0 256c0 11.8-6.5 22.6-16.9 28.2s-23 5-32.9-1.6l-96-64L416 337.1l0-17.1 0-128 0-17.1 14.2-9.5 96-64c9.8-6.5 22.4-7.2 32.9-1.6z" />
                    </svg>
                    <span class="darkblue"><?php echo $box['titolo_video'];?></span>
                </h4>
                <iframe title="YouTube video player" src="<?php echo $box['link_video'];?>" frameborder="0" allowfullscreen="allowfullscreen"></iframe>
            </div>
        <?php } ?>
    </div>
</div>
<style>
    .gallery {
        display: flex;
        flex-wrap: wrap;
        justify-content: center;
        gap: 20px;
        background-color: #e0e0e0;
        padding: 20px;
        border-radius: 10px;
    } 

    .video-container {
        flex-basis: calc(50% - 20px);
        background-color: #ffffff;
        padding: 10px;
        text-align: center;
        border-radius: 10px;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    }
    .video-container iframe {
        width: 100%;
        height: 520px;
        border-radius: 9px;
        border: none;
    }

    .blog-title-2 {
        margin-bottom: 12px;
        font-size: 17px;
        font-weight: bold;
        color: #333;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .blog-title-2 svg {
        margin-right: 10px;
    }

    .darkblue {
        color: #004085;
    }
</style>
