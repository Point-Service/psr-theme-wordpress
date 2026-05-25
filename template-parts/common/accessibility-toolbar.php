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
        <button type="button" class="dci-a11y-btn" data-a11y-action="line-height">Interlinea</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="letter-spacing">Spaziatura</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="readable-font">Readable Font</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="dyslexia">Dislessia</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="cursor">Cursore</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="keyboard">Keyboard</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="links">Highlight Links</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="highlight-all">Highlight All</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="highlight-titles">Highlight Titles</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="hide-images">Hide Images</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="invert">Inverti Colori</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="brightness">Luminosità</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="contrast">Contrasto</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="grayscale">Grigio</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="saturation">Saturazione</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="read">Text to Speech</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="mute">Mute Sounds</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="stop-animations">Stop Animations</button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="reset">Reset</button>
    </div>
</div>
