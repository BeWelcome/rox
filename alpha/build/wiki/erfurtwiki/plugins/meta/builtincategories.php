<?php

/*
   This plugin provides WikiFeatures:BuiltinCategories using the default
   meta data entry.
*/



$ewiki_categories = array(
   "book" => "Book",
   "discussion" => "Discussion/Talk", 
   "building" => "Building",
   "person" => "Person",
);

define("EWIKI_BULTINCAT_METAID", "category");
define("EWIKI_UP_SET_CATEGORY", "set_category");

$ewiki_t["de"]["category"] = "Kategorie";


$ewiki_plugins["edit_form_append"][] = "ewiki_builtin_categories";
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_category";


/*
   store selected category
*/
function ewiki_edit_save_category(&$save, &$old_data) {
   #-- anything to do?
   if (($set_to=$_REQUEST[EWIKI_UP_SET_CATEGORY]) || isset($set_to)) {
      #-- test if user supplied a more concrete setting
      $old_cat = @$old_data["meta"]["meta"][EWIKI_BULTINCAT_METAID];
      $cur_cat = @$save["meta"]["meta"][EWIKI_BULTINCAT_METAID];
      if ($cur_cat == $old_cat) {
         $save["meta"]["meta"][EWIKI_BULTINCAT_METAID] = trim(strtolower($set_to));
      }
   }
}


/*
   show category selection dropdown
*/
function ewiki_builtin_categories($id, &$data, $action) {

   global $ewiki_categories;
   $cat = array_merge(
      array("" => "-"),
      $ewiki_categories
   );
   $current = @$data["meta"]["meta"][EWIKI_BULTINCAT_METAID];

   $o = "<br /> \n " . ewiki_t("category") . ": ";
   $o .= ewiki_htm_select(EWIKI_UP_SET_CATEGORY, $cat, $current);

   return($o);
}


/*
   returns a html <select>(<option>)+ form chunk
*/
function ewiki_htm_select($input_id, $args, $default, $use_vals=0) {
   $default = strtolower($default);
   $o = "<select name=\"$input_id\">";
   foreach ($args as $val=>$str) {
      if (!$use_vals && is_int($val)) {
         $val = $str;
      }
      $o .= "<option value=\"$val\""
         . (strtolower($val) == $default ? " selected" : "")
         . ">$str</option>";
   }
   $o .= "</select>";
   return($o);
}


?>