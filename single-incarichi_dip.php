<?php
/**
 * incarichi_concessione template file
 *
 * @package Design_Comuni_Italia
 */

get_header();
?>

<main>
<?php
while (have_posts()) :
    the_post();

    $prefix = '_dci_incarichi_dip_';

    $descrizione_breve = get_post_meta(get_the_ID(), $prefix . 'descrizione_breve', true);

    $data = get_the_date('j F Y');
    
    $anno_beneficio_raw = get_post_meta(get_the_ID(), $prefix . 'anno_beneficio', true);
    // Se è una data o anno in timestamp o stringa? Se timestamp, va convertito
    // altrimenti lo mostriamo come è
    if (is_numeric($anno_beneficio_raw) && intval($anno_beneficio_raw) > 0) {
        $anno_beneficio = date_i18n('Y', intval($anno_beneficio_raw));
    } elseif (!empty($anno_beneficio_raw)) {
        $anno_beneficio = esc_html($anno_beneficio_raw);
    } else {
        $anno_beneficio = '-';
    }

    $importo_raw = get_post_meta(get_the_ID(), $prefix . 'importo', true);
    $importo_numeric = floatval(str_replace(',', '.', preg_replace('/[^\d,\.]+/', '', $importo_raw)));
    $importo = $importo_numeric > 0 ? number_format($importo_numeric, 2, ',', '.') . '€' : 'Non specificato';

    $importo_liquidato_raw = get_post_meta(get_the_ID(), $prefix . 'importo_liquidato', true);
    $importo_liquidato_numeric = floatval(str_replace(',', '.', preg_replace('/[^\d,\.]+/', '', $importo_liquidato_raw)));

    $responsabile = get_post_meta(get_the_ID(), $prefix . 'responsabile', true);
    $responsabile = !empty($responsabile) ? esc_html($responsabile) : "Non specificato";

    $rag_incarico = get_post_meta(get_the_ID(), $prefix . 'rag_incarico', true);
    $rag_incarico = !empty($rag_incarico) ? esc_html($rag_incarico) : 'Non specificato';

    $ragione_sociale = get_post_meta(get_the_ID(), $prefix . 'ragione_sociale', true);
    $codice_fiscale = get_post_meta(get_the_ID(), $prefix . 'codice_fiscale', true);

    $ragione_sociale = !empty($ragione_sociale) ? esc_html($ragione_sociale) : 'Non specificato';
    $codice_fiscale = !empty($codice_fiscale) ? esc_html($codice_fiscale) : 'Non specificato';

    $documenti = get_post_meta(get_the_ID(), $prefix . 'allegati', true);
    if (!is_array($documenti)) {
        $documenti = [];
    }
?>

<div class="container" id="main-container">
    <div class="row">
        <div class="col px-lg-4">
            <?php get_template_part("template-parts/common/breadcrumb"); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 px-lg-4 py-lg-2">
            <h1 data-audio>
                <?php
                $title = get_the_title();
                echo preg_match('/[A-Z]{5,}/', $title) ? ucfirst(strtolower($title)) : esc_html($title);
                ?>
            </h1>
            <h2 class="visually-hidden" data-audio>Dettagli incarichi di Concessione</h2>

            <?php if (!empty($descrizione_breve)) : ?>
                <p data-audio>
                    <?php echo preg_match('/[A-Z]{5,}/', $descrizione_breve) ? ucfirst(strtolower($descrizione_breve)) : esc_html($descrizione_breve); ?>
                </p>
            <?php endif; ?>
        </div>
        <div class="col-lg-3 offset-lg-1">
            <?php
            $inline = true;
            get_template_part('template-parts/single/actions');
            ?>
        </div>
    </div>

    <div class="row mt-5 mb-4">
        <div class="col-6">
            <small>Data Pubblicazione:</small>
            <p class="fw-semibold font-monospace"><?php echo esc_html($data); ?></p>
        </div>
        <div class="col-6">
            <small>Anno Beneficio:</small>
            <p class="fw-semibold font-monospace"><?php echo esc_html($anno_beneficio); ?></p>
        </div>
    </div>
</div>

<div class="container">
    <div class="row border-top border-light row-column-border row-column-menu-left">
        <aside class="col-lg-4">
            <nav class="navbar it-navscroll-wrapper navbar-expand-lg" aria-label="Indice della pagina" data-bs-navscroll>
                <div class="navbar-custom" id="navbarNavProgress">
                    <div class="menu-wrapper">
                        <div class="link-list-wrapper">
                            <div class="accordion">
                                <div class="accordion-item">
                                    <span class="accordion-header" id="accordion-title-one">
                                        <button class="accordion-button pb-10 px-3 text-uppercase" type="button"
                                            aria-controls="collapse-one" aria-expanded="true"
                                            data-bs-toggle="collapse" data-bs-target="#collapse-one">
                                            INDICE DELLA PAGINA
                                            <svg class="icon icon-sm icon-primary align-top">
                                                <use xlink:href="#it-expand"></use>
                                            </svg>
                                        </button>
                                    </span>
                                    <div class="progress">
                                        <div class="progress-bar it-navscroll-progressbar" role="progressbar"
                                            aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                    <div id="collapse-one" class="accordion-collapse collapse show"
                                        role="region" aria-labelledby="accordion-title-one">
                                        <div class="accordion-body">
                                            <ul class="link-list" data-element="page-index">
                                                <?php if (!empty($descrizione_breve)) : ?>
                                                    <li class="nav-item"><a class="nav-link" href="#desc-breve"><span class="title-medium">Descrizione Breve</span></a></li>
                                                <?php endif; ?>
                                                <?php if ($ragione_sociale !== 'Non specificato' || $codice_fiscale !== 'Non specificato') : ?>
                                                    <li class="nav-item"><a class="nav-link" href="#beneficiario"><span class="title-medium">Beneficiario</span></a></li>
                                                <?php endif; ?>
                                                <?php if ($importo !== 'Non specificato') : ?>
                                                    <li class="nav-item"><a class="nav-link" href="#importo"><span class="title-medium">Importo</span></a></li>
                                                <?php endif; ?>
                                                <?php if ($importo_liquidato_numeric > 0) : ?>
                                                    <li class="nav-item"><a class="nav-link" href="#liquidato"><span class="title-medium">Importo Liquidato</span></a></li>
                                                <?php endif; ?>
                                                <li class="nav-item"><a class="nav-link" href="#more-info"><span class="title-medium">Ulteriori informazioni</span></a></li>
                                                <?php if (!empty($documenti)) : ?>
                                                    <li class="nav-item"><a class="nav-link" href="#documenti"><span class="title-medium">Documenti</span></a></li>
                                                <?php endif; ?>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </nav>
        </aside>

        <section class="col-lg-8 it-page-sections-container border-light mb-5">
            <?php if (!empty($descrizione_breve)) : ?>
                <article class="it-page-section anchor-offset" data-audio>
                    <h4 id="desc-breve">Descrizione Breve</h4>
                    <div class="richtext-wrapper lora">
                        <?php echo preg_match('/[A-Z]{5,}/', $descrizione_breve) ? ucfirst(strtolower($descrizione_breve)) : esc_html($descrizione_breve); ?>
                    </div>
                </article>
            <?php endif; ?>

            <?php if ($ragione_sociale !== 'Non specificato' || $codice_fiscale !== 'Non specificato') : ?>
                <article class="it-page-section anchor-offset mt-5">
                    <h4 id="beneficiario">Beneficiario</h4>
                    <div class="card card-border-top mb-0">
                        <p class="mb-0" style="text-align:justify"><strong>Ragione Sociale: </strong> <?php echo $ragione_sociale; ?></p>
                        <p class="mb-0" style="text-align:justify"><strong>Codice Fiscale: </strong> <?php echo $codice_fiscale; ?></p>
                    </div>
                </article>
            <?php endif; ?>

            <?php if ($importo !== 'Non specificato') : ?>
                <section class="it-page-section mb-3">
                    <h5 id="importo">Importo</h5>
                    <div class="richtext-wrapper lora" data-element="service-cost"><?php echo $importo; ?></div>
                </section>
            <?php endif; ?>

            <?php if ($importo_liquidato_numeric > 0) : ?>
                <section class="it-page-section mb-3">
                    <h5 id="liquidato">Importo Liquidato</h5>
                    <div class="richtext-wrapper lora" data-element="service-cost">
                        <?php echo number_format($importo_liquidato_numeric, 2, ',', '.') . '€'; ?>
                    </div>
                </section>
            <?php endif; ?>

            <section class="it-page-section mb-3">
                <h5 id="more-info">Ulteriori informazioni</h5>
                <div class="card card-border-top mb-0">
                    <p class="mb-0" style="text-align:justify"><strong>Ragione dell'incarico: </strong> <?php echo $rag_incarico; ?></p>
                    <p class="mb-0" style="text-align:justify"><strong>Responsabile: </strong> <?php echo $responsabile; ?></p>
                </div>
            </section>

            <?php if (!empty($documenti)) : ?>
                <article class="it-page-section anchor-offset mt-5">
                    <h4 id="documenti">Documenti</h4>
                    <div class="card-wrapper card-teaser-wrapper card-teaser-wrapper-equal">
                        <?php
                        foreach ($documenti as $file_url) {
                            $file_id = attachment_url_to_postid($file_url);
                            $allegato = get_post($file_id);

                            if ($allegato) {
                                $title_allegato = $allegato->post_title;
                                if (strlen($title_allegato) > 50) {
                                    $title_allegato = substr($title_allegato, 0, 50) . '...';
                                }
                                $title_allegato = preg_match('/[A-Z]{5,}/', $title_allegato) ? ucfirst(strtolower($title_allegato)) : esc_html($title_allegato);
                        ?>
                                <div class="card card-teaser shadow-sm p-4 mt-3 rounded border border-light flex-nowrap">
                                    <svg class="icon" aria-hidden="true">
                                        <use xlink:href="#it-clip"></use>
                                    </svg>
                                    <div class="card-body">
                                        <h5 class="card-title">
                                            <a class="text-decoration-none" href="<?php echo esc_url($file_url); ?>"
                                               aria-label="Scarica l'allegato <?php echo esc_attr($allegato->post_title); ?>"
                                               title="Scarica l'allegato <?php echo esc_attr($allegato->post_title); ?>"
                                               target="_blank" rel="noopener noreferrer">
                                               <?php echo esc_html($title_allegato); ?>
                                            </a>
                                        </h5>
                                    </div>
                                </div>
                        <?php
                            }
                        }
                        ?>
                    </div>
                </article>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php get_template_part("template-parts/common/valuta-servizio"); ?>
<?php get_template_part("template-parts/common/assistenza-contatti"); ?>

</main>

<?php
endwhile;
get_footer();
?>

