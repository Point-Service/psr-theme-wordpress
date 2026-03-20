<?php get_header(); ?>

<main id="main">

 <?php
$strip = get_option('strip_home');

if (!empty($strip['strip_items']) && count($strip['strip_items']) > 0) :
?>

<section class="strip">
  
  <button class="strip-arrow left">&#10094;</button>

  <div class="strip-inner" id="stripScroll">

    <?php foreach ($strip['strip_items'] as $item) : 
        $target = (!empty($item['blank'])) ? ' target="_blank"' : '';
    ?>

      <div class="strip-item">
        <a href="<?php echo esc_url($item['url']); ?>" <?php echo $target; ?>>

          <div class="strip-icon">
            <?php if (!empty($item['icon'])): ?>
              <i class="<?php echo esc_attr($item['icon']); ?>"></i>
            <?php endif; ?>
          </div>

          <div class="strip-text">
            <strong><?php echo esc_html($item['title']); ?></strong>
            <?php echo esc_html($item['desc']); ?>
          </div>

        </a>
      </div>

    <?php endforeach; ?>

  </div>

  <button class="strip-arrow right">&#10095;</button>

</section>

<?php endif; ?>

</main>




<style>

/* ===== STRISCIA ===== */
/* ===== STRIP ===== */
.strip {
  position: relative;
  background: var(--bs-primary, #980847);
  transform: skewY(-3deg);
  margin: 80px 0;
  padding: 60px 0;
  z-index: 1;
  overflow: visible !important;
}

/* CONTENUTO */
.strip .strip-inner {
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

/* SCROLL SOLO QUI */
.strip .strip-inner.scrollable {
  overflow-x: auto;
  flex-wrap: nowrap;
  padding-bottom: 10px;
}

/* scrollbar */
.strip .strip-inner.scrollable::-webkit-scrollbar {
  height: 6px;
}

.strip .strip-inner.scrollable::-webkit-scrollbar-thumb {
  background: rgba(255,255,255,0.3);
  border-radius: 10px;
}

/* ITEM */
.strip .strip-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-width: 180px;
  flex: 0 0 auto;
}

/* ICONA */
.strip .strip-icon {
  font-size: 40px;
  margin-bottom: 12px;
  height: 50px;
  display: flex;
  justify-content: center;
  align-items: center;
}

/* TESTO */
.strip .strip-text strong {
  display: block;
  font-size: 16px;
  margin-bottom: 5px;
}

.strip .strip-text {
  font-size: 13px;
  opacity: 0.9;
}

/* LINK */
.strip a {
  color: #fff;
  text-decoration: none;
}

.strip a:hover {
  color: #f1f1f1;
}

/* OMBRA */
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

 /* nasconde scrollbar */
.strip .strip-inner {
  overflow-x: hidden;
  scroll-behavior: smooth;
}

/* frecce */
.strip-arrow {
  position: absolute;
  top: 50%;
  transform: translateY(-50%);
  background: rgba(0,0,0,0.3);
  border: none;
  color: #fff;
  font-size: 24px;
  width: 40px;
  height: 40px;
  border-radius: 50%;
  cursor: pointer;
  z-index: 3;
}

.strip-arrow.left {
  left: 10px;
}

.strip-arrow.right {
  right: 10px;
}

.strip-arrow:hover {
  background: rgba(0,0,0,0.5);
}

/* RESPONSIVE SOLO STRIP */
@media (max-width: 768px) {
  .strip .strip-inner {
    flex-direction: column;
    gap: 30px;
  }
}
</style>

<script>
document.addEventListener("DOMContentLoaded", function() {
  const container = document.getElementById("stripScroll");

  document.querySelector(".strip-arrow.left").onclick = function() {
    container.scrollBy({ left: -300, behavior: 'smooth' });
  };

  document.querySelector(".strip-arrow.right").onclick = function() {
    container.scrollBy({ left: 300, behavior: 'smooth' });
  };
});
</script>
