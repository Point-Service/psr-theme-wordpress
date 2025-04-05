<?php 

global $post;


//Se la sezione viene disattivata con caricare niente
$ck_osl = dci_get_option('ck_osl', 'Amministrazione');
if ($ck_osl !== 'true') {
    echo 'SEZIONE DISABILITATA';
    exit; 
}




$descrizione_breve = dci_get_meta("descrizione_breve", '_dci_commissario_', $post->ID);
$arrdata = dci_get_data_pubblicazione_arr("data_pubblicazione", '_dci_commissario_', $post->ID);
$monthName = date_i18n('M', mktime(0, 0, 0, $arrdata[1], 10));
$img = dci_get_meta('immagine', '_dci_commissario_', $post->ID);
$tipo_terms = get_the_terms($post->ID, 'tipi_commissario');

if ($tipo_terms && !is_wp_error($tipo_terms)) {
    $tipo = $tipo_terms[0];
} else {
    $tipo = null;
}

// Definisci l'icona predefinita
$default_icon = "#it-file"; 

// Se il tipo è specifico, cambia l'icona
$custom_icon = null;
if ($tipo) {
    // Esempio di controllo sul tipo
    if ($tipo->slug == 'modulistica-osl') {
        $custom_icon = "#it-clip";         
    } elseif ($tipo->slug == 'avviso-ai-creditori') {
        $custom_icon = "#it-info-circle"; 
    } elseif ($tipo->slug == 'atti-della-commissione') {
        $custom_icon = "#it-copy";         
    }


} 

if ($img) {
?>
    <div class="col-12 col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
            <div class="row g-2 g-md-0 flex-md-column">
                <div class="row g-2 g-md-0 flex-md-column">
                    <?php dci_get_img($img, 'rounded-top img-fluid img-responsive'); ?>
                </div>
                <div class="col-12 order-1 order-md-2">
                <div class="card-body">
                    <div class="category-top cmp-list-card-img__body">
                    <?php if ($tipo){?>
                                    <svg class="icon">
                                        <use xlink:href="<?php echo $custom_icon ? $custom_icon : $default_icon; ?>" />
                                    </svg>
                            <a class="category text-decoration-none" href="<?php echo get_term_link($tipo->term_id); ?>">
                                <?php echo strtoupper($tipo->name); ?>
                            </a>
                    <?php } ?>
                    <span class="data"><?php echo $arrdata[0].' '.strtoupper($monthName).' '.$arrdata[2] ?></span>
                    </div>
                    <a class="text-decoration-none" href="<?php echo get_permalink(); ?>">
                        <h3 class="h5 card-title u-grey-light"><?php echo the_title(); ?></h3>
                    </a>
                    <p class="card-text d-none d-md-block">
                        <?php echo $descrizione_breve; ?>
                    </p>
                </div>
                </div>                
            </div><br>&nbsp;
                    &nbsp;<a class="read-more ps-3"
                       href="<?php echo esc_url(get_permalink($post->ID)); ?>"
                       aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                       title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                       style="display: inline-flex; align-items: center; margin-top: 30px;"> <!-- Margine aggiunto -->
                        <span class="text">Vai alla pagina</span>
                        <svg class="icon">
                            <use xlink:href="#it-arrow-right"></use>
                        </svg>
                    </a>    
            </div>
        </div>
    </div>
<?php } else { ?>
    <div class="col-md-6 col-xl-4">
        <div class="card-wrapper border border-light rounded shadow-sm cmp-list-card-img cmp-list-card-img-hr">
            <div class="card no-after rounded">
                <div class="row g-2 g-md-0 flex-md-column">
                    <div class="col-12 order-1 order-md-2">
                        <div class="card-body card-img-none rounded-top">
                            <div class="category-top cmp-list-card-img__body">
                            <?php if ($tipo){?>
                               
                                    <svg class="icon">
                                        <use xlink:href="<?php echo $custom_icon ? $custom_icon : $default_icon; ?>" />
                                    </svg>
  
                                <a class="category text-decoration-none" href="<?php echo get_term_link($tipo->term_id); ?>">
                                 <?php echo strtoupper($tipo->name); ?>
                             </a>
                            <?php } ?>
                                <span class="data"><?php echo $arrdata[0].' '.strtoupper($monthName).' '.$arrdata[2] ?></span>
                            </div>
                            <a class="text-decoration-none" href="<?php echo get_permalink(); ?>">
                                <h3 class="h5 card-title u-grey-light"><?php echo the_title(); ?></h3>
                            </a>
                            <p class="card-text d-none d-md-block">
                                <?php echo $descrizione_breve; ?>
                            </p>
                            
                        </div>
                    </div>
                </div><br>&nbsp;
                       &nbsp;<a class="read-more ps-3"
                       href="<?php echo esc_url(get_permalink($post->ID)); ?>"
                       aria-label="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                       title="Vai alla pagina <?php echo esc_attr($post->post_title); ?>" 
                       style="display: inline-flex; align-items: center; margin-top: 30px;"> <!-- Margine aggiunto -->
                        <span class="text">Vai alla pagina</span>
                        <svg class="icon">
                            <use xlink:href="#it-arrow-right"></use>
                        </svg>
                    </a>    
            </div>
        </div>
    </div>
<?php } ?>
