<?php
global $sito_tematico_id, $siti_tematici;

$categorie_genitori = get_terms('tipi_cat_amm_trasp', array(
    'hide_empty' => false,
    'parent' => 0,
    'orderby' => 'ID',
    'order' => 'ASC'
));

$siti_tematici = !empty(dci_get_option("siti_tematici", "trasparenza")) ? dci_get_option("siti_tematici", "trasparenza") : [];
?>

<style>
ul.menu-categorie, ul.menu-categorie ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

ul.menu-categorie > li {
    font-weight: 700;
    color: #0b4a71; /* colore blu scuro per categoria principale */
    cursor: pointer;
    margin-bottom: 8px;
}

ul.menu-categorie ul {
    margin-left: 25px;  /* rientro sottocategorie */
    border-left: 2px solid #ccc; /* linea verticale */
    padding-left: 15px;
    color: #555;
    font-style: italic;
    font-size: 15px;
}

ul.menu-categorie ul li {
    font-weight: 400;
    margin: 6px 0;
}

ul.menu-categorie ul ul {
    margin-left: 20px; /* ulteriore rientro per sottosottocategorie */
    border-left: 1px dashed #bbb;
    padding-left: 15px;
    font-style: normal;
    color: #666;
    font-size: 14.5px;
}

/* Evidenziare categoria attiva */
ul.menu-categorie > li.active,
ul.menu-categorie > li:hover {
    color: #004080;
}

/* Link sottocategorie */
ul.menu-categorie ul li a {
    text-decoration: none;
    color: inherit;
}

ul.menu-categorie ul li a:hover {
    text-decoration: underline;
}



</style>


<script>
function toggleContent(id) {
    var allContents = document.querySelectorAll('.content');
    allContents.forEach(function(content) {
        if (content.id === id) {
            content.style.display = (content.style.display === "block") ? "none" : "block";
        } else {
            content.style.display = "none";
        }
    });
    updateToggleAllButton();
}

function toggleAllCategories() {
    var allContents = document.querySelectorAll('.content');
    var toggleAllBtn = document.getElementById('toggle-all-btn');

    // Controlla se almeno una categoria è chiusa
    var anyClosed = Array.from(allContents).some(content => content.style.display !== 'block');

    if (anyClosed) {
        // Apri tutte
        allContents.forEach(content => content.style.display = 'block');
        toggleAllBtn.textContent = 'Chiudi tutte le Voci';
    } else {
        // Chiudi tutte
        allContents.forEach(content => content.style.display = 'none');
        toggleAllBtn.textContent = 'Espandi tutte le Voci';
    }
}

function updateToggleAllButton() {
    var allContents = document.querySelectorAll('.content');
    var toggleAllBtn = document.getElementById('toggle-all-btn');

    var allOpen = Array.from(allContents).every(content => content.style.display === 'block');
    toggleAllBtn.textContent = allOpen ? 'Chiudi tutte' : 'Espandi tutte le Voci';
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

                            <!-- BOTTONE PER ESPANDERE/CHIUDERE TUTTE -->
                            <div id="toggle-all-wrapper">
                                <div>Elenco di tutte le voci</div>
                                    <div id="toggle-all-container" class="d-flex justify-content-end mb-3">
                                        <button type="button" id="toggle-all-btn" class="btn btn-outline-primary py-1 px-3" style="font-size:14px; height: 30px;" onclick="toggleAllCategories()">
                                            Espandi tutte le Voci
                                        </button>
                                    </div>
                            </div>


                            <?php foreach ($categorie_genitori as $genitore) {
                                $nome_genitore = esc_html($genitore->name);
                                $id_genitore = 'cat_' . $genitore->term_id;
                            ?>
                                
                                <h2 class="title-custom" onclick="toggleContent('<?= $id_genitore ?>')"><?= $nome_genitore ?></h2>
                                
                                <div id="<?= $id_genitore ?>" class="content">
                                    <?php
                                    $sottocategorie = get_terms('tipi_cat_amm_trasp', array(
                                        'hide_empty' => false,
                                        'parent' => $genitore->term_id
                                    ));
                                    ?>
                                    <ul class="link-list t-primary">
                                        <?php foreach ($sottocategorie as $sotto) {
                                            $link = get_term_link($sotto);
                                            $nome_sotto = esc_html($sotto->name); ?>
                                            
                                            <li class="mb-3 mt-3">
                                                <a class="list-item ps-0 title-medium underline" style="text-decoration:none;" href="<?= $link; ?>">
                                                    <svg class="icon">
                                                        <use xlink:href="#it-arrow-right-triangle"></use>
                                                    </svg>
                                                    <span><?= $nome_sotto; ?></span>
                                                </a>
                            
                                                <?php
                                                // Include sottocategorie 3° e 4° livello
                                                $term_id = $sotto->term_id;
                                                include locate_template('template-parts/amministrazione-trasparente/sottocategorie_list.php');
                                                ?>
                                            </li>
                                        <?php } ?>
                                    </ul>
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

