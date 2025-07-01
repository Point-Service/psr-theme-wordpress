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
    .title-custom {
        font-size: 19px;
        background-color: rgb(255, 252, 252);
        padding: 10px;
        border: 1px solid #ccc;
        cursor: pointer;
    }

    .content {
        display: none;
        padding: 10px 15px;
    }

    .content a {
        display: block;
        margin: 6px 0;
        color: #111;
        text-decoration: none;
        padding-left: 10px;
        font-size: 17px;
        font-weight: 500;
    }

    .content a:hover {
        text-decoration: underline;
    }

    .sub-sub-list {
        margin-top: 8px;
        margin-left: 20px;
        padding-left: 15px;
        border-left: 2px solid #e0e0e0;
        font-size: 15px;
    }

    .sub-sub-list li {
        margin: 4px 0;
    }

    .sub-sub-list a {
        color: #444;
        font-style: italic;
        padding-left: 5px;
        font-size: 15px;
    }

    .sub-sub-list .sub-sub-list a {
        font-style: normal;
        font-size: 14px;
        color: #666;
    }

    .sub-sub-list .sub-sub-list {
        margin-left: 20px;
        border-left: 1px dashed #ccc;
        padding-left: 10px;
    }

    #toggle-all-container {
    display: flex;
    justify-content: flex-end; /* allinea il contenuto a destra */
    margin-bottom: 1rem;
    }
    
    #toggle-all-btn {
        padding: 4px 12px;
        font-size: 14px;
        height: 30px;
        line-height: 1.2;
        border-radius: 4px;
        cursor: pointer;
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
        toggleAllBtn.textContent = 'Chiudi tutte le Categorie';
    } else {
        // Chiudi tutte
        allContents.forEach(content => content.style.display = 'none');
        toggleAllBtn.textContent = 'Espandi tutte le Categorie';
    }
}

function updateToggleAllButton() {
    var allContents = document.querySelectorAll('.content');
    var toggleAllBtn = document.getElementById('toggle-all-btn');

    var allOpen = Array.from(allContents).every(content => content.style.display === 'block');
    toggleAllBtn.textContent = allOpen ? 'Chiudi tutte' : 'Espandi tutte le Categorie';
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
                             <div id="toggle-all-container">
                                <button type="button" id="toggle-all-btn" class="btn btn-primary" onclick="toggleAllCategories()">Espandi tutte</button>
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

