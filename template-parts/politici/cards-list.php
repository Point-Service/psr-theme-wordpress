<?php 
global $posts;

$description = dci_get_meta('descrizione_breve');
$incarichi = dci_get_meta('incarichi');

// Verifica che $incarichi sia un array e contenga almeno un elemento
if (is_array($incarichi) && !empty($incarichi[0])) {
    $incarico = $incarichi[0];
    $nome_incarico = get_the_title($incarico);
} else {
    $incarico = null;
    $nome_incarico = ''; // Valore predefinito
}

// Estrai la data inizio incarico e controlla che abbia almeno tre elementi
$arrdata = explode('-', dci_get_meta("data_inizio_incarico"));
if (count($arrdata) === 3) { // Verifica che ci siano esattamente 3 elementi
    $day = $arrdata[0];
    $month = $arrdata[1];
    $year = $arrdata[2];
    $monthName = date_i18n('M', mktime(0, 0, 0, $month, 10));
} else {
    // Gestione dell'errore: valori predefiniti se non ci sono 3 elementi
    $day = '';
    $monthName = ''; // Valore predefinito
    $year = '';
}

// Verifica che get_the_terms() restituisca un array valido con almeno un elemento
$terms = $incarico ? get_the_terms($incarico, 'tipi_incarico') : null;
if (is_array($terms) && !empty($terms[0])) {
    $tipo = $terms[0];
} else {
    $tipo = null; // Valore predefinito
}

$prefix = '_dci_incarico_';
$img = dci_get_meta('foto');

// Contenitore per i riquadri
?>
<div class="container py-5">
    <h2 class="title-xxlarge mb-4">
        Esplora l'amministrazione
    </h2>
    <div class="row g-4">
        <?php 
        // Verifica che $tipo sia definito e che il suo nome sia "politico"
        if ($tipo && $tipo->name == "politico") {
            if ($img) {
        ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
                        <div class="card shadow-sm rounded">
                            <div class="row g-2 g-md-0 flex-md-column">
                                <div class="col-4 order-2 order-md-1">
                                    <?php dci_get_img($img, 'rounded-top img-fluid img-responsive'); ?>
                                </div>
                                <div class="col-8 order-1 order-md-2">
                                    <div class="card-body">
                                        <div class="category-top cmp-list-card-img__body">
                                            <span class="category cmp-list-card-img__body-heading-title underline">
                                                <?php echo $nome_incarico ? $nome_incarico : 'POLITICO'; ?>
                                            </span>
                                            <span class="data">
                                                <?php echo $day . ' ' . $monthName . ' ' . $year; ?>
                                            </span>
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
        } else { 
        ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
                    <div class="card shadow-sm rounded">
                        <div class="row g-2 g-md-0 flex-md-column">
                            <div class="col-12 order-1 order-md-2">
                                <div class="card-body card-img-none rounded-top">
                                    <div class="category-top cmp-list-card-img__body">
                                        <span class="category cmp-list-card-img__body-heading-title underline">
                                            <?php echo isset($tipo->name) ? strtoupper($tipo->name) : 'POLITICO'; ?>
                                        </span>
                                        <span class="data">
                                            <?php echo $day . ' ' . strtoupper($monthName) . ' ' . $year; ?>
                                        </span>
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
        ?>
    </div>
</div>


