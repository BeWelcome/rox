<?php

/*
   This plugin makes virtual page aliases from and to plural and non-plural
   page names. Then "WikiWord" and "WikiWords" link to the same page.

   (Best practice, btw, is to always give page names only in singular.)
*/

#-- config
$ewiki_config["plural"] = array("s", "en", "n");
# (one may want to add "e" and others)

#-- plugin registration
$ewiki_plugins["format_prepare_linking"][] = "ewiki_plural_linking_patch";
$ewiki_plugins["handler"][] = "ewiki_handler_plural";


/*  this function tries to cross-convert from singular to plural, and
    returns possible permutations as array() result;
    you will wish to rewrite this function for better results
*/
function ewiki_plural_variants($id) {

   global $ewiki_config;

   #-- built base id
   $n_id = strlen($id);
   $base_id = $id;
   foreach ($ewiki_config["plural"] as $app) {
      if (substr($id, $n_id - strlen($app)) == $app) {
         $base_id = substr($id, 0, $n_id - strlen($app));
         break;
      }
   }

   #-- make array of variants
   $r = array($id);
   if ($id != $base_id) {
      $r[] = $base_id;
   }
   foreach ($ewiki_config["plural"] as $app) {
      $r[] = $id.$app;
      if ($id != $base_id) {
         $r[] = $base_id.$app;
      }
   }
   
   return($r);
}


/*  returns the first (plural/singular permutation) alternative for $id
    found in the database
*/
function ewiki_plural_alternative($id) {
   #-- make possible plural/singular $id versions
   $variants = ewiki_plural_variants($id);
   array_shift($variants);   # skip original

   #-- search in DB
   $variants = ewiki_db::FIND($variants);
   foreach ($variants as $new_id=>$exists) {

      if ($new_id && $exists) {
         return($new_id);
      }
   }

   #-- none found
   return(false);
}


/*  searches for and loads another page, if requested $id not found
*/
function ewiki_handler_plural(&$id, &$data, &$action) {

   #-- current page not found, but an alternative exists in DB
   if (!$data["version"] && ($new_id = ewiki_plural_alternative($id))) {

      #-- restart ewiki
      return(ewiki_page($id = $new_id));
   }
}


/*  fakes existing pages by creating ["page"] plugin entries
*/
function ewiki_plural_linking_patch(&$src) {

   global $ewiki_links, $ewiki_plugins;


   #-- search for not-found entries
   foreach ($ewiki_links as $missing_id=>$exists) {

      if (!$exists && ewiki_plural_alternative($missing_id)) {
         $ewiki_plugins["page"][$missing_id] = "ewiki_page";
      }

   }
}


?>