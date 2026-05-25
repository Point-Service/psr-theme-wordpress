<?php
$enabled_raw = dci_get_option( 'ck_accessibility_toolbar', 'dci_options', 'true' );
$enabled     = filter_var( $enabled_raw, FILTER_VALIDATE_BOOLEAN );

if ( ! $enabled ) {
    return;
}
?>
<div class="dci-a11y" data-dci-a11y>
    <button type="button" class="dci-a11y-toggle" data-a11y-action="toggle" aria-expanded="false" aria-controls="dci-a11y-panel">⚙️ Accessibilità</button>
    <div id="dci-a11y-panel" class="dci-a11y-panel" hidden>
        <button type="button" class="dci-a11y-btn" data-a11y-action="font-up"><span class="dci-a11y-ico">🔎</span><span>Aumenta testo</span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="font-down"><span class="dci-a11y-ico">🔍</span><span>Riduci testo</span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="line-height" data-level-control="1"><span class="dci-a11y-ico">↕️</span><span>Interlinea</span><span class="dci-a11y-level" aria-hidden="true"><i></i><i></i><i></i></span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="letter-spacing" data-level-control="1"><span class="dci-a11y-ico">↔️</span><span>Spaziatura</span><span class="dci-a11y-level" aria-hidden="true"><i></i><i></i><i></i></span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="readable-font"><span class="dci-a11y-ico">🔤</span><span>Font leggibile</span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="dyslexia"><span class="dci-a11y-ico">🅰️</span><span>Font dislessia</span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="cursor"><span class="dci-a11y-ico">🖱️</span><span>Cursore</span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="keyboard"><span class="dci-a11y-ico">⌨️</span><span>Tastiera</span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="links"><span class="dci-a11y-ico">🔗</span><span>Evidenzia link</span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="highlight-all"><span class="dci-a11y-ico">💡</span><span>Evidenzia tutto</span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="highlight-titles"><span class="dci-a11y-ico">🏷️</span><span>Evidenzia titoli</span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="hide-images"><span class="dci-a11y-ico">🖼️</span><span>Nascondi immagini</span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="invert"><span class="dci-a11y-ico">◐</span><span>Inverti colori</span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="brightness" data-level-control="1"><span class="dci-a11y-ico">☀️</span><span>Luminosità</span><span class="dci-a11y-level" aria-hidden="true"><i></i><i></i><i></i></span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="contrast" data-level-control="1"><span class="dci-a11y-ico">◑</span><span>Contrasto</span><span class="dci-a11y-level" aria-hidden="true"><i></i><i></i><i></i></span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="grayscale" data-level-control="1"><span class="dci-a11y-ico">◻️</span><span>Scala di grigi</span><span class="dci-a11y-level" aria-hidden="true"><i></i><i></i><i></i></span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="saturation" data-level-control="1"><span class="dci-a11y-ico">🎨</span><span>Saturazione</span><span class="dci-a11y-level" aria-hidden="true"><i></i><i></i><i></i></span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="read"><span class="dci-a11y-ico">🔊</span><span>Lettura vocale</span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="mute"><span class="dci-a11y-ico">🔇</span><span>Disattiva suoni</span></button>
        <button type="button" class="dci-a11y-btn" data-a11y-action="stop-animations"><span class="dci-a11y-ico">⏹️</span><span>Ferma animazioni</span></button>
        <button type="button" class="dci-a11y-btn dci-a11y-btn-reset" data-a11y-action="reset"><span class="dci-a11y-ico">♻️</span><span>Ripristina</span></button>
    </div>
</div>
