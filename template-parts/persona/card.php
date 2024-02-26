<?php

global $img, $tipo, $periodo, $description;

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
                            <span class="category cmp-list-card-img__body-heading-title underline"><?php echo $tipo->name ? $tipo->name : 'AMMINISTRATIVO'; ?></span>
                        <?php } ?>                    
                    <span class="data"><?php echo $periodo[0].' '.$monthName.' '.$periodo[2] ?></span>
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