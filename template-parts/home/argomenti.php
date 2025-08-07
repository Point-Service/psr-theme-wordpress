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
            <div class="col-lg-10 col-xl-6 offset-lg-1 offset-xl-2" id="altri-argomenti-container">
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
/* Contenitore per la sezione "Altri argomenti" */
#altri-argomenti-container {
    background-color: #f9f9f9; /* Colore di sfondo grigio chiaro */
    padding: 40px 0;
    border-radius: 8px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); /* Ombra morbida per evidenziare la sezione */
}

/* Titolo della sezione "Altri argomenti" */
#altri-argomenti-container h3 {
    font-size: 22px;
    font-weight: 700;
    color: #333; /* Colore grigio scuro per il titolo */
    text-transform: uppercase;
    margin-bottom: 20px;
    letter-spacing: 1px;
}

/* Layout della lista di chip: espansione orizzontale */
#altri-argomenti-container ul {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    justify-content: space-between; /* Distribuisce uniformemente i chip orizzontalmente */
    width: 100%; /* Si estende al 100% della larghezza */
    padding: 0;
    margin: 0;
}

/* Stile per ogni chip */
#altri-argomenti-container .chip {
    background-color: #3498db; /* Colore blu standard per i chip */
    color: #ffffff; /* Testo bianco per i chip */
    border-radius: 25px;
    padding: 8px 15px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    transition: background-color 0.3s ease, transform 0.2s ease;
    border: 1px solid #2980b9; /* Bordo blu leggermente più scuro per i chip */
    max-width: 45%; /* Limita la larghezza del chip al 45% */
    margin-bottom: 10px; /* Spaziatura tra i chip */
}

/* Hover sul chip */
#altri-argomenti-container .chip:hover {
    background-color: #2980b9; /* Colore blu più scuro al passaggio del mouse */
    transform: translateY(-3px); /* Solleva il chip al passaggio del mouse */
}

/* Stile per la colonna che contiene i chip */
#altri-argomenti-container .col-lg-9 {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    flex-wrap: wrap;
}

/* Stile per la colonna del titolo */
#altri-argomenti-container .col-lg-3 {
    display: flex;
    align-items: center;
}

/* Modifica il comportamento su mobile */
@media (max-width: 767px) {
    #altri-argomenti-container {
        padding: 30px 15px;
    }

    #altri-argomenti-container h3 {
        font-size: 18px;
        margin-bottom: 15px;
    }

    #altri-argomenti-container ul {
        justify-content: center; /* Centra la lista di chip su mobile */
    }

    #altri-argomenti-container .chip {
        font-size: 12px; /* Riduce la dimensione del testo su mobile */
        padding: 6px 12px; /* Riduce le dimensioni del chip su mobile */
        max-width: 80%; /* Limita la larghezza dei chip al 80% su mobile */
    }
}

/* Stile per il bottone "Mostra tutti" */
#altri-argomenti-container .btn-primary {
    padding: 12px 30px;
    border-radius: 30px;
    background-color: #3498db;
    color: white;
    font-weight: bold;
    text-transform: uppercase;
    border: none;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
    transition: background-color 0.3s ease, transform 0.2s ease;
}

#altri-argomenti-container .btn-primary:hover {
    background-color: #2980b9;
    transform: translateY(-3px); /* Solleva il bottone */
}
    
</style>
