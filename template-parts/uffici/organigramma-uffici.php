<?php
global $uo_id, $with_border;

$ufficio = get_post($uo_id);

// DESCRIZIONE: uso excerpt nativo WordPress
$descrizione = get_the_excerpt($ufficio->ID);

// IMMAGINE: immagine in evidenza (featured image)
$img = get_the_post_thumbnail($ufficio->ID, 'medium');
?>

<?php if ($with_border) { ?>

<div class="card card-teaser border rounded shadow p-4 flex-nowrap">
    <div class="card-body pe-3">

        <!-- TITOLO -->
        <h4 class="u-main-black mb-1 title-small-semi-bold-medium">
            <a class="text-decoration-none" href="<?php echo get_permalink($ufficio->ID); ?>">
                <?php echo esc_html($ufficio->post_title); ?>
            </a>
        </h4>

        <!-- DESCRIZIONE -->
        <?php if (!empty($descrizione)) { ?>
        <div class="card-text">
            <p><?php echo esc_html($descrizione); ?></p>
        </div>
        <?php } ?>

    </div>

    <?php if ($img) { ?>
    <div class="avatar size-xl">
        <?php echo $img; ?>
    </div>
    <?php } ?>

</div>

<?php } else { ?>

<div class="card card-teaser card-teaser-info rounded shadow-sm p-3 flex-nowrap">
    <div class="card-body pe-3">

        <!-- TITOLO -->
        <p class="card-title text-paragraph-regular-medium-semi mb-2">
            <a class="text-decoration-none" href="<?php echo get_permalink($ufficio->ID); ?>">
                <?php echo esc_html($ufficio->post_title); ?>
            </a>
        </p>

        <!-- DESCRIZIONE -->
        <?php if (!empty($descrizione)) { ?>
        <div class="card-text">
            <p class="u-main-black">
                <?php echo esc_html($descrizione); ?>
            </p>
        </div>
        <?php } ?>

    </div>

    <?php if ($img) { ?>
    <div class="avatar size-xl">
        <?php echo $img; ?>
    </div>
    <?php } ?>

</div>

<?php }

$with_border = false;
?>
