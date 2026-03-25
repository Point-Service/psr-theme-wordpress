<?php 
  $links = dci_get_option('contenuti','ricerca');
  $unique_id = 'search-' . uniqid();
?>
<!-- Search Modal -->
<div class="modal fade search-modal" id="search-modal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content perfect-scrollbar">
      <div class="modal-body">

        <form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
          <div class="container">

            <!-- HEADER -->
            <div class="row">
              <div class="col">
                <div class="form-group">
                  <div class="input-group">
                    <div class="input-group-prepend">
                      <div class="input-group-text">
                        <svg class="icon icon-md"><use href="#it-search"></use></svg>
                      </div>
                    </div>

                    <input type="search" class="form-control" id="<?php echo $unique_id; ?>" name="s"
                      placeholder="Cerca nel sito" value="<?php echo get_search_query(); ?>" />
                  </div>

                  <button type="submit" class="btn btn-primary mt-2">
                    Cerca
                  </button>
                </div>
              </div>
            </div>

            <!-- CONTENUTO -->
            <div class="row p-4">

              <!-- SINISTRA -->
              <div class="col-lg-5">

                <div class="h4 other-link-title">Ricerche frequenti</div>

                <div class="link-list-wrapper mb-4 scroll-frequenti">
                  <ul class="link-list">

                    <?php
                    $popular_posts = new WP_Query([
                        'post_type' => dci_get_sercheable_tipologie(),
                        'posts_per_page' => 20,
                        'meta_key' => 'views',
                        'orderby' => 'meta_value_num',
                        'order' => 'DESC'
                    ]);

                    if (empty($popular_posts->posts)) {
                        $popular_posts = new WP_Query([
                            'post_type' => dci_get_sercheable_tipologie(),
                            'posts_per_page' => 20,
                            'orderby' => 'date',
                            'order' => 'DESC',
                        ]);
                    }

                    if (!empty($popular_posts->posts)) {
                      foreach ($popular_posts->posts as $post) { ?>
                        <li>
                          <a class="list-item active large py-1 icon-left" href="<?php echo get_permalink($post); ?>">
                            <span class="list-item-title-icon-wrapper">
                              <svg class="icon icon-primary icon-sm"><use href="#it-search"></use></svg>
                              <span class="list-item-title"><?php echo get_the_title($post); ?></span>
                            </span>
                          </a>
                        </li>
                    <?php } } else { ?>
                        <li>Nessun risultato</li>
                    <?php } wp_reset_postdata(); ?>

                  </ul>
                </div>

              </div>

              <!-- DESTRA -->
              <div class="col-lg-6">
                <?php
                $argomenti = get_terms([
                    'taxonomy' => 'argomenti',
                    'orderby' => 'count',
                    'order'   => 'DESC',
                    'hide_empty' => 1,
                    'number' => 20
                ]);

                if(!empty($argomenti)) { ?>
                  <div class="badges-wrapper">
                    <div class="h4 other-link-title">Potrebbero interessarti</div>
                    <div class="badges">
                      <?php foreach ($argomenti as $argomento){
                        $taglink = get_tag_link($argomento); ?>
                        <a href="<?php echo $taglink; ?>" class="chip chip-simple chip-lg">
                          <span class="chip-label"><?php echo $argomento->name; ?></span>
                        </a>
                      <?php } ?>
                    </div>
                  </div>
                <?php } ?>
              </div>

            </div>
          </div>
        </form>

      </div>
    </div>
  </div>
</div>

<!-- CSS -->
<style>
.scroll-frequenti {
  max-height: 320px; /* circa 10 elementi */
  overflow-y: auto;
  overflow-x: hidden;
  -webkit-overflow-scrolling: touch;
  padding-right: 5px;
}

/* scrollbar */
.scroll-frequenti::-webkit-scrollbar {
  width: 6px;
}

.scroll-frequenti::-webkit-scrollbar-thumb {
  background: #ccc;
  border-radius: 3px;
}
</style>
