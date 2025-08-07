<div class="container">
    <?php if (!empty($argomenti_evidenza)) { ?>
        <div class="row">
            <h2 class="text-black title-xlarge mb-4 text-center">Argomenti in Evidenza</h2>
        </div>
        <div>
            <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal card-teaser-block-3">
                <?php
                if (is_array($argomenti_evidenza)) {
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
                        <ul class="d-flex flex-wrap gap-2">
                            <?php if (is_array($altri_argomenti)) {
                                foreach ($altri_argomenti as $arg_id) {
                                    $argomento = get_term_by('term_taxonomy_id', $arg_id);
                                    $url = get_term_link(intval($arg_id),'argomenti');
                            ?>
                            <li>
                                <a href="<?php echo $url ?>" class="chip chip-simple mb-2">
                                    <span class="chip-label"><?php echo $argomento->name ?></span>
                                </a>
                            </li>
                            <?php } } ?>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="col-lg-10 col-xl-8 offset-lg-1 offset-xl-2 text-center">
                <a href="<?php echo dci_get_template_page_url("page-templates/argomenti.php"); ?>" class="btn btn-primary mt-40 px-4 py-2 rounded-pill shadow-lg">Mostra tutti</a>
            </div>
        </div>
    <?php } ?>
</div>

<style>
/* Titolo "Argomenti in Evidenza" */
.title-xlarge {
    font-size: 32px;
    font-weight: 700;
    color: #333;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    text-align: center;
}

/* Sezione "Altri argomenti" */
ul.d-flex {
    display: flex;
    flex-wrap: wrap;
    gap: 0.8rem;
    justify-content: center; /* Centra gli item */
}

/* Chip per gli altri argomenti */
.chip {
    background-color: #f1f1f1;
    border: 1px solid #ddd;
    border-radius: 20px;
    padding: 10px 15px;
    font-size: 14px;
    font-weight: 600;
    color: #333;
    text-decoration: none;
    transition: all 0.3s ease;
    margin-bottom: 10px;
}

.chip:hover {
    background-color: #007bff;
    color: #fff;
    border-color: #007bff;
}

/* Label della chip */
.chip-label {
    white-space: nowrap;
}

/* Bottone "Mostra tutti" */
.btn-primary {
    background-color: #007bff;
    color: #fff;
    border-radius: 30px;
    padding: 10px 20px;
    font-size: 16px;
    font-weight: 600;
    border: none;
    box-shadow: 0 4px 12px rgba(0, 123, 255, 0.15);
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background-color: #0056b3;
    box-shadow: 0 6px 16px rgba(0, 123, 255, 0.2);
}

/* Card wrapper per argomenti in evidenza */
.card-wrapper {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: space-between;
}

.card-teaser-wrapper {
    width: 30%;
    background-color: #fff;
    border-radius: 10px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
    transition: all 0.3s ease;
}

.card-teaser-wrapper:hover {
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.2);
}

/* Padding e margin personalizzati */
.pt-30 {
    padding-top: 30px;
}

.mb-3 {
    margin-bottom: 20px;
}

.mb-4 {
    margin-bottom: 30px;
}

/* Responsività per mobile */
@media (max-width: 768px) {
    .title-xlarge {
        font-size: 28px;
    }

    .card-teaser-wrapper {
        width: 48%;
    }

    .card-wrapper {
        gap: 15px;
    }

    .btn-primary {
        width: 100%;
        padding: 12px;
    }
}


    
</style>
