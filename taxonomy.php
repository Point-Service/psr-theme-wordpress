<?php
global $obj;
$obj = get_queried_object();

if ($obj->taxonomy == "categorie_servizio")
	get_template_part("archive-servizio");
else if ( $obj->post_type=="documento_pubblico" ||  $obj->taxonomy == "tipi_documento" || $obj->taxonomy == "tipi_doc_albo_pretorio" || $obj->taxonomy == "dataset"){
	get_template_part( "archive-documento" );
}
else if ( $obj->post_type=="tipi_notizia" ||  $obj->taxonomy == "tipi_notizia" || $obj->taxonomy == "tipi_notizia"){
	get_template_part( "taxonomy-tipi_notizia" );
}
else if($obj->taxonomy == "argomenti"){
	get_template_part("archive-argomento");
} else if ($obj->post_type=="tipi_luogo" || $obj->taxonomy == "tipi_luogo") {
	get_template_part("archive-luogo");
} else if($obj->post_type=="tipi_progetto" ||  $obj->taxonomy == "tipi_progetto"){
	get_template_part( "taxonomy-tipi_progetto" );
}
else{
	get_template_part("archive");
}
?>
