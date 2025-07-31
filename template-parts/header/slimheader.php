<div class="it-header-slim-wrapper">
  <div class="container">
    <div class="row">
      <div class="col-12">
        <div class="it-header-slim-wrapper-content d-flex flex-column flex-lg-row justify-content-between align-items-start align-items-lg-center gap-2">

          <!-- Nome Regione -->
          <a class="navbar-brand text-decoration-none" target="_blank"
             href="<?php echo dci_get_option("url_sito_regione"); ?>"
             aria-label="Vai al portale <?php echo dci_get_option("nome_regione"); ?> - link esterno - apertura nuova scheda"
             title="Vai al portale <?php echo dci_get_option("nome_regione"); ?>">
            <?php echo dci_get_option("nome_regione"); ?>
          </a>

          <!-- Right Zone -->
          <div class="it-header-slim-right-zone d-flex flex-column flex-lg-row align-items-start align-items-lg-center gap-2" role="navigation">

            <!-- Amministrazione Trasparente -->
            <?php if (dci_get_option("link_ammtrasparente")) { ?>
              <div class="it-user-wrapper nav-item">
                <a class="navbar-brand text-decoration-none" target="_blank"
                   href="<?php echo dci_get_option("link_ammtrasparente"); ?>"
                   aria-label="Amministrazione trasparente">
                  Amministrazione trasparente
                </a>
              </div>
            <?php } ?>

            <!-- Albo Pretorio -->
            <?php if (dci_get_option("link_albopretorio")) { ?>
              <div class="it-user-wrapper nav-item">
                <a class="navbar-brand text-decoration-none" target="_blank"
                   href="<?php echo dci_get_option("link_albopretorio"); ?>"
                   aria-label="Albo pretorio">
                  Albo pretorio
                </a>
              </div>
            <?php } ?>

            <!-- Google Translator -->
            <?php
              $shortcode_output = do_shortcode('[google-translator]');
              if ($shortcode_output !== '[google-translator]') {
                echo '<div class="nav-item">' . $shortcode_output . '</div>';
              }
            ?>

            <!-- Login / User -->
            <div class="nav-item">
              <?php
              if (!is_user_logged_in()) {
                get_template_part("template-parts/header/header-anon");
              } else {
                get_template_part("template-parts/header/header-logged");
              }
              ?>
            </div>

          </div><!-- end slim-right-zone -->

        </div><!-- end wrapper-content -->
      </div>
    </div>
  </div>
</div>

