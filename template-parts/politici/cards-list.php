<?php 
    global $posts;

        $description = dci_get_meta('descrizione_breve');

        $incarichi = dci_get_meta('incarichi');

        $incarico = $incarichi[0];

       

        $arrdata = explode( '-', dci_get_meta("data_inizio_incarico") );
        $tipo = get_the_terms($incarico, 'tipi_incarico')[0];

        $prefix = '_dci_incarico_';
        $nome_incarico = get_the_title($incarichi[0]);

        //var_dump($nome_incarico);

        $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
        $img = dci_get_meta('foto');
        if($tipo->name == "politico") {
        if ($img) {
?>
    <div class="col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
            <div class="row g-2 g-md-0 flex-md-column">
                <div class="col-4 order-2 order-md-1">
                <?php dci_get_img($img, 'rounded-top img-fluid img-responsive'); ?>
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
    <?php } else { ?>
    <div class="col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
                <div class="row g-2 g-md-0 flex-md-column">
                    <div class="col-12 order-1 order-md-2">
                        <div class="card-body card-img-none rounded-top">
                            <div class="category-top cmp-list-card-img__body">
                                <span class="category cmp-list-card-img__body-heading-title underline"><?php
                                echo isset($tipo->name) ? strtoupper($tipo->name) : 'POLITICO'; ?>
                                </span>
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
<?php } }?>
