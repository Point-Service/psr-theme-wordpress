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
/* ===== STRISCIA ===== */
.strip {
  position: relative;
  background: var(--main-color);
  transform: skewY(-3deg);
  margin: 80px 0;
  padding: 60px 0;
  z-index: 1;
  overflow: visible !important;
}

/* ===== CONTENUTO DRITTO ===== */
.strip-inner {
  transform: skewY(3deg);
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 80px;
  max-width: 1200px;
  margin: auto;
  color: #fff;
  text-align: center;
  position: relative;
  z-index: 2;
}

/* ===== BLOCCO SINGOLO ===== */
.item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-width: 180px;
}

/* ===== ICONA ===== */
.icon {
  font-size: 40px;
  margin-bottom: 12px;
  height: 50px; /* allineamento perfetto */
  display: flex;
  justify-content: center;
  align-items: center;
}

/* ===== TESTO ===== */
.text strong {
  display: block;
  font-size: 16px;
  margin-bottom: 5px;
}

.text {
  font-size: 13px;
  opacity: 0.9;
}

/* ===== LINK ===== */
.strip a {
  color: #fff;
  text-decoration: none;
}

.strip a:hover {
  color: #f1f1f1;
}

/* ===== OMBRA REALISTICA ===== */
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

/* ===== FIX WORDPRESS ===== */
.elementor-section,
.wp-block-group {
  overflow: visible !important;
}

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
  .strip-inner {
    flex-direction: column;
    gap: 30px;
  }

  .item {
    min-width: auto;
  }
}
</style><br>
