<?php if(dci_get_option("area_riservata")) { ?>
<button type="button" style="flex-basis: auto;" class="btn btn-primary btn-icon btn-full" aria-label="<?php _e("Accedi all'area personale", "design_comuni_italia"); ?>" data-bs-toggle="modal" data-bs-target="#access-modal">
    <span class="rounded-icon" aria-hidden="true">
        <svg class="icon icon-primary">
            <use xlink:href="#it-user"></use>
        </svg>
    </span>
    <span class="mr-2 d-none d-lg-block"><?php _e("Accedi all'area personale", "design_comuni_italia"); ?></span>
</button>
<?php } else { ?>
   <a class="btn btn-primary btn-icon btn-full" title="Accedi all'area personale" href="<?php echo get_admin_url(); ?>" data-element="personal-area-login">
    <span class="rounded-icon" aria-hidden="true">
        <svg class="icon icon-primary">
            <use xlink:href="#it-user"></use>
        </svg>
    </span>
    <span class="d-none d-lg-block">Accedi all'area personale</span>
  </a>
<?php } ?>


