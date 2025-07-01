<?php
global $sito_tematico_id,$siti_tematici;

// Recupera le categorie principali (genitori)
$categorie_genitori = get_terms('tipi_cat_amm_trasp', array(
    'hide_empty' => false,
    'parent' => 0,  // Le categorie senza genitore
    'orderby' => 'ID',
    'order'   => 'ASC'
));

$siti_tematici = !empty(dci_get_option("siti_tematici", "trasparenza")) ? dci_get_option("siti_tematici", "trasparenza") : [];
?>

<style>
    .title-custom {
        font-size: 18px;
        background-color: rgb(255, 252, 252);
        padding: 10px;
        border: 1px solid #ccc;
        cursor: pointer;
    }

    .content {
        display: none;
        padding: 10px;
    }

    .content a {
        display: block;
        margin: 5px 0;
        color: rgb(17, 17, 17);
        text-decoration: none;
        padding-left: 10px;
    }

    .content a:hover {
        text-decoration: underline;
    }
</style>

<script>
    function toggleContent(id) {
        var content = document.getElementById(id);
        content.style.display = (content.style.display === "block") ? "none" : "block";
    }
</script>

<main>
    <div class="bg-grey-card">
        <form role="search" id="search-form" method="get" class="search-form">
            <button type="submit" class="d-none"></button>
            <div class="container">
                <div class="row">
                    <h2 class="visually-hidden">Esplora tutti i servizi</h2>
                    <!-- Colonna sinistra: categorie -->
                    <div class="col-12 col-lg-8 pt-30 pt-lg-50 pb-lg-50">
                        <div class="mycontainer p-3">
                            <?php foreach ($categorie_genitori as $genitore) {
                                $nome_genitore = esc_html($genitore->name);
                                $id_genitore = 'cat_' . $genitore->term_id;
                                ?>
                                <h2 class="title-custom" onclick="toggleContent('<?= $id_genitore ?>')"><?= $nome_genitore ?></h2>
                                <div id="<?= $id_genitore ?>" class="content">
                                    <?php
                                    // Recupera le sottocategorie per questa categoria principale
                                    $sottocategorie = get_terms('tipi_cat_amm_trasp', array(
                                        'hide_empty' => false,
                                        'parent' => $genitore->term_id  // Ottieni le sottocategorie della categoria principale
                                    ));

                                    // Verifica se ci sono sottocategorie prima di iniziare il markup HTML
                                    if (!empty($sottocategorie)) { ?>
                                        <ul class="link-list t-primary">
                                            <?php foreach ($sottocategorie as $sottocategoria) {
                                                $link = get_term_link($sottocategoria);  // Ottieni il link della sottocategoria
                                                $nome_sotto = esc_html($sottocategoria->name); ?>                                      
                                                <li class="mb-3 mt-3">
                                        <?php echo ucfirst($sottocategoria->name); ?>
                                                    <a class="list-item ps-0 title-medium underline" style="text-decoration:none;" href="<?= $link; ?>">
                                                        <svg class="icon">
                                                            <use xlink:href="#it-arrow-right-triangle"></use>
                                                        </svg>
                                                        <span><?= $nome_sotto; ?></span>
                                                    </a>
                                                </li>
                                            <?php } ?>
                                        </ul>
                                    <?php } else { ?>
                                        <p>Non ci sono sottocategorie per questa categoria.</p>
                                    <?php } ?>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <!-- Colonna destra: link utili -->
                    <?php get_template_part("template-parts/amministrazione-trasparente/side-bar"); ?>
                </div>
            </div>
        </form>
    </div>
</main>

