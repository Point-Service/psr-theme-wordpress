<?php
$link_evidenza = dci_get_option('titolari_incarico_link_evidenza', 'trasparenza', array());

if (!is_array($link_evidenza) || empty($link_evidenza)) {
    return;
}

$link_evidenza = array_filter($link_evidenza, static function ($link) {
    return is_array($link)
        && !empty(trim((string) ($link['titolo'] ?? '')))
        && !empty(trim((string) ($link['url'] ?? '')));
});

if (empty($link_evidenza)) {
    return;
}
?>

<section class="dci-titolari-link-evidenza mb-5" aria-labelledby="dci-titolari-link-evidenza-title">
    <h3 id="dci-titolari-link-evidenza-title" class="h4 mb-3">
        <?php esc_html_e('Collegamenti in evidenza', 'design_comuni_italia'); ?>
    </h3>

    <div class="row g-4">
        <?php foreach ($link_evidenza as $link) :
            $titolo = isset($link['titolo']) ? trim((string) $link['titolo']) : '';
            $descrizione = isset($link['descrizione']) ? trim((string) $link['descrizione']) : '';
            $url = isset($link['url']) ? trim((string) $link['url']) : '';

            $is_external = !dci_is_internal_portal_url($url);
            ?>
            <div class="col-12 col-md-6">
                <article class="card h-100 rounded-3 shadow-sm border">
                    <div class="card-body d-flex flex-column">
                        <h4 class="h5 card-title mb-2"><?php echo esc_html($titolo); ?></h4>

                        <?php if ('' !== $descrizione) : ?>
                            <p class="card-text mb-3"><?php echo esc_html($descrizione); ?></p>
                        <?php endif; ?>

                        <a class="fw-semibold mt-auto"
                           href="<?php echo esc_url($url); ?>"
                           <?php if ($is_external) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>>
                            <?php esc_html_e('Vai al collegamento', 'design_comuni_italia'); ?>
                            <?php if ($is_external) : ?>
                                <span class="visually-hidden"><?php esc_html_e('(si apre in una nuova scheda)', 'design_comuni_italia'); ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </article>
            </div>
        <?php endforeach; ?>
    </div>
</section>
