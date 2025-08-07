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
/* Stile per la card */
.card-teaser {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Ombra leggera */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background-color: #fff; /* Sfondo bianco */
}

.card-teaser:hover {
    transform: translateY(-5px); /* Solleva leggermente la card al passaggio del mouse */
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15); /* Ombra più pronunciata al passaggio del mouse */
}

/* Titolo della card */
.card-title {
    font-size: 24px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Descrizione dell'argomento */
.card-text {
    font-size: 16px;
    color: #555;
    line-height: 1.6;
    margin-bottom: 20px;
}

/* Sito tematico */
.card-text p {
    font-size: 16px;
    font-weight: 600;
    color: #007bff;
    margin-bottom: 10px;
}

/* Link list wrapper */
.link-list-wrapper {
    margin-top: 30px;
}

.link-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.link-list .list-item {
    display: flex;
    align-items: center;
    font-size: 16px;
    color: #007bff;
    text-decoration: none;
    transition: color 0.3s ease, transform 0.3s ease;
    margin-bottom: 15px;
}

.link-list .list-item:hover {
    color: #0056b3;
    transform: translateX(5px); /* Sposta leggermente il link verso destra */
}

/* Icona per ogni link */
.list-item-title-icon-wrapper {
    display: flex;
    align-items: center;
}

.list-item-title-icon-wrapper svg {
    margin-left: 8px;
    width: 18px;
    height: 18px;
    fill: #007bff;
    transition: fill 0.3s ease;
}

/* Hover sull'icona */
.list-item:hover .list-item-title-icon-wrapper svg {
    fill: #0056b3;
}

/* Link "Esplora argomento" */
.read-more {
    display: block;
    padding-top: 15px;
    color: #007bff;
    font-size: 16px;
    font-weight: 600;
    text-decoration: none;
    transition: color 0.3s ease;
}

.read-more:hover {
    color: #0056b3;
}

.read-more svg {
    margin-left: 5px;
    width: 18px;
    height: 18px;
    fill: #007bff;
    transition: fill 0.3s ease;
}

/* Hover sul link "Esplora argomento" */
.read-more:hover svg {
    fill: #0056b3;
}

/* Padding e margini per la card */
.card-body {
    padding: 25px 20px;
}

/* Responsività per mobile */
@media (max-width: 768px) {
    .card-title {
        font-size: 20px;
    }

    .card-text {
        font-size: 14px;
    }

    .link-list .list-item {
        font-size: 14px;
    }

    .read-more {
        font-size: 14px;
    }
}

    
</style>
<?php
$sito_tematico_id = null;
