<?php
/**
 * Template Name: Sitemap Personalizzata
 */

get_header(); ?>

<div class="container my-5">
    <h1>Mappa del sito</h1>

    <ul>
        <?php
        $pages = get_pages(array(
            'sort_column' => 'post_title',
            'sort_order' => 'asc'
        ));

        foreach ($pages as $page) {
            echo '<li>';
            echo '<a href="' . get_permalink($page->ID) . '">' . esc_html($page->post_title) . '</a>';

            // Mostra sottopagine, se ci sono
            $children = get_pages(array(
                'child_of' => $page->ID,
                'sort_column' => 'post_title',
                'sort_order' => 'asc'
            ));

            if (!empty($children)) {
                echo '<ul>';
                foreach ($children as $child) {
                    echo '<li><a href="' . get_permalink($child->ID) . '">' . esc_html($child->post_title) . '</a></li>';
                }
                echo '</ul>';
            }

            echo '</li>';
        }
        ?>
    </ul>
</div>

<?php get_footer(); ?>
