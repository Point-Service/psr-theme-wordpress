<aside class="aside-list sticky-sidebar search-results-filters">
    <form role="search" method="get" class="search-form" action="<?php echo home_url(""); ?>">
        <h3 class="h6 text-uppercase"><strong><?php _e("Tipologia", "design_comuni_italia"); ?></strong></h3>
        <ul>
            <?php
            // Recupera i termini per "tipologia-servizio"
            $terms = get_terms( array(
                'taxonomy' => 'tipologia-servizio',
                'hide_empty' => true,
            ) );

            // Ciclo attraverso i termini
            foreach ( $terms as $term ) {
                // Recupera il primo post (servizio) associato a questa tipologia
                $args = array(
                    'post_type' => 'servizio', // Assumendo che "servizio" sia il nome del post type
                    'posts_per_page' => 1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'tipologia-servizio',
                            'field'    => 'slug',
                            'terms'    => $term->slug,
                        ),
                    ),
                );

                $servizi_query = new WP_Query( $args );
                $stato_attivo = true; 

                // Se ci sono risultati, recupero la data di inizio e fine
                if ( $servizi_query->have_posts() ) {
                    while ( $servizi_query->have_posts() ) {
                        $servizi_query->the_post();

                        $prefix = '_dci_servizio_';
                        $data_inizio_servizio = dci_get_meta('data_inizio_servizio', $prefix, get_the_ID());
                        $data_fine_servizio = dci_get_meta('data_fine_servizio', $prefix, get_the_ID());

                        // Converte le date
                        $startDate = DateTime::createFromFormat('d/m/Y', $data_inizio_servizio);
                        $endDate = $data_fine_servizio ? DateTime::createFromFormat('d/m/Y', $data_fine_servizio) : null;
                        $oggi = new DateTime();

                        // Valuta se il servizio è attivo
                        if ($startDate && $endDate && $startDate < $endDate) {
                            $stato_attivo = ($oggi >= $startDate && $oggi <= $endDate);
                        }
                    }
                    wp_reset_postdata(); // Reset della query per evitare conflitti
                }

                ?>
                <li>
                    <div class="form-check my-0">
                        <input type="radio" class="custom-control-input" name="tipologia-servizio" value="<?php echo $term->slug; ?>" id="check-<?php echo $term->slug; ?>" <?php if($term->slug == get_query_var("tipologia-servizio")) echo " checked "; ?> onChange="this.form.submit()">
                        <label class="mb-0" for="check-<?php echo $term->slug; ?>">
                            <?php echo $term->name; ?>
                ddddddddddddddddd
                        </label>

                        <!-- Badge di stato -->
                        <span class="badge <?php echo $stato_attivo ? 'bg-success' : 'bg-danger'; ?> text-white ms-2">
                            <?php echo $stato_attivo ? 'Attivo' : 'Non attivo'; ?>
                        </span>

                        <!-- Periodo di validità -->
                        <?php if ($startDate && $endDate) { ?>
                            <div class="service-period mt-1">
                                <small><strong>Periodo:</strong> <?php echo $startDate->format('d/m/Y'); ?> - <?php echo $endDate->format('d/m/Y'); ?></small>
                            </div>
                        <?php } ?>
                    </div>
                </li>
                <?php
            }
            ?>
        </ul>
    </form>
</aside>
