<?php
/**
 * PSR Security Suite | Antispam commenti WordPress.
 *
 * Modulo indipendente, per-sito, attivo per impostazione predefinita.
 * Al primo avvio classifica i nuovi commenti e pianifica una sola scansione
 * progressiva dei commenti gia' presenti (pending + approved).
 * Solo evidenze ad alta confidenza causano lo spostamento automatico in spam;
 * WordPress ne conserva il recupero. Nessuna cancellazione automatica.
 */
if (!defined('ABSPATH')) { exit; }

function psr_antispam_enabled() {
    return get_option('_psr_antispam_comments_enabled', '1') === '1';
}

/** Honeypot esclusivamente nel form commenti standard; non nei form cittadini. */
function psr_antispam_honeypot() {
    if (!psr_antispam_enabled()) { return; }
    echo '<p class="psr-antispam-trap" aria-hidden="true" style="position:absolute!important;left:-10000px!important;width:1px!important;height:1px!important;overflow:hidden!important">';
    echo '<label for="psr_comment_contact_hint">Lasciare vuoto <input type="text" name="psr_comment_contact_hint" id="psr_comment_contact_hint" value="" tabindex="-1" autocomplete="off" /></label></p>';
}
add_action('comment_form', 'psr_antispam_honeypot', 15);

/**
 * Valutazione conservativa basata su segnali indipendenti.
 * Non classifica come spam un messaggio solo perche' in inglese o con un URL.
 */
function psr_antispam_score($commentdata) {
    $body = substr(wp_strip_all_tags((string)($commentdata['comment_content'] ?? '')), 0, 16000);
    $author = substr((string)($commentdata['comment_author'] ?? ''), 0, 180);
    $website = substr((string)($commentdata['comment_author_url'] ?? ''), 0, 500);
    $text = html_entity_decode($body . ' ' . $author, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $score = 0;
    $patterns = array(
        '~\b(?:vip\s+rewards?\s+program|vip\s+rewards?)\b~iu' => 3,
        '~\b(?:daily\s+drops?|loyal\s+players?|free\s+spins?)\b~iu' => 2,
        '~\b(?:online\s+casino|casino\s+online|slot\s+gacor|judi\s+online|sports?\s*betting|betting\s+site|bonus\s+senza\s+deposito)\b~iu' => 4,
        '~\b(?:jackpot|scommesse|gambling|casino|scommetti|scommettere)\b~iu' => 2,
        '~\b[a-z][a-z0-9-]{3,}777\b~iu' => 3,
        '~\b(?:backlinks?\s+for\s+sale|buy\s+backlinks?|seo\s+packages?|cheap\s+followers?)\b~iu' => 4,
    );
    foreach ($patterns as $regex => $points) {
        if (preg_match($regex, $text)) { $score += $points; }
    }
    $link_count = preg_match_all('~https?://|www\.~i', $body);
    if ($link_count !== false && $link_count >= 3) { $score += 3; }
    elseif ($link_count !== false && $link_count >= 2) { $score += 2; }

    $host = parse_url($website, PHP_URL_HOST);
    if (is_string($host) && strlen($host) >= 6 && stripos($body, $host) !== false) { $score += 2; }
    return $score;
}

/**
 * Richiede piu' segnali pubblicitari indipendenti per lo spostamento automatico.
 * Un cittadino che cita semplicemente un casino', un URL o un argomento sensibile
 * non deve essere classificato spam per quella sola parola.
 */
function psr_antispam_promotional_signals($commentdata) {
    $text = wp_strip_all_tags((string)($commentdata['comment_content'] ?? '')) . ' ' .
        (string)($commentdata['comment_author'] ?? '');
    $matches = array();
    $count = preg_match_all('~\b(?:vip\s+rewards?(?:\s+program)?|daily\s+drops?|loyal\s+players?|free\s+spins?|online\s+casino|casino\s+online|slot\s+gacor|judi\s+online|sports?\s+betting|betting\s+site|bonus\s+senza\s+deposito|backlinks?\s+for\s+sale|buy\s+backlinks?|seo\s+packages?|cheap\s+followers?)\b~iu', $text, $matches);
    return $count === false ? 0 : (int)$count;
}

/** Rate-limit best effort con chiave HMAC; nessun IP in chiaro nei transient. */
function psr_antispam_rate_exceeded($commentdata) {
    $ip = trim((string)($commentdata['comment_author_IP'] ?? ''));
    if (!filter_var($ip, FILTER_VALIDATE_IP)) { return false; }
    $period = 600;
    $slot = (int) floor(time() / $period);
    $key = 'psr_as_' . substr(hash_hmac('sha256', $slot . '|' . $ip, wp_salt('auth')), 0, 32);
    $count = (int) get_transient($key);
    if ($count >= 20) { return true; }
    set_transient($key, $count + 1, $period * 2);
    return false;
}

/** Ricezione nuovi commenti: niente interventi su REST generiche/AJAX/servizi. */
function psr_antispam_check_comment($approved, $commentdata) {
    if (!psr_antispam_enabled() || !is_array($commentdata) || is_wp_error($approved)) { return $approved; }
    if (in_array((string)$approved, array('spam', 'trash'), true)) { return $approved; }
    $type = (string)($commentdata['comment_type'] ?? '');
    if ($type !== '' && $type !== 'comment') { return $approved; }
    if (current_user_can('moderate_comments')) { return $approved; }
    if (isset($_POST['psr_comment_contact_hint'])) {
        $trap = wp_unslash($_POST['psr_comment_contact_hint']);
        if (!is_string($trap) || trim($trap) !== '') { return 'spam'; }
    }
    $score = psr_antispam_score($commentdata);
    $promos = psr_antispam_promotional_signals($commentdata);
    if ($score >= 6 && $promos >= 2) { return 'spam'; }
    // Un IP condiviso (scuola/ufficio/rete mobile) non deve essere bloccato:
    // dopo una raffica il commento va soltanto in moderazione WordPress.
    if (psr_antispam_rate_exceeded($commentdata)) { return 0; }
    if ($score >= 4 && $promos >= 1) { return 0; }
    return $approved;
}
add_filter('pre_comment_approved', 'psr_antispam_check_comment', 25, 2);

/** Da usare esclusivamente per scansioni retroattive di commenti gia' pubblicati. */
function psr_antispam_is_existing_spam($comment) {
    if (!$comment || !is_object($comment)) { return false; }
    $type = (string)($comment->comment_type ?? '');
    if ($type !== '' && $type !== 'comment') { return false; }
    if (!in_array((string)($comment->comment_approved ?? ''), array('0', '1'), true)) { return false; }
    if (!empty($comment->user_id) && user_can((int)$comment->user_id, 'moderate_comments')) { return false; }
    $score = psr_antispam_score(array(
        'comment_content' => $comment->comment_content ?? '',
        'comment_author' => $comment->comment_author ?? '',
        'comment_author_url' => $comment->comment_author_url ?? '',
    ));
    // Richiede piu' evidenze. Sugli approvati una soglia ancora piu' elevata.
    return psr_antispam_promotional_signals(array(
        'comment_content' => $comment->comment_content ?? '',
        'comment_author' => $comment->comment_author ?? '',
    )) >= 2 && $score >= ((string)$comment->comment_approved === '1' ? 10 : 8);
}

function psr_antispam_schedule_scan($delay = 60) {
    if (!psr_antispam_enabled() || !get_option('_psr_antispam_scan_state')) { return; }
    if (!wp_next_scheduled('psr_antispam_scan_existing_batch')) {
        wp_schedule_single_event(time() + max(30, (int)$delay), 'psr_antispam_scan_existing_batch');
    }
}

/** Avvia un unico passaggio fino all'ultimo ID gia' presente all'attivazione. */
function psr_antispam_start_scan() {
    global $wpdb;
    if (!psr_antispam_enabled()) { return false; }
    $last_result = $wpdb->get_var("SELECT MAX(comment_ID) FROM {$wpdb->comments}");
    if (!empty($wpdb->last_error)) { return false; }
    $last = (int)$last_result;
    update_option('_psr_antispam_scan_state', array(
        'cursor' => 0, 'max_id' => $last, 'examined' => 0, 'flagged' => 0,
        'errors' => 0, 'started' => time(), 'updated' => time(),
    ), false);
    if ($last > 0) {
        psr_antispam_schedule_scan(300);
    } else {
        update_option('_psr_antispam_scan_last', get_option('_psr_antispam_scan_state'), false);
        delete_option('_psr_antispam_scan_state');
    }
    return true;
}

/**
 * Batch massimo 40 commenti, tempo massimo ~2 s, lock anti-concorrenza.
 * Cursor per ID evita OFFSET sempre piu' lenti; un errore DB non segna fine.
 */
function psr_antispam_scan_existing_batch() {
    if (!psr_antispam_enabled()) { return; }
    $state = get_option('_psr_antispam_scan_state');
    if (!is_array($state) || !isset($state['cursor'], $state['max_id'])) { return; }
    $lock = (int)get_option('_psr_antispam_scan_lock', 0);
    if ($lock > 0 && $lock >= time() - 180) { psr_antispam_schedule_scan(180); return; }
    if ($lock > 0) { delete_option('_psr_antispam_scan_lock'); }
    if (!add_option('_psr_antispam_scan_lock', time(), '', false)) { psr_antispam_schedule_scan(180); return; }

    global $wpdb;
    $started = microtime(true);
    $seen = 0;
    $cursor = (int)$state['cursor'];
    $max_id = (int)$state['max_id'];
    $finished = false;
    $db_failed = false;
    try {
        while ($seen < 40 && microtime(true) - $started < 2.0) {
            // Recupera ID reali, non tutti gli oggetti: meno memoria / overhead.
            $rows = $wpdb->get_col($wpdb->prepare(
                "SELECT comment_ID FROM {$wpdb->comments} WHERE comment_ID > %d AND comment_ID <= %d AND comment_approved IN ('0','1') AND (comment_type = '' OR comment_type = 'comment') ORDER BY comment_ID ASC LIMIT %d",
                $cursor, $max_id, min(20, 40 - $seen)
            ));
            if (!is_array($rows) || !empty($wpdb->last_error)) { $db_failed = true; break; }
            if (!$rows) { $finished = true; break; }
            foreach ($rows as $id) {
                $id = (int)$id;
                $cursor = max($cursor, $id);
                $seen++;
                $state['examined'] = (int)$state['examined'] + 1;
                $comment = get_comment($id);
                if (!$comment || !psr_antispam_is_existing_spam($comment)) { continue; }
                if (get_comment_meta($id, '_psr_antispam_auto_flagged', true)) { continue; }
                if (wp_spam_comment($id)) {
                    add_comment_meta($id, '_psr_antispam_auto_flagged', '1', true);
                    $state['flagged'] = (int)$state['flagged'] + 1;
                } else {
                    $state['errors'] = (int)$state['errors'] + 1;
                }
            }
        }
        $state['cursor'] = $cursor;
        $state['updated'] = time();
        if ($db_failed) { $state['errors'] = (int)$state['errors'] + 1; }
        if ($finished || $cursor >= $max_id) {
            update_option('_psr_antispam_scan_last', $state, false);
            delete_option('_psr_antispam_scan_state');
        } else {
            update_option('_psr_antispam_scan_state', $state, false);
            psr_antispam_schedule_scan($db_failed ? 600 : 180);
        }
    } finally {
        delete_option('_psr_antispam_scan_lock');
    }
}
add_action('psr_antispam_scan_existing_batch', 'psr_antispam_scan_existing_batch');

/**
 * Prima installazione: filtro ON per ogni Comune, senza sovrascrivere OFF
 * impostato esplicitamente dall'amministratore. Il controllo retroattivo
 * parte una sola volta; i normali aggiornamenti del tema non lo riavviano.
 * Nessun commento viene eliminato.
 */
function psr_antispam_default_setup_once() {
    if (get_option('_psr_antispam_default_setup_v1', '') === '1') { return; }
    $existing = get_option('_psr_antispam_comments_enabled', null);
    if ($existing === null) {
        add_option('_psr_antispam_comments_enabled', '1', '', false);
    }
    if (psr_antispam_enabled() &&
        !get_option('_psr_antispam_scan_state') &&
        !get_option('_psr_antispam_scan_last')) {
        if (!psr_antispam_start_scan()) { return; } // Ritenta se il database non risponde.
    }
    update_option('_psr_antispam_default_setup_v1', '1', false);
}
add_action('init', 'psr_antispam_default_setup_once', 100);

/** Recupero da un evento WP-Cron mancante, solo su visita del pannello ID 1. */
function psr_antispam_rearm_scan() {
    if (get_current_user_id() === 1) { psr_antispam_schedule_scan(120); }
}
add_action('admin_init', 'psr_antispam_rearm_scan', 25);

function psr_antispam_save() {
    if ((int)get_current_user_id() !== 1 || !current_user_can('manage_options')) {
        wp_die('Operazione non autorizzata.', '', array('response' => 403));
    }
    check_admin_referer('psr_antispam_save');
    $enabled = isset($_POST['psr_antispam_enabled']) && (string)$_POST['psr_antispam_enabled'] === '1';
    $was_enabled = psr_antispam_enabled();
    update_option('_psr_antispam_comments_enabled', $enabled ? '1' : '0', false);
    if ($enabled && !$was_enabled && !get_option('_psr_antispam_scan_last')) {
        psr_antispam_start_scan();
    }
    if (function_exists('psr_center_log_event')) {
        psr_center_log_event('security_setting_changed', 'warning', '', '', '', 'dashboard',
            'Antispam commenti: ' . ($enabled ? 'attivato' : 'disattivato') . ' da ID 1');
    }
    wp_safe_redirect(add_query_arg(array('page'=>'psr-security-center', 'tab'=>'antispam', 'psr_msg'=>'saved'), admin_url('admin.php')));
    exit;
}
add_action('admin_post_psr_antispam_save', 'psr_antispam_save');

/** Una nuova scansione e' sempre esplicita, dopo la prima automatica. */
function psr_antispam_rescan() {
    if ((int)get_current_user_id() !== 1 || !current_user_can('manage_options')) {
        wp_die('Operazione non autorizzata.', '', array('response' => 403));
    }
    check_admin_referer('psr_antispam_rescan');
    if (psr_antispam_enabled() && !get_option('_psr_antispam_scan_state') && !get_option('_psr_antispam_scan_lock')) { psr_antispam_start_scan(); }
    wp_safe_redirect(add_query_arg(array('page'=>'psr-security-center','tab'=>'antispam','psr_msg'=>'saved'), admin_url('admin.php')));
    exit;
}
add_action('admin_post_psr_antispam_rescan', 'psr_antispam_rescan');

function psr_antispam_admin_page() {
    if ((int)get_current_user_id() !== 1 || !current_user_can('manage_options')) {
        wp_die('Non autorizzato', '', array('response'=>403));
    }
    $enabled = psr_antispam_enabled();
    $counts = wp_count_comments();
    $spam = isset($counts->spam) ? (int)$counts->spam : 0;
    $pending = isset($counts->moderated) ? (int)$counts->moderated : 0;
    $state = get_option('_psr_antispam_scan_state');
    $last = get_option('_psr_antispam_scan_last');

    echo '<div class="psrc-card"><h2>Antispam commenti WordPress</h2>';
    echo '<p>Filtra soltanto i commenti WordPress. Non modifica moduli dei cittadini, AJAX, prenotazioni, OTP o sessioni.</p>';
    echo '<p><strong>Stato: </strong><span class="psrc-pill '.($enabled?'ok':'warning').'">'.($enabled?'Attivo':'Disattivato').'</span></p>';
    echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
    wp_nonce_field('psr_antispam_save');
    echo '<input type="hidden" name="action" value="psr_antispam_save">';
    echo '<label><input type="checkbox" name="psr_antispam_enabled" value="1" '.checked($enabled, true, false).'> Abilita il filtro antispam su questo Comune</label>';
    echo '<p class="description">Attivo per impostazione predefinita. La prima installazione avvia una sola scansione graduale dei commenti preesistenti; solo indizi pubblicitari forti li spostano in Spam, mai eliminati dal modulo. Puoi disattivare il filtro qui.</p>';
    echo '<p><button type="submit" class="button button-primary">Salva impostazione</button></p></form>';

    echo '<hr><h3>Commenti e scansione retroattiva</h3>';
    echo '<p>In Spam: <strong>'.esc_html((string)$spam).'</strong> &nbsp; | &nbsp; Da approvare: <strong>'.esc_html((string)$pending).'</strong></p>';
    if (is_array($state)) {
        echo '<p><strong>Scansione in corso:</strong> '.(int)($state['examined']??0).' esaminati, '.(int)($state['flagged']??0).' passati in Spam; avanzamento fino all\'ID '.(int)($state['cursor']??0).' di '.(int)($state['max_id']??0).'.</p>';
        echo '<p class="description">L\'avanzamento usa WP-Cron: richiede traffico sul sito o un cron server gia\' configurato.</p>';
    } elseif (is_array($last)) {
        echo '<p><strong>Ultima scansione conclusa:</strong> '.(int)($last['examined']??0).' esaminati, '.(int)($last['flagged']??0).' passati in Spam, '.(int)($last['errors']??0).' errori.</p>';
    } else {
        echo '<p>Scansione retroattiva non ancora eseguita.</p>';
    }
    if ($enabled && !is_array($state)) {
        echo '<form method="post" action="'.esc_url(admin_url('admin-post.php')).'">';
        wp_nonce_field('psr_antispam_rescan');
        echo '<input type="hidden" name="action" value="psr_antispam_rescan">';
        echo '<button type="submit" class="button">Avvia nuova scansione dei commenti esistenti</button></form>';
    }
    echo '<p><a class="button" href="'.esc_url(admin_url('edit-comments.php?comment_status=spam')).'">Apri Spam e recupera eventuali falsi positivi</a></p>';
    echo '<p class="description">I messaggi palesemente promozionali sono spostati in Spam e restano recuperabili. I casi incerti non sono eliminati. Dopo 20 commenti per 10 minuti dallo stesso IP, i successivi vengono moderati senza impedire l’invio (solo commenti).</p>';
    echo '</div>';
}
