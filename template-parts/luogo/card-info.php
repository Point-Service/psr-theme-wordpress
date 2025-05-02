<?php
global $luogo, $showTitle, $showPermalink, $showParent;
$prefix = '_dci_luogo_';
$c = 0;

$post_title = $luogo->post_title;
$permalink = get_permalink($luogo);

$childof = dci_get_meta("childof", $prefix, $luogo->ID);

if (!empty($childof)) {
    $childofwhile = $childof;
    while (!empty($childofwhile)) {
        $posizione_gps = dci_get_meta("posizione_gps", $prefix, $childof);
        if (is_array($posizione_gps) && isset($posizione_gps["lat"]) && isset($posizione_gps["lng"])) {
            break; // Esci dal loop se la posizione è valida
        }
        $childofwhile = dci_get_meta("childof", $prefix, $childof);
    }
} else {
    $posizione_gps = dci_get_meta("posizione_gps", $prefix, $luogo->ID);
}

if (isset($posizione_gps["lat"]) && isset($posizione_gps["lng"]) && $posizione_gps["lat"] && $posizione_gps["lng"]) {
    $indirizzo = dci_get_meta("indirizzo", $prefix, $luogo->ID);
    ?>
    <div class="card-body p-0">
        <div class="map-wrapper">
            <div class="map" id="map_all"></div>
        </div>

        <script>
            jQuery(function() {
                var mymap = L.map('map_all', {
                    zoomControl: true,
                    scrollWheelZoom: false
                }).setView([<?php echo $posizione_gps["lat"]; ?>, <?php echo $posizione_gps["lng"]; ?>], 13);

                let marker = L.marker([<?php echo $posizione_gps["lat"]; ?>, <?php echo $posizione_gps["lng"]; ?>])
                    .addTo(mymap)
                    .bindPopup('<b><a href="<?php echo $permalink ?>"><?php echo addslashes($post_title); ?></a></b><br><?php echo addslashes($indirizzo); ?>');

                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '',
                    maxZoom: 18
                }).addTo(mymap);

                var arrayOfMarkers = [ [ <?php echo $posizione_gps["lat"]; ?>, <?php echo $posizione_gps["lng"]; ?> ] ];
                var bounds = new L.LatLngBounds(arrayOfMarkers);
                mymap.fitBounds(bounds);
            });
        </script>
    </div>
    <?php
} else {
    echo "Posizione GPS non disponibile per il luogo: " . $luogo->post_title;
}
?>


    
