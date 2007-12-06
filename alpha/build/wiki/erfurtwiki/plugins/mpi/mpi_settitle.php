<?php

/*
   Use <?plugin SetTitle NewPageTitle ?> to define the currently
   shown pages' title to something different.
*/

$ewiki_plugins["mpi"]["settitle"] = "ewiki_mpi_settitle";


function ewiki_mpi_settitle($action, &$args, &$iii, &$s) {

   global $ewiki_title;

   if ($args[1]) {
      $ewiki_title = $args["_"];
   }
   elseif ($args["id"]) {
      $ewiki_title = $args["id"];
   }
   return("");
}


?>