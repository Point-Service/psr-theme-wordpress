<?php
    $tipologie = get_terms('tipi_luogo', array(
        'hide_empty' => false,
    ));

    if (!is_wp_error($tipologie) && !empty($tipologie)) {
?>
<div class="container py-5" id="tipologia">
    <h2 class="title-xxlarge mb-4">Esplora per categoria</h2>
    <div class="row g-4">       
        <?php foreach ($tipologie as $tipologia) { 
            if ($tipologia->count > 0) {
                // Ottieni il link del termine
                $term_link = get_term_link($tipologia->term_id);

                // Verifica che non ci siano errori con il link
                if (!is_wp_error($term_link)) {
                    // Effettua una richiesta HEAD per verificare se il link esiste
                    $response = wp_remote_head($term_link);
                    $is_valid_link = !is_wp_error($response) && wp_remote_retrieve_response_code($response) == 200;
                } else {
                    $is_valid_link = false;
                }
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
              <div class="card shadow-sm rounded">
                <div class="card-body">
                    <?php if ($is_valid_link) { ?>
                        <!-- Link cliccabile se la pagina esiste -->
                        <a class="text-decoration-none" href="<?php echo esc_url($term_link); ?>" data-element="news-category-link">
                            <h3 class="card-title t-primary title-xlarge"><?php echo ucfirst($tipologia->name); ?></h3>
                        </a>
                    <?php } else { ?>
                        <!-- Testo non cliccabile se la pagina non esiste -->
                        <h3 class="card-title t-primary title-xlarge"><?php echo ucfirst($tipologia->name); ?></h3>
                    <?php } ?>
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
