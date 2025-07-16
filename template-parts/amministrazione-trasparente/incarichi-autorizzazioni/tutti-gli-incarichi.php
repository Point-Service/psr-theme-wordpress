<?php
$paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;

$args = [
  'post_type'      => 'incarichi_dip',
  'posts_per_page' => $max_posts,
  'paged'          => $paged,
  'orderby'        => 'date',
  'order'          => 'DESC',
];
if ($main_search_query) $args['s'] = $main_search_query;

$the_query = new WP_Query($args);
?>

<?php if ($the_query->have_posts()) : ?>
  <div class="row">
    <?php while ($the_query->have_posts()) : $the_query->the_post(); ?>
      <?php get_template_part('…/card'); ?>
    <?php endwhile; ?>
  </div>
  <?php wp_reset_postdata(); ?>

  <?php if ($the_query->max_num_pages > 1): ?>
    <?php
    $base   = trailingslashit( get_permalink() ) . '%_%';
    $format = '?paged=%#%';
    $pages = paginate_links([
      'base'      => $base,
      'format'    => $format,
      'current'   => $paged,
      'total'     => $the_query->max_num_pages,
      'prev_text' => '&laquo;',
      'next_text' => '&raquo;',
      'type'      => 'array',
    ]);
    ?>
    <nav class="my-4">
      <ul class="pagination justify-content-center">
        <?php foreach ((array) $pages as $link): ?>
          <?php if (strpos($link, 'current') !== false): ?>
            <li class="page-item active"><span class="page-link"><?php echo strip_tags($link); ?></span></li>
          <?php else: ?>
            <li class="page-item"><?php echo str_replace('page-numbers', 'page-link', $link); ?></li>
          <?php endif; ?>
        <?php endforeach; ?>
      </ul>
    </nav>
  <?php endif; ?>
<?php else: ?>
  <div class="alert alert-info text-center">Nessun incarico trovato.</div>
<?php endif; ?>

