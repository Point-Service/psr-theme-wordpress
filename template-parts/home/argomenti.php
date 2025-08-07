<?php
global $argomento_full, $count;
$argomenti_evidenza = array();
for ($i = 1; $i <= 9; $i++) {
    $argomento = dci_get_option('argomenti_evidenziati_' . $i, 'homepage')[0] ?? null;
    if ($argomento) {
        $argomenti_evidenza[$i] = $argomento;       
    }
}
$altri_argomenti = dci_get_option('argomenti_altri','homepage');
?>

<div class="container">
 <?php if (!empty($argomenti_evidenza)) { ?>
    <div class="row"> 
            <h2 class="text-black title-xlarge mb-3">Argomenti in Evidenza</h2> 
    </div>
    <div>
        <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
            <?php
            if(is_array($argomenti_evidenza)) {
                foreach ($argomenti_evidenza as $key => $argomento_full) {
                    $count = $key;
                    if ($argomento_full && isset($argomento_full['argomento_'.$count.'_argomento'])) {
                        get_template_part("template-parts/home/scheda-argomento");
                    }
                } 
            } ?>
        </div>
    </div>
    <?php } 
    if ($altri_argomenti) { ?>
        <div class="row pt-30 altri-argomenti-section"> <!-- Aggiunta classe per la sezione -->
            <div class="col-lg-10 col-xl-6 offset-lg-1 offset-xl-2">
                <div class="row d-lg-inline-flex">
                    <div class="col-lg-3">
                        <h3 class="text-uppercase mb-3 title-xsmall-bold text u-grey-light">
                            Altri argomenti
                        </h3>
                    </div>
                    <div class="col-lg-9">
                        <ul class="d-flex flex-wrap gap-1">
                            <?php if (is_array($altri_argomenti)) {
                                foreach ($altri_argomenti as $arg_id) {
                                    $argomento = get_term_by('term_taxonomy_id', $arg_id);
                                    $url = get_term_link(intval($arg_id),'argomenti');
                            ?>
                                <li>
                                    <a href="<?php echo $url ?>" class="chip chip-simple">
                                        <span class="chip-label"><?php echo $argomento->name ?></span>
                                    </a>
                                </li>
                            <?php } } ?>
                        </ul>
                    </div>
                </div>
            </div>
             <div class="col-lg-10 col-xl-8 offset-lg-1 offset-xl-2 text-center">
                <a href="<?php echo dci_get_template_page_url("page-templates/argomenti.php"); ?>" class="btn btn-primary mt-40">Mostra tutti</a>
            </div>
        </div> 
    <?php } ?>
</div>
<style>
/* Stile moderno per i chip */
.altri-argomenti-section .chip {
    background-color: #e1eff7; /* Colore chiaro e moderno */
    border: 1px solid #c2e1f0; /* Bordo sottile e morbido */
    color: #007bff; /* Colore del testo blu per contrasto */
    padding: 10px 18px;
    border-radius: 30px; /* Angoli arrotondati per un look morbido */
    margin-right: 12px;
    margin-bottom: 12px;
    font-size: 0.9rem;
    font-weight: 600;
    display: inline-block;
    text-transform: capitalize;
    transition: all 0.3s ease-in-out; /* Animazione di transizione per hover */
}

/* Effetto hover sui chip */
.altri-argomenti-section .chip:hover {
    background-color: #007bff; /* Colore di sfondo blu quando hover */
    color: #fff; /* Colore del testo bianco */
    border-color: #0056b3; /* Colore del bordo al passaggio */
    transform: translateY(-3px); /* Effetto di sollevamento per rendere il chip più interattivo */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); /* Aggiunta di un'ombra leggera */
}

/* Opzione per chip disabilitati o non cliccabili */
.altri-argomenti-section .chip.disabled {
    background-color: #f2f6fa;
    color: #b0c4d0;
    border: 1px solid #d1e2f1;
    cursor: not-allowed;
}

</style>
