<?php
global $servizio, $hide_categories;

$prefix = '_dci_servizio_';
$categorie = get_the_terms($servizio->ID, 'categorie_servizio');
$descrizione_breve = dci_get_meta('descrizione_breve', $prefix, $servizio->ID);

if($servizio->post_status == "publish") {
    ?>
    <div class="col-12 col-sm-6 col-lg-4 mb-4">
        <div class="card shadow-lg rounded-3 overflow-hidden" data-bs-toggle="modal" data-bs-target="#">
            <div class="card-body p-4">
                <!-- Categoria -->
                <?php if (!$hide_categories && is_array($categorie) && count($categorie)) { ?>
                <div class="mb-3">
                    <span class="text-muted">Categoria:</span>
                    <div class="d-flex flex-wrap">
                        <?php 
                        $count = 1;
                        foreach ($categorie as $categoria) {
                            echo $count == 1 ? '' : ' - ';
                            echo '<a class="text-decoration-none text-uppercase text-primary fw-bold me-2" href="'.get_term_link($categoria->term_id).'">'.$categoria->name.'</a>';
                            ++$count;
                        }
                        ?>
                    </div>
                </div>
                <?php } ?>

                <!-- Titolo del Servizio -->
                <h3 class="card-title text-dark mb-3">
                    <a class="text-decoration-none text-dark" href="<?php echo get_permalink($servizio->ID); ?>" data-element="service-link"><?php echo $servizio->post_title; ?></a>
                </h3>

                <!-- Descrizione Breve -->
                <p class="card-text text-muted mb-4"><?php echo $descrizione_breve; ?></p>

                <!-- Stato e Periodo -->
                <?php
                $data_inizio_servizio = dci_get_meta('data_inizio_servizio', $prefix, $servizio->ID);
                $data_fine_servizio = dci_get_meta('data_fine_servizio', $prefix, $servizio->ID);
                $startDate = DateTime::createFromFormat('d/m/Y', $data_inizio_servizio);
                $endDate = $data_fine_servizio ? DateTime::createFromFormat('d/m/Y', $data_fine_servizio) : null;
                $oggi = new DateTime();
                $stato_attivo = true;

                if ($startDate && $endDate && $startDate < $endDate) {
                    $stato_attivo = ($oggi >= $startDate && $oggi <= $endDate);
                }
                ?>
                <div class="d-flex justify-content-between align-items-center">
                    <span class="badge <?php echo $stato_attivo ? 'bg-success' : 'bg-danger'; ?> text-white">
                        <?php echo $stato_attivo ? 'Servizio attivo' : 'Servizio non attivo'; ?>
                    </span>

                    <?php if ($startDate && $endDate) { ?>
                    <small class="text-muted">
                        <strong>Periodo:</strong> <?php echo $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y'); ?>
                    </small>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
    <?php
}
?>

