<?php
global $the_query, $load_posts;

$count = 0;
$max_posts = isset($_GET['max_posts']) ? (int) $_GET['max_posts'] : 1000000;
$load_posts = 6;
$query = isset($_GET['search']) ? dci_removeslashes($_GET['search']) : null;

/* =========================
   FUNZIONI (le tue, intatte)
========================= */

if (!function_exists('dci_articolazione_post_matches')) {
    function dci_articolazione_post_matches($post, $terms, $keywords)
    {
        $haystacks = [
            sanitize_title($post->post_title),
            sanitize_title($post->post_name),
        ];

        if (!empty($terms) && !is_wp_error($terms)) {
            foreach ($terms as $term) {
                $haystacks[] = sanitize_title($term->slug);
                $haystacks[] = sanitize_title($term->name);
            }
        }

        foreach ($keywords as $keyword) {
            $needle = sanitize_title($keyword);
            foreach ($haystacks as $haystack) {
                if ($haystack !== '' && strpos($haystack, $needle) !== false) {
                    return true;
                }
            }
        }

        return false;
    }
}

/* =========================
   QUERY
========================= */

$args = [
    's' => $query,
    'posts_per_page' => $max_posts,
    'post_type' => 'unita_organizzativa',
    'orderby' => 'post_title',
    'order' => 'ASC',
    'post_status' => 'publish',
];

$the_query = new WP_Query($args);
$posts = $the_query->posts;

/* =========================
   LOGICA
========================= */

$organi_indirizzo = [];
$organi_gestione = [];
$aree = [];

foreach ($posts as $post) {

    $terms = wp_get_post_terms($post->ID, 'tipi_unita_organizzativa');
    if (empty($terms) || is_wp_error($terms)) continue;

    // ORGANI POLITICI
    if (dci_articolazione_post_matches($post, $terms, ['consiglio'])) {
        $organi_indirizzo[] = $post;
        continue;
    }

    if (dci_articolazione_post_matches($post, $terms, ['giunta', 'sindaco'])) {
        $organi_gestione[] = $post;
        continue;
    }

    $is_ufficio = false;
    $area_name = 'Altri uffici';

    foreach ($terms as $term) {
        $slug = sanitize_title($term->slug);

        if ($slug === 'ufficio') {
            $is_ufficio = true;
        }

        if ($slug !== 'ufficio') {
            $area_name = $term->name;
        }
    }

    if ($is_ufficio) {
        if (!isset($aree[$area_name])) {
            $aree[$area_name] = [];
        }

        $aree[$area_name][] = $post;
    }
}
?>

<style>
.dci-at-wrap { max-width: 1140px; margin: auto; padding: 0 15px; }
.dci-at-section { margin-top: 3rem; }
.dci-at-office-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 1.25rem;
}
.dci-at-area-title {
    font-size: 1.5rem;
    font-weight: 700;
    margin: 1.5rem 0 1rem;
    border-left: 4px solid #0066cc;
    padding-left: .75rem;
}
@media(max-width:991px){ .dci-at-office-grid{grid-template-columns:1fr 1fr;} }
@media(max-width:767px){ .dci-at-office-grid{grid-template-columns:1fr;} }
</style>

<div class="container">
<div class="dci-at-wrap">

<!-- ORGANI -->
<section class="dci-at-section">
<h2>Organi di indirizzo politico</h2>
<div class="row g-4">
<?php foreach ($organi_indirizzo as $post) {
    dci_articolazione_render_simple_card($post);
} ?>
</div>
</section>

<section class="dci-at-section">
<h2>Organi di amministrazione e gestione</h2>
<div class="row g-4">
<?php foreach ($organi_gestione as $post) {
    dci_articolazione_render_simple_card($post);
} ?>
</div>
</section>

<!-- AREE -->
<section class="dci-at-section">
<h2>Articolazione degli uffici</h2>

<?php foreach ($aree as $area_name => $uffici) { ?>

    <h3 class="dci-at-area-title"><?php echo esc_html($area_name); ?></h3>

    <div class="dci-at-office-grid">
        <?php foreach ($uffici as $post) {
            dci_articolazione_render_office_card($post);
        } ?>
    </div>

<?php } ?>

</section>

</div>
</div>

<?php wp_reset_postdata(); ?>
