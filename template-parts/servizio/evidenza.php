<?php
global $post, $posts;

// Recupero i servizi in evidenza (ad esempio servizio1, servizio2, ...)
$servizi_evidenza = dci_get_option('servizi_evidenziati', 'servizi');
?>

<div class="container">
    <div class="row">
        <?php if (is_array($servizi_evidenza) && count($servizi_evidenza) > 0) { ?>
            <div class="col-12">
                <div class="row"> 
                    <h2 class="text-black title-xlarge mb-3">Servizi in evidenza</h2> 
                </div>
                <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
                    <h3 class="title-large mb-4"></h3>
                    <table class="table table-striped table-hover table-soft">
                        <thead>
                            <tr>
                                <th>Servizio</th>
                                <th>Categoria</th>
                                <th>Periodo</th>
                                <th>Stato</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($servizi_evidenza as $servizio_id) {
                                $post = get_post($servizio_id);

                                // Recupero date dal servizio
                                $prefix = '_dci_servizio_';
                                $data_inizio_servizio = dci_get_meta('data_inizio_servizio', $prefix, $post->ID);
                                $data_fine_servizio = dci_get_meta('data_fine_servizio', $prefix, $post->ID);

                                // Recupero checkbox di stato (true / false come stringa)
                                $checkbox_stato = get_post_meta($post->ID, '_dci_servizio_stato', true);

                                // Conversione in DateTime
                                $startDate = DateTime::createFromFormat('d/m/Y', $data_inizio_servizio);
                                $endDate = $data_fine_servizio ? DateTime::createFromFormat('d/m/Y', $data_fine_servizio) : null;
                                $oggi = new DateTime();

                                // Controllo validità periodo
                                $periodo_valido = false;
                                if ($startDate && $endDate && $startDate < $endDate) {
                                    $periodo_valido = ($oggi >= $startDate && $oggi <= $endDate);
                                }

                                // Stato attivo SOLO se:
                                // - le date sono valide e oggi è nel periodo
                                // - il checkbox è spuntato (valore "true")
                                $stato_attivo = ($periodo_valido && $checkbox_stato === 'true');

                                // Recupero le categorie del servizio
                                $categorie = get_the_terms($post->ID, 'categorie_servizio');
                                $categoria = is_array($categorie)
                                    ? implode(", ", array_map(function($cat) {
                                        return $cat->name;
                                    }, $categorie))
                                    : 'N/D';
                            ?>
                            <tr>
                                <td>
                                    <a class="text-decoration-none" href="<?php echo get_permalink($post->ID); ?>">
                                        <?php echo esc_html($post->post_title); ?>
                                    </a>
                                </td>
                                <td><?php echo esc_html($categoria); ?></td>
                                <td>
                                    <?php
                                    if ($startDate && $endDate) {
                                        echo $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo $stato_attivo ? 'bg-success' : 'bg-danger'; ?> text-white">
                                        <?php echo $stato_attivo ? 'Attivo' : 'Non attivo'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php } // fine foreach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } // fine if ?>
    </div>
</div>
<br>

