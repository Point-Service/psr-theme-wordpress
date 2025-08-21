<?php
global $post, $posts;

// Per selezionare i contenuti in evidenza tramite configurazione
$servizi_evidenza = dci_get_option('servizi_evidenziati', 'servizi');
?>

<div class="container">
    <div class="row">
        <?php if (is_array($servizi_evidenza) && count($servizi_evidenza) > 0) { ?>
            <div class="col-12">
                <div class="card shadow-lg rounded-3 border-light p-4">
                    <div class="link-list-wrap">
                        <h3 class="title-large mb-4">
                            <span>Servizi in evidenza</span>
                        </h3>
                        <ul class="list-unstyled">
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
                                <li class="mb-4">
                                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center">
                                        <!-- Nome del servizio -->
                                        <a class="d-flex align-items-center list-item text-decoration-none text-dark ps-0 title-medium mb-2 mb-sm-0" href="<?php echo get_permalink($post->ID); ?>">
                                            <svg class="icon me-2"><use xlink:href="#it-arrow-right-triangle"></use></svg>
                                            <span><?php echo $post->post_title; ?></span>
                                        </a>

                                        <!-- Categoria e Periodo -->
                                        <div class="d-flex flex-column flex-sm-row align-items-start align-items-sm-center mt-2 mt-sm-0">
                                            <!-- Categoria -->
                                            <?php 
                                            $categorie = get_the_terms($post->ID, 'categorie_servizio');
                                            if (is_array($categorie) && count($categorie)) { ?>
                                                <div class="text-muted me-3 mb-2 mb-sm-0">
                                                    <strong>Categoria:</strong>
                                                    <?php 
                                                    $count = 1;
                                                    foreach ($categorie as $categoria) {
                                                        echo $count == 1 ? '' : ' - ';
                                                        echo '<a class="text-decoration-none text-primary" href="'.get_term_link($categoria->term_id).'">' . $categoria->name . '</a>';
                                                        ++$count;
                                                    }
                                                    ?>
                                                </div>
                                            <?php } ?>

                                            <!-- Periodo -->
                                            <?php if ($startDate && $endDate) { ?>
                                                <div class="service-period me-3">
                                                    <small><strong>Periodo:</strong> <?php echo $startDate->format('d/m/Y'); ?> - <?php echo $endDate->format('d/m/Y'); ?></small>
                                                </div>
                                            <?php } ?>
                                        </div>
                                    </div>

                                    <!-- Badge stato (spostato a destra) -->
                                    <div class="d-flex justify-content-end mt-2">
                                        <span class="badge <?php echo $stato_attivo ? 'bg-success' : 'bg-danger'; ?> text-white">
                                            <?php echo $stato_attivo ? 'Servizio attivo' : 'Servizio non attivo'; ?>
                                        </span>
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
