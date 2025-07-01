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
    font-size: 22px;
    background-color: #fff9f9;
    padding: 14px 20px;
    border: 1px solid #ddd;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    line-height: 1.3;
    color: #222;
    user-select: none;
    transition: background-color 0.3s ease, color 0.3s ease;
    margin-bottom: 8px;
}

.title-custom:hover {
    background-color: #f0f8ff;
    color: #007bff;
}

.content {
    display: none;
    padding: 15px 25px;
    font-size: 18px;
    line-height: 1.6;
    color: #333;
    background-color: #fafafa;
    border-left: 3px solid #007bff;
    border-radius: 0 6px 6px 0;
    margin-bottom: 18px;
}

/* Link generali nelle categorie e sottocategorie */
.content a,
.sub-sub-list a,
.sub-sub-list .sub-sub-list a {
    display: block;
    margin: 10px 0;
    color: #222; /* colore uniforme come titolo primo livello */
    text-decoration: none;
    padding-left: 20px;
    font-size: 18px;
    font-weight: 600;
    position: relative;
    transition: color 0.3s ease;
    font-style: normal;
}

/* Freccia prima di ogni link */
.content a::before,
.sub-sub-list a::before,
.sub-sub-list .sub-sub-list a::before {
    content: '▶';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    font-size: 14px;
    color: #222;
    transition: transform 0.3s ease;
}

.content a:hover,
.sub-sub-list a:hover,
.sub-sub-list .sub-sub-list a:hover {
    text-decoration: underline;
    color: #007bff;
}

.content a:hover::before,
.sub-sub-list a:hover::before,
.sub-sub-list .sub-sub-list a:hover::before {
    transform: translateY(-50%) rotate(90deg);
}

/* Lista sottocategorie: livello 2 e 3 e 4 */
.sub-sub-list,
.sub-sub-list .sub-sub-list {
    margin-top: 15px;
    margin-left: 32px;
    padding-left: 18px;
    border-left: 2px solid #ccc;
    font-size: 18px;
    line-height: 1.6;
    color: #222; /* stesso colore */
    font-style: normal;
    font-weight: 600;
}

/* Margin più piccolo per i livelli più profondi */
.sub-sub-list .sub-sub-list {
    margin-left: 30px;
    border-left: 1px dashed #bbb;
    padding-left: 14px;
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

