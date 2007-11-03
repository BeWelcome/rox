<?php

/*
   This mpi can insert a list of pages referencing the current one.
   <?plugin BackLinks ?>
   <?plugin BackLinks page=ForThatPage ?>
*/

$ewiki_plugins["mpi"]["backlinks"] = "ewiki_mpi_backlinks";

function ewiki_mpi_backlinks($action, &$args, &$iii, &$s) {

   ($id = $args["page"]) or ($id = $GLOBALS["ewiki_id"]);
   if ($pages = ewiki_get_backlinks($id)) {
      return(ewiki_list_pages($pages));
   } 
}

?>