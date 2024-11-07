<?php 
global $posts;

$description = dci_get_meta('descrizione_breve');
$incarichi = dci_get_meta('incarichi'); // Assicurati che questo contenga tutti gli incarichi della persona

// Itera sugli incarichi e visualizzali
foreach ($incarichi as $incarico) {
    $arrdata = explode( '-', dci_get_meta("data_inizio_incarico") );
    $tipo = get_the_terms($incarico, 'tipi_incarico')[0];
    $prefix = '_dci_incarico_';
    $nome_incarico = get_the_title($incarico);

    $monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
    $img = dci_get_meta('foto'); // Foto associata all'incarico
    if($tipo->name == "politico") {
        ?>
        <div class="col-md-6 col-lg-4 col-xl-3"> <!-- Modifica per riquadri più piccoli -->
            <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
                <div class="card no-after rounded">
                    <div class="row g-2 g-md-0">
                        <!-- Foto a sinistra -->
                        <div class="col-4 col-md-3">
                            <?php if ($img) { dci_get_img($img, 'rounded-top img-fluid img-responsive'); } ?>
                        </div>
                        
                        <!-- Dati a destra -->
                        <div class="col-8 col-md-9">
                            <div class="card-body p-2"> <!-- Aggiunto padding minore -->
                                <!-- Nome e Data di inizio incarico -->
                                <div class="category-top cmp-list-card-img__body">
                                    <span class="category cmp-list-card-img__body-heading-title underline"><?php echo $nome_incarico ? $nome_incarico : 'POLITICO'; ?></span>
                                    <span class="data"><?php echo $arrdata[0].' '.$monthName.' '.$arrdata[2] ?></span>
                                </div>

                                <!-- Incarichi -->
                                <div class="incarichi">
                                    <?php
                                    // Stampa tutti gli incarichi associati
                                    foreach ($incarichi as $incarico) {
                                        $nome_incarico = get_the_title($incarico);
                                        echo '<span class="badge bg-primary badge-sm">' . $nome_incarico . '</span>'; // Badge più piccolo
                                    }
                                    ?>
                                </div>

                                <!-- Descrizione -->
                                <p class="card-text mt-2" style="font-size: 0.9rem;">
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

