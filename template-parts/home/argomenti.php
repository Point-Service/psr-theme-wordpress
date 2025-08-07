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
        <div class="altri-argomenti-section">
            <!-- Pulsante "Mostra tutti" sopra gli altri argomenti -->
            <div class="col-lg-10 col-xl-8 offset-lg-1 offset-xl-2 text-center">
                <a href="<?php echo dci_get_template_page_url("page-templates/argomenti.php"); ?>" class="btn btn-primary mt-40">Mostra tutti</a>
            </div>

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
                                        $url = get_term_link(intval($arg_id), 'argomenti');
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
            </div>
        </div>
    <?php } ?>
</div>

<style>
/* Sezione principale degli altri argomenti */
.altri-argomenti-section {
    background-color: #f9f9f9; /* Colore di sfondo soft */
    padding: 40px 0;
    border-radius: 8px; /* Bordo arrotondato */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

/* Pulsante "Mostra tutti" sopra gli altri argomenti */
.altri-argomenti-section .btn-primary {
    background-color: #007bff;
    border-color: #007bff;
    padding: 12px 30px;
    border-radius: 30px;
    font-size: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    transition: all 0.3s ease;
    display: block;
    margin: 0 auto 30px auto; /* Spazio sopra gli altri argomenti */
}

.altri-argomenti-section .btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

/* Titolo "Altri argomenti" */
.altri-argomenti-section .title-xsmall-bold {
    font-size: 1.25rem;
    color: #333; /* Colore scuro per il titolo */
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 20px;
}

/* Collezione dei chip (tag) */
.altri-argomenti-section .chip {
    background: linear-gradient(135deg, #a0c4ff, #78a1f1); /* Colore gradiente */
    border: 1px solid #78a1f1;
    color: #fff;
    padding: 8px 16px;
    border-radius: 30px;
    margin-right: 10px;
    margin-bottom: 10px;
    font-size: 0.875rem;
    font-weight: 600;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.altri-argomenti-section .chip:hover {
    background: linear-gradient(135deg, #007bff, #0056b3); /* Cambio gradiente al passaggio del mouse */
    border-color: #0056b3;
    transform: translateY(-2px); /* Leggera animazione per l'hover */
}

.altri-argomenti-section .chip .chip-label {
    font-size: 0.875rem;
    font-weight: 600;
}

/* Aggiustamenti responsivi */
@media (max-width: 768px) {
    .altri-argomenti-section .col-lg-3 {
        margin-bottom: 20px;
    }

    .altri-argomenti-section .col-lg-9 {
        text-align: center;
    }

    .altri-argomenti-section .chip {
        margin-right: 5px;
        margin-bottom: 5px;
    }

    .altri-argomenti-section .btn-primary {
        width: 100%;
    }
}

</style>
