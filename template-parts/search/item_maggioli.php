<?php
global $post;

// URL remoto da cui ottenere i dati
$remote_url = "https://sportellotelematico.comune.roccalumera.me.it/rest/pnrr/procedures";

// Ottieni i dati dal link remoto
$response = file_get_contents($remote_url);

// Decodifica il JSON in un array associativo
$data = json_decode($response, true);

// Filtra i dati in base al testo di ricerca
$filtered_data = array_filter($data, function($item) use ($search_text) {
    return stripos($item['title'], $search_text) !== false || stripos($item['descrizione'], $search_text) !== false;
});

// Cicla attraverso i risultati filtrati e visualizzali
foreach ($filtered_data as $item) {
    // Qui puoi utilizzare i dati come nel tuo codice originale, ad esempio:
    ?>
    <div class="cmp-card-latest-messages mb-3 mb-30" data-bs-toggle="modal" data-bs-target="#">
        <div class="card shadow-sm px-4 pt-4 pb-4 rounded">
            <div class="card-header border-0 p-0">
                <?php
                if ($post_type = dci_get_group_name($post->post_type)) {
                ?>
                    <a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase" href="<?php echo get_permalink(get_page_by_path(dci_get_group($post->post_type))); ?>">
                        <?= $post_type ?>
                    </a>
                <?php
                } else {
                ?>
                    <span class="title-xsmall-bold mb-2 category text-uppercase text-primary">Pagina</span>
                <?php
                }
                ?>
            </div>
            <div class="card-body p-0 my-2">
                <h3 class="green-title-big t-primary mb-8">
                    <a class="text-decoration-none" href="<?= $item['link'] ?>" data-element="service-link"><?php echo $item['title']; ?></a>
                </h3>
                <p class="text-paragraph">
                    <?php echo $item['descrizione']; ?>
                </p>
            </div>
        </div>
    </div>
    <?php
}
?> 

