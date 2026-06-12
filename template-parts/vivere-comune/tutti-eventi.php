<?php
global $the_query, $load_posts, $load_card_type;

$per_page = 9;
$load_posts = $per_page;

$query = isset($_GET['search'])
    ? sanitize_text_field(wp_unslash($_GET['search']))
    : '';

$paged = isset($_GET['paged'])
    ? max(1, intval($_GET['paged']))
    : 1;

$args = array(
    's'                   => $query,
    'post_type'           => 'evento',
    'posts_per_page'      => $per_page,
    'paged'               => $paged,
    'orderby'             => 'meta_value',
    'order'               => 'ASC',
    'meta_key'            => '_dci_evento_data_orario_inizio',
    'post_status'         => 'publish',
    'ignore_sticky_posts' => true,
);

$the_query = new WP_Query($args);
?>

<div class="bg-card bg-grey-card py-5">
    <form role="search" id="search-form" method="get" class="search-form">
        <button type="submit" class="d-none"></button>

        <div class="container">

            <h2 class="title-xxlarge mb-4">
                Esplora tutti gli eventi
            </h2>

            <div class="cmp-input-search">
                <div class="form-group autocomplete-wrapper mb-0">
                    <div class="input-group">

                        <label for="autocomplete-two" class="visually-hidden">
                            Cerca
                        </label>

                        <input
                            type="search"
                            class="autocomplete form-control"
                            placeholder="Cerca per parola chiave"
                            id="autocomplete-two"
                            name="search"
                            value="<?php echo esc_attr($query); ?>"
                            data-bs-autocomplete="[]" />

                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                Invio
                            </button>
                        </div>

                    </div>

                    <p class="u-grey-light text-paragraph-card mt-2 mb-30 mt-lg-3 mb-lg-40">
                        <?php echo intval($the_query->found_posts); ?> eventi trovati
                    </p>

                </div>
            </div>

            <div class="row g-4">

                <?php
                if ($the_query->have_posts()) :

                    while ($the_query->have_posts()) :
                        $the_query->the_post();

                        $load_card_type = 'evento';

                        get_template_part(
                            'template-parts/evento/card-full'
                        );

                    endwhile;

                else :

                    get_template_part(
                        'template-parts/content',
                        'none'
                    );

                endif;
                ?>

            </div>

            <?php if ($the_query->max_num_pages > 1) : ?>

                <div class="row my-4">
                    <div class="col-12 text-center">

                        <?php
                        echo paginate_links(array(
                            'base' => add_query_arg(
                                'paged',
                                '%#%'
                            ),
                            'format' => '',
                            'current' => $paged,
                            'total' => $the_query->max_num_pages,
                            'prev_text' => '«',
                            'next_text' => '»',
                        ));
                        ?>

                    </div>
                </div>

            <?php endif; ?>

        </div>
    </form>
</div>

<?php wp_reset_postdata(); ?>
