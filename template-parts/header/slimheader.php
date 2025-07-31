<div class="it-header-slim-wrapper">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="it-header-slim-wrapper-content d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center py-2">

          <!-- Nome Regione -->
          <a class="d-lg-block navbar-brand text-decoration-none"
             href="<?php echo esc_url(dci_get_option("url_sito_regione")); ?>"
             target="_blank"
             rel="noopener"
             aria-label="Vai al portale <?php echo esc_attr(dci_get_option("nome_regione")); ?> - link esterno - apertura nuova scheda"
             title="Vai al portale <?php echo esc_attr(dci_get_option("nome_regione")); ?>">
            <?php echo esc_html(dci_get_option("nome_regione")); ?>
          </a>

          <!-- Right Zone -->
          <div class="it-header-slim-right-zone d-flex flex-column flex-lg-row align-items-start align-items-lg-center" role="navigation">

            <!-- Amministrazione trasparente -->
            <?php if (dci_get_option("link_ammtrasparente")) : ?>
              <div class="it-user-wrapper nav-item dropdown me-3">
                <a class="d-lg-block navbar-brand text-decoration-none"
                   href="<?php echo esc_url(dci_get_option("link_ammtrasparente")); ?>"
                   target="_blank"
                   rel="noopener"
                   aria-label="Amministrazione trasparente - link esterno">
                  Amministrazione trasparente
                </a>
              </div>
            <?php endif; ?>

            <!-- Albo Pretorio -->
            <?php if (dci_get_option("link_albopretorio")) : ?>
              <div class="it-user-wrapper nav-item dropdown me-3">
                <a class="d-lg-block navbar-brand text-decoration-none"
                   href="<?php echo esc_url(dci_get_option("link_albopretorio")); ?>"
                   target="_blank"
                   rel="noopener"
                   aria-label="Albo pretorio - link esterno">
                  Albo pretorio
                </a>
              </div>
            <?php endif; ?>

            <!-- Google Translator -->
            <?php
            $shortcode_output = do_shortcode('[google-translator]');
            if (trim($shortcode_output) !== '[google-translator]') {
              echo '<div class="nav-item me-3">' . $shortcode_output . '</div>';
            }
            ?>

            <!-- Login/Utente -->
            <div class="nav-item">
              <?php
              if (!is_user_logged_in()) {
                get_template_part("template-parts/header/header-anon");
              } else {
                get_template_part("template-parts/header/header-logged");
              }
              ?>
            </div>

          </div><!-- /it-header-slim-right-zone -->

        </div><!-- /it-header-slim-wrapper-content -->
      </div>
    </div>
  </div>
</div>


