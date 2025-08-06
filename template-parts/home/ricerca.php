<?php
$links = dci_get_option('link', 'link_utili');
?>

<section id="link-utili" class="useful-links-section bg-light py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <h2 class="mb-4 text-primary text-uppercase border-bottom pb-2">Link utili</h2>

                <!-- Ricerca -->
                <form role="search" method="get" class="search-form mb-4" action="<?php echo esc_url(home_url('/')); ?>">
                    <div class="input-group shadow-sm">
                        <input type="search" class="form-control" placeholder="Cerca una parola chiave" name="s" value="<?php echo get_search_query(); ?>" aria-label="Cerca una parola chiave">
                        <button class="btn btn-primary" type="submit">
                            <svg class="icon icon-sm me-1" aria-hidden="true">
                                <use href="#it-search"></use>
                            </svg>
                            Cerca
                        </button>
                    </div>
                </form>

                <!-- Lista link -->
                <?php if ($links) { ?>
                    <ul class="list-group list-group-flush shadow-sm border rounded">
                        <?php foreach ($links as $link) { ?>
                            <li class="list-group-item d-flex align-items-center justify-content-between border-bottom">
                                <div class="d-flex align-items-center">
                                    <svg class="icon icon-primary me-2" aria-hidden="true">
                                        <use href="#it-link"></use>
                                    </svg>
                                    <a href="<?php echo esc_url($link['url']); ?>" class="text-decoration-none fw-semibold link-primary">
                                        <?php echo esc_html($link['testo']); ?>
                                    </a>
                                </div>
                                <svg class="icon icon-sm text-secondary" aria-hidden="true">
                                    <use href="#it-external-link"></use>
                                </svg>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p class="text-muted">Nessun link disponibile al momento.</p>
                <?php } ?>
            </div>
        </div>
    </div>
</section>

