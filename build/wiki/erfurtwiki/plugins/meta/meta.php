<?php

/*
   This makes the core of the wiki page meta/ data framework, as it
   represents the main user interface. It places a smaller <textarea>
   below the edit/ box, which allows to add meta data for the edited
   page.
   Such meta data is evaluated by other plugins to group pages together
   or provide additional hints for users or for handling them in a
   specialized way.
*/


#-- settings
$ewiki_config["meta"]["rename_tag"] = array(
   "description"=>"teaser",  "desc"=>"teaser",
   "cat"=>"category",
   "back"=>"prev",
   "forward"=>"next",  "forw"=>"next",
   "up"=>"parent",
#  "pageno"=>"num",  "no"=>"num",  "number"=>"num",  "n"=>"num",
);
$ewiki_config["meta"]["keep_string"] = array(
   "teaser", "title",
);


define("EWIKI_UP_METABOX", "edit_meta_data");
$ewiki_t["de"]["meta data"] = "Meta-Daten";


#-- meta <textarea> 
$ewiki_plugins["edit_form_append"][] = "ewiki_aedit_metadata_box";
function ewiki_aedit_metadata_box($id, &$data, $action) {

   global $ewiki_config;

   #-- retrieve {meta} data
   $val = "";
   if ($m = $data["meta"]["meta"]) foreach ($m as $id => $line) {
      if (!in_array($id, $ewiki_config["meta"]["keep_string"])) {
         $line = preg_replace("/,\s*/", ", ", $line);
      }
      $val .= "$id: " . $line . "\n";
   }

   #-- show it up in an edit box
   $var = EWIKI_UP_METABOX;
   $val = htmlentities($val);
   return(ewiki_t(<<< EOT
<br />
 _{meta data}:<br /><textarea rows="4" cols="50" name="$var">$val</textarea>
<br />
EOT
   ));
}


#-- save into db {meta}{meta} field
$ewiki_plugins["edit_save"][] = "ewiki_edit_save_metadata";
function ewiki_edit_save_metadata(&$save, &$old_data) {

   global $ewiki_config;

   #-- clean old meta array (to allow for entry removal)
   unset($save["meta"]["meta"]);

   if ($val = trim($_REQUEST[EWIKI_UP_METABOX])) {

      #-- walk through specified entries
      preg_match_all('/^(\w+):(.+(?:(?:\n[^:\n]+)*\n)?)/m', $val, $uu);
      if (count($uu[1])) {
         foreach ($uu[1] as $i=>$name) {

            #-- entry name
            $name = strtolower($name);
            if ($new = $ewiki_config["meta"]["rename_tag"][$name]) {
               $name = $new;
            }

            #-- clean up
            if (!in_array($id, $ewiki_config["meta"]["keep_string"])) {
               $str = preg_replace("/\s*,\s*/", ",", trim($uu[2][$i]));
            }

            #-- store (also empty strings)
            $save["meta"]["meta"][$name] = $str;
      }  }
   }
}


?>