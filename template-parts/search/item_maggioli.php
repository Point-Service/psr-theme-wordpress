<?php
global $post;

// URL remoto da cui ottenere i dati
$remote_url = "https://sportellotelematico.comune.roccalumera.me.it/rest/pnrr/procedures";

// Ottieni i dati dal link remoto
$response = file_get_contents($remote_url);

// Verifica se la richiesta ha avuto successo
if ($response === false) {
    echo "Errore durante il recupero dei dati.";
    return;
}

// Decodifica il JSON in un array associativo
$data = json_decode($response, true);

// Controlla se il JSON è stato decodificato correttamente
if ($data === null) {
    echo "Errore durante la decodifica del JSON.";
    return;
}

// Testo di ricerca (puoi sostituirlo con un valore dinamico o una query string)
$search_text = "titolo servizio"; // Sostituisci con il testo che vuoi cercare

// Filtra i dati in base al testo di ricerca nel titolo
$filtered_data = array_filter($data, function($item) use ($search_text) {
    return stripos($item['title'], $search_text) !== false;
});

// Se non ci sono risultati, mostra un messaggio
if (empty($filtered_data)) {
    echo "Nessun risultato trovato per '$search_text'.";
    return;
}

// Cicla attraverso i risultati filtrati e visualizzali
foreach ($filtered_data as $item) {
    ?>
    <div class="cmp-card-latest-messages mb-3 mb-30" data-bs-toggle="modal" data-bs-target="#">
        <div class="card shadow-sm px-4 pt-4 pb-4 rounded">
            <div class="card-header border-0 p-0">
                <span class="title-xsmall-bold mb-2 category text-uppercase text-primary">
                    Servizio
                </span>
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


