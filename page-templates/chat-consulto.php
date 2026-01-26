<?php

/**
 * Template Name: Chat Consulto
 * Description: Pagina dedicata alla prenotazione di appuntamenti via videochiamata o vocale con gli uffici comunali, con possibilità di interagire direttamente con un operatore tramite chat.
 *
 * @package Design_Comuni_Italia
 */

get_header();
?>

<main>
    <div class="container" id="main-container">

        <div class="row">
            <div class="col px-lg-4">
                <?php get_template_part("template-parts/common/breadcrumb"); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12 px-lg-4 py-lg-2">

                <h1 class="mb-4">Contatta gli uffici comunali</h1>

                <p>
                    Benvenuto nella pagina dedicata al contatto con gli uffici comunali.
                    Puoi richiedere un incontro tramite <strong>videociamata</strong> o <strong>chiamata vocale</strong>, oppure interagire direttamente con un operatore attraverso la chat.
                </p>

                <p>
                    Per effettuare una videochiamata o una chiamata vocale è necessario prenotare un appuntamento tramite l'apposito modulo.
                    Ti verrà richiesto di fornire i tuoi dati, quali nome e cognome, indirizzo email e un messaggio.
                    Nella sezione "messaggio" dovrai indicare il motivo della prenotazione e il nome dell'ufficio da contattare.
                </p>

                <p>
                    Per accedere alla videochiamata o alla chiamata vocale basterà utilizzare il link fornito nell'email che riceverai all'indirizzo email inserito.
                </p>

                <p>
                    <strong>Attenzione</strong>: l'apertura della chat con un operatore potrebbe richiedere qualche istante.
                    Se non dovesse aprirsi, puoi utilizzare il pulsante nella parte destra della pagina.
                </p>

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
                
                <script>
                (function(){
                
                  var btn = document.getElementById("btn-consolto");
                  if (!btn) return;
                
                  function enable(){
                    btn.disabled = false;
                    btn.style.background = "#0b5ed7";
                    btn.style.color = "#fff";
                    btn.style.cursor = "pointer";
                  }
                
                  function disable(){
                    btn.disabled = true;
                    btn.style.background = "#999";
                    btn.style.color = "#eee";
                    btn.style.cursor = "not-allowed";
                  }
                
                  function getCookie(name) {
                    return document.cookie.split('; ').find(row => row.startsWith(name+'='))?.split('=')[1];
                  }
                
                  function setCookie(name, value, minutes) {
                    var d = new Date();
                    d.setTime(d.getTime() + (minutes*60*1000));
                    document.cookie = name + "=" + value + ";path=/;expires=" + d.toUTCString();
                  }
                
                  // 1) Se arriva con ?ok=1 → setta cookie
                  if (new URLSearchParams(window.location.search).get("ok") === "1") {
                    setCookie("consolto_ok", "1", 10); // valido 10 minuti
                  }
          
                    enable();
             
                
                })();
                </script>


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

