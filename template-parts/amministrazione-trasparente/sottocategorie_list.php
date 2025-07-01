<?php
// Prendi il parametro 'title' dalla query string
$title = isset($_GET['title']) ? sanitize_text_field($_GET['title']) : '';

// Recupera la categoria principale (genitore) usando lo slug passato tramite 'title'
$categoria_genitore = get_terms('tipi_cat_amm_trasp', array(
    'hide_empty' => false,
    'field' => 'slug',
    'name' => $title
));

if (!empty($categoria_genitore) && !is_wp_error($categoria_genitore)) {
    $parent_term_id = $categoria_genitore[0]->term_id;

    // Recupera le sottocategorie usando l'ID del genitore
    $sottocategorie = get_terms('tipi_cat_amm_trasp', array(
        'hide_empty' => false,
        'parent' => $parent_term_id
    ));
}
?>

<div class="container py-5">
    <h2 class="title-custom"><?php echo esc_html($categoria_genitore[0]->name); ?></h2>
    <p><?php echo esc_html($categoria_genitore[0]->description); ?></p>

    <?php if (!empty($sottocategorie)) { ?>
        <ul>
            <?php foreach ($sottocategorie as $sottocategoria) { ?>
                <li>
                    <a href="<?php echo get_term_link($sottocategoria->term_id); ?>">
                        <?php echo esc_html($sottocategoria->name); ?>
                    </a>
                    <!-- Se la sottocategoria ha ulteriori sottocategorie (cioè ha dei figli) -->
                    <?php
                    $sotto_sottocategorie = get_terms('tipi_cat_amm_trasp', array(
                        'hide_empty' => false,
                        'parent' => $sottocategoria->term_id
                    ));
                    if (!empty($sotto_sottocategorie)) { ?>
                        <ul>
                            <?php foreach ($sotto_sottocategorie as $sotto_sottocategoria) { ?>
                                <li>
                                    <a href="<?php echo get_term_link($sotto_sottocategoria->term_id); ?>">
                                        <?php echo esc_html($sotto_sottocategoria->name); ?>
                                    </a>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } ?>
                </li>
            <?php } ?>
        </ul>
    <?php } else { ?>
        <p>Non ci sono sottocategorie per questa categoria.</p>
    <?php } ?>
</div>

