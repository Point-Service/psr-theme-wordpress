<?php
    $luoghi = dci_get_option('luoghi_evidenziati','vivi');
    $url_luoghi = get_permalink(get_page_by_title('Luoghi'));
    
    if (is_array($luoghi) && count($luoghi)) {
?>
<div class="bg-light py-5">
    <div class="container">
        <h2 class="title-xxlarge mb-4 text-center fw-bold">Luoghi in evidenza</h2>
        <div class="row row-cols-1 row-cols-md-3 g-4">
            <?php
                foreach ($luoghi as $luogo_id) {
                    $post = get_post($luogo_id);
                    $title = get_the_title($post);
                    $excerpt = get_the_excerpt($post);
                    $permalink = get_permalink($post);
                    $image = get_the_post_thumbnail_url($post, 'medium');
                    $category = get_the_category($post);
                    $category_name = $category ? $category[0]->name : '';

                    ?>
                    <div class="col">
                        <div class="card h-100 shadow-sm border-0">
                            <img src="<?php echo esc_url($image); ?>" class="card-img-top" alt="<?php echo esc_attr($title); ?>">
                            <div class="card-body">
                                <small class="text-primary text-uppercase fw-bold"><?php echo esc_html($category_name); ?></small>
                                <h5 class="card-title fw-bold"><?php echo esc_html($title); ?></h5>
                                <p class="card-text text-muted"><?php echo esc_html($excerpt); ?></p>
                                <a href="<?php echo esc_url($permalink); ?>" class="text-primary fw-bold">Leggi di più →</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            ?>
        </div>
        <div class="d-flex justify-content-center mt-4">
            <a href="<?php echo esc_url($url_luoghi); ?>" class="btn btn-primary px-5 py-3 rounded-pill fw-bold">
                Tutti i luoghi
            </a>
        </div>
    </div>
</div>
<?php } ?>
