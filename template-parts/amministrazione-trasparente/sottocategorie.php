<?php foreach ( $sottocategorie as $sottocategoria ) { 
if($title != $sottocategoria->name){

    // 👉 LINK DEFAULT
    $link = get_term_link($sottocategoria->term_id);

    if (is_wp_error($link)) {
        $link = '#';
    }

    // 👉 META
    $term_url = get_term_meta($sottocategoria->term_id, 'term_url', true);
    $open_new_window = get_term_meta($sottocategoria->term_id, 'open_new_window', true);

    // 👉 DEFAULT
    $target = '';

    // 👉 LINK PERSONALIZZATO
    if (!empty($term_url)) {
        $link = esc_url($term_url);
        $target = ($open_new_window) ? ' target="_blank" rel="noopener noreferrer"' : '';
    }
?>
    <div class="col-md-3 col-xl-4">
        <div class="cmp-card-simple card-wrapper pb-0 rounded border border-light">
          <div class="card shadow-sm rounded">
            <div class="card-body">
                <a class="text-decoration-none" href="<?php echo esc_url($link); ?>"<?php echo $target; ?>>
                    <h4 class="card-title t-primary title-xlarge">
                        <?php echo ucfirst($sottocategoria->name); ?>
                    </h4>
                </a>
                <p class="titillium text-paragraph mb-0 description">
                    <?php echo $sottocategoria->description; ?>
                </p>
            </div>
          </div>
        </div>
    </div>
<?php } } ?>

