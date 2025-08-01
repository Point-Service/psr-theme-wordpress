<?php
/**
 * Template Name: Sitemap Personalizzata (Gerarchica e Stilizzata)
 */

get_header();

function sitemap_gerarchica($parent_id = 0) {
    $args = array(
        'parent' => $parent_id,
        'sort_column' => 'post_title',
        'sort_order' => 'asc',
        'post_type' => 'page',
        'post_status' => 'publish',
    );

    $pages = get_pages($args);

    if ($pages) {
        echo '<ul class="sitemap-list">';
        foreach ($pages as $page) {
            echo '<li>';
            echo '<a href="' . get_permalink($page->ID) . '">' . esc_html($page->post_title) . '</a>';
            // Ricorsione per figli
            sitemap_gerarchica($page->ID);
            echo '</li>';
        }
        echo '</ul>';
    }
}
?>

<style>
    .sitemap-list {
        list-style: none;
        padding-left: 1rem;
        margin-bottom: 1rem;
        border-left: 2px solid #ccc;
    }
    .sitemap-list li {
        margin: 0.5rem 0;
        position: relative;
        padding-left: 1rem;
    }
    .sitemap-list li::before {
        content: '▸';
        position: absolute;
        left: -1.2rem;
        color: #0073aa;
        transition: color 0.3s ease;
    }
    .sitemap-list li a {
        text-decoration: none;
        color: #0073aa;
        font-weight: 600;
        transition: color 0.3s ease;
    }
    .sitemap-list li a:hover {
        color: #005177;
        text-decoration: underline;
    }
</style>

<div class="container my-5">
    <h1>Mappa del sito</h1>
    <?php sitemap_gerarchica(); ?>
</div>

<?php get_footer(); ?>
