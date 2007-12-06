<?php

/*
   <?plugin RedirectLinks to="WardsWiki" ?>

   will make ALL WikiWords of the current page link to the according
   pages on another Wiki (see intermap), see also FallBack plugin
*/


$ewiki_plugins["mpi"]["redirectlinks"] = "ewiki_mpi_redirectlinks";
$ewiki_plugins["format_prepare_linking"][] = "ewiki_redirect_links_iw";


function ewiki_mpi_redirectlinks($action, $args, &$s, &$iii) {
   global $ewiki_config;
   if (($to = $args["to"]) || ($to = $args[0])) {
      $ewiki_config["links_redirect_iw"] = ewiki_array($ewiki_config["interwiki"], $to);
   }
}

function ewiki_redirect_links_iw(&$src) {
   global $ewiki_config, $ewiki_links;
   foreach ($ewiki_links as $i=>$v) {
      if (!strpos($v, "://") && !strpos($i, ":")) {
         $ewiki_links[$i] = $ewiki_config["links_redirect_iw"] . urlencode($i);
      }
   }
}

?>