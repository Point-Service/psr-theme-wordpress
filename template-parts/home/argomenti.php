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
    <div class="row pt-30">
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
/* Sezione "Altri argomenti" */
.container .row.pt-30 {
    padding-top: 30px;
    background-color: #f4f4f4; /* Colore di sfondo chiaro per separare visivamente la sezione */
    border-radius: 8px;
    padding-bottom: 40px;
}

/* Intestazione "Altri argomenti" */
.container .row.pt-30 .title-xsmall-bold.text.u-grey-light {
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    letter-spacing: 0.5px;
}

/* Colonna con il testo e i link */
.container .row.pt-30 .col-lg-9 {
    padding-top: 10px;
}

/* Listaggio argomenti */
.container .row.pt-30 ul.d-flex.flex-wrap.gap-1 {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    padding: 0;
    margin: 0;
    list-style-type: none;
}

/* Stile per ogni argomento */
.container .row.pt-30 ul.d-flex.flex-wrap.gap-1 li a {
    padding: 10px 16px;
    background-color: #ffffff;
    border-radius: 30px;
    color: #333;
    font-size: 1rem;
    font-weight: 500;
    border: 2px solid #dcdcdc;
    transition: all 0.3s ease;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
}

/* Hover sui link degli argomenti */
.container .row.pt-30 ul.d-flex.flex-wrap.gap-1 li a:hover {
    background-color: #007bff;
    color: white;
    border-color: #007bff;
    transform: translateY(-4px); /* Leggera animazione di spostamento */
    box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
}

/* Stile del pulsante "Mostra tutti" - Senza modifiche */
.container .row.pt-30 .btn.btn-primary.mt-40 {
    background-color: #007bff;
    border-color: #007bff;
    padding: 12px 24px;
    border-radius: 25px;
    font-size: 1.1rem;
    font-weight: 600;
    transition: all 0.3s ease;
}

.container .row.pt-30 .btn.btn-primary.mt-40:hover {
    background-color: #0056b3;
    border-color: #0056b3;
    transform: translateY(-2px); /* Animazione di elevazione al passaggio del mouse */
}

/* Media queries per schermi più piccoli */
@media (max-width: 768px) {
    .container .row.pt-30 .title-xsmall-bold.text.u-grey-light {
        font-size: 1rem;
    }

    .container .row.pt-30 ul.d-flex.flex-wrap.gap-1 {
        gap: 12px;
    }

    .container .row.pt-30 ul.d-flex.flex-wrap.gap-1 li a {
        padding: 8px 14px;
        font-size: 0.9rem;
    }
}

    
</style>
