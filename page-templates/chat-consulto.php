


<button id="btn-consolto" disabled style="
  padding: 12px 22px;
  background: #999;
  color: #eee;
  border: none;
  border-radius: 6px;
  font-size: 16px;
  cursor: not-allowed;
">
  Avvia video chat
</button>





                <!-- Box recapiti -->
                <div class="card border-primary my-3 p-3">
                    <h5>Contatti</h5>

                    <?php
                    $indirizzo = dci_get_option("contatti_indirizzo", 'footer');
                    if (!empty($indirizzo)) {
                        echo '<p>Indirizzo: ' . esc_html($indirizzo) . '</p>';
                    }

                    $cf_piva = dci_get_option("contatti_CF_PIVA", 'footer');
                    if (!empty($cf_piva)) {
                        echo '<p>Codice fiscale / P. IVA: ' . esc_html($cf_piva) . '</p>';
                    }

                    $ufficio_id = dci_get_option("contatti_URP", 'footer');
                    if (!empty($ufficio_id)) {
                        $ufficio = get_post($ufficio_id);
                        if ($ufficio) {
                            echo '<p><a href="' . esc_url(get_permalink($ufficio_id)) . '" class="list-item" title="Vai alla pagina URP">'
                                . esc_html($ufficio->post_title) . '</a></p>';
                        }
                    }

                    $numero_verde = dci_get_option("numero_verde", 'footer');
                    if (!empty($numero_verde)) {
                        echo '<p>Numero verde: <a href="tel:' . esc_html($numero_verde) . '" class="list-item">' . esc_html($numero_verde) . '</a></p>';
                    }

                    $sms_whatsapp = dci_get_option("SMS_Whatsapp", 'footer');
                    if (!empty($sms_whatsapp)) {
                        echo '<p>SMS e WhatsApp: ' . esc_html($sms_whatsapp) . '</p>';
                    }

                    $pec = dci_get_option("contatti_PEC", 'footer');
                    if (!empty($pec)) {
                        echo '<p>PEC: <a href="mailto:' . esc_attr($pec) . '" class="list-item" title="PEC ' . esc_attr(dci_get_option("nome_comune")) . '">' . esc_html($pec) . '</a></p>';
                    }

                    $centralino = dci_get_option("centralino_unico", 'footer');
                    if (!empty($centralino)) {
                        echo '<p>Centralino unico: <a href="tel:' . esc_html($centralino) . '" class="list-item">' . esc_html($centralino) . '</a></p>';
                    }
                    ?>
                </div>

            </div>
        </div>

        <article id="more-info">
            <div class="row variable-gutters">
                <div class="col-lg-12">
                    <?php get_template_part("template-parts/single/bottom"); ?>
                </div>
            </div>
        </article>

    </div>

    <?php get_template_part("template-parts/common/valuta-servizio"); ?>
</main>

<?php
get_footer();

?>


