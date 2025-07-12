<?php 

function dci_trasparenza_activation() {
    set_time_limit(400);  // Aumenta il timeout

    // Inserisce i termini di tassonomia
    insertTaxonomyTrasparenzaTerms();

    // Imposta un'opzione per indicare che il setup è avvenuto
    update_option("dci_has_installed", true);

    // Disabilita i commenti di default per i nuovi post
    if ('' != get_option('default_comment_status')) {
        update_option('default_comment_status', '');
    }
}
add_action('after_switch_theme', 'dci_trasparenza_activation');
//dci_reload_trasparenza_option_page('themes.php', 'dci_trasparenza_activation');


// ===========================
// Pagina Admin per forzare la ricarica
// ===========================
function dci_reload_trasparenza_option_page() {
    if (isset($_GET["action"]) && $_GET["action"] === "reload") {
        dci_trasparenza_activation(); // Esegue nuovamente l'attivazione
        echo '<div class="notice notice-success is-dismissible"><p>Dati ricaricati con successo.</p></div>';
    }

    echo "<div class='wrap'>";
    echo "<h1>Ricarica i dati della Trasparenza</h1>";
    echo '<p>Questa operazione reinserisce le tassonomie e opzioni di default relative alla sezione "Amministrazione Trasparente".</p>';
    echo '<a href="' . esc_url(admin_url('themes.php?page=reload-trasparenza-theme-options&action=reload')) . '" class="button button-primary">Ricarica Trasparenza</a>';
    echo "</div>";
}

function dci_add_trasparenza_theme_page() {
    add_theme_page(
        'Ricarica Trasparenza',
        'Ricarica Trasparenza',
        'edit_theme_options',
        'reload-trasparenza-theme-options',
        'dci_reload_trasparenza_option_page'
    );
}
add_action('admin_menu', 'dci_add_trasparenza_theme_page');


// ===========================
// Struttura delle tassonomie
// ===========================
if (!function_exists("dci_tipi_cat_amm_trasp_array")) {
    function dci_tipi_cat_amm_trasp_array() {
        return [
            'Disposizioni generali' => [
                "Piano triennale per la prevenzione della corruzione e della trasparenza",
                'Atti generali',
                "Oneri informativi per cittadini e imprese"
            ],
            'Organizzazione' => [
                'Organi di indirizzo politico-amministrativo',
                "Sanzioni per mancata comunicazione dei dati",
                "Rendiconti gruppi consiliari regionali/provinciali",
                "Articolazione degli uffici",
                "Telefono e posta elettronica"
            ],
            'Consulenti e collaboratori' => [
                'Titolari di incarichi di collaborazione o consulenza'
            ],
            'Personale' => [
                'Incarichi amministrativi di vertice',
                'Dirigenti',
                'Posizioni organizzative',
                'Dotazione organica',
                'Personale non a tempo indeterminato',
                'Tassi di assenza',
                "Incarichi conferiti e autorizzati ai dipendenti",
                "Contrattazione collettiva",
                "Contrattazione integrativa",
                "OIV"
            ],
            'Bandi di concorso' => [
                'Concorsi'
            ],
            'Performance'=> [
                "Piano della Performance",
                "Relazione sulla Performance",
                "Ammontare complessivo dei premi",
                "Benessere organizzativo"
            ],
            'Enti controllati' =>[
                "Enti pubblici vigilati",
                "Società partecipate",
                "Enti di diritto privato controllati",
                "Rappresentazione grafica"
            ],
            "Attività e procedimenti"=>[
                "Dati aggregati attività amministrativa",
                "Tipologie di procedimento",
                "Monitoraggio tempi procedimentali",
                "Dichiarazioni sostitutive  e acquisizione d'ufficio dei dati"
            ],
            "Provvedimenti" =>[
                "Provvedimenti organi indirizzo-politico",
                "Provvedimenti dirigenti"
            ],
            "Bandi di Gara e contratti"=>[
               "Informazioni sulle singole procedure in formato tabellare",
               "Atti delle amministrazioni aggiudicatrici e degli enti aggiudicatori distintamente per ogni procedura",
               "Contratti Pubblici",
            ],
            "Sovvenzioni , contributi sussidi, vantaggi economici"=>[
                "Criteri e modalità",
                "Atti di concessione",
                "Elenchi"
            ],
            "Bilanci"=>[
                "Bilancio preventivo e consuntivo",
                "Piano degli indicatori e risultati attesi di bilancio"
            ],
            "Beni immobili e gestione patrimonio"=>[
                "Patrimonio immobiliare",
                "Canoni di locazione o affitto"
            ],
            "Controlli e rilievi sull'amministrazione"=>[
                "Organismi indipendenti di valutazione, nuclei di valutazione o altri organismi con funzioni analoghe",
                "Organi di revisione amministrativa e contabile",
                "Corte dei conti"
            ],
            "Servizi Erogati"=>[
                "Carta dei servizi e standard di qualità",
                "Costi contabilizzati",
                "Tempi medi di erogazione dei servizi",
                "Liste di attesa"
            ],
            "Pagamenti dell'amministrazione" => [
                    "Dati sui pagamenti",
                    "Indicatore di tempestività dei pagamenti",
                    "IBAN e pagamenti informatici",
                    "Dati sui pagamenti del servizio sanitario nazionale"
            ],
            "Opere pubbliche" => [
                "Nuclei di valutazione e verifica degli investimenti pubblici",
                "Atti di programmazione delle opere pubbliche",
                "Tempi costi e indicatori di realizzazione delle opere pubbliche"
            ],
            "Pianificazione e governo del territorio" => [
                "Pianificazione e governo del territorio"
            ],
            "Informazioni ambientali" => [
                "Informazioni ambientali"
            ],
            "Strutture sanitarie private accreditate" => [
                "Strutture sanitarie private accreditate"
            ],
            "Interventi straordinari e di emergenza" => [
                "Interventi straordinari e di emergenza"
            ],
            "Altri contenuti" => [
                "Prevenzione della corruzione",
                "Accesso civico",
                "Accessibilità e Catalogo di dati, metadati e banche dati",
                "Dati ulteriori"
            ]
        ];
    }
}



if (!function_exists("dci_tipi_procedura_contraente_array")) {
    function dci_tipi_procedura_contraente_array() {
        return [
            "01 - Procedura aperta",
            "02 - Procedura ristretta",
            "03 - Procedura negoziata previa pubblicazione",
            "04 - Procedura negoziata senza previa pubblicazione",
            "05 - Dialogo competitivo",
            "06 - Procedura negoziata senza previa i nozione cl gara (settori speciali)",
            "07 - Sistema dinamico dl acquisizione",
            "08 - Affloamento in economia - cottimo fiduciario",
            "14 - Procedura selettiva ex art 238 c7, d.lgs.",
            "17 - Affidamento diretto ex art. 5 cella legge",
            "21 - Procedura ristretta derivante da avvisi con cui si indice la gara",
            "22 - Procedura negoziata previa indizione dl gara (settori speciali}",
            "23 - Affidamento diretto",
            "24 - Affidamento diretto a societa' in house",
            "25 - Affidamento diretto a societa raggruppate/consorziate o controllate nelle concessioni e nei partenariati",
            "26 - Affldamento diretto in adesione ad accordo quadro/convenzione",
            "27 - Confronto competitivo in adesione ad accordo quadro/convenzione",
            "28 - Procedura al sensi dei regolamenti degli organi costituzionali",
            "29 - Procedura ristretta semplificata",
            "30 - Procedura derivante oa legge regionale",
            "31 - Affidamento diretto per variante superiore al dell'importo contrattuale",
            "32 - Affidamento riservato",
            "33 -Procedura negoziata per affidamenti sotto soglia",
            "34 - Procedura art. 16 comma 2. opr 280/2001 per opere urbanizzazione a scomputo primarie sotto soglia comunitaria",
            "35 - Parternariato per l'innovazione",
            "36 - Affidamento diretto per lavori. servizi o forniture supplementari",
            "37 - Procedura competitiva con negoziazione",
            "38 - Procedura disciplinata da regolamento interno per settori speciali",
            "39 - Diretto per modifiche contrattuali o varianti per le quali é necessaria una nuova procedura dl affidamento",
        ];
    }
}

if (!function_exists("dci_tipi_stato_bando_array")) {
    function dci_tipi_stato_bando_array() {
        return [
            "Attivo",
            "Scaduto",
            "Archiviato",
        ];
    }
}


// ===========================
// Funzione di inserimento tassonomie
// ===========================
function insertTaxonomyTrasparenzaTerms() {

    // Categorie Trasparenza
    $tipi_cat_amm_trasp_array = dci_tipi_cat_amm_trasp_array();
    recursionInsertTaxonomy($tipi_cat_amm_trasp_array, 'tipi_cat_amm_trasp');

    
    sistemaidordinamentoTaxonomy($tipi_cat_amm_trasp_array, 'tipi_cat_amm_trasp');

    
    // Tipi di procedure contraente
    $tipi_procedura_contraente_array = dci_tipi_procedura_contraente_array();
    recursionInsertTaxonomy($tipi_procedura_contraente_array, 'tipi_procedura_contraente');

    // Tipi di stati di bando
    $tipi_stato_bando_array = dci_tipi_stato_bando_array();
    recursionInsertTaxonomy($tipi_stato_bando_array, 'tipi_stato_bando');


/* --- AGGIORNO LA DESCRIZIONE DI "Contratti Pubblici" --- */
$term = get_term_by('name', 'Contratti Pubblici', 'tipi_cat_amm_trasp');
if ($term) {
    // Nuova descrizione dettagliata
        $new_desc = "In adempimento all’Art. 37 del D.Lgs. 50/2016 (Codice dei Contratti Pubblici). <p> Elenco trasparente dei contratti pubblici di lavori, servizi e forniture stipulati dall’amministrazione.";


    // Cambia descrizione solo se è assente o diversa da quella desiderata
    if (empty($term->description) || $term->description !== $new_desc) {
        wp_update_term($term->term_id, 'tipi_cat_amm_trasp', [
            'description' => $new_desc,
        ]);
    }
}


$term = get_term_by('name', 'Atti di concessione', 'tipi_cat_amm_trasp');
if ($term) {
       // Nuova descrizione dettagliata
        $new_desc = "Art. 26 del D.Lgs. 33/2013 disciplina proprio la pubblicazione degli atti di concessione. <p> Atti con cui l’amministrazione concede sovvenzioni, contributi, sussidi, vantaggi economici o altre forme di sostegno a persone, enti pubblici o privati.";


    // Cambia descrizione solo se è assente o diversa da quella desiderata
    if (empty($term->description) || $term->description !== $new_desc) {
        wp_update_term($term->term_id, 'tipi_cat_amm_trasp', [
            'description' => $new_desc,
        ]);
    }
}

    
    
}


function sistemaidordinamentoTaxonomy($terms, $taxonomy, $parent_id = 0, $ordine = 1) {
    foreach ($terms as $term_name => $subterms) {
        // Verifica se il termine esiste già nella tassonomia
        $existing_term = term_exists($term_name, $taxonomy);

        if ($existing_term) {
            // Se il termine esiste già, prendi il suo ID
            $term_id = $existing_term['term_id'];

            // Aggiorna il metadato 'ordinamento' per il termine esistente
            update_term_meta($term_id, 'ordinamento', $ordine);
        }

        // Incrementa l'ordine per il prossimo termine
        $ordine++;

        // Se ci sono sotto-termini, chiama ricorsivamente per aggiornarli
        if (!empty($subterms)) {
         //   sistemaidordinamentoTaxonomy($subterms, $taxonomy, $term_id, $ordine);
        }
    }
}








?>


