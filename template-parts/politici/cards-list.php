<?php 
global $posts;

$description = dci_get_meta('descrizione_breve');
$incarichi = dci_get_meta('incarichi');
$arrdata = explode( '-', dci_get_meta("data_inizio_incarico") );
$tipo = get_the_terms($incarichi[0], 'tipi_incarico')[0];

$prefix = '_dci_incarico_';
$nome_incarico = get_the_title($incarichi[0]);

$monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
$img = dci_get_meta('foto');

// Aggiungiamo la classe personalizzata per ingrandire l'immagine
if ($tipo->name == "politico") {
    if ($img) {
?>
    <div class="col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
                <div class="row g-2 g-md-0 flex-md-column">
                    <!-- Foto ingrandita -->
                    <div class="col-4 order-2 order-md-1">
                        <?php dci_get_img($img, 'rounded-top img-fluid img-responsive img-custom'); ?>
                    </div>
                    <div class="col-8 order-1 order-md-2">
                        <div class="card-body">
                            <div class="category-top cmp-list-card-img__body">
                                <?php if ($tipo) { ?> 
                                    <span class="category cmp-list-card-img__body-heading-title underline"><?php echo $nome_incarico ? $nome_incarico : 'POLITICO'; ?></span>
                                <?php } ?>                    
                                <span class="data"><?php echo $arrdata[0].' '.$monthName.' '.$arrdata[2] ?></span>
                            </div>
                            <a class="text-decoration-none" href="<?php echo get_permalink(); ?>" data-element="administration-element">
                                <h3 class="h5 card-title"><?php echo the_title(); ?></h3>
                            </a>
                            <p class="card-text d-none d-md-block">
                                <?php echo $description; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php 
    } else {
?>
    <div class="col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
                <div class="row g-2 g-md-0 flex-md-column">
                    <div class="col-12 order-1 order-md-2">
                        <div class="card-body card-img-none rounded-top">
                            <div class="category-top cmp-list-card-img__body">
                                <span class="category cmp-list-card-img__body-heading-title underline"><?php echo isset($tipo->name) ? strtoupper($tipo->name) : 'POLITICO'; ?></span>
                                <span class="data"><?php echo $arrdata[0].' '.strtoupper($monthName).' '.$arrdata[2] ?></span>
                            </div>
                            <a class="text-decoration-none" href="<?php echo get_permalink(); ?>" data-element="administration-element">
                                <h3 class="h5 card-title"><?php echo the_title(); ?></h3>
                            </a>
                            <p class="card-text d-none d-md-block">
                                <?php echo $description; ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php 
    }
} 
?>

<!-- Codice per visualizzare tutti gli incarichi -->
<div class="row">
    <?php 
        // Eseguiamo un ciclo per visualizzare tutti gli incarichi
        if (!empty($incarichi)) {
            foreach ($incarichi as $incarico_id) {
                $incarico_title = get_the_title($incarico_id);
                $arrdata_incarico = explode('-', dci_get_meta('data_inizio_incarico', $incarico_id));
                $monthName_incarico = date_i18n('M', mktime(0, 0, 0, $arrdata_incarico[1], 10));
                $tipo_incarico = get_the_terms($incarico_id, 'tipi_incarico')[0];

                // Visualizziamo ogni incarico
    ?>
        <div class="col-md-6 col-xl-4">
            <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
                <div class="card no-after rounded">
                    <div class="row g-2 g-md-0 flex-md-column">
                        <div class="col-12 order-1 order-md-2">
                            <div class="card-body card-img-none rounded-top">
                                <div class="category-top cmp-list-card-img__body">
                                    <span class="category cmp-list-card-img__body-heading-title underline"><?php echo isset($tipo_incarico->name) ? strtoupper($tipo_incarico->name) : 'INCARICO'; ?></span>
                                    <span class="data"><?php echo $arrdata_incarico[0].' '.strtoupper($monthName_incarico).' '.$arrdata_incarico[2] ?></span>
                                </div>
                                <a class="text-decoration-none" href="<?php echo get_permalink($incarico_id); ?>" data-element="administration-element">
                                    <h3 class="h5 card-title"><?php echo $incarico_title; ?></h3>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php 
            }
        }
    ?>
</div>




