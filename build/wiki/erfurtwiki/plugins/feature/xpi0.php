<?php

/*
   This plugin provides the run-time part of the xpi feature, thus keeping
   all installed plugins active, if you use it to replace the big xpi.php
   plugin (the version with the built-in PlugInstall page).
*/


define("XPI_DB", "system/xpi/registry");

$ewiki_plugins["handler"][] = "xpi_exec";
$ewiki_plugins["init"][] = "xpi_init_plugins";


#-- executes pages with the _EXEC flag set
function xpi_exec($id, $data, $action) {

   global $ewiki_id, $ewiki_title, $ewiki_action, $ewiki_data,
      $ewiki_config, $ewiki_t, $ewiki_plugins, $_EWIKI;

   if ($data["flags"] & EWIKI_DB_F_EXEC) {
      eval($data["content"]);
      return($o);
   }
}


#-- runs plugins at init time
function xpi_init_plugins() {

   global $ewiki_id, $ewiki_title, $ewiki_action, $ewiki_data,
      $ewiki_config, $ewiki_t, $ewiki_plugins, $_EWIKI;

   #-- load xpi registry
   $conf = ewiki_db::GET(XPI_DB);
   if ($conf && ($conf["flags"] & EWIKI_DB_F_SYSTEM)
   && ($conf = unserialize($conf["content"]))) {

      #-- collect xpi code, execute it
      $eval_this = "";
      foreach ($conf as $xpi) {
         if ($xpi["state"] && ($xpi["type"] != "page")) {
            $d = ewiki_db::GET($xpi["id"]);
            if ($d && ($d["flags"] & EWIKI_DB_F_EXEC)) {
               $eval_this .= $d["content"];
            }
         }
      }
      eval($eval_this);
   }
}


?>