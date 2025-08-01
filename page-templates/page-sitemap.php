<?php
/**
 * Template Name: Sitemap Modern PA con Sottolivelli
 */

get_header();

function sitemap_comune_style($parent_id = 0) {
    $args = array(
        'post_type'    => 'page',
        'post_status'  => 'publish',
        'sort_column'  => 'menu_order, post_title',
        'sort_order'   => 'asc',
        'parent'       => $parent_id,
    );

    $pages = get_pages($args);

    if ($pages) {
        echo '<ul class="sitemap-list">';
        foreach ($pages as $page) {
            $children = get_pages(array(
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'sort_column'  => 'menu_order, post_title',
                'sort_order'   => 'asc',
                'parent'       => $page->ID,
            ));

            if ($parent_id == 0) {
                echo '<li class="sitemap-section">';
                echo '<h3><a href="' . get_permalink($page->ID) . '">' . esc_html($page->post_title) . '</a></h3>';
            } else {
                echo '<li>';
                echo '<a href="' . get_permalink($page->ID) . '">' . esc_html($page->post_title) . '</a>';
            }

            if (!empty($children)) {
                sitemap_comune_style($page->ID);
            }

            echo '</li>';
        }
        echo '</ul>';
    }
}
?>

<style>
    /* Definisci queste variabili nel tuo tema o nel file style.css globale */
    :root {
        --pa-blue: #005aa7;        /* blu istituzionale PA */
        --pa-blue-light: #007acc;  /* blu chiaro */
        --pa-blue-dark: #003c70;   /* blu scuro */
        --pa-border-light: #d0d7de; /* bordo chiaro */
        --pa-text: #1a1a1a;        /* testo principale */
        --font-family-pa: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
    }

    .sitemap-list {
        list-style: none;
        margin-left: 0;
        padding-left: 0;
        font-family: var(--font-family-pa);
        color: var(--pa-text);
    }

    /* Sezione principale */
    .sitemap-section {
        margin-bottom: 2rem;
        border-left: 5px solid var(--pa-blue);
        padding-left: 1rem;
    }

    /* Titolo sezione */
    .sitemap-section > h3 {
        font-weight: 700;
        font-size: 1.6rem;
        margin-bottom: 0.5rem;
        color: var(--pa-blue);
        border-bottom: 2px solid var(--pa-blue);
        padding-bottom: 0.3rem;
    }

    .sitemap-section > h3 a {
        color: var(--pa-blue);
        text-decoration: none;
        transition: color 0.3s ease;
    }

    .sitemap-section > h3 a:hover,
    .sitemap-section > h3 a:focus {
        color: var(--pa-blue-light);
        text-decoration: underline;
        outline-offset: 3px;
        outline: 2px solid var(--pa-blue-light);
        outline-radius: 4px;
    }

    /* Lista annidata */
    .sitemap-list ul {
        margin-left: 1.5rem;
        margin-top: 0.5rem;
        padding-left: 1rem;
        border-left: 2px solid var(--pa-border-light);
    }

    /* Link pagine secondarie e successive */
    .sitemap-list li > a {
        font-weight: 600;
        color: var(--pa-blue-dark);
        text-decoration: none;
        display: inline-block;
        margin: 0.25rem 0;
        transition: color 0.3s ease;
    }

    .sitemap-list li > a:hover,
    .sitemap-list li > a:focus {
        color: var(--pa-blue);
        text-decoration: underline;
        outline-offset: 3px;
        outline: 2px solid var(--pa-blue);
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
