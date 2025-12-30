<?php
/**
 * Template Name: Contatti
 * Description: Pagina contenente i contatti dell'ente
 *
 * @package Design_Comuni_Italia
 */

get_header();
?>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<!-- Stile personalizzato per la tabella accessi -->
<style>
    /* CSS Personalizzato per la tabella accessi */
    .custom-access-table {
        width: 100%; /* Aumenta la larghezza della tabella */
        margin: 0; /* Allinea a sinistra */
        border-spacing: 0; /* Elimina lo spazio tra le celle della tabella */
    }

    .custom-access-table th, .custom-access-table td {
        padding: 12px;
        border: 1px solid #ddd; /* Aggiungi il bordo alla tabella */
    }

    /* Allargare le colonne per una migliore visualizzazione */
    .custom-access-table th:nth-child(1),
    .custom-access-table td:nth-child(1) {
        width: 5%; /* Colonna # */
    }

    .custom-access-table th:nth-child(2),
    .custom-access-table td:nth-child(2) {
        width: 40%; /* Colonna IP */
    }

    .custom-access-table th:nth-child(3),
    .custom-access-table td:nth-child(3) {
        width: 25%; /* Colonna Ora */
    }

    .custom-access-table th:nth-child(4),
    .custom-access-table td:nth-child(4) {
        width: 30%; /* Colonna Browser/User Agent */
    }

    /* Rendere la tabella responsive su dispositivi mobili */
    @media (max-width: 768px) {
        .custom-access-table th, .custom-access-table td {
            font-size: 14px;
            padding: 8px;
        }

        /* Ridurre la larghezza delle colonne su schermi più piccoli */
        .custom-access-table th:nth-child(1),
        .custom-access-table td:nth-child(1) {
            width: 10%;
        }

        .custom-access-table th:nth-child(2),
        .custom-access-table td:nth-child(2) {
            width: 30%;
        }

        .custom-access-table th:nth-child(3),
        .custom-access-table td:nth-child(3) {
            width: 30%;
        }

        .custom-access-table th:nth-child(4),
        .custom-access-table td:nth-child(4) {
            width: 30%;
        }
    }
</style>


<main id="content">
    <div class="container" id="main-container">

        <!-- Breadcrumb -->
        <div class="row mb-4">
            <div class="col px-lg-4">
                <?php get_template_part("template-parts/hero/hero"); ?>
            </div>
        </div>

        <!-- Card Contatti Moderna -->
        <div class="row">
            <div class="col-lg-10 offset-lg-1 px-lg-4">
                <div class="card contact-card mb-4" role="region" aria-labelledby="contact-header">

                    <div class="card-header contact-header bg-primary" id="contact-header">
                        <h2 class="h5 mb-0">
                            <i class="fa-solid fa-address-book me-2" aria-hidden="true"></i>
                            Contatti istituzionali
                        </h2>
                    </div>

                    <div class="card-body contact-body bg-grey-dsk">
                        <ul class="list-group list-group-flush">

                            <?php
                            $indirizzo = dci_get_option("contatti_indirizzo", 'footer');
                            if (!empty($indirizzo)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-location-dot contact-icon text-decoration-none" aria-hidden="true"></i>
                                        <div>
                                            <strong>Indirizzo:</strong><br>
                                            ' . esc_html($indirizzo) . '
                                        </div>
                                      </li>';
                            }

                            $cf_piva = dci_get_option("contatti_CF_PIVA", 'footer');
                            if (!empty($cf_piva)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-id-card contact-icon text-decoration-none" aria-hidden="true"></i>
                                        <div>
                                            <strong>Codice fiscale / Partita IVA:</strong><br>
                                            ' . esc_html($cf_piva) . '
                                        </div>
                                      </li>';
                            }

                            $ufficio_id = dci_get_option("contatti_URP", 'footer');
                            if (!empty($ufficio_id)) {
                                $ufficio = get_post($ufficio_id);
                                if ($ufficio) {
                                    echo '<li class="list-group-item contact-list-item">
                                            <i class="fa-solid fa-building-user contact-icon text-decoration-none" aria-hidden="true"></i>
                                            <div>
                                                <strong>Ufficio Relazioni con il Pubblico (URP):</strong><br>
                                                <a href="' . esc_url(get_permalink($ufficio_id)) . '" class="text-decoration-none">
                                                    ' . esc_html($ufficio->post_title) . '
                                                </a>
                                            </div>
                                          </li>';
                                }
                            }

                            $numero_verde = dci_get_option("numero_verde", 'footer');
                            if (!empty($numero_verde)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-phone-volume contact-icon text-decoration-none" aria-hidden="true"></i>
                                        <div>
                                            <strong>Numero verde:</strong><br>
                                            <a href="tel:' . esc_html($numero_verde) . '" class="text-decoration-none">
                                                ' . esc_html($numero_verde) . '
                                            </a>
                                        </div>
                                      </li>';
                            }

                            $sms_whatsapp = dci_get_option("SMS_Whatsapp", 'footer');
                            if (!empty($sms_whatsapp)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-brands fa-whatsapp contact-icon text-decoration-none" aria-hidden="true"></i>
                                        <div>
                                            <strong>SMS e WhatsApp:</strong><br>
                                            ' . esc_html($sms_whatsapp) . '
                                        </div>
                                      </li>';
                            }

                            $pec = dci_get_option("contatti_PEC", 'footer');
                            if (!empty($pec)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-envelope-circle-check contact-icon text-decoration-none" aria-hidden="true"></i>
                                        <div>
                                            <strong>Posta Elettronica Certificata (PEC):</strong><br>
                                            <a href="mailto:' . esc_attr($pec) . '" class="text-decoration-none">
                                                ' . esc_html($pec) . '
                                            </a>
                                        </div>
                                      </li>';
                            }

                            $centralino = dci_get_option("centralino_unico", 'footer');
                            if (!empty($centralino)) {
                                echo '<li class="list-group-item contact-list-item">
                                        <i class="fa-solid fa-headset contact-icon text-decoration-none" aria-hidden="true"></i>
                                        <div>
                                            <strong>Centralino unico:</strong><br>
                                            <a href="tel:' . esc_html($centralino) . '" class="text-decoration-none">
                                                ' . esc_html($centralino) . '
                                            </a>
                                        </div>
                                      </li>';
                            }
                            ?>

                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenuti aggiuntivi -->
        <article id="more-info">
            <div class="row">
                <div class="col-lg-12">
                    <?php get_template_part("template-parts/single/bottom"); ?>
                </div>
            </div>
        </article>

    </div>

    <?php get_template_part("template-parts/common/valuta-servizio"); ?>
</main>

<?php get_footer(); ?>

