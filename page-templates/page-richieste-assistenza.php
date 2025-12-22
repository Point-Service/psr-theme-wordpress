<?php
/**
 * Template Name: Elenco Richieste Assistenza
 * Description: Visualizza le richieste di assistenza (Ticket)
 */

if ( ! is_user_logged_in() ) {
    wp_redirect( wp_login_url( get_permalink() ) );
    exit;
}

get_header();
?>

<main id="main-container" class="container my-5">

    <h1 class="mb-4">Richieste di assistenza</h1>

    <?php
    $args = array(
        'post_type'      => 'richiesta_assistenza',
        'post_status'    => 'publish',
        'posts_per_page' => 20,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $query = new WP_Query( $args );

    if ( $query->have_posts() ) :
    ?>

    <div class="table-responsive">
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Richiedente</th>
                    <th>Email</th>
                    <th>Categoria</th>
                    <th>Servizio</th>
                    <th>Dettagli</th>
                </tr>
            </thead>
            <tbody>

                <?php while ( $query->have_posts() ) : $query->the_post(); ?>

                <?php
                    $nome      = get_post_meta( get_the_ID(), '_dci_richiesta_assistenza_nome', true );
                    $cognome   = get_post_meta( get_the_ID(), '_dci_richiesta_assistenza_cognome', true );
                    $email     = get_post_meta( get_the_ID(), '_dci_richiesta_assistenza_email', true );
                    $categoria = get_post_meta( get_the_ID(), '_dci_richiesta_assistenza_categoria_servizio', true );
                    $servizio  = get_post_meta( get_the_ID(), '_dci_richiesta_assistenza_servizio', true );
                    $dettagli  = get_post_meta( get_the_ID(), '_dci_richiesta_assistenza_dettagli', true );
                ?>

                <tr>
                    <td><?php echo esc_html( get_the_date('d/m/Y H:i') ); ?></td>
                    <td><?php echo esc_html( $cognome . ' ' . $nome ); ?></td>
                    <td><?php echo esc_html( $email ); ?></td>
                    <td><?php echo esc_html( $categoria ); ?></td>
                    <td><?php echo esc_html( $servizio ); ?></td>
                    <td><?php echo esc_html( wp_trim_words( $dettagli, 20 ) ); ?></td>
                </tr>

                <?php endwhile; ?>

            </tbody>
        </table>
    </div>

    <?php else : ?>

        <p>Nessuna richiesta di assistenza presente.</p>

    <?php endif; wp_reset_postdata(); ?>

</main>

<?php get_footer(); ?>
