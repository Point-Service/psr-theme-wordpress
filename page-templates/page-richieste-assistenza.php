<?php
/*
Template Name: Tickets Assistenza
Description: Visualizza tutte le Richieste di Assistenza
*/

get_header(); ?>

<div class="container">
    <h1>Richieste di Assistenza</h1>

    <?php
    // Query tutti i ticket pubblicati
    $args = array(
        'post_type'      => 'richiesta_assistenza',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'date',
        'order'          => 'DESC',
    );

    $tickets = new WP_Query($args);

    if ($tickets->have_posts()) : ?>

        <table style="width:100%; border-collapse: collapse;">
            <thead>
                <tr style="background:#f2f2f2;">
                    <th style="padding:8px; border:1px solid #ddd;">Richiedente</th>
                    <th style="padding:8px; border:1px solid #ddd;">Email</th>
                    <th style="padding:8px; border:1px solid #ddd;">Categoria</th>
                    <th style="padding:8px; border:1px solid #ddd;">Servizio</th>
                    <th style="padding:8px; border:1px solid #ddd;">Dettagli</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($tickets->have_posts()) : $tickets->the_post(); 
                    $nome      = get_post_meta(get_the_ID(), '_dci_richiesta_assistenza_nome', true);
                    $cognome   = get_post_meta(get_the_ID(), '_dci_richiesta_assistenza_cognome', true);
                    $email     = get_post_meta(get_the_ID(), '_dci_richiesta_assistenza_email', true);
                    $categoria = get_post_meta(get_the_ID(), '_dci_richiesta_assistenza_categoria_servizio', true);
                    $servizio  = get_post_meta(get_the_ID(), '_dci_richiesta_assistenza_servizio', true);
                    $dettagli  = get_post_meta(get_the_ID(), '_dci_richiesta_assistenza_dettagli', true);
                ?>
                    <tr>
                        <td style="padding:8px; border:1px solid #ddd;"><?php echo esc_html($cognome . ' ' . $nome); ?></td>
                        <td style="padding:8px; border:1px solid #ddd;"><?php echo esc_html($email); ?></td>
                        <td style="padding:8px; border:1px solid #ddd;"><?php echo esc_html($categoria); ?></td>
                        <td style="padding:8px; border:1px solid #ddd;"><?php echo esc_html($servizio); ?></td>
                        <td style="padding:8px; border:1px solid #ddd;"><?php echo esc_html($dettagli); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

    <?php else : ?>
        <p>Nessuna richiesta di assistenza trovata.</p>
    <?php endif; 

    wp_reset_postdata(); ?>
</div>

<?php get_footer(); ?>

