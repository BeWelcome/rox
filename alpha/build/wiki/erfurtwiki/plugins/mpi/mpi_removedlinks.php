<?php
/*
   <?plugin RemovedLinks ?>  summarizes the page/url links that have
   been removed from the current page during its edit history
*/


$ewiki_plugins["mpi"]["backlinks"] = "ewiki_mpi_removedlinks";

function ewiki_mpi_removedlinks($action, &$args, &$iii, &$s) {

   ($id = $args["id"])
   or ($id = $args["page"])
   or ($id = $GLOBALS["ewiki_id"]);
   
   $data = ewiki_db::GET($id);
   $ver = $cdata["version"];
   $clinks = explode("\n", trim($data["refs"]));
   
   $rm = array();
   
   while ((--$ver) >= 1) {
      $data = ewiki_db::GET($id, $ver);
      $refs = explode("\n", trim($data["refs"]));
      
      $rm = $rm + array_diff($refs, $clinks, $rm);
   }

   if ($rm) {
      return(ewiki_list_pages($rm, 0));
   }
   else {
      return(" ");
   }
}


?>