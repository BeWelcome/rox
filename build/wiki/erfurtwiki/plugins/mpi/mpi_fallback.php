<?php

/*
   <?plugin FallBack to="MetaWiki" ?>

   will make all locally not-existing WikiWords on the current page link
   to the according pages on the given Wiki (see intermap) - much like
   the RedirectLinks plugin
*/


$ewiki_plugins["mpi"]["fallback"] = "ewiki_mpi_fallback";


function ewiki_mpi_fallback($action, $args, &$s, &$iii) {
   global $ewiki_config, $ewiki_plugins;
   if (($to = $args["to"]) || ($to = $args[0])) {
      $ewiki_config["links_fallback_iw"] = ewiki_array($ewiki_config["interwiki"], $to);
      $ewiki_plugins["format_prepare_linking"][] = "ewiki_fallback_iw";
   }
}

function ewiki_fallback_iw(&$src) {
   global $ewiki_config, $ewiki_links;
   foreach ($ewiki_links as $i=>$v) {
      if (!strpos($v, "://") && !$v) {
         $ewiki_links[$i] = $ewiki_config["links_fallback_iw"] . urlencode($i);
      }
   }
}

?>