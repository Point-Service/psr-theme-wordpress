<?php
$enabled_raw = dci_get_option( 'ck_accessibility_toolbar', 'dci_options', 'true' );
$enabled     = filter_var( $enabled_raw, FILTER_VALIDATE_BOOLEAN );

if ( ! $enabled ) {
    return;
}
?>
<div class="dci-a11y" data-dci-a11y>
    <button type="button" class="dci-a11y-toggle" data-a11y-action="toggle" aria-expanded="false" aria-controls="dci-a11y-panel">Accessibilità</button>
    <div id="dci-a11y-panel" class="dci-a11y-panel" hidden>
        <button type="button" class="dci-a11y-btn" data-a11y-action="font-up">A+</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="font-down">A-</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="read">Leggi</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="cursor">Cursore</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="links">Link</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="dyslexia">Dislessia</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="reset">Reset</button>
    </div>
</div>
