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
.it-header-slim-wrapper {
    background-color: var(--main-header-bg) !important;
}

.sitemap-section {
    margin-bottom: 2rem;
    border-left: 5px solid var(--main-header-bg);
    padding-left: 1rem;
}

.sitemap-section > h3 {
    font-weight: 700;
    font-size: 1.6rem;
    margin-bottom: 0.5rem;
    color: var(--main-header-bg);
    border-bottom: 2px solid var(--main-header-bg);
    padding-bottom: 0.3rem;
}

.sitemap-section > h3 a {
    color: var(--main-header-bg);
    text-decoration: none;
    transition: color 0.3s ease;
}

.sitemap-section > h3 a:hover,
.sitemap-section > h3 a:focus {
    color: var(--main-header-bg-light);
    text-decoration: underline;
    outline-offset: 3px;
    outline: 2px solid var(--main-header-bg-light);
    outline-radius: 4px;
}

.sitemap-list ul {
    margin-left: 1.5rem;
    margin-top: 0.5rem;
    padding-left: 1rem;
    border-left: 2px solid #d0d7de;
}

.sitemap-list li > a {
    font-weight: 600;
    color: var(--main-header-bg);
    text-decoration: none;
    display: inline-block;
    margin: 0.25rem 0;
    transition: color 0.3s ease;
}

.sitemap-list li > a:hover,
.sitemap-list li > a:focus {
    color: var(--main-header-bg-light);
    text-decoration: underline;
    outline-offset: 3px;
    outline: 2px solid var(--main-header-bg-light);
    outline-radius: 4px;
}

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
