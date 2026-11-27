<?php
$links = dci_get_option('link', 'link_utili');
?>

<section id="link-utili" class="useful-links-section bg-grey-card py-5">

  <div class="container">

    <div class="row justify-content-center">

      <div class="col-12 col-lg-10">


        <!-- Titolo sezione -->
        <h2 class="title-xxlarge mb-4">Link utili</h2>


        <!-- Form ricerca -->
        <form role="search"
              method="get"
              class="search-form mb-4"
              action="<?php echo esc_url(home_url('/')); ?>">

          <div class="input-group shadow-sm">

            <input type="search"
                   class="form-control"
                   placeholder="Cerca una parola chiave"
                   name="s"
                   value="<?php echo get_search_query(); ?>"
                   aria-label="Cerca una parola chiave">

            <button class="btn btn-primary" type="submit">

              <svg class="icon icon-sm me-1"
                   aria-hidden="true"
                   style="fill:white;pointer-events:none;">

                <use href="#it-search"></use>

              </svg>

              Cerca

            </button>

          </div>

        </form>


        <!-- Link utili -->
        <?php if ($links) { ?>

          <div class="d-flex flex-wrap gap-3">

            <?php foreach ($links as $link) {
              $link_url = $link['url'] ?? '';
              $link_is_external = !dci_is_internal_portal_url($link_url);
              // Il contenitore esiste soltanto se il comando autorizzato viene emesso.
              $psr_link_edit_html = '';
              if (function_exists('psr_home_tools_edit_row')) {
                  ob_start();
                  psr_home_tools_edit_row('link_utili', $link);
                  $psr_link_edit_html = ob_get_clean();
              }
            ?>
              <?php if ($psr_link_edit_html !== '') { ?><span class="psr-useful-link"><?php } ?>

              <!-- LINK -->
              <a href="<?php echo esc_url($link_url); ?>"
                 <?php if ($link_is_external) : ?>target="_blank" rel="noopener noreferrer"<?php endif; ?>
                 class="d-inline-flex align-items-center gap-2
                        px-3 py-2 border border-light rounded
                        shadow-sm bg-white text-primary
                        text-decoration-none link-hover
                        flex-shrink-0 link-utili-item"
                 style="max-width:100%;">

                <!-- ICONA -->
                <svg class="icon icon-primary"
                     aria-hidden="true"
                     style="min-width:1.2rem;pointer-events:none;">

                  <use href="#it-link"></use>

                </svg>

                <!-- TESTO -->
                <span class="fw-semibold text-truncate"
                      style="max-width:200px;pointer-events:none;">

                  <?php echo esc_html($link['testo']); ?>

                </span>

              </a>
              <?php if ($psr_link_edit_html !== '') { echo $psr_link_edit_html; ?></span><?php } ?>

            <?php } ?>

          </div>

        <?php } else { ?>

          <p class="text-muted">
            Nessun link disponibile al momento.
          </p>

        <?php } ?>


      </div>

    </div>

  </div>

</section>


<style>

/* ===============================
   FIX WEBVIEW CLICK
================================ */

.link-utili-item {
    touch-action: manipulation;
}

/* Disabilita overlay su icone e testo */
.link-utili-item svg,
.link-utili-item span {
    pointer-events: none;
}

/* Link utili: un riquadro visivo, con due collegamenti indipendenti.
   Non applicato ai visitatori o agli utenti privi del comando di modifica. */
#link-utili .psr-useful-link {
    position: relative;
    display: inline-flex;
    align-items: stretch;
    max-width: 100%;
    min-width: 0;
    transition: transform 0.3s ease;
}
#link-utili .psr-useful-link > a.link-utili-item {
    padding-inline-end: 44px !important;
    flex-shrink: 1 !important;
    min-width: 0;
    max-width: 100%;
}
#link-utili .psr-useful-link > a.link-utili-item > .text-truncate {
    min-width: 0;
}
#link-utili .psr-useful-link > .psr-home-item-actions {
    position: absolute;
    inset-inline-end: 8px;
    top: 50%;
    transform: translateY(-50%);
    margin: 0;
    padding: 0;
    z-index: 4;
}
#link-utili .psr-useful-link > a.link-utili-item:hover {
    transform: none;
}
@media (hover: hover) and (pointer: fine) {
    #link-utili .psr-useful-link:hover {
        transform: translateY(-4px);
    }
    #link-utili .psr-useful-link:hover > a.link-utili-item {
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
    }
}
@media (max-width: 576px) {
    #link-utili .psr-useful-link > a.link-utili-item {
        padding-inline-end: 50px !important;
        min-height: 46px;
    }
}
@media (prefers-reduced-motion: reduce) {
    #link-utili .psr-useful-link,
    #link-utili .psr-useful-link > a.link-utili-item {
        transition: none !important;
        transform: none !important;
    }
}

/* Evita hover mobile */
@media (hover:none) {

    .link-utili-item:hover {
        background-color: inherit;
    }
}

</style>

