<section class="strip">
  <div class="strip-inner">
    
    <div class="item">
      <div class="icon">&#128196;</div>
      <div class="text">
        <strong>Albo pretorio online</strong><br>
        Art.32 Legge 69/2009
      </div>
    </div>

    <div class="item">
      <div class="icon">&#128216;</div>
      <div class="text">
        <strong>Atti del Comune</strong><br>
        Delibere, Determine e Ordinanze
      </div>
    </div>

    <div class="item">
      <div class="icon">&#127963;&#65039;</div>
      <div class="text">
        <strong>Amministrazione Trasparente</strong><br>
        D.Lgs. 33/2013
      </div>
    </div>

    <div class="item">
      <div class="icon">&#8596;&#65039;</div>
      <div class="text">
        <strong>ANAC - Contratti pubblici</strong><br>
        Art.37 D.Lgs. 33/2013
      </div>
    </div>

  </div>
</section>
<style>
.strip {
  position: relative;
  background: var(--bs-primary); /* 🔥 prende il colore ufficiale */
  transform: skewY(-3deg);
  margin: 80px 0;
  padding: 60px 0;
  z-index: 1;
  overflow: visible !important;
}

/* CONTENUTO DRITTO */
.strip-inner {
  transform: skewY(3deg);
  display: flex;
  justify-content: space-around;
  align-items: center;
  max-width: 1200px;
  margin: auto;
  color: #fff;
  text-align: center;
  position: relative;
  z-index: 2;
}

/* ELEMENTI */
.item {
  width: 22%;
}

.icon {
  font-size: 40px;
  margin-bottom: 10px;
}

.text strong {
  display: block;
  font-size: 16px;
}

.text {
  font-size: 13px;
  opacity: 0.9;
}

/* 🔥 OMBRA */
.strip::after {
  content: "";
  position: absolute;
  left: 10%;
  right: 10%;
  bottom: -25px;
  height: 30px;
  background: rgba(0,0,0,0.3);
  filter: blur(15px);
  border-radius: 100px;
  pointer-events: none;
}

/* FIX WORDPRESS */
.elementor-section,
.wp-block-group {
  overflow: visible !important;
}
</style><br>
