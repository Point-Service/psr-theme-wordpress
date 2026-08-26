<?php
function dci_get_amministrazione_politica(WP_REST_Request $request) {
    $cache_key = 'dci_api_amministrazione_politica_v4';
    $cached = get_transient($cache_key);
    if (is_array($cached)) {
        return $cached;
    }

    // Confronto a inizio giornata, coerente con la pagina Politici del sito.
    $today_ts = strtotime(date('Y-m-d', current_time('timestamp')));

    $candidate_person_ids = array();
    $incarichi_politici_attivi = array();
    $ruoli_politici_per_persona = array();

    // Recupera esclusivamente gli incarichi classificati come politici.
    $incarichi_politici = get_posts(array(
        'post_type' => 'incarico',
        'post_status' => 'publish',
        'numberposts' => -1,
        'fields' => 'ids',
        'tax_query' => array(
            array(
                'taxonomy' => 'tipi_incarico',
                'field' => 'slug',
                'terms' => array('politico'),
            ),
        ),
    ));

    foreach ($incarichi_politici as $incarico_id) {
        $incarico_id = (int) $incarico_id;
        if ($incarico_id <= 0) {
            continue;
        }

        $incarico = get_post($incarico_id);
        if (!$incarico instanceof WP_Post || $incarico->post_status !== 'publish') {
            continue;
        }

        $persone_incarico = dci_normalize_meta_ids(get_post_meta($incarico_id, '_dci_incarico_persona', false));
        foreach ($persone_incarico as $persona_id) {
            $candidate_person_ids[$persona_id] = true;
        }

        $fine_incarico = dci_get_meta('data_conclusione_incarico', '_dci_incarico_', $incarico_id);
        if (!empty($fine_incarico)) {
            $fine_incarico_ts = is_numeric($fine_incarico)
                ? intval($fine_incarico)
                : strtotime(str_replace('/', '-', (string) $fine_incarico));

            if ($fine_incarico_ts && $fine_incarico_ts < $today_ts) {
                continue;
            }
        }

        $incarichi_politici_attivi[$incarico_id] = $incarico->post_title;

        foreach ($persone_incarico as $persona_id) {
            $ruoli_politici_per_persona[$persona_id][$incarico_id] = $incarico->post_title;
        }
    }

    // Mantiene il fallback già presente per Sindaco, Giunta e Consiglio.
    $uo_politiche = get_posts(array(
        'post_type' => 'unita_organizzativa',
        'post_status' => 'publish',
        'numberposts' => -1,
        'fields' => 'ids',
        'post_name__in' => array('sindaco', 'giunta-comunale', 'consiglio-comunale'),
    ));

    foreach ($uo_politiche as $uo_id) {
        $responsabili = dci_normalize_meta_ids(dci_get_meta('responsabile', '_dci_unita_organizzativa_', $uo_id));
        $persone_struttura = dci_normalize_meta_ids(dci_get_meta('persone_struttura', '_dci_unita_organizzativa_', $uo_id));

        foreach (array_merge($responsabili, $persone_struttura) as $persona_id) {
            $candidate_person_ids[$persona_id] = true;
        }
    }

    if (empty($candidate_person_ids)) {
        set_transient($cache_key, array(), 5 * MINUTE_IN_SECONDS);
        return array();
    }

    $response = array();

    $role_rank = function ($role_title) {
        $normalized = sanitize_title((string) $role_title);

        $priority_map = array(
            'sindaco' => 0,
            'vicesindaco' => 1,
            'vice-sindaco' => 1,
            'assessore' => 2,
            'giunta-comunale' => 2,
            'presidente-del-consiglio' => 3,
            'vicepresidente-del-consiglio' => 4,
            'consigliere' => 5,
            'consigliere-comunale' => 5,
        );

        if (isset($priority_map[$normalized])) {
            return $priority_map[$normalized];
        }

        // Controlli "vice" prima dei ruoli principali per evitare classificazioni errate.
        if (strpos($normalized, 'vice') !== false && strpos($normalized, 'sindaco') !== false) {
            return 1;
        }
        if (strpos($normalized, 'vicepresidente-del-consiglio') !== false) {
            return 4;
        }
        if (strpos($normalized, 'sindaco') !== false) {
            return 0;
        }
        if (strpos($normalized, 'assessore') !== false || strpos($normalized, 'giunta') !== false) {
            return 2;
        }
        if (strpos($normalized, 'presidente-del-consiglio') !== false) {
            return 3;
        }
        if (strpos($normalized, 'consigliere') !== false) {
            return 5;
        }

        return 99;
    };

    foreach (array_keys($candidate_person_ids) as $person_id) {
        $person = get_post($person_id);
        if (!$person instanceof WP_Post || $person->post_type !== 'persona_pubblica' || $person->post_status !== 'publish') {
            continue;
        }

        $data_conclusione_persona = dci_get_meta('data_conclusione_incarico', '_dci_persona_pubblica_', $person_id);
        if (!empty($data_conclusione_persona)) {
            $fine_persona_ts = is_numeric($data_conclusione_persona)
                ? intval($data_conclusione_persona)
                : strtotime(str_replace('/', '-', (string) $data_conclusione_persona));

            if ($fine_persona_ts && $fine_persona_ts < $today_ts) {
                continue;
            }
        }

        // Parte dai collegamenti incarico -> persona e completa con persona -> incarichi.
        $ruoli = isset($ruoli_politici_per_persona[$person_id])
            ? $ruoli_politici_per_persona[$person_id]
            : array();

        $incarichi_persona = dci_normalize_meta_ids(dci_get_meta('incarichi', '_dci_persona_pubblica_', $person_id));
        foreach ($incarichi_persona as $incarico_id) {
            if (isset($incarichi_politici_attivi[$incarico_id])) {
                $ruoli[$incarico_id] = $incarichi_politici_attivi[$incarico_id];
            }
        }

        $ruoli_unici = array_values(array_unique(array_filter(array_values($ruoli))));
        if (empty($ruoli_unici)) {
            continue;
        }

        usort($ruoli_unici, function ($a, $b) use ($role_rank) {
            $rank_cmp = $role_rank($a) <=> $role_rank($b);
            if ($rank_cmp !== 0) {
                return $rank_cmp;
            }

            return strcasecmp((string) $a, (string) $b);
        });

        $thumbnail_id = get_post_thumbnail_id($person_id);
        $foto_persona = dci_get_meta('foto', '_dci_persona_pubblica_', $person_id);
        $immagine = $thumbnail_id ? wp_get_attachment_image_url($thumbnail_id, 'full') : null;

        if (empty($immagine) && !empty($foto_persona)) {
            if (is_numeric($foto_persona)) {
                $img_url = wp_get_attachment_image_url((int) $foto_persona, 'full');
                $immagine = $img_url ? $img_url : null;
            } elseif (is_array($foto_persona) && isset($foto_persona['url']) && is_string($foto_persona['url'])) {
                $immagine = $foto_persona['url'];
            } elseif (is_string($foto_persona)) {
                $immagine = $foto_persona;
            }
        }

        $contatti = dci_get_contatti_da_punti_ids(
            dci_normalize_meta_ids(dci_get_meta('punti_contatto', '_dci_persona_pubblica_', $person_id))
        );

        $response[] = array(
            'id' => $person_id,
            'nome' => get_the_title($person_id),
            'url' => get_permalink($person_id),
            'ruoli' => $ruoli_unici,
            'descrizione_breve' => dci_get_meta('descrizione_breve', '_dci_persona_pubblica_', $person_id),
            'immagine' => $immagine,
            'contatti' => $contatti,
        );
    }

    usort($response, function ($a, $b) use ($role_rank) {
        $a_primary = isset($a['ruoli'][0]) ? $role_rank($a['ruoli'][0]) : 99;
        $b_primary = isset($b['ruoli'][0]) ? $role_rank($b['ruoli'][0]) : 99;

        $rank_cmp = $a_primary <=> $b_primary;
        if ($rank_cmp !== 0) {
            return $rank_cmp;
        }

        return strcasecmp((string) $a['nome'], (string) $b['nome']);
    });

    set_transient($cache_key, $response, 5 * MINUTE_IN_SECONDS);

    return $response;
}
