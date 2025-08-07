<?php
global $argomento_full, $count, $sito_tematico_id;

$argomento = get_term_by('slug', $argomento_full['argomento_'.$count.'_argomento'], 'argomenti');

$icon = dci_get_term_meta('icona', "dci_term_", $argomento->term_id);

if (isset($argomento_full['argomento_'.$count.'_siti_tematici']))
$sito_tematico_id = $argomento_full['argomento_'.$count.'_siti_tematici'];
if (isset($argomento_full['argomento_'.$count.'_contenuti']))
$links = $argomento_full['argomento_'.$count.'_contenuti'];
?>

<div class="card card-teaser no-after rounded shadow-sm border border-light">
    <div class="card-body pb-5">
        <!-- card head -->
        <!-- <div class="category-top">
            <svg class="icon">
            <use
                xlink:href="#<?php #echo $icon ? $icon : 'it-info-circle'; ?>"
            ></use>
            </svg>
        </div> -->
        <h3 class="card-title title-xlarge-card"><?php echo $argomento->name?></h3>
        <p class="card-text">
            <?php echo $argomento->description?>
            <!-- Aggiungi l'icona SVG in alto a destra -->
            <svg class="icon icon-white svg-top-right" style="width: 20px; height: 20px;">
                <use href="#it-external-link"></use>
            </svg>
        </p>

        <!-- sito tematico -->
        <?php if($sito_tematico_id) { ?>
        <p class="card-text pb-3">Visita il sito:</p>
        <?php 
            $custom_class = "no-after mt-0";
            get_template_part("template-parts/sito-tematico/card_argomento");
        ?>
        <?php } ?>

        <!-- links -->
        <?php if(isset($links) && is_array($links) && count($links)) { ?>
        <div class="link-list-wrapper mt-4">
            <ul class="link-list">
                <?php foreach ($links as $link_id) { 
                    $link_obj = get_post($link_id);
                ?>
                <li>
                    <a class="list-item active icon-left mb-2" href="<?php echo get_permalink(intval($link_id)); ?>">
                    <span class="list-item-title-icon-wrapper">
                        <span><?php echo $link_obj->post_title; ?></span>
                    </span>
                    </a>
                </li>
                <?php } ?>
            </ul>
        </div>
        <?php } ?>
    </div>
    <a class="read-more pt-0" href="<?php echo get_term_link(intval($argomento->term_id),'argomenti'); ?>">
        <span class="list-item-title-icon-wrapper">
            <span class="text">Esplora argomento</span>
            <svg class="icon">
                <use xlink:href="#it-arrow-right"></use>
            </svg>
        </span>
    </a>
</div>
<style>

   /* Posiziona l'icona in alto a destra */
.svg-top-right {
    position: absolute;
    top: 10px; /* Distanza dal top */
    right: 10px; /* Distanza dal lato destro */
    z-index: 10; /* Assicurati che l'icona sia sopra gli altri contenuti */
}

/* Aggiungi un po' di padding alla card per far spazio all'icona */
.card-body {
    position: relative; /* Importante per il posizionamento assoluto */
    padding-right: 40px; /* Spazio per evitare che l'icona si sovrapponga al testo */
}
 
</style>
<?php
$sito_tematico_id = null;
