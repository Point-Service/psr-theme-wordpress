<?php
GLOBAL $title, $title;

$categoria_genitore = get_terms('tipi_cat_amm_trasp', array(
    'hide_empty' => false,
    'field' => 'slug',
    'name' => $title
));

if ( ! empty( $categoria_genitore ) && ! is_wp_error( $categoria_genitore ) ) {
    $parent_term_id = $categoria_genitore[0]->term_id; 

    $sottocategorie = get_terms('tipi_cat_amm_trasp', array(
        'hide_empty' => false, 
        'parent' => $parent_term_id
    ));

    // 👉 FILTRO VISIBILITÀ
    $sottocategorie = array_filter($sottocategorie, function($term) {
        return get_term_meta($term->term_id, 'visualizza_elemento', true) == 1;
    });

    // 👉 ORDINAMENTO (già che ci siamo lo mettiamo)
    if (!empty($sottocategorie)) {
        usort($sottocategorie, function($a, $b) {

            $a_ord = (int) get_term_meta($a->term_id, 'ordinamento', true);
            $b_ord = (int) get_term_meta($b->term_id, 'ordinamento', true);

            if ($a_ord === $b_ord) {
                return strcmp($a->name, $b->name);
            }

            return $a_ord <=> $b_ord;
        });
    }
}

// Verifica se ci sono sottocategorie
if ( ! empty( $sottocategorie ) ) { ?>
<div class="container py-5" id="categorie">
    <div class="row g-2">           
        <?php foreach ( $sottocategorie as $sottocategoria ) { 
        if($title != $sottocategoria->name){
            ?>
            <div class="col-md-3 col-xl-4">
                <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
                  <div class="card shadow-sm rounded">
                    <div class="card-body">
                        <a class="text-decoration-none" href="<?php echo get_term_link($sottocategoria->term_id); ?>">
                            <h4 class="card-title t-primary title-xlarge">
                                <?php echo ucfirst($sottocategoria->name); ?>
                            </h4>
                        </a>
                        <p class="titillium text-paragraph mb-0 description">
                            <?php echo $sottocategoria->description; ?>
                        </p>
                    </div>
                  </div>
                </div>
            </div>
        <?php } } ?>
    </div>
</div>
<?php } ?>


