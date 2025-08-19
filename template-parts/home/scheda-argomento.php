<?php
global $argomento_full, $count, $sito_tematico_id;

$argomento = get_term_by('slug', $argomento_full['argomento_'.$count.'_argomento'], 'argomenti');

$icon = dci_get_term_meta('icona', "dci_term_", $argomento->term_id);

if (isset($argomento_full['argomento_'.$count.'_siti_tematici']))
  $sito_tematico_id = $argomento_full['argomento_'.$count.'_siti_tematici'];
if (isset($argomento_full['argomento_'.$count.'_contenuti']))
  $links = $argomento_full['argomento_'.$count.'_contenuti'];
?>

<div class="card card-teaser no-after rounded shadow-sm border border-light" style="overflow:hidden;">
  <div class="card-body pb-4">

    <!-- card head -->
    <div class="category-top d-flex align-items-center mb-2" style="text-align:left;">
      <svg class="icon text-primary" style="width:22px; height:22px; display:block; margin-left:0; margin-right:8px;">
        <use xlink:href="#it-bookmark"></use>
      </svg>
      <h3 class="card-title title-xlarge-card mb-0" style="font-size:1.3rem; font-weight:600;">
        <?php echo $argomento->name ?>
      </h3>
    </div>

    <!-- badge -->
    <div class="mb-3">
      <span style="background-color:#eef2f7; color:#495057; font-size:0.75rem; padding:0.25rem 0.6rem; border-radius:0.5rem;">
        Argomento • <?php echo isset($links) && is_array($links) ? count($links) . " link" : "0 link"; ?>
      </span>
    </div>

    <p class="card-text text-muted" style="font-size:0.95rem; line-height:1.4;">
      <?php echo $argomento->description ?>
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
      <div class="link-list-wrapper mt-5 mb-5">
        <ul class="link-list" style="padding-left:0; list-style:none; margin:0;">
          <?php foreach ($links as $link_id) { 
            $link_obj = get_post($link_id);
          ?>
  
            <li class="mb-2" style="margin-bottom:8px;">
              <a class="list-item icon-left d-flex align-items-center"
                 href="<?php echo get_permalink(intval($link_id)); ?>"
                 style="padding:8px 12px; border:1px solid #e9ecef; border-radius:6px; background-color:#fff; text-decoration:none; box-shadow:0 1px 2px rgba(0,0,0,0.05); transition:background-color 0.2s;">
                <svg class="icon text-secondary me-2" style="width:18px; height:18px; margin-right:6px;">
                  <use xlink:href="#it-link"></use>
                </svg>
                <span style="font-size:0.95rem; color:#212529;"><?php echo $link_obj->post_title; ?></span>
              </a>
            </li>
          <?php } ?>
        </ul>
      </div>
    <?php } ?>

  </div>

  <!-- footer -->
    <a class="read-more d-flex align-items-center justify-content-between"
       href="<?php echo get_term_link(intval($argomento->term_id), 'argomenti'); ?>"
       style="display:flex; align-items:center; justify-content:space-between; text-decoration:none; font-weight:500; padding:8px 12px; border:1px solid #e9ecef; border-radius:6px; background-color:#fff; margin:20px 12px 12px 12px; box-shadow:0 1px 2px rgba(0,0,0,0.05);">
      <span class="text" style="font-size:0.95rem; color:#0d6efd;">Esplora argomento</span>
      <svg class="icon" style="width:18px; height:18px; margin-left:6px; fill:#0d6efd;">
        <use xlink:href="#it-arrow-right"></use>
      </svg>
    </a>

</div>

<?php
$sito_tematico_id = null;
