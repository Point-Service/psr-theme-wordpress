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

                                // Conversione in DateTime
                                $startDate = DateTime::createFromFormat('d/m/Y', $data_inizio_servizio);
                                $endDate = $data_fine_servizio ? DateTime::createFromFormat('d/m/Y', $data_fine_servizio) : null;
                                $oggi = new DateTime();


                		        // Recupera lo stato del servizio
                		        $stato = dci_get_meta('_dci_servizio_stato', $prefix, $post->ID);


                                  // Controlla se entrambe le date sono presenti e che la data di inizio sia inferiore alla data di fine
                            			if ($startDate && $endDate && $startDate < $endDate) {
                            			    // Verifica se la data di oggi è all'interno del periodo
                            			    if ($oggi >= $startDate && $oggi <= $endDate) {
                            			        // Servizio attivo
                            			        $stato = "true";
                            			    } else {
                            			        // Servizio disattivato automaticamente
                            			        $stato = "false";
                            			    }
                            			
                            			    // Aggiorna lo stato del servizio nel database solo se entra in questa condizione
                            			    update_post_meta($post->ID, "_dci_servizio_stato", $stato);
                            			} else {
                            			    // Se le date non sono valide (entrambe mancanti o data inizio >= data fine), stato è "false"
                            			    $stato = "false";
                            			    // Non aggiorno lo stato nel database se non entra in questa condizione
                            			}

                                // Recupero le categorie del servizio
                                $categorie = get_the_terms($post->ID, 'categorie_servizio');
                                $categoria = is_array($categorie) ? implode(", ", array_map(function($cat) {
                                    return $cat->name;
                                }, $categorie)) : 'N/D';
                                ?>
                                <tr>
                                    <td>
                                        <a class="text-decoration-none" href="<?php echo get_permalink($post->ID); ?>"><?php echo $post->post_title; ?></a>
                                    </td>
                                    <td><?php echo $categoria; ?></td>
                                    <td>
                                        <?php if ($startDate && $endDate) {
                                            echo $startDate->format('d/m/Y') . ' - ' . $endDate->format('d/m/Y');
                                        } else {
                                            echo '';
                                        } ?>
                                    </td>
                                    <td>
                                        <span class="badge <?php echo $stato_attivo ? 'bg-success' : 'bg-danger'; ?> text-white">
                                            <?php echo $stato ? 'Attivo' : 'Non attivo'; ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
        <?php }  ?>
    </div>
</div>
<br>
