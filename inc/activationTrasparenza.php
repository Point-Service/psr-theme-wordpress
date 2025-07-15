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
                ['name' => "Piano triennale per la prevenzione della corruzione e della trasparenza", 'url' => ''],
                ['name' => 'Atti generali', 'url' => ''],
                ['name' => "Oneri informativi per cittadini e imprese", 'url' => '']
            ],
            'Organizzazione' => [
                ['name' => 'Organi di indirizzo politico-amministrativo', 'url' => ''],
                ['name' => "Sanzioni per mancata comunicazione dei dati", 'url' => ''],
                ['name' => "Rendiconti gruppi consiliari regionali/provinciali", 'url' => ''],
                ['name' => "Articolazione degli uffici", 'url' => ''],
                ['name' => "Telefono e posta elettronica", 'url' => '']
            ],
            'Consulenti e collaboratori' => [
                ['name' => 'Titolari di incarichi di collaborazione o consulenza', 'url' => '']
            ],
            'Personale' => [
                ['name' => 'Incarichi amministrativi di vertice', 'url' => ''],
                ['name' => 'Dirigenti', 'url' => ''],
                ['name' => 'Posizioni organizzative', 'url' => ''],
                ['name' => 'Dotazione organica', 'url' => ''],
                ['name' => 'Personale non a tempo indeterminato', 'url' => ''],
                ['name' => 'Tassi di assenza', 'url' => ''],
                ['name' => "Incarichi conferiti e autorizzati ai dipendenti", 'url' => ''],
                ['name' => "Contrattazione collettiva", 'url' => ''],
                ['name' => "Contrattazione integrativa", 'url' => ''],
                ['name' => "OIV", 'url' => '']
            ],
            'Bandi di concorso' => [
                ['name' => 'Concorsi', 'url' => '']
            ],
            'Performance' => [
                ['name' => "Piano della Performance", 'url' => ''],
                ['name' => "Relazione sulla Performance", 'url' => ''],
                ['name' => "Ammontare complessivo dei premi", 'url' => ''],
                ['name' => "Benessere organizzativo", 'url' => '']
            ],
            'Enti controllati' => [
                ['name' => "Enti pubblici vigilati", 'url' => ''],
                ['name' => "Società partecipate", 'url' => ''],
                ['name' => "Enti di diritto privato controllati", 'url' => ''],
                ['name' => "Rappresentazione grafica", 'url' => '']
            ],
            "Attività e procedimenti" => [
                ['name' => "Dati aggregati attività amministrativa", 'url' => ''],
                ['name' => "Tipologie di procedimento", 'url' => ''],
                ['name' => "Monitoraggio tempi procedimentali", 'url' => ''],
                ['name' => "Dichiarazioni sostitutive  e acquisizione d'ufficio dei dati", 'url' => '']
            ],
            "Provvedimenti" => [
                ['name' => "Provvedimenti organi indirizzo-politico", 'url' => ''],
                ['name' => "Provvedimenti dirigenti", 'url' => '']
            ],
            "Bandi di Gara e contratti" => [
               ['name' => "Informazioni sulle singole procedure in formato tabellare", 'url' => ''],
               ['name' => "Atti delle amministrazioni aggiudicatrici e degli enti aggiudicatori distintamente per ogni procedura", 'url' => ''],
               ['name' => "Contratti Pubblici", 'url' => ''],
            ],
            "Sovvenzioni , contributi sussidi, vantaggi economici" => [
                ['name' => "Criteri e modalità", 'url' => ''],
                ['name' => "Atti di concessione", 'url' => 'edit.php?post_type=atto_concessione'],
                ['name' => "Elenchi", 'url' => ''],
            ],
            "Bilanci" => [
                ['name' => "Bilancio preventivo e consuntivo", 'url' => ''],
                ['name' => "Piano degli indicatori e risultati attesi di bilancio", 'url' => '']
            ],
            "Beni immobili e gestione patrimonio" => [
                ['name' => "Patrimonio immobiliare", 'url' => ''],
                ['name' => "Canoni di locazione o affitto", 'url' => '']
            ],
            "Controlli e rilievi sull'amministrazione" => [
                ['name' => "Organismi indipendenti di valutazione, nuclei di valutazione o altri organismi con funzioni analoghe", 'url' => ''],
                ['name' => "Organi di revisione amministrativa e contabile", 'url' => ''],
                ['name' => "Corte dei conti", 'url' => '']
            ],
            "Servizi Erogati" => [
                ['name' => "Carta dei servizi e standard di qualità", 'url' => ''],
                ['name' => "Costi contabilizzati", 'url' => ''],
                ['name' => "Tempi medi di erogazione dei servizi", 'url' => ''],
                ['name' => "Liste di attesa", 'url' => '']
            ],
            "Pagamenti dell'amministrazione" => [
                ['name' => "Dati sui pagamenti", 'url' => ''],
                ['name' => "Indicatore di tempestività dei pagamenti", 'url' => ''],
                ['name' => "IBAN e pagamenti informatici", 'url' => ''],
                ['name' => "Dati sui pagamenti del servizio sanitario nazionale", 'url' => '']
            ],
            "Opere pubbliche" => [
                ['name' => "Nuclei di valutazione e verifica degli investimenti pubblici", 'url' => ''],
                ['name' => "Atti di programmazione delle opere pubbliche", 'url' => ''],
                ['name' => "Tempi costi e indicatori di realizzazione delle opere pubbliche", 'url' => '']
            ],
            "Pianificazione e governo del territorio" => [
                ['name' => "Pianificazione e governo del territorio", 'url' => '']
            ],
            "Informazioni ambientali" => [
                ['name' => "Informazioni ambientali", 'url' => '']
            ],
            "Strutture sanitarie private accreditate" => [
                ['name' => "Strutture sanitarie private accreditate", 'url' => '']
            ],
            "Interventi straordinari e di emergenza" => [
                ['name' => "Interventi straordinari e di emergenza", 'url' => '']
            ],
            "Altri contenuti" => [
                ['name' => "Prevenzione della corruzione", 'url' => ''],
                ['name' => "Accesso civico", 'url' => ''],
                ['name' => "Accessibilità e Catalogo di dati, metadati e banche dati", 'url' => ''],
                ['name' => "Dati ulteriori", 'url' => '']
            ]
        ];
    }
}



if ( ! function_exists( 'dci_tipi_cat_amm_trasp_links' ) ) {
    function dci_tipi_cat_amm_trasp_links() {
        return [
         
            'Atti di concessione'                               => 'edit.php?post_type=atto_concessione',
            

            // Aggiungi qui altre associazioni voce → URL quando ti servono…
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

    /* --------------------------- */
    /* 1) Inserimento tassonomie   */
    /* --------------------------- */
    // Categorie Trasparenza
    $tipi_cat_amm_trasp_array = dci_tipi_cat_amm_trasp_array();
    recursionInsertTaxonomy( $tipi_cat_amm_trasp_array, 'tipi_cat_amm_trasp' );
    sistemaidordinamentoTaxonomy( $tipi_cat_amm_trasp_array, 'tipi_cat_amm_trasp' );

    // Tipi di procedura contraente
    $tipi_procedura_contraente_array = dci_tipi_procedura_contraente_array();
    recursionInsertTaxonomy( $tipi_procedura_contraente_array, 'tipi_procedura_contraente' );

    // Tipi di stato bando
    $tipi_stato_bando_array = dci_tipi_stato_bando_array();
    recursionInsertTaxonomy( $tipi_stato_bando_array, 'tipi_stato_bando' );


    /* ----------------------------------------------------------- */
    /* 2) Aggiornamento descrizioni dettagliate di termini chiave  */
    /* ----------------------------------------------------------- */

    // Mappa: 'Nome termine' => 'Descrizione desiderata'
  $descrizioni = [

    // Disposizioni generali
    "Piano triennale per la prevenzione della corruzione e della trasparenza" => 
        "Documento programmatico che definisce le strategie e le misure per prevenire la corruzione "
      . "e garantire la trasparenza nelle attività dell’amministrazione, in ottemperanza agli artt. 1 e 10 del D.Lgs. 33/2013.",

    "Atti generali" =>
        "Documenti amministrativi di carattere generale che disciplinano l’organizzazione, "
      . "il funzionamento e le modalità operative dell’ente pubblico.",

    "Oneri informativi per cittadini e imprese" => 
        "Elenco delle informazioni e documenti che l’amministrazione è tenuta a pubblicare "
      . "e aggiornare per garantire la massima trasparenza nei confronti di cittadini e imprese.",

    // Organizzazione
    "Organi di indirizzo politico-amministrativo" =>
        "Informazioni relative agli organi politici e amministrativi, quali giunta, consiglio e dirigenti, "
      . "con relativi incarichi e competenze.",

    "Sanzioni per mancata comunicazione dei dati" =>
        "Dettagli sulle sanzioni previste per la mancata o ritardata comunicazione delle informazioni obbligatorie.",

    "Rendiconti gruppi consiliari regionali/provinciali" =>
        "Documentazione e rendicontazione economica dei gruppi consiliari a livello regionale e provinciale.",

    "Articolazione degli uffici" =>
        "Descrizione della struttura organizzativa interna dell’amministrazione, con indicazione di uffici, servizi e loro funzioni.",

    "Telefono e posta elettronica" =>
        "Elenco dei recapiti telefonici e indirizzi di posta elettronica istituzionali per il contatto con l’amministrazione.",

    // Consulenti e collaboratori
    "Titolari di incarichi di collaborazione o consulenza" =>
        "Elenco dei soggetti esterni incaricati di collaborazioni o consulenze, con dettagli sugli incarichi conferiti.",

    // Personale
    "Incarichi amministrativi di vertice" =>
        "Informazioni sugli incarichi di vertice politico-amministrativo conferiti all’interno dell’ente, con relativi nominativi e dati.",

    "Dirigenti" =>
        "Elenco dei dirigenti dell’amministrazione con dettagli sugli incarichi e qualifiche professionali.",

    "Posizioni organizzative" =>
        "Informazioni sulle posizioni organizzative intermedie, con relativa descrizione di funzioni e competenze.",

    "Dotazione organica" =>
        "Descrizione del personale in servizio in termini numerici e di qualifiche, suddivisa per aree e categorie.",

    "Personale non a tempo indeterminato" =>
        "Dettaglio sul personale assunto con contratti a tempo determinato o altre forme contrattuali non stabili.",

    "Tassi di assenza" =>
        "Dati statistici relativi ai tassi di assenza del personale, per monitorare l’efficienza e la produttività.",

    "Incarichi conferiti e autorizzati ai dipendenti" =>
        "Informazioni sugli incarichi aggiuntivi affidati ai dipendenti pubblici, con relativi dettagli e autorizzazioni.",

    "Contrattazione collettiva" =>
        "Dettagli sugli accordi e contratti collettivi applicati all’interno dell’amministrazione.",

    "Contrattazione integrativa" =>
        "Informazioni sulle negoziazioni e accordi integrativi stipulati per migliorare le condizioni di lavoro.",

    "OIV" =>
        "Dati sull’Organismo Indipendente di Valutazione, con funzioni di controllo e monitoraggio della performance.",

    // Bandi di concorso
    "Concorsi" =>
        "Elenco e informazioni relative ai bandi di concorso pubblico per l’assunzione nel settore pubblico.",

    // Performance
    "Piano della Performance" =>
        "Documento programmatico che definisce obiettivi e risultati attesi dall’amministrazione pubblica.",

    "Relazione sulla Performance" =>
        "Rapporto annuale che analizza i risultati ottenuti in relazione agli obiettivi prefissati.",

    "Ammontare complessivo dei premi" =>
        "Informazioni sul totale delle risorse erogate sotto forma di premi e incentivi al personale.",

    "Benessere organizzativo" =>
        "Dati e iniziative volte a migliorare il clima e il benessere lavorativo all’interno dell’ente.",

    // Enti controllati
    "Enti pubblici vigilati" =>
        "Elenco degli enti pubblici soggetti a vigilanza da parte dell’amministrazione.",

    "Società partecipate" =>
        "Informazioni sulle società a partecipazione pubblica, con dati su attività e governance.",

    "Enti di diritto privato controllati" =>
        "Dettaglio sugli enti privati sotto controllo pubblico e sulle modalità di controllo esercitate.",

    "Rappresentazione grafica" =>
        "Visualizzazioni grafiche e schemi relativi alla rete di enti e società controllate.",

    // Attività e procedimenti
    "Dati aggregati attività amministrativa" =>
        "Raccolta e sintesi statistica delle principali attività svolte dall’amministrazione.",

    "Tipologie di procedimento" =>
        "Classificazione e descrizione delle diverse tipologie di procedimenti amministrativi gestiti.",

    "Monitoraggio tempi procedimentali" =>
        "Dati e analisi sui tempi medi di esecuzione dei procedimenti amministrativi.",

    "Dichiarazioni sostitutive  e acquisizione d'ufficio dei dati" =>
        "Informazioni sulle modalità di autocertificazione e sull’acquisizione automatica dei dati da parte dell’amministrazione.",

    // Provvedimenti
    "Provvedimenti organi indirizzo-politico" =>
        "Elenco e dettagli dei provvedimenti adottati dagli organi di indirizzo politico-amministrativo.",

    "Provvedimenti dirigenti" =>
        "Informazioni relative ai provvedimenti emanati dai dirigenti dell’amministrazione.",

    // Bandi di Gara e contratti
    "Informazioni sulle singole procedure in formato tabellare" =>
        "Dati dettagliati e organizzati delle singole procedure di gara in formato facilmente consultabile.",

    "Atti delle amministrazioni aggiudicatrici e degli enti aggiudicatori distintamente per ogni procedura" =>
        "Documenti ufficiali relativi alle amministrazioni che aggiudicano le gare, organizzati per procedura.",

    "Contratti Pubblici" =>
        "Elenco completo e aggiornato dei contratti pubblici di lavori, servizi e forniture stipulati "
      . "dall’amministrazione, in conformità all’Art. 37 del D.Lgs. 50/2016.",

    // Sovvenzioni , contributi sussidi, vantaggi economici
    "Criteri e modalità" =>
        "Descrizione dei criteri e delle modalità con cui sono erogati sovvenzioni, contributi, sussidi e altri vantaggi economici.",

    "Atti di concessione" =>
        "Atti amministrativi con cui l’ente concede sovvenzioni, contributi, sussidi o vantaggi economici "
      . "a soggetti pubblici o privati, secondo quanto previsto dall’Art. 26 del D.Lgs. 33/2013.",

    "Elenchi" =>
        "Elenco dettagliato dei beneficiari di sovvenzioni, contributi, sussidi e vantaggi economici concessi dall’amministrazione.",

    // Bilanci
    "Bilancio preventivo e consuntivo" =>
        "Documenti contabili che illustrano le previsioni e i risultati finanziari dell’amministrazione, "
      . "pubblicati per garantire trasparenza nell’utilizzo delle risorse pubbliche.",

    "Piano degli indicatori e risultati attesi di bilancio" =>
        "Piano dettagliato degli indicatori di performance e degli obiettivi finanziari attesi dall’ente.",

    // Beni immobili e gestione patrimonio
    "Patrimonio immobiliare" =>
        "Informazioni sul patrimonio immobiliare dell’ente, comprese proprietà, beni e relativi dati gestionali.",

    "Canoni di locazione o affitto" =>
        "Dettaglio dei canoni di locazione o affitto pagati o incassati dall’amministrazione.",

    // Controlli e rilievi sull'amministrazione
    "Organismi indipendenti di valutazione, nuclei di valutazione o altri organismi con funzioni analoghe" =>
        "Informazioni sugli organismi indipendenti che valutano la performance e l’efficienza dell’amministrazione.",

    "Organi di revisione amministrativa e contabile" =>
        "Dettagli sugli organi incaricati della revisione amministrativa e contabile interna.",

    "Corte dei conti" =>
        "Informazioni e documenti relativi ai controlli e alle decisioni della Corte dei conti sull’ente.",

    // Servizi Erogati
    "Carta dei servizi e standard di qualità" =>
        "Documento che illustra i servizi offerti dall’amministrazione e gli standard di qualità garantiti.",

    "Costi contabilizzati" =>
        "Dati relativi ai costi sostenuti per l’erogazione dei servizi pubblici.",

    "Tempi medi di erogazione dei servizi" =>
        "Statistiche sui tempi medi necessari per la fornitura dei servizi ai cittadini.",

    "Liste di attesa" =>
        "Informazioni sulle liste di attesa per l’accesso a determinati servizi o prestazioni.",

    // Pagamenti dell'amministrazione
    "Dati sui pagamenti" =>
        "Dati aggregati relativi ai pagamenti effettuati dall’amministrazione verso fornitori e terzi.",

    "Indicatore di tempestività dei pagamenti" =>
        "Indicatore che misura la tempestività con cui l’ente effettua i pagamenti, in conformità alla normativa vigente.",

    "IBAN e pagamenti informatici" =>
        "Informazioni sugli IBAN utilizzati e sulle modalità di pagamento elettronico adottate dall’amministrazione.",

    "Dati sui pagamenti del servizio sanitario nazionale" =>
        "Dati specifici relativi ai pagamenti effettuati nell’ambito del servizio sanitario nazionale.",

    // Opere pubbliche
    "Nuclei di valutazione e verifica degli investimenti pubblici" =>
        "Informazioni sui nuclei incaricati della valutazione e verifica degli investimenti pubblici.",

    "Atti di programmazione delle opere pubbliche" =>
        "Documenti relativi alla programmazione e pianificazione delle opere pubbliche.",

    "Tempi costi e indicatori di realizzazione delle opere pubbliche" =>
        "Dati e indicatori relativi ai tempi, costi e qualità delle opere pubbliche realizzate.",

    // Pianificazione e governo del territorio
    "Pianificazione e governo del territorio" =>
        "Informazioni e documenti relativi alla pianificazione urbanistica e al governo del territorio comunale.",

    // Informazioni ambientali
    "Informazioni ambientali" =>
        "Dati e documenti riguardanti la gestione ambientale e le politiche di sostenibilità adottate dall’ente.",

];

    foreach ( $descrizioni as $term_name => $new_desc ) {
        dci_update_term_description( $term_name, 'tipi_cat_amm_trasp', $new_desc );
    }
}

/**
 * Aggiorna la descrizione di un termine se assente o diversa.
 *
 * @param string $term_name Nome del termine.
 * @param string $taxonomy  Tassonomia di appartenenza.
 * @param string $new_desc  Nuova descrizione (testo con \n\n per i paragrafi).
 */
function dci_update_term_description( $term_name, $taxonomy, $new_desc ) {
    $term = get_term_by( 'name', $term_name, $taxonomy );

    if ( $term && ( empty( $term->description ) || $term->description !== $new_desc ) ) {
        wp_update_term(
            $term->term_id,
            $taxonomy,
            [ 'description' => $new_desc ]
        );
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


