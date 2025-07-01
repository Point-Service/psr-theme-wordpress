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
    font-size: 22px;               /* +2 rispetto a 20 */
    background-color: rgb(255, 252, 252);
    padding: 14px 16px;            /* leggermente più padding */
    border: 1px solid #ccc;
    cursor: pointer;
    font-weight: 600;              /* un po’ più grassetto */
    line-height: 1.3;              /* migliore leggibilità */
    color: #222;                   /* colore testo più scuro */
    user-select: none;             /* evita selezione accidentale */
}

.content {
    display: none;
    padding: 14px 20px;            /* padding più ampio */
    font-size: 18px;               /* +1 rispetto a 17 */
    line-height: 1.5;
    color: #111;
}

.content a {
    display: block;
    margin: 8px 0;                 /* margini leggermente aumentati */
    color: #111;
    text-decoration: none;
    padding-left: 14px;            /* padding più largo per “rientro” */
    font-size: 18px;               /* +1 rispetto a 17 */
    font-weight: 600;              /* leggermente più marcato */
    transition: color 0.3s ease;
}

.content a:hover {
    text-decoration: underline;
    color: #007bff;                /* colore blu su hover per evidenziare */
}

.sub-sub-list {
    margin-top: 12px;              /* più spazio sopra */
    margin-left: 28px;             /* aumentato */
    padding-left: 20px;            /* più padding */
    border-left: 2px solid #d0d0d0;  /* colore bordo leggermente più chiaro */
    font-size: 17.5px;             /* leggermente più grande */
    line-height: 1.5;
    color: #444;
    font-style: italic;
}

.sub-sub-list li {
    margin: 6px 0;                /* margine verticale maggiore */
}

.sub-sub-list a {
    color: #555;
    font-style: italic;
    padding-left: 8px;             /* più padding */
    font-size: 16px;               /* più grande */
    font-weight: 500;
    transition: color 0.3s ease;
}

.sub-sub-list a:hover {
    color: #007bff;
    text-decoration: underline;
}

.sub-sub-list .sub-sub-list a {
    font-style: normal;
    font-size: 17px;
    color: #666;
    font-weight: 600;
}

.sub-sub-list .sub-sub-list {
    margin-left: 28px;
    border-left: 1px dashed #bbb; /* colore leggermente più scuro */
    padding-left: 14px;
}

#toggle-all-container {
    display: flex;
    justify-content: flex-end;
    margin-bottom: 1.5rem;
}

#toggle-all-btn {
    font-size: 15px;             /* più leggibile */
    height: 36px;                /* bottone un po’ più alto */
    padding: 6px 16px;
    cursor: pointer;
    border-radius: 5px;
    transition: background-color 0.3s ease, color 0.3s ease;
    user-select: none;
}

#toggle-all-btn:hover {
    background-color: #007bff;
    color: white;
}

#toggle-all-wrapper {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    font-weight: 700;            /* più marcato */
    font-size: 24px;             /* più grande */
    color: #222;
    letter-spacing: 0.03em;
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

