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
                                $data_inizio_servizio = dci_get_meta("data_inizio_servizio", "_dci_servizio_", $post->ID);
                                $data_fine_servizio = dci_get_meta("data_fine_servizio", "_dci_servizio_", $post->ID);

                                // Recupero lo stato dal checkbox nel DB
                                $stato_db = dci_get_meta("_dci_servizio_stato", "", $post->ID); 
                                // attenzione: dci_get_meta pare che già legga il metadato

                                // Conversione date
                                $oggi = new DateTime();
                                $startDate = DateTime::createFromFormat('d/m/Y', $data_inizio_servizio);
                                $endDate = $data_fine_servizio ? DateTime::createFromFormat('d/m/Y', $data_fine_servizio) : null;

                                // Logica identica al template singolo: se date valide & oggi nel periodo allora "true", altrimenti "false"
                                $stato_calcolato = "false"; // predefinito
                                if ($startDate && $endDate && $startDate < $endDate) {
                                    if ($oggi >= $startDate && $oggi <= $endDate) {
                                        $stato_calcolato = "true";
                                    } else {
                                        $stato_calcolato = "false";
                                    }
                                    // salvo nel DB solo se entri in questa condizione
                                    update_post_meta($post->ID, "_dci_servizio_stato", $stato_calcolato);
                                } else {
                                    // date non valide
                                    $stato_calcolato = "false";
                                    // non aggiorno altrove
                                }

                                // Ora uso lo stato corrente che dovrebbe essere coerente con DB e calcolo
                                $stato_effettivo = ($stato_db === "true" && $stato_calcolato === "true") ? "true" : "false";

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
                                    <span class="badge <?php echo ($stato_effettivo === "true") ? 'bg-success' : 'bg-danger'; ?> text-white">
                                        <?php echo ($stato_effettivo === "true") ? 'Attivo' : 'Non attivo'; ?>
                                    </span>
                                </td>
                            </tr>
                            <?php } // fine foreach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<br>


