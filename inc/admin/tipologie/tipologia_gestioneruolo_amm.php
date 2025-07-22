<?php
/**
 * Pagina di gestione permessi categorie per ruolo
 * File: tipologia_gestioneruolo_amm.php
 */

// Aggiungi voce al menu admin
add_action('admin_menu', 'dci_add_permessi_ruoli_submenu');
function dci_add_permessi_ruoli_submenu() {
    add_submenu_page(
        'edit.php?post_type=elemento_trasparenza',   // slug menu padre
        __('Gestione Permessi Ruoli', 'design_comuni_italia'), // titolo pagina
        __('Permessi Trasparenza', 'design_comuni_italia'),   // titolo menu
        'manage_options',    // capability
        'gestione_permessi_ruoli',   // slug pagina
        'dci_render_permessi_ruoli_page'  // callback funzione
    );
}

// Funzione ricorsiva per stampare i termini gerarchici con simboli
function dci_stampa_termini_gerarchici($terms_by_parent, $parent_id = 0, $ruolo_selezionato = '') {
    if (!isset($terms_by_parent[$parent_id])) {
        return;
    }

    foreach ($terms_by_parent[$parent_id] as $term) {
        // Calcola profondità (livello)
        $level = 0;
        $p = $term->parent;
        global $terms_by_id;
        while ($p != 0) {
            $level++;
            $p = isset($terms_by_id[$p]) ? $terms_by_id[$p]->parent : 0;
        }

        // Recupera ruoli esclusi
        $excluded_roles = get_term_meta($term->term_id, 'excluded_roles', true);
        if (!is_array($excluded_roles)) {
            $excluded_roles = [];
        }
        $checked = !in_array($ruolo_selezionato, $excluded_roles);

        // Definisci simbolo da mostrare in base al livello
        if ($level === 0) {
            $symbol = '&nbsp;● '; // nessun simbolo per livello 0
        } elseif ($level === 1) {
            $symbol = '➤ ';
        } elseif ($level === 2) {
            $symbol = '➔ ';
        } else {
            $symbol = str_repeat('· ', $level - 2);
        }

        // Stile indentazione e grassetto per livello 0 (genitore)
        $style = 'padding-left: ' . (20 * $level) . 'px;';
        if ($level === 0) {
            $style .= ' font-weight: bold;';
        }

        echo '<tr>';
        echo '<td style="' . esc_attr($style) . '">' . $symbol . esc_html($term->name) . '</td>';
        echo '<td><input type="checkbox" name="permessi_ruolo[]" value="' . esc_attr($term->term_id) . '" ' . checked($checked, true, false) . '></td>';
        echo '</tr>';

        // Ricorsione per figli
        dci_stampa_termini_gerarchici($terms_by_parent, $term->term_id, $ruolo_selezionato);
    }
}

// Render pagina
function dci_render_permessi_ruoli_page() {
    $ruoli = wp_roles()->roles;
    $ruolo_selezionato = isset($_GET['ruolo']) ? sanitize_text_field($_GET['ruolo']) : '';
    $categorie = get_terms(array(
        'taxonomy' => 'tipi_cat_amm_trasp',
        'hide_empty' => false,
        'orderby' => 'name',
        'order' => 'ASC',
        'parent' => 0, // partiamo dai top-level
    ));

    // Prendiamo tutti i termini per costruire mappa id->term
    $all_terms = get_terms(array(
        'taxonomy' => 'tipi_cat_amm_trasp',
        'hide_empty' => false,
    ));

    // Mappa ID -> term per calcolo livello e genitore
    global $terms_by_id;
    $terms_by_id = [];
    foreach ($all_terms as $term) {
        $terms_by_id[$term->term_id] = $term;
    }

    // Organizza termini per parent
    $terms_by_parent = [];
    foreach ($all_terms as $term) {
        $terms_by_parent[$term->parent][] = $term;
    }
    ?>

    <div class="wrap">
        <h1><?php _e('Permessi per Ruolo - Trasparenza', 'design_comuni_italia'); ?></h1>

        <div style="display: flex; gap: 2rem; align-items: flex-start;">

            <!-- COLONNA RUOLI -->
            <div style="width: 250px;">
                <h2><?php _e('Ruoli', 'design_comuni_italia'); ?></h2>
                <ul style="list-style: none; padding: 0;">
                    <?php foreach ($ruoli as $slug => $dati): ?>
                        <li style="margin-bottom: 0.5rem;">
                            <a href="<?php echo admin_url('admin.php?page=gestione_permessi_ruoli&ruolo=' . esc_attr($slug)); ?>"
                               style="<?php echo ($slug === $ruolo_selezionato) ? 'font-weight: bold;' : ''; ?>">
                                <?php echo esc_html(translate_user_role($dati['name'])); ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <!-- COLONNA CATEGORIE -->
            <div style="flex: 1;">
                <?php if ($ruolo_selezionato): ?>
                    <h2><?php echo sprintf(__('Permessi per il ruolo: %s', 'design_comuni_italia'), translate_user_role($ruoli[$ruolo_selezionato]['name'])); ?></h2>

                    <form method="post">
                        <?php wp_nonce_field('salva_permessi_ruoli', 'permessi_ruoli_nonce'); ?>
                        <input type="hidden" name="ruolo" value="<?php echo esc_attr($ruolo_selezionato); ?>">

                        <p>
                            <button type="button" class="button" id="seleziona-tutti"><?php _e('Seleziona tutto', 'design_comuni_italia'); ?></button>
                            <button type="button" class="button" id="deseleziona-tutti"><?php _e('Deseleziona tutto', 'design_comuni_italia'); ?></button>
                        </p>

                        <table class="widefat fixed striped">
                            <thead>
                                <tr>
                                    <th><?php _e('Categoria', 'design_comuni_italia'); ?></th>
                                    <th><?php _e('Accesso consentito?', 'design_comuni_italia'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                // Stampa ricorsiva gerarchica
                                dci_stampa_termini_gerarchici($terms_by_parent, 0, $ruolo_selezionato);
                                ?>
                            </tbody>
                        </table>

                        <p>
                            <?php submit_button(__('Salva Permessi', 'design_comuni_italia'), 'primary', 'salva_permessi_ruolo', false); ?>
                        </p>
                    </form>

                    <script>
                    document.getElementById('seleziona-tutti').addEventListener('click', function () {
                        document.querySelectorAll('input[type="checkbox"][name="permessi_ruolo[]"]').forEach(function (checkbox) {
                            checkbox.checked = true;
                        });
                    });

                    document.getElementById('deseleziona-tutti').addEventListener('click', function () {
                        document.querySelectorAll('input[type="checkbox"][name="permessi_ruolo[]"]').forEach(function (checkbox) {
                            checkbox.checked = false;
                        });
                    });
                    </script>
                <?php else: ?>
                    <p><?php _e('Seleziona un ruolo a sinistra per gestire i permessi.', 'design_comuni_italia'); ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php
}

// Gestione salvataggio permessi
add_action('admin_init', 'dci_salva_permessi_ruoli');
function dci_salva_permessi_ruoli() {
    if (
        isset($_POST['salva_permessi_ruolo']) &&
        isset($_POST['permessi_ruoli_nonce']) &&
        wp_verify_nonce($_POST['permessi_ruoli_nonce'], 'salva_permessi_ruoli')
    ) {
        $ruolo = sanitize_text_field($_POST['ruolo']);
        $tutti_termini = get_terms(array(
            'taxonomy' => 'tipi_cat_amm_trasp',
            'hide_empty' => false,
        ));

        $permessi_consentiti = isset($_POST['permessi_ruolo']) ? array_map('intval', $_POST['permessi_ruolo']) : array();

        foreach ($tutti_termini as $term) {
            $excluded = get_term_meta($term->term_id, 'excluded_roles', true);
            $excluded = is_array($excluded) ? $excluded : [];

            if (in_array($term->term_id, $permessi_consentiti)) {
                // Rimuovo ruolo se presente
                if (in_array($ruolo, $excluded)) {
                    $excluded = array_diff($excluded, [$ruolo]);
                    update_term_meta($term->term_id, 'excluded_roles', array_values($excluded));
                }
            } else {
                // Aggiungo ruolo se non presente
                if (!in_array($ruolo, $excluded)) {
                    $excluded[] = $ruolo;
                    update_term_meta($term->term_id, 'excluded_roles', array_values(array_unique($excluded)));
                }
            }
        }

        wp_safe_redirect(admin_url('admin.php?page=gestione_permessi_ruoli&ruolo=' . urlencode($ruolo) . '&aggiornato=1'));
        exit;
    }
}

