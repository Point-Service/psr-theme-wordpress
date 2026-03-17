<?php
if (!isset($term_id)) {
    return;
}

// TERZO LIVELLO
$sub_sub_categories = get_terms('tipi_cat_amm_trasp', array(
    'hide_empty' => false,
    'parent' => $term_id
));








// 👉 ORDINAMENTO LIVELLO 3
if (!empty($sub_sub_categories) && !is_wp_error($sub_sub_categories)) {
usort($sub_sub_categories, function($a, $b) {

    $ordinamento_a = get_term_meta($a->term_id, 'ordinamento', true);
    $ordinamento_b = get_term_meta($b->term_id, 'ordinamento', true);

    // 👉 vuoto = 0
    $ordinamento_a = ($ordinamento_a === '' || $ordinamento_a === null) ? 0 : (int)$ordinamento_a;
    $ordinamento_b = ($ordinamento_b === '' || $ordinamento_b === null) ? 0 : (int)$ordinamento_b;

    // prima per ordinamento
    if ($ordinamento_a === $ordinamento_b) {
        // fallback per stabilità
        return strcmp($a->name, $b->name);
    }

    return $ordinamento_a <=> $ordinamento_b;
});
}

if (!empty($sub_sub_categories) && !is_wp_error($sub_sub_categories)) { ?>
    <ul class="sub-sub-list">
        <?php foreach ($sub_sub_categories as $sub_sub) { 

            $term_url = get_term_meta($sub_sub->term_id, 'term_url', true);
            $open_new_window = get_term_meta($sub_sub->term_id, 'open_new_window', true);

            if (!empty($term_url)) {
                $link = $term_url;
                $target = $open_new_window ? ' target="_blank"' : '';
            } else {
                $link = get_term_link($sub_sub->term_id);
                $target = '';
            }
        ?>
            <li>
                <a href="<?= esc_url($link); ?>"<?= $target; ?>>
                    <?= esc_html($sub_sub->name); ?>
                </a>

                <?php
                // QUARTO LIVELLO
                $sub_sub_sub_categories = get_terms('tipi_cat_amm_trasp', array(
                    'hide_empty' => false,
                    'parent' => $sub_sub->term_id
                ));

                // 👉 ORDINAMENTO LIVELLO 4
                if (!empty($sub_sub_sub_categories) && !is_wp_error($sub_sub_sub_categories)) {
                    usort($sub_sub_categories, function($a, $b) {
                    
                        $ordinamento_a = get_term_meta($a->term_id, 'ordinamento', true);
                        $ordinamento_b = get_term_meta($b->term_id, 'ordinamento', true);
                    
                        // 👉 vuoto = 0
                        $ordinamento_a = ($ordinamento_a === '' || $ordinamento_a === null) ? 0 : (int)$ordinamento_a;
                        $ordinamento_b = ($ordinamento_b === '' || $ordinamento_b === null) ? 0 : (int)$ordinamento_b;
                    
                        // prima per ordinamento
                        if ($ordinamento_a === $ordinamento_b) {
                            // fallback per stabilità
                            return strcmp($a->name, $b->name);
                        }
                    
                        return $ordinamento_a <=> $ordinamento_b;
                    });
                }

                if (!empty($sub_sub_sub_categories) && !is_wp_error($sub_sub_sub_categories)) { ?>
                    <ul class="sub-sub-list">
                        <?php foreach ($sub_sub_sub_categories as $sub_sub_sub) { ?>
                            <li>
                                <a href="<?= esc_url(get_term_link($sub_sub_sub->term_id)); ?>">
                                    <?= esc_html($sub_sub_sub->name); ?>
                                </a>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } ?>

            </li>
        <?php } ?>
    </ul>
<?php } ?>

