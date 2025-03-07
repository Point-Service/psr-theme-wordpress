<?php
global $post, $inline, $hide_arguments;
$argomenti = get_the_terms($post, 'argomenti');
$tipi_notizia= get_the_terms($post, 'tipi_notizia');
$categorie_servizio= get_the_terms($post, 'categorie_servizio');
$tipi_unita_organizzativa= get_the_terms($post, 'tipi_unita_organizzativa');


$post_url = get_permalink();
$tipi_luogo = get_the_terms($post->ID,'tipi_luogo');
$tipi_incarico = get_the_terms($post->ID,'tipi_incarico');
$tipo_evento = get_the_terms($post->ID,'tipi_evento');


echo $tipi_incarico;

if ($hide_arguments) $argomenti = array();
?>

<div class="dropdown <?php echo $inline ? 'd-inline' : '' ?>">
    <button
        class="btn btn-dropdown dropdown-toggle text-decoration-underline d-inline-flex align-items-center fs-0"
        type="button"
        id="shareActions"
        data-bs-toggle="dropdown"
        aria-haspopup="true"
        aria-expanded="false"
        aria-label="condividi sui social"
    >
        <svg class="icon" aria-hidden="true">
            <use xlink:href="#it-share"></use>
        </svg>
        <small>Condividi</small>
    </button>
    <div class="dropdown-menu shadow-lg" aria-labelledby="shareActions">
        <div class="link-list-wrapper">
            <ul class="link-list" role="menu">
                <li role="none">
                <a class="list-item" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $post_url; ?>" target="_blank" role="menuitem">
                <svg class="icon" aria-hidden="true">
                    <use
                        xlink:href="#it-facebook"
                    ></use>
                    </svg>
                    <span>Facebook</span></a
                >
                </li>
                <li role="none">
                <a class="list-item" href="https://twitter.com/intent/tweet?text=<?php echo $post_url; ?>" target="_blank" role="menuitem">
                <svg class="icon" aria-hidden="true">
                    <use
                        xlink:href="#it-twitter"
                    ></use>
                    </svg>
                    <span>Twitter</span></a
                >
                </li>
                <li role="none">
                <a class="list-item" href="https://www.linkedin.com/shareArticle?url=<?php echo $post_url; ?>" target="_blank" role="menuitem">
                <svg class="icon" aria-hidden="true">
                    <use
                        xlink:href="#it-linkedin"
                    ></use>
                    </svg>
                    <span>Linkedin</span></a
                >
                </li>
                <li role="none">
                <a class="list-item" href="https://api.whatsapp.com/send?text=<?php echo $post_url; ?>" target="_blank" role="menuitem">
                <svg class="icon" aria-hidden="true">
                    <use
                        xlink:href="#it-whatsapp"
                    ></use>
                    </svg>
                    <span>Whatsapp</span></a
                >
                </li>
            </ul>
        </div>
    </div>
</div>
<div class="dropdown <?php echo $inline ? 'd-inline' : '' ?>">
    <button
        class="btn btn-dropdown dropdown-toggle text-decoration-underline d-inline-flex align-items-center fs-0"
        type="button"
        id="viewActions"
        data-bs-toggle="dropdown"
        aria-haspopup="true"
        aria-expanded="false"
    >
        <svg class="icon" aria-hidden="true">
        <use
            xlink:href="#it-more-items"
        ></use>
        </svg>
        <small>Vedi azioni</small>
    </button>
    <div class="dropdown-menu shadow-lg" aria-labelledby="viewActions">
        <div class="link-list-wrapper">
            <ul class="link-list" role="menu">
                <li role="none">
                <a class="list-item" href="#" onclick="window.print()" role="menuitem">
                    <svg class="icon" aria-hidden="true">
                    <use
                        xlink:href="#it-print"
                    ></use>
                    </svg>
                    <span>Stampa</span></a
                >
                </li>
                <li role="none">
                <a class="list-item" href="#" role="menuitem" onclick="window.listenElements(this, '[data-audio]')"><svg class="icon" aria-hidden="true">
                    <use
                        xlink:href="#it-hearing"
                    ></use>
                    </svg>
                    <span>Ascolta</span></a
                >
                </li>
                <li role="none">
                <a class="list-item" href="mailto:?subject=<?php echo the_title(); ?>&body=<?php echo get_permalink(); ?>" role="menuitem"
                    ><svg class="icon" aria-hidden="true">
                    <use
                        xlink:href="#it-mail"
                    ></use>
                    </svg>
                    <span>Invia</span></a
                >
                </li>
            </ul>
        </div>
    </div>
</div>

<?php if ($tipi_notizia && is_array($tipi_notizia) && count($tipi_notizia) ) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Tipo Notizia</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($tipi_notizia as $tip_not) { ?>
        <li>
            <a class="chip chip-simple" href="<?php echo get_term_link($tip_not->term_id); ?>">
                <span class="chip-label"><?php echo $tip_not->name; ?></span>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>

<?php if (is_array($argomenti) && count($argomenti) ) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Argomenti</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($argomenti as $argomento) { ?>
        <li>
            <a class="chip chip-simple" href="<?php echo get_term_link($argomento->term_id); ?>">
                <span class="chip-label"><?php echo $argomento->name; ?></span>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>
    
<?php if ($categorie_servizio && is_array($categorie_servizio) && count($categorie_servizio) ) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Categorie Servizio</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($categorie_servizio as $cat_serv) { ?>
        <li>
            <a class="chip chip-simple" href="<?php echo get_term_link($cat_serv->term_id); ?>">
                <span class="chip-label"><?php echo $cat_serv->name; ?></span>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>


<?php 
if (get_post_type() == 'persona_pubblica') {
echo get_post_type();

                   // Recupera gli incarichi, se esistono
					$incarichi = dci_get_meta("incarichi") ?? []; // Recupera tutti gli incarichi associati al post				
					
				        // Prende il primo incarico (se esiste) per mostrare il titolo e il tipo di incarico
				        $incarico = get_the_title($incarichi[0]);
				
				        // Recupero dei termini di tipo incarico
				        $tipo_incarico_terms = get_the_terms(get_post($incarichi[0]), 'tipi_incarico');
				
				        // Controllo se i termini di tipo incarico esistono e non ci sono errori
				        if (!empty($tipo_incarico_terms) && !is_wp_error($tipo_incarico_terms) && isset($tipo_incarico_terms[0])) {
				            $tipo_incarico = $tipo_incarico_terms[0]->name;
				        } else {
				            $tipo_incarico = ''; // Valore di fallback se non ci sono termini
				        }
				
echo $tipo_incarico;
echo $incarico;
echo $tipo_incarico_terms;
  
}
?>

<?php if ($tipi_unita_organizzativa && is_array($tipi_unita_organizzativa) && count($tipi_unita_organizzativa) ) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Tipo</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($tipi_unita_organizzativa as $tipo) { ?>
        <li>
            <div class="chip chip-simple">
                <span class="chip-label"><?php echo $tipo->name; ?></span>
            </div>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>

    
<?php if ($tipo_evento && is_array($tipo_evento) && count($tipo_evento) ) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Tipi evento</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($tipo_evento as $evento) { ?>
        <li>
            <a class="chip chip-simple" href="<?php echo get_term_link($evento->term_id); ?>">
                <span class="chip-label"><?php echo $evento->name; ?></span>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>


<?php if ($tipi_incarico && is_array($tipi_incarico) && count($tipi_incarico) ) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Tipi incarico</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($tipi_luogo as $tipo_luogo) { ?>
        <li>
            <a class="chip chip-simple" href="<?php echo get_term_link($tipi_incarico->term_id); ?>">
                <span class="chip-label"><?php echo $tipi_incarico->name; ?></span>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>


<?php if ($tipi_luogo && is_array($tipi_luogo) && count($tipi_luogo) ) { ?>
<div class="mt-4 mb-4">
    <span class="subtitle-small">Tipi luogo</span>
    <ul class="d-flex flex-wrap gap-1">
        <?php foreach ($tipi_luogo as $tipo_luogo) { ?>
        <li>
            <a class="chip chip-simple" href="<?php echo get_term_link($tipo_luogo->term_id); ?>">
                <span class="chip-label"><?php echo $tipo_luogo->name; ?></span>
            </a>
        </li>
        <?php } ?>
    </ul>
</div>
<?php } ?>
