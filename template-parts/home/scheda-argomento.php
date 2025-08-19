<div class="card card-teaser no-after rounded shadow-sm border border-light" style="overflow:hidden;">

  <div class="card-body pb-4">

    <!-- card head -->
    <div class="d-flex align-items-center mb-3">
      <svg class="icon text-primary" style="width:22px; height:22px; margin-right:8px;">
        <use xlink:href="#it-bookmark"></use>
      </svg>
      <h3 class="card-title title-xlarge-card mb-0" style="font-size:1.3rem; font-weight:600;">
        <?php echo $argomento->name?>
      </h3>
    </div>

    <p class="card-text text-muted" style="font-size:0.95rem; line-height:1.4;">
      <?php echo $argomento->description?>
    </p>

    <!-- sito tematico -->
    <?php if($sito_tematico_id) { ?>
      <p class="card-text pb-3 mt-3 fw-bold">🌐 Visita il sito:</p>
      <?php 
        $custom_class = "no-after mt-0";
        get_template_part("template-parts/sito-tematico/card_argomento");
      ?>
    <?php } ?>

    <!-- links -->
    <?php if(isset($links) && is_array($links) && count($links)) { ?>
      <div class="link-list-wrapper mt-4">
        <ul class="link-list">
          <?php foreach ($links as $link_id) { 
              $link_obj = get_post($link_id);
          ?>
          <li class="mb-2">
            <a class="list-item icon-left d-flex align-items-center px-3 py-2 rounded shadow-sm border border-light bg-light-hover" 
               href="<?php echo get_permalink(intval($link_id)); ?>"
               style="transition: all 0.2s;">
              <svg class="icon text-secondary me-2" style="width:18px; height:18px;">
                <use xlink:href="#it-link"></use>
              </svg>
              <span class="list-item-title-icon-wrapper">
                <span style="font-size:0.95rem;"><?php echo $link_obj->post_title; ?></span>
              </span>
            </a>
          </li>
          <?php } ?>
        </ul>
      </div>
    <?php } ?>

  </div>

  <!-- footer -->
  <a class="read-more pt-0 d-flex align-items-center justify-content-between px-3 py-2 bg-light border-top" 
     href="<?php echo get_term_link(intval($argomento->term_id),'argomenti'); ?>"
     style="text-decoration:none; font-weight:500; transition:background 0.2s;">
    <span class="text">Esplora argomento</span>
    <svg class="icon text-primary" style="width:18px; height:18px;">
      <use xlink:href="#it-arrow-right"></use>
    </svg>
  </a>

</div>

<?php
$sito_tematico_id = null;
