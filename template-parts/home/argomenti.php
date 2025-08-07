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
        </div>
            <div class="col-lg-10 col-xl-8 offset-lg-1 offset-xl-2 text-center">
                <a href="<?php echo dci_get_template_page_url("page-templates/argomenti.php"); ?>" class="btn btn-primary mt-40">Mostra tutti</a>
            </div>
    <?php } ?>
</div>
<style>

    /* Sezione Altri Argomenti */
.altri-argomenti-section {
    background-color: #f7f9fb; /* Sfondo chiaro per la sezione */
    padding: 40px 0;
    border-radius: 10px; /* Angoli arrotondati */
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); /* Ombra leggera */
    margin-top: 20px;
}

/* Titolo "Altri argomenti" */
.altri-argomenti-section h3 {
    font-size: 1.25rem;
    font-weight: 700;
    color: #333;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 20px;
}

/* Chip (tag) per gli argomenti */
.altri-argomenti-section .chip {
    background: #eef3f9; /* Colore di sfondo chiaro per i chip */
    border: 1px solid #d1e0e8;
    color: #007bff;
    padding: 8px 16px;
    border-radius: 25px;
    margin-right: 12px;
    margin-bottom: 12px;
    font-size: 0.875rem;
    font-weight: 600;
    transition: all 0.3s ease; /* Animazione al passaggio del mouse */
}

.altri-argomenti-section .chip:hover {
    background-color: #007bff; /* Colore di sfondo al passaggio del mouse */
    color: #fff; /* Colore del testo al passaggio */
    border-color: #0056b3; /* Colore del bordo al passaggio */
    transform: translateY(-2px); /* Leggero sollevamento */
}

/* Lista degli argomenti */
.altri-argomenti-section .chip-label {
    display: inline-block;
    text-transform: capitalize; /* Prima lettera maiuscola */
}

/* Colonna dei chip */
.altri-argomenti-section .col-lg-9 {
    display: flex;
    flex-wrap: wrap;
    gap: 15px; /* Distanza tra i chip */
    justify-content: flex-start;
}

/* Pulsante "Mostra tutti" */
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
    margin: 20px auto;
    max-width: 220px;
}

.altri-argomenti-section .btn-primary:hover {
    background-color: #0056b3;
    border-color: #0056b3;
}

/* Responsività: Ottimizzazione per schermi più piccoli */
@media (max-width: 768px) {
    .altri-argomenti-section .col-lg-10 {
        width: 100%;
    }

    .altri-argomenti-section h3 {
        font-size: 1.125rem; /* Ridurre la dimensione del titolo su schermi piccoli */
        text-align: center;
    }

    .altri-argomenti-section .col-lg-9 {
        justify-content: center;
    }

    .altri-argomenti-section .chip {
        margin-right: 8px;
        margin-bottom: 8px;
    }
}

</style>
