<?php get_header(); ?>

<main id="main">

  <?php
  // =========================
  // STRIP HOME DINAMICA
  // =========================
  $strip = get_option('strip_home');

  if (!empty($strip['strip_items']) && count($strip['strip_items']) > 0) :
  ?>

  <section class="strip">
    <div class="strip-inner scrollable">

      <?php foreach ($strip['strip_items'] as $item) : 
          $target = (!empty($item['blank'])) ? ' target="_blank"' : '';
      ?>

        <div class="item">
          <a href="<?php echo esc_url($item['url']); ?>" <?php echo $target; ?>>

            <div class="icon">
              <?php if (!empty($item['icon'])): ?>
                <i class="<?php echo esc_attr($item['icon']); ?>"></i>
              <?php endif; ?>
            </div>

            <div class="text">
              <strong><?php echo esc_html($item['title']); ?></strong>
              <?php echo esc_html($item['desc']); ?>
            </div>

          </a>
        </div>

      <?php endforeach; ?>

    </div>
  </section>

  <?php endif; ?>


  <!-- =========================
       CONTENUTO NORMALE PAGINA
       ========================= -->

  <div class="container">
    <?php
    if ( have_posts() ) :
      while ( have_posts() ) : the_post();
        the_content();
      endwhile;
    endif;
    ?>
  </div>

</main>

<?php get_footer(); ?>


<style>

/* ===== STRISCIA ===== */
.strip {
  position: relative;
  background: var(--bs-primary, #980847);
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

/* ===== SCROLL AUTOMATICO ===== */
.strip-inner.scrollable {
  overflow-x: auto;
  flex-wrap: nowrap;
  padding-bottom: 10px;
}

/* scrollbar elegante */
.strip-inner.scrollable::-webkit-scrollbar {
  height: 6px;
}

.strip-inner.scrollable::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.3);
  border-radius: 10px;
}

/* ===== BLOCCO ===== */
.item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-width: 180px;
  flex: 0 0 auto;
}

/* ===== ICONA ===== */
.icon {
  font-size: 40px;
  margin-bottom: 12px;
  height: 50px;
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

/* ===== OMBRA ===== */
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
}

</style>
