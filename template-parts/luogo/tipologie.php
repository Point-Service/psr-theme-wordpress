<?php
    // Ottieni i termini con un controllo di errore
    $tipologie = get_terms('tipi_luogo', array(
        'hide_empty' => false,
    ));

    // Verifica che non sia un errore e che ci siano termini
    if (!is_wp_error($tipologie) && !empty($tipologie)) {
?>
<div class="container py-5" id="tipologia">
    <h2 class="title-xxlarge mb-4">Esplora per categoria</h2>
    <div class="row g-4">       
        <?php foreach ($tipologie as $tipologia) { 
            if ($tipologia->count > 0) {
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
              <div class="card shadow-sm rounded">
                <div class="card-body">
                    <a class="text-decoration-none" href="<?php echo get_term_link($tipologia->term_id); ?>" data-element="news-category-link">
                        <h3 class="card-title t-primary title-xlarge"><?php echo ucfirst($tipologia->name); ?></h3>
                    </a>
                    <p class="titillium text-paragraph mb-0 description">
                        <?php echo $tipologia->description; ?>
                    </p>
                </div>
              </div>
            </div>
          </div>
        <?php 
            }
        } 
        ?>
    </div>
</div>
<?php
    } else {
        // Mostra un messaggio se non ci sono termini o se c'è un errore
        echo '<div class="container py-5"><p></p></div>';
    }
?>
