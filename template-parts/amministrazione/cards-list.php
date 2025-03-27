 <?php
    $pages = dci_get_children_pages('amministrazione');
    $arr_pages = array_keys((array)$pages);
?>
<div class="container py-5">
    <h2 class="title-xxlarge mb-4">
        Esplora l'amministrazione
    </h2>
    <div class="row g-4">
        <?php foreach ($arr_pages as $key => $page_name) { 
            $page = $pages[$page_name]; ?>
        <div class="col-12 col-md-6 col-lg-4">
            <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
            <div class="card shadow-sm rounded">
                <div class="card-body">
                <a class="text-decoration-none" href="<?php echo $page['link']; ?>" data-element="management-category-link"><h3 class="card-title t-primary title-xlarge"><?php echo $page_name; ?></h3></a>
                <p class="text-paragraph mb-0">
                    <?php echo $page['description']; ?>
                </p>
                </div>
            </div>
            </div>
        </div>
        <?php } ?>


            <?php

		// Recupera il valore dell'opzione 'ck_dataset'
                $ck_dataset = get_option( 'amministrazione_ck_dataset'); // 'true' è il valore predefinito se l'opzione non è impostata
echo $ck_dataset;

		// Verifica se l'opzione è impostata su 'true'
		if ( 'true' === $ck_dataset ) :
                 ?>
                <!-- Blocco HTML da visualizzare -->
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
                        <div class="card shadow-sm rounded">
                            <div class="card-body">
                                <a class="text-decoration-none" href="/dataset">
                                    <h3 class="card-title t-primary title-xlarge">Dataset</h3>
                                </a>
                                <p class="text-paragraph mb-0">
                                    "Dataset" fornisce l'accesso ai dati aperti pubblicati dall'Autorità Nazionale Anticorruzione (ANAC) riguardanti i contratti pubblici in Italia. Questi dataset, disponibili in formato aperto, comprendono informazioni dettagliate sulle procedure di appalto, le stazioni appaltanti e altri elementi chiave relativi ai contratti pubblici, permettendo un'analisi approfondita e promuovendo la trasparenza nel settore degli appalti pubblici.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php
            endif;
            ?>
      
        
    </div>
</div>

