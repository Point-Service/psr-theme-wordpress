<?php
global $post, $posts;

// Per selezionare i contenuti in evidenza tramite configurazione
$servizi_evidenza = dci_get_option('servizi_evidenziati', 'servizi');
?>

<div class="container">
    <div class="row">
        <?php if (is_array($servizi_evidenza) && count($servizi_evidenza) > 0) { ?>
            <div class="col-12">
                <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
                    <div class="link-list-wrap">
                        <h3 class="title-large">
                            <span>Servizi in evidenza</span>
                        </h3>
                        <ul class="link-list t-primary">
                            <?php foreach ($servizi_evidenza as $servizio_id) {
                                $post = get_post($servizio_id);

                                // Recupero date dal servizio
                                $prefix = '_dci_servizio_';
                                $data_inizio_servizio = dci_get_meta('data_inizio_servizio', $prefix, $post->ID);
                                $data_fine_servizio = dci_get_meta('data_fine_servizio', $prefix, $post->ID);

                                // Conversione in DateTime
                                $startDate = DateTime::createFromFormat('d/m/Y', $data_inizio_servizio);
                                $endDate = $data_fine_servizio ? DateTime::createFromFormat('d/m/Y', $data_fine_servizio) : null;
                                $oggi = new DateTime();

                                // Valutazione stato
                                $stato_attivo = true;
                                if ($startDate && $endDate && $startDate < $endDate) {
                                    $stato_attivo = ($oggi >= $startDate && $oggi <= $endDate);
                                }
                                ?>
                                <li class="mb-4 mt-4">
                                    <!-- Nome del servizio e stato sulla stessa riga -->
                                    <a class="list-item ps-0 title-medium underline" style="text-decoration:none;" href="<?php echo get_permalink($post->ID); ?>">
                                        <svg class="icon"><use xlink:href="#it-arrow-right-triangle"></use></svg>
                                        <span><?php echo $post->post_title; ?></span>
                                    </a>

                                    <div class="service-info d-flex justify-content-between mt-2">
                                        <!-- Badge stato -->
                                        <span class="chip chip-simple" data-element="service-status">
                                            <span class="chip-label">
                                                <?php echo $stato_attivo ? '<span class="text-success">Servizio attivo</span>' : '<span class="text-danger">Servizio non attivo</span>'; ?>
                                            </span>
                                        </span>

                                        <!-- Periodo -->
                                        <?php if ($startDate && $endDate) { ?>
                                            <div class="service-period">
                                                <small><strong>Periodo:</strong> <?php echo $startDate->format('d/m/Y'); ?> - <?php echo $endDate->format('d/m/Y'); ?></small>
                                            </div>
                                        <?php } ?>
                                    </div>
                                </li>
                            <?php } ?>
                        </ul>
                    </div>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<br>
<style>
/* Usa Flexbox per disporre i badge e il periodo sulla stessa riga */
.link-list .service-info {
    display: flex;
    justify-content: space-between;
    align-items: center; /* Allinea verticalmente */
    margin-top: 10px; /* Spazio sopra la riga */
}

/* Aggiungi uno spazio tra il badge e il periodo */
.link-list .service-period {
    margin-left: 20px; /* Distanza tra il badge e il periodo */
}

/* Modifica del chip per migliorare la visibilità */
.link-list .chip {
    display: inline-block;
    margin-right: 15px; /* Spazio tra il badge e il periodo */
}
    
</style>
