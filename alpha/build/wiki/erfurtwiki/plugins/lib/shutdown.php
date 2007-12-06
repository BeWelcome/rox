<?php

/*
   Registration of shutdown-functions via $ewiki_plugins["shutdown"][],
   where each entry is either a function name or funcname + fixed args.
*/


function ewiki_shutdown() {
   global $ewiki_plugins, $ewiki_id, $ewiki_data, $ewiki_action;

   if ($pf_a = $ewiki_plugins["shutdown"]) {
      foreach ($pf_a as $pf) {

         $args = array();
         if (is_array($pf)) {
            $args = $pf;
            $pf = array_shift($args);
         }

         set_time_limit(+20);
         $pf($ewiki_id, $ewiki_data, $ewiki_action, $args);
      }
   }
}

register_shutdown_function("ewiki_shutdown");

?>