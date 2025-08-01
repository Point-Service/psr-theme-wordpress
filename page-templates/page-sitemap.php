<?php
/**
 * Template Name: Sitemap Personalizzata PA
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
        echo '<ul class="pa-sitemap-list">';
        foreach ($pages as $page) {
            echo '<li>';
            echo '<a href="' . get_permalink($page->ID) . '">' . esc_html($page->post_title) . '</a>';
            sitemap_gerarchica($page->ID);
            echo '</li>';
        }
        echo '</ul>';
    }
}
?>

<style>
    /* Font e colori istituzionali */
    .pa-sitemap-list {
        list-style: none;
        padding-left: 1.25rem;
        margin-bottom: 2rem;
        border-left: 3px solid #004a99; /* blu istituzionale */
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen,
            Ubuntu, Cantarell, "Open Sans", "Helvetica Neue", sans-serif;
        font-size: 1rem;
        color: #333;
    }

    .pa-sitemap-list li {
        margin: 0.75rem 0;
        position: relative;
        padding-left: 1.25rem;
        transition: background-color 0.3s ease;
    }

    .pa-sitemap-list li::before {
        content: "›"; /* simbolo elegante */
        position: absolute;
        left: -1.25rem;
        color: #004a99;
        font-weight: bold;
        font-size: 1.25rem;
        line-height: 1;
        top: 0.1rem;
        transition: color 0.3s ease;
    }

    .pa-sitemap-list li:hover::before {
        color: #0073e6; /* colore hover più chiaro */
    }

    .pa-sitemap-list li a {
        text-decoration: none;
        color: #004a99;
        font-weight: 600;
        transition: color 0.3s ease;
    }

    .pa-sitemap-list li a:hover,
    .pa-sitemap-list li a:focus {
        color: #0073e6;
        text-decoration: underline;
        outline-offset: 2px;
        outline: 2px solid #0073e6;
        outline-radius: 4px;
    }

    /* Gestione indentazione e spazi per i livelli successivi */
    .pa-sitemap-list ul {
        margin-top: 0.5rem;
        margin-left: 1rem;
        border-left: 2px solid #cbd5e1; /* linea più chiara per sotto-livelli */
        padding-left: 1rem;
    }

    /* Responsive */
    @media (max-width: 600px) {
        .pa-sitemap-list {
            font-size: 0.9rem;
        }
        .pa-sitemap-list li::before {
            font-size: 1rem;
            left: -1rem;
            top: 0.15rem;
        }
    }
</style>

<div class="container my-5">
    <h1>Mappa del sito</h1>
    <?php sitemap_gerarchica(); ?>
</div>

<?php get_footer(); ?>

