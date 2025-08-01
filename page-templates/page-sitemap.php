<?php
/**
 * Template Name: Sitemap Modern PA con Sottolivelli
 */

get_header();

function sitemap_comune_style($parent_id = 0) {
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
            // Se livello top (parent = 0) mettiamo il titolo come h3 e classe sitemap-section
            if ($parent_id == 0) {
                echo '<li class="sitemap-section">';
                echo '<h3><a href="' . get_permalink($page->ID) . '">' . esc_html($page->post_title) . '</a></h3>';
            } else {
                echo '<li>';
                echo '<a href="' . get_permalink($page->ID) . '">' . esc_html($page->post_title) . '</a>';
            }

            // Ricorsione per i figli
            sitemap_comune_style($page->ID);

            echo '</li>';
        }
        echo '</ul>';
    }
}
?>

<style>
    /* Font e palette colori istituzionali PA */
    .sitemap-list {
        list-style: none;
        margin-left: 0;
        padding-left: 0;
        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
        color: #1a1a1a;
    }

    /* Sezione principale */
    .sitemap-section {
        margin-bottom: 2rem;
        border-left: 5px solid #005aa7; /* blu istituzionale PA */
        padding-left: 1rem;
    }

    /* Titolo sezione */
    .sitemap-section > h3 {
        font-weight: 700;
        font-size: 1.6rem;
        margin-bottom: 0.5rem;
        color: #005aa7;
        border-bottom: 2px solid #005aa7;
        padding-bottom: 0.3rem;
    }

    .sitemap-section > h3 a {
        color: #005aa7;
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .sitemap-section > h3 a:hover,
    .sitemap-section > h3 a:focus {
        color: #007acc;
        text-decoration: underline;
        outline-offset: 3px;
        outline: 2px solid #007acc;
        outline-radius: 4px;
    }

    /* Lista annidata */
    .sitemap-list ul {
        margin-left: 1.5rem;
        margin-top: 0.5rem;
        padding-left: 1rem;
        border-left: 2px solid #d0d7de;
    }

    /* Link pagine secondarie e successive */
    .sitemap-list li > a {
        font-weight: 600;
        color: #003c70;
        text-decoration: none;
        display: inline-block;
        margin: 0.25rem 0;
        transition: color 0.3s ease;
    }

    .sitemap-list li > a:hover,
    .sitemap-list li > a:focus {
        color: #005aa7;
        text-decoration: underline;
        outline-offset: 3px;
        outline: 2px solid #005aa7;
        outline-radius: 4px;
    }

    /* Spaziatura tra items */
    .sitemap-list li {
        margin-bottom: 0.4rem;
    }

    /* Responsive */
    @media (max-width: 600px) {
        .sitemap-section > h3 {
            font-size: 1.3rem;
        }

        .sitemap-list li > a {
            font-size: 0.95rem;
        }
    }
</style>

<div class="container my-5">
    <h1>Mappa del sito</h1>
    <?php sitemap_comune_style(); ?>
</div>

<?php get_footer(); ?>
