<?php
    // Mi ricavo i campi dalla pagina_vivi
    $show_map = dci_get_option('ck_show_map', 'vivi');
    $link_map = dci_get_option('link_map', 'vivi');

    // Controllo se l'opzione per visualizzare la mappa e se il link non è null
    if ($show_map === 'true' && !empty($link_map) && $link_map != null) {
?>
        <div class="row g-4">
            <!-- Div per la mappa -->
            <div id="map" style="height: 450px; width: 100%;"></div>
        </div>
        <script src="https://maps.googleapis.com/maps/api/js?key=YOUR_API_KEY&callback=initMap" async defer></script>
        <script>
            function initMap() {
                var mapOptions = {
                    center: {lat: 40.7128, lng: -74.0060}, // Imposta la posizione iniziale (puoi sostituirla con le coordinate della tua mappa)
                    zoom: 14, // Zoom della mappa
                    mapTypeId: 'roadmap', // Tipo di mappa
                    disableDefaultUI: true, // Disabilita i controlli di default (barra di scorrimento, etc.)
                };

                var map = new google.maps.Map(document.getElementById('map'), mapOptions);

                var marker = new google.maps.Marker({
                    position: {lat: 40.7128, lng: -74.0060}, // Posizione del marker (sostituisci con la posizione corretta)
                    map: map,
                    title: 'La tua posizione' // Messaggio del marker
                });
            }
        </script>
<?php }
?>

