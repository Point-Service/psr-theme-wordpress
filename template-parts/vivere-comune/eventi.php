<?php
    // Per selezionare i contenuti in evidenza tramite flag
    // $eventi = dci_get_highlighted_posts(['evento'], 6);

    //Per selezionare i contenuti in evidenza tramite configurazione
    $eventi = dci_get_option('eventi_evidenziati','vivi');

    $url_eventi = get_permalink( get_page_by_title('Eventi') );
    if (is_array($eventi) && count($eventi)) {
?>


</div>
<?php } ?>
