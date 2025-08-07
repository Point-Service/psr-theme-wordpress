<?php
    $argomenti = dci_get_terms_options('argomenti');
    $arr_ids = array_keys((array)$argomenti);
?>
<div class="container py-5" id="argomento">
    <h2 class="title-xxlarge mb-4">Esplora per argomento</h2>
    <div class="row g-4">       
        <?php foreach ($arr_ids as $arg_id) { 
            $argomento = get_term_by('term_id', $arg_id, 'argomenti');    
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
              <div class="card shadow-sm rounded">
                <div class="card-body">
                    <a class="text-decoration-none" href="<?php echo get_term_link($argomento->term_id); ?>" data-element="topic-element"><h3 class="card-title t-primary title-xlarge"><?php echo $argomento->name; ?></h3></a>
                    <p class="titillium text-paragraph mb-0 description">
                        <?php echo $argomento->description; ?>
                    </p>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
    </div>
</div>


<style>

/* Stile per la sezione "Esplora per argomento" */
#argomento {
    background-color: #f9f9f9; /* Colore di sfondo chiaro per la sezione */
    padding-top: 50px;
    padding-bottom: 50px;
}

#argomento h2 {
    font-size: 32px;
    font-weight: 700;
    color: #333;
    text-align: center;
    margin-bottom: 40px;
    text-transform: uppercase;
}

/* Contenitore per le card */
.row.g-4 {
    display: flex;
    flex-wrap: wrap;
    gap: 20px;
}

/* Stile per le singole card */
.cmp-card-simple .card {
    border: none;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1); /* Ombra leggera */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    background-color: #fff; /* Sfondo bianco */
    overflow: hidden;
}

.cmp-card-simple .card:hover {
    transform: translateY(-5px); /* Solleva leggermente la card al passaggio del mouse */
    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.15); /* Ombra più pronunciata al passaggio del mouse */
}

/* Stile per la card-body */
.cmp-card-simple .card-body {
    padding: 25px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    height: 100%;
}

/* Titolo della card */
.cmp-card-simple .card-title {
    font-size: 22px;
    font-weight: 600;
    color: #333;
    margin-bottom: 15px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    transition: color 0.3s ease;
}

.cmp-card-simple .card-title:hover {
    color: #007bff; /* Cambia colore al passaggio del mouse */
}

/* Descrizione dell'argomento */
.cmp-card-simple .titillium {
    font-size: 16px;
    color: #666;
    line-height: 1.5;
    flex-grow: 1;
    margin-bottom: 20px;
}

/* Link che porta all'argomento */
.cmp-card-simple .text-decoration-none {
    text-decoration: none;
}

/* Aggiungi spaziatura tra le colonne */
.col-md-6, .col-xl-4 {
    display: flex;
    justify-content: center;
}

/* Card in mobile */
@media (max-width: 768px) {
    #argomento h2 {
        font-size: 28px;
    }

    .cmp-card-simple .card-title {
        font-size: 18px;
    }

    .cmp-card-simple .titillium {
        font-size: 14px;
    }
}

/* Aggiungi un effetto di hover sugli interni dei link */
.cmp-card-simple .card-title a:hover {
    color: #007bff; /* Cambia colore al passaggio del mouse sul titolo */
}

.cmp-card-simple .card-body {
    position: relative;
    overflow: hidden;
    border-radius: 12px;
}

.cmp-card-simple .card-body::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 123, 255, 0.1);
    transition: opacity 0.3s ease;
    opacity: 0;
    z-index: -1;
}

.cmp-card-simple .card:hover .card-body::before {
    opacity: 1;
}

    
</style>
