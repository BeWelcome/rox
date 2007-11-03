<?php

/*
   This plugin automatically stores global variables into a
   pages {meta} field, if they is saved after editing.

   In the following configuration array you can define which variable
   is to be stored with which name into the {meta} field.
*/

#-- which vars to store:
$ewiki_config["save_storevars"] = array(
#   "global_var" => "meta_field_name",
#   "icon" => "icon",
#   "counter" => "X-Hit-Counter",
);


#-- glue
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_storevars";


#-- implementation
function ewiki_edit_save_storevars(&$save, &$old_data) {

   global $ewiki_config;

   foreach ($ewiki_config["save_storevars"] as $globalname => $metaname) {
      if ($value = &$GLOBALS[$globalname]) {
         $save["meta"][$metaname] = $value;
      }
   }
}

?>