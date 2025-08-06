<?php
global $sito_tematico_id, $count, $location;

$siti_tematici = dci_get_option('siti_tematici', $location??'homepage');
if (is_array($siti_tematici) && count($siti_tematici)) {
?>

  <div class="container mb-4">
    <div class="row">
      <h2 class="mb-0">Siti tematici</h2>
    </div>
    <div class="pt-4 pt-lg-30">
      <div class="row gy-4">
        <?php
        $count = 0;
        foreach ($siti_tematici as $sito_tematico_id) {
        ?>
          <div class="col-12 col-md-6 col-lg-4 card-wrapper pb-0">
            <?php
            get_template_part("template-parts/sito-tematico/card");
            ?>
          </div>
        <?php
        ++$count;
        }
        ?>
      </div>
    </div>
  </div>
<?php } ?>
<style>

  /* Stile per i siti tematici */
.siti-tematici-section .card-wrapper {
    display: flex;
    align-items: stretch; /* Allineare i pulsanti nella stessa riga */
    height: 100%;
}

.siti-tematici-section .card {
    background-color: #f9f9f9; /* Sfondo neutro chiaro */
    border: 1px solid #e0e0e0; /* Bordo grigio chiaro */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Ombra leggera */
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 200px;
    border-radius: 10px; /* Bordo arrotondato per ogni card */
}

.siti-tematici-section .card:hover {
    transform: translateY(-5px); /* Sollevamento al passaggio del mouse */
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.2); /* Ombra più forte */
}

.siti-tematici-section .card-body {
    display: flex;
    align-items: center;
    justify-content: space-between; /* Per allineare il contenuto */
    padding: 20px;
    flex-grow: 1;
}

.siti-tematici-section .card-title {
    font-size: 1.2rem;
    color: #333333; /* Colore scuro per il titolo */
    margin-bottom: 8px;
}

.siti-tematici-section .description {
    font-size: 1rem;
    line-height: 1.5;
    color: #777777; /* Grigio per la descrizione */
    margin-top: 10px;
}

/* Icona - Circolare e centrale con sfondo chiaro */
.siti-tematici-section .avatar {
    background-color: #f0f0f0;
    border-radius: 50%;
    padding: 12px;
}

.siti-tematici-section .avatar i {
    color: #555555; /* Colore scuro per l'icona */
    font-size: 24px;
}

/* Pulsanti con sfondo e testo contrastante */
.siti-tematici-section .card.bg-neutral {
    background-color: #fafafa; /* Colore neutro chiaro */
    color: #333; /* Colore scuro per il testo */
}

.siti-tematici-section .card.bg-neutral:hover {
    background-color: #e0e0e0; /* Cambio sfondo su hover */
}

/* Colori per hover */
.siti-tematici-section .card.bg-neutral:hover .card-title {
    color: #0056b3; /* Blu scuro per il titolo al passaggio del mouse */
}

/* Layout e spaziatura */
.siti-tematici-section .card-wrapper {
    width: 100%;
}

.siti-tematici-section .card-body {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 20px;
}

.siti-tematici-section .card-title {
    font-size: 1.2rem; /* Dimensione del titolo */
    font-weight: 600;
}

/* Layout responsive per schermi più piccoli */
@media (max-width: 768px) {
    .siti-tematici-section .card-body {
        flex-direction: column;
        text-align: center;
    }
    .siti-tematici-section .card-title {
        font-size: 1.1rem;
    }
}

/* Colonna e card uniforme per altezza */
.siti-tematici-section .col-md-6, .siti-tematici-section .col-lg-4 {
    display: flex;
    align-items: stretch;
}

.siti-tematici-section .card {
    display: flex;
    flex-direction: column;
    height: 100%;
    min-height: 200px; /* Altezza minima */
}

.siti-tematici-section .card-body {
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 20px;
    flex-grow: 1;
}

</style>
