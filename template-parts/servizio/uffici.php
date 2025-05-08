<?php
    $amministrazione = dci_get_related_unita_amministrative();
?>

<div class="container py-5" id="argomento">
    <h2 class="title-xxlarge mb-4">Uffici</h2>
    <div class="row g-4">       
        <?php foreach ($amministrazione as $item) {         
        ?>
        <div class="col-md-6 col-xl-4">
            <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
              <div class="card shadow-sm rounded">
                <div class="card-body">
                    <a class="text-decoration-none" href="<?php echo $item['link']; ?>" data-element="news-category-link"><h3 class="card-title t-primary title-xlarge"><?php echo $item['title'];?></h3></a>
                </div>
              </div>
            </div>
          </div>
        <?php } ?>
    </div>
</div>
              <?php if ( is_array($amministrazione) && count($amministrazione) ) { ?>
                <div class="col-12 col-lg-4 pt-50 pb-30 pt-lg-5 ps-lg-5">
                  <div class="link-list-wrap">
                    <h2 class="title-xsmall-semi-bold"><span>UFFICI</span></h2>
                    <ul class="link-list t-primary">
                      <?php foreach ($amministrazione as $item) { ?>
                        <li class="mb-3 mt-3">
                          <a class="list-item ps-0 title-medium underline" href="<?php echo $item['link']; ?>">
                            <span><?php echo $item['title']; ?></span>
                          </a>
                        </li>
                      <?php } ?>                      
                      <li>
                        <a class="list-item ps-0 text-button-xs-bold d-flex align-items-center text-decoration-none" href="<?php echo get_permalink( get_page_by_path( 'amministrazione' ) ); ?>">
                          <span class="mr-10">VAI ALL’AREA AMMINISTRATIVA</span>
                          <svg class="icon icon-xs icon-primary">
                            <use href="#it-arrow-right"></use>
                          </svg>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              <?php } ?>
