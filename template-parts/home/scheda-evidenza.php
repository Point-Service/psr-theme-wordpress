<?php
global $scheda, $count;

$post = get_post($scheda['scheda_'.$count.'_contenuto'][0]);
$img = dci_get_meta('immagine');
$descrizione_breve = dci_get_meta('descrizione_breve');
$icon = dci_get_post_type_icon_by_id($post->ID);
$page = get_page_by_path( dci_get_group($post->post_type) ); 
$argomenti = dci_get_meta("argomenti", '_dci_notizia_', $post->ID);

$arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", '_dci_notizia_', $post->ID);
$monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));

$page_macro_slug = dci_get_group($post->post_type);
$page_macro = get_page_by_path($page_macro_slug);
?>

<?php if ($img) { ?>
<div class="card card-teaser card-teaser-image card-flex no-after rounded shadow-sm border border-light mb-0">
    <div class="card-image-wrapper with-read-more">
        <div class="card-body p-3 u-grey-light">
            <div class="category-top">
            <!-- <svg class="icon">
                <use xlink:href="#<?php #echo $icon ?>"></use>
            </svg> -->
            <span class="category title-xsmall-semi-bold fw-semibold" ><?php echo $page->post_title ?></span>
            <?php if (is_array($arrdata) && count($arrdata)) { ?>
                  <span class="data fw-normal"><?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?></span>
             <?php } ?>
            </div>
            <p class="card-title text-paragraph-medium u-grey-light"><?php echo $post->post_title ?></p>
            <p class="text-paragraph-card u-grey-light m-0" style="margin-bottom: 40px!important;"><?php echo $descrizione_breve ?></p>    
            <hr align="left" size="1" width="200" color="red" noshade>
            <div class="card-body">Argomenti: <?php get_template_part("template-parts/common/badges-argomenti"); ?></div>    
            <hr align="left" size="1" width="200" color="red" noshade>
        </div>
        <div class="card-image card-image-rounded pb-5">            
            <?php dci_get_img($img); ?>
        </div>
<p></p>
     <a class="read-more ps-3"
        href="<?php echo get_permalink($post->ID); ?>"
        aria-label="Vai alla pagina <?php echo $post->post_title ?>" 
        title="Vai alla pagina <?php echo $post->post_title ?>">
            <span class="text">Vai alla pagina</span>
            <svg class="icon">
                <use xlink:href="#it-arrow-right"></use>
            </svg>
        </a>

    </div>
   
</div>
<?php } else { ?>
    <div class="card card-teaser no-after rounded shadow-sm mb-0 border border-light">
        <div class="card-body pb-5">
        <div class="category-top">
            <!-- <svg class="icon">
                <use xlink:href="#<?php #echo $icon ?>"></use>
            </svg> -->
            <span class="category title-xsmall-semi-bold fw-semibold"><?php echo $page->post_title ?></span>
            <?php if (is_array($arrdata) && count($arrdata)) { ?>
              <span class="data fw-normal"><?php echo $arrdata[0] . ' ' . $monthName . ' ' . $arrdata[2]; ?></span>
            <?php } ?>
        </div>
        <p class="card-title text-paragraph-medium u-grey-light">
            <?php echo $post->post_title ?>
        </p>
        <p class="text-paragraph-card u-grey-light m-0">
            <?php echo $descrizione_breve ?>
               <hr align="left" size="1" width="200" color="red" noshade>
              <div class="card-body">Argomenti: <?php get_template_part("template-parts/common/badges-argomenti"); ?></div>            
               <hr align="left" size="1" width="200" color="red" noshade>
<p></p>
            <a class="read-more" href="<?php echo get_permalink($post->ID); ?>" aria-label="Vai alla pagina <?php echo $post->post_title ?>" title="Vai alla pagina <?php echo $post->post_title ?>">
                <span class="text">Vai alla pagina</span>
                <svg class="icon ms-0">
                    <use xlink:href="#it-arrow-right">                
                    </use>
                </svg>
            </a>
            
        </div>
       
    </div>
<?php } ?>
