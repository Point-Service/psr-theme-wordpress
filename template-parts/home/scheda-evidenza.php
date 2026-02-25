<?php
global $scheda, $count, $post;

if (!isset($post) || empty($post)) {
    if (!empty($scheda['scheda_' . $count . '_contenuto'][0])) {
        $post = get_post($scheda['scheda_' . $count . '_contenuto'][0]);
    }
}

$img = dci_get_meta('immagine');
$descrizione_breve = dci_get_meta('descrizione_breve');

$page = get_page_by_path(dci_get_group($post->post_type));

$argomenti = dci_get_meta("argomenti", '_dci_notizia_', $post->ID);
$luogo_notizia = dci_get_meta("luoghi", '_dci_notizia_', $post->ID);

$arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", '_dci_notizia_', $post->ID);
$monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));

$post_type = get_post_type($post->ID);
$post_type_object = get_post_type_object($post_type);
$post_type_label = $post_type_object->labels->singular_name;

$tipo_name = '';
$url_tipo = '#';

/* SWITCH ORIGINALE INALTERATO */
switch ($post_type_label) {

    case 'Servizio':
        $tipo_terms = get_the_terms($post->ID, 'categorie_servizio');
        if ($tipo_terms && !is_wp_error($tipo_terms)) {
            $tipo = $tipo_terms[0];
            $tipo_name = 'Servizio';
            $url_tipo = '/servizi-categoria/' . sanitize_title($tipo->name);
        }
        break;

    case 'Luogo':
        $tipo_terms = get_the_terms($post->ID, 'tipi_luogo');
        if ($tipo_terms && !is_wp_error($tipo_terms)) {
            $tipo = $tipo_terms[0];
            $tipo_name = 'Luogo';
            $url_tipo = '/tipi-luogo/' . sanitize_title($tipo->name);
        }
        break;

    case 'Evento':
        $tipo_terms = get_the_terms($post->ID, 'tipi_evento');
        if ($tipo_terms && !is_wp_error($tipo_terms)) {
            $tipo = $tipo_terms[0];
            $tipo_name = 'Evento';
            $url_tipo = '/vivere-il-comune/tipo-evento/' . sanitize_title($tipo->name);
        }
        break;

    case 'Documento Pubblico':
        $tipo_terms = get_the_terms($post->ID, 'tipi_documento');
        if ($tipo_terms && !is_wp_error($tipo_terms)) {
            $tipo = $tipo_terms[0];
            $tipo_name = 'Documento Pubblico';
            $url_tipo = '/tipi_documento/' . sanitize_title($tipo->name);
        }
        break;

    case 'Unità Organizzativa':
        $tipo_terms = get_the_terms($post->ID, 'tipi_unita_organizzativa');
        if ($tipo_terms && !is_wp_error($tipo_terms)) {
            $tipo = $tipo_terms[0];
            $tipo_name = 'Unita Organizzativa';
            $url_tipo = '/amministrazione/unita_organizzativa/';
        }
        break;

    case 'Notizia':
        $tipo_terms = get_the_terms($post->ID, 'tipi_notizia');
        if ($tipo_terms && !is_wp_error($tipo_terms)) {
            $tipo = $tipo_terms[0];
            $tipo_name = $tipo->name;
            $url_tipo = '/tipi_notizia/' . sanitize_title($tipo->name);
        }
        break;

    case 'Dataset':
        $tipo_name = 'Dataset';
        $url_tipo = '/dataset';
        break;

    default:
        $tipo_name = 'Novità';
        $url_tipo = '#';
}

?>


<div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr h-100 scheda-card">

<div class="card no-after rounded h-100 d-flex flex-column">

<?php if ($img) { ?>

<div class="d-flex flex-column">

<div style="max-width:420px;text-align:center;">

<?php dci_get_img($img, 'rounded-top img-fluid img-responsive'); ?>

</div>

</div>

<?php } ?>


<div class="card-body d-flex flex-column">


<!-- ================= META ================= -->

<div class="category-top">

<span class="category title-xsmall-semi-bold fw-semibold">

<a href="<?php echo esc_url($url_tipo); ?>"
   class="category title-xsmall-semi-bold fw-semibold scheda-link">

<?php echo strtoupper(esc_html($tipo_name)); ?>

</a>

</span>

<?php if (!empty($arrdata)) { ?>

<span class="data fw-normal">

<?php echo esc_html($arrdata[0].' '.$monthName.' '.$arrdata[2]); ?>

</span>

<?php } ?>

</div>


<!-- ================= TITOLO ================= -->

<h3 class="h5 card-title u-grey-light">

<?php

$title = get_the_title();

if (strlen($title) > 100) {
    $title = substr($title, 0, 97) . '...';
}

if (preg_match('/[A-Z]{5,}/', $title)) {
    $title = ucfirst(strtolower($title));
}

echo esc_html($title);

?>

</h3>


<!-- ================= DESCRIZIONE ================= -->

<p class="text-paragraph-card u-grey-light m-0">

<?php

if (preg_match('/[A-Z]{5,}/', $descrizione_breve)) {
    echo esc_html(ucfirst(strtolower($descrizione_breve)));
} else {
    echo esc_html($descrizione_breve);
}

?>

</p>


<!-- ================= LUOGO ================= -->

<?php if (is_array($luogo_notizia)) { ?>

<br><br>

<span class="data fw-normal">

<i class="fas fa-map-marker-alt" style="pointer-events:none;"></i>

<?php foreach ($luogo_notizia as $luogo_id) {

$luogo_post = get_post($luogo_id);

if ($luogo_post) {

?>

<a href="<?php echo esc_url(get_permalink($luogo_post->ID)); ?>"
   class="card-text text-secondary text-uppercase pb-3 scheda-link">

<?php echo esc_html($luogo_post->post_title); ?>

</a>

<?php } } ?>

</span>


<!-- ================= ARGOMENTI ================= -->

<?php if (has_term('', 'argomenti', $post)) { ?>

<hr>

<div class="card-body p-0" style="font-weight:600;">

Argomenti:

<?php get_template_part("template-parts/common/badges-argomenti"); ?>

</div>

<?php } ?>

<hr>


<!-- ================= CTA ================= -->

<a class="read-more d-inline-flex align-items-center scheda-link"
   href="<?php echo esc_url(get_permalink($post->ID)); ?>"
   style="margin-top:30px;">

<span class="text" style="pointer-events:none;">
Vai alla pagina
</span>

<svg class="icon ms-1" style="pointer-events:none;">
<use xlink:href="#it-arrow-right"></use>
</svg>

</a>


</div>

</div>

</div>


<style>

/* ===============================
   FIX WEBVIEW
================================ */

.scheda-card,
.scheda-link {
    touch-action: manipulation;
}

.scheda-card svg,
.scheda-card i,
.scheda-card span {
    pointer-events: none;
}

@media (hover:none){

.scheda-link:hover{
    background:inherit;
}

}

</style>
