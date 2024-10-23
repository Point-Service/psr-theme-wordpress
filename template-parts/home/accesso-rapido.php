<?php
global $boxes;
$box_accesso_rapido = $boxes;
?>
<head><link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet"></head>

<div class="container py-5">
    <?php if (!empty($boxes)) { ?>
        <h2 class="title-xxlarge mb-4">Accesso rapido</h2>
    <?php } ?>
    <div class="row g-4">
        <?php foreach($boxes as $box) { ?>
        <div class="col-md-6 col-xl-4">
            <div class="cmp-card-simple card-wrapper pb-0 rounded border-none">
                <div class="card shadow-sm rounded">
                    <div class="card-body card-bg-blue d-flex align-items-center">
                    <?php 
                        if(array_key_exists('icona_message', $box)){
                            if(array_key_exists('icon', $box)) { ?>
                                <div class="avatar size-lg me-3">
                                    <i class="fas fa-<?php echo htmlspecialchars($box['icon']); ?>"></i>
                                </div>
                            <?php } ?>
                        <?php }?>
                        <div class="content">
                        <a class="text-decoration-none card-bg-blue" href="<?php echo $box['link_message']; ?>" data-element="topic-element" target="_blank">
                            <h3 class="card-title t-primary title-xlarge text-white"><?php echo $box['titolo_message']; ?></h3>
                        </a>
                        <?php if (isset($box['desc_message']) && $box['desc_message']) { ?>
                            <p class="titillium text-paragraph mb-0 description text-white">
                                <?php echo $box['desc_message']; ?>            
                            </p>
                        <?php } ?>
                        </div> 
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<style>
    .custom-styles .row {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
    }

    .custom-styles .card-wrapper {
        width: 100%;
    }

    .custom-styles .card {
        display: flex;
        flex-direction: column;
        height: 100%;
    }

    .custom-styles .card-body {
        flex: 1;
    }

    .custom-styles .card-title {
        margin-bottom: auto;
    }

    .custom-styles .btn {
        width: max-content;
    }
</style>
