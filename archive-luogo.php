<?php
/**
 * The template for displaying archive
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Design_Comuni_Italia
 */

global $obj, $the_query, $load_posts, $load_card_type, $luogo, $additional_filter, $title, $description, $data_element, $hide_tipi, $sup;

$max_posts = isset($_GET['max_posts']) ? $_GET['max_posts'] : 100;
$load_posts = 3;
$query = isset($_GET['search']) ? $_GET['search'] : null;
$args = array(
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type'      => 'luogo',
    'tipo_luogo' => $obj->slug,
    'orderby'        => 'post_title',
    'order'          => 'ASC',
  
);

// Esegui la query
$the_query = new WP_Query( $args );
$luoghi = $the_query->posts;

// Filtri aggiuntivi, se necessari
$additional_filter = array();
$additional_filter['tipi_luogo'] = isset($obj->slug) ? $obj->slug : ''; 

get_header();
?>

sssssssssss
 <main>
    <?php 
      $title = isset($obj->name) ? $obj->name : 'Titolo'; 
      $description = isset($obj->description) ? $obj->description : 'Descrizione';
      $data_element = 'data-element="page-name"';
      get_template_part("template-parts/hero/hero"); 
      $amministrazione = dci_get_related_unita_amministrative();
    ?>
  
    <div class="bg-grey-card">
      <form role="search" id="search-form" method="get" class="search-form">
          <button type="submit" class="d-none"></button>
           <div class="container">
              <div class="row ">
                <h2 class="visually-hidden">Esplora tutti i luoghi</h2>
                <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                  <div class="cmp-input-search">
                    <div class="form-group autocomplete-wrapper mb-2 mb-lg-4">
                      <div class="input-group">
                        <label for="autocomplete-two" class="visually-hidden">Cerca una parola chiave</label>
                        <input type="search" 
                          class="autocomplete form-control" 
                          placeholder="Cerca una parola chiave"
                          id="autocomplete-two"
                          name="search"
                          value="<?php echo esc_attr($query); ?>"
                          data-bs-autocomplete="[]">
                        <div class="input-group-append">
                          <button class="btn btn-primary" type="submit" id="button-3">Invio</button>
                        </div>
                        <span class="autocomplete-icon" aria-hidden="true">
                          <svg class="icon icon-sm icon-primary" role="img" aria-labelledby="autocomplete-label"><use href="#it-search"></use></svg>
                        </span>
                      </div>
                    </div>
                    <p id="autocomplete-label" class="mb-4"><strong><?php echo $the_query->found_posts; ?> </strong>luoghi trovati in ordine alfabetico</p>
                  </div>
                  <?php  
                    if($luoghi!=null){
                    foreach ($luoghi as $luogo) { ?>
                      <div id="load-more">
                        <?php
                          $load_card_type = "tipi_luogo"; 
                          get_template_part("template-parts/luogo/card-taxonomy");?>
                      </div>
                    <?php } }?>
                  </div>
                  <?php get_template_part("template-parts/search/more-results"); ?>
                </div>
                <div class="col-12 col-lg-4 pt-50 pb-30 pt-lg-5 ps-lg-5">
    <div class="link-list-wrap">
        <h2 class="title-xsmall-semi-bold"><span>Pagine Correlate</span></h2>
        <ul class="link-list t-primary">
            <li>
                <a class="list-item ps-0 text-button-xs-bold d-flex align-items-center text-decoration-none" href="<?php echo get_permalink( get_page_by_path( 'vivere-il-comune' ) ); ?>">
                    <span class="mr-10">VAI ALLA PAGINA VIVERE IL COMUNE</span>
                    <svg class="icon icon-xs icon-primary">
                        <use href="#it-arrow-right"></use>
                    </svg>
                </a>
            </li>
        </ul>
    </div>
</div>  
              </div>
            </div>
      </form>
    </div>
  </main>
<?php get_footer(); ?>
