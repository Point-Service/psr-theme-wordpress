<?php 
    global $persone;
    if ($persone && is_array($persone) && count($persone)>0) { ?>
                                       <div class="col-12 col-md-8 col-lg-6 mb-30">
                                        <div class="card-wrapper rounded h-auto mt-10">
                                            <div class="card card-teaser shadow-sm p-4s rounded border border-light flex-nowrap">
                                               <?php foreach ($persone as $person_id) { 
                                                $prefix = '_dci_persona_pubblica_';
                                                $nome = dci_get_meta('nome', $prefix, $person_id);
                                                $cognome = dci_get_meta('cognome', $prefix, $person_id); ?>                                             
                                              <div class="card-body pe-3">
                                                  <p class="card-title text-paragraph-regular-medium-semi mb-3">
                                                      <a href="<?php echo get_permalink($person_id); ?>">
                                                        <span class="chip-label"><?php echo $nome.' '.$cognome; ?></span>
                                                     </a>
                                                  </p>
                                                    <div class="card-text">
                                                        <div class="richtext-wrapper lora">
                                                          <?php echo $incarico ?>
                                                        </div>
                                                    </div>
                                                 </div>    
                                                <?php } ?>
                                             </div>
                                           </div>
                                        </div>


      <?php }
?>
