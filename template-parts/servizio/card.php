<?php
function get_procedures_data() {
    $url = "https://sportellotelematico.comune.roccalumera.me.it/rest/pnrr/procedures";
    $response = wp_remote_get( $url );

    if ( is_array( $response ) && ! is_wp_error( $response ) ) {
        $body = wp_remote_retrieve_body( $response );
        $data = json_decode( $body, true );

        if ( $data ) {
            foreach ( $data as $procedure ) {
                $name = $procedure['nome'];
                $description = $procedure['descrizione_breve'];
                $categories = $procedure['categoria'];
                $url = esc_url( $procedure['url'] );

                // Start card wrapper
                echo '<div class="cmp-card-latest-messages card-wrapper" data-bs-toggle="modal" data-bs-target="#">';
                echo '<div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">';

                // Categories
                if (!empty($categories)) {
                    echo '<span class="visually-hidden">Categoria:</span>';
                    echo '<div class="card-header border-0 p-0">';
                    $count = 1;
                    foreach ($categories as $category) {
                        echo $count == 1 ? '' : ' - ';
                        echo '<a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase" href="' . get_term_link($category['term_id']) . '">';
                        echo $category['name'];
                        echo '</a>';
                        ++$count;
                    }
                    echo '</div>';
                }

                // Procedure name
                echo '<div class="card-body p-0 my-2">';
                echo '<h3 class="green-title-big t-primary mb-8">';
                echo '<a class="text-decoration-none" href="' . $url . '" data-element="service-link">' . $name . '</a>';
                echo '</h3>';

                // Description
                echo '<p class="text-paragraph">';
                echo $description;
                echo '</p>';
                echo '</div>'; // Close card-body
                echo '</div>'; // Close card
                echo '</div>'; // Close card-wrapper
            }
        }
    } else {
        echo "Failed to fetch data.";
    }
}

// Aggiungi il codice HTML/PHP nel tuo template dove desideri visualizzare i dati
?>
<div class="procedures-list">
    <h2>Procedures List</h2>
    <?php get_procedures_data(); ?>
</div>


<?php
global $servizio, $hide_categorie, $post;

$prefix = '_dci_servizio_';
$categorie = get_the_terms($servizio->ID, 'categorie_servizio');
$descrizione_breve = dci_get_meta('descrizione_breve', $prefix, $servizio->ID);
if($post->post_status == "publish") {
    ?>
        <div class="cmp-card-latest-messages card-wrapper" data-bs-toggle="modal" data-bs-target="#">
            <div class="card shadow-sm px-4 pt-4 pb-4 rounded border border-light">
                <?php if (!$hide_categories) { ?>
                <span class="visually-hidden">Categoria:</span>
                <div class="card-header border-0 p-0">
                    <?php if (is_array($categorie) && count($categorie)) {
                        $count = 1;
                        foreach ($categorie as $categoria) {
                            echo $count == 1 ? '' : ' - ';
                            echo '<a class="text-decoration-none title-xsmall-bold mb-2 category text-uppercase" href="'.get_term_link($categoria->term_id).'">';
                            echo $categoria->name ;                                    
                            echo '</a>';
                            ++$count;
                        }
                    }                        
                    ?>
                </div>
                <?php } ?>
                <div class="card-body p-0 my-2">
                <h3 class="green-title-big t-primary mb-8">
                    <a class="text-decoration-none" href="<?php echo get_permalink($servizio->ID); ?>" data-element="service-link"><?php echo $servizio->post_title; ?></a>
                </h3>
                <p class="text-paragraph">
                    <?php echo $descrizione_breve; ?>
                </p>
                </div>
            </div>
        </div>
    <?php
}

