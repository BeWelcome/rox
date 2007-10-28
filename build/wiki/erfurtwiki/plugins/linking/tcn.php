<?php

/*
  This plugin does minimal HTTP language negotiation (RFC2095) by selecting
  the best matching variant of a page.
  Language codes are always only two-letters in ewiki (country abbreviations
  are ignored or break it).
  Allowed page names are "PageName.en" or "PageName*en"
*/

define("EWIKI_DEFAULT_LANG", "en");
define("EWIKI_AUTO_CHOOSE", 1);
define("EWIKI_UP_FORCE_LANG", "lang");
$ewiki_config["langnames"] = array(
   "English" => "en",
   "Spanish" => "es",
   "German" => "de",
   "Dutch" => "nl",
);


$ewiki_plugins["handler"][] = "ewiki_tcn_handler_select_best";
$ewiki_plugins["view_final"][] = "ewiki_tcn_view_final_add_variants";


function ewiki_tcn_which_variant($id) {

   $langnames = &$GLOBALS["ewiki_config"]["langnames"];

   $n = strlen($id);
   $r = array($id, EWIKI_DEFAULT_LANG);

   if (($id[$n-3] == ".") || ($id[$n-3] == "*")) {
      $r = array(substr($id, 0, $n-3), substr($id, $n-2, 2));
   }
   else {
      foreach ($langnames as $str=>$cd) {
         if (substr($id, $n-strlen($str)) == $str) {
            $r = array(substr($id, 0, $n-strlen($str)), $cd);
         }
      }
   }
   return($r);
}


function ewiki_tcn_find_variants($id) {

   list($base_id, $id_lang) = ewiki_tcn_which_variant($id);
   $variants = array(
      "$id" => "$id_lang",
   );

   $result = ewiki_db::SEARCH("id",$base_id);
   while ($result && ($row = $result->get())) {
      if (substr($row["id"], 0, strlen($base_id)) == $base_id) {
         list($i, $l) = ewiki_tcn_which_variant($row["id"]);
         if ($i == $base_id) {
            $variants[$row["id"]] = $l;
         }
      }
   }

   return($variants);
}


function ewiki_tcn_handler_select_best(&$id, &$data, &$action) {

   global $ewiki_variants, $ewiki_t;

   $ewiki_variants = ewiki_tcn_find_variants($id);

   if ($action == "view") {
      if (count($ewiki_variants) >= 2) {

         $wanted_langs = $ewiki_t["languages"];
         if ($force_lang = $_REQUEST[EWIKI_UP_FORCE_LANG]) {
            array_unshift($wanted_langs, $force_lang);
         }         

         foreach ($wanted_langs as $l) {
            foreach ($ewiki_variants as $v_id=>$v_l) {
               if ($l==$v_l) {
                  $id = $v_id;
                  $data = ewiki_db::GET($id);
                  break 2;
               }
            }
         }#--langs

      }
   }

}


function ewiki_tcn_view_final_add_variants(&$o, $id, &$data, &$action) {

   global $ewiki_variants;

   if (count($ewiki_variants) >= 2) {

      $add = '<div class="language-variants">';
      foreach ($ewiki_variants as $v_id=>$v_l) {
         $add .= " <a href=\""
              . ewiki_script($action, $v_id, array(EWIKI_UP_FORCE_LANG=>$v_l))
              . "\" class=\"lang {$v_l}\" lang=\"{$v_l}\">{$v_l}</a> ";
      }
      $add .= '</div>'."\n";

      $o = $add . $o;
   }
}


?>