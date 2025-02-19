<?php
    global $title, $description, $with_shadow, $data_element;

    if ($title == 'notizia') $title = 'Notizie';
    if (!$title) $title = get_the_title();
    if (!$description) $description = dci_get_meta('descrizione','_dci_page_', $post->ID ?? null);
?>

<div class="container" id="main-container">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10">
            <?php get_template_part("template-parts/common/breadcrumb"); ?>
        </div>
    </div>
</div>


