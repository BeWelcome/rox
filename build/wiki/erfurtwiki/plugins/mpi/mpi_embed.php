<?php

/*
   This allows to embed a dynamic page, like the PowerSearch plugin,
   into an ordinary wiki page. So this is similar to the Insert mpi.

   <?plugin embed PageIndex ?>
   <?plugin embed page=PowerSearch real=1 ?>

   The real=1 tells to use "PowerSearch" as the embeded pages title,
   a page name can be given as first parameter or as id= or page=
   parameter.
*/

$ewiki_plugins["mpi"]["embed"] = "ewiki_mpi_embed";


function ewiki_mpi_embed($action="html", $args, &$iii, &$s) {

   global $ewiki_plugins, $ewiki_id, $ewiki_action;

   if ($action == "html") {
      $o = "";

      #-- get page name
      ($get = $args["id"]) or ($get = $args["page"]);
      if (!$get) {
         $get = array_keys($args);
         $get = array_shift($get);
      }

      #-- params
      $id = $ewiki_id;
      if ($args["real"]) {
         $id = $get;
      }

      #-- plugin function
      $pf = ewiki_array($ewiki_plugins["page"], $get);

      #-- exec dynamic page plugin
      if ($get && function_exists($pf)) {
         $uu = array();
         $o .= $pf($id, $uu, $ewiki_action);
      }
   }

   return($o);
}

?>