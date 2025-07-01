<?php
// Recupera il parametro "title" dalla query string (lo slug della categoria)
$title = isset($_GET['title']) ? sanitize_text_field($_GET['title']) : '';

// Verifica se c'è un titolo valido
if (!empty($title)) {
    // Recupera la categoria principale usando lo slug
    $categoria_genitore = get_terms('tipi_cat_amm_trasp', array(
        'hide_empty' => false,
        'field' => 'slug',
        'name' => $title
    ));

    if (!empty($categoria_genitore) && !is_wp_error($categoria_genitore)) {
        // Ottieni l'ID del genitore
        $parent_term_id = $categoria_genitore[0]->term_id;

        // Recupera le sottocategorie utilizzando l'ID del genitore
        $sottocategorie = get_terms('tipi_cat_amm_trasp', array(
            'hide_empty' => false,
            'parent' => $parent_term_id
        ));

        // Verifica se ci sono sottocategorie
        if (!empty($sottocategorie)) {
            ?>
            <div class="container py-5" id="categorie">
                <div class="row g-2">
                    <!-- Visualizza la categoria principale -->
                    <div class="col-12">
                        <h2 class="title-custom"><?= esc_html($categoria_genitore[0]->name); ?></h2>
                        <p class="description"><?= esc_html($categoria_genitore[0]->description); ?></p>
                    </div>

                    <!-- Visualizza le sottocategorie -->
                    <?php foreach ($sottocategorie as $sottocategoria) { ?>
                        <div class="col-md-3 col-xl-4">
                            <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
                                <div class="card shadow-sm rounded">
                                    <div class="card-body">
                                        <a class="text-decoration-none" href="<?php echo get_term_link($sottocategoria->term_id); ?>" data-element="news-category-link">
                                            <h3 class="card-title t-primary title-xlarge"><?= ucfirst($sottocategoria->name); ?></h3>
                                        </a>
                                        <p class="titillium text-paragraph mb-0 description">
                                            <?= esc_html($sottocategoria->description); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php
        } else {
            echo "<p>Non ci sono sottocategorie per questa categoria.</p>";
        }
    } else {
        echo "<p>Categoria non trovata.</p>";
    }
} else {
    echo "<p>Categoria non specificata.</p>";
}
?>

