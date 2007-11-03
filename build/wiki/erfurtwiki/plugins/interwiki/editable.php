<?php

/*
   A publically EditableInterMap implementation. Entries therein cannot
   override the default entries of the bultin intermap array (or the one
   from the list extension plugin).
   All entries must be provided in the definition list form:
     :WikiMoniker:httpz://www.example.net/cgi-bin/wiki.cgi/...

   Set the _APPENDONLY page flag to restrict editing.
*/


#-- load data from intermap page
$ewiki_plugins["init"][] = "ewiki_load_editable_intermap";


function ewiki_load_editable_intermap($uu=0, $uu=0, $uu=0) {

   global $ewiki_plugins, $ewiki_config;
   $inter = &$ewiki_config["interwiki"];

   #-- fetch from db
   $id = "EditableInterMap";
   if ($data = ewiki_db::GET($id)) {

      #-- extract entries
      if (preg_match_all('/^:(\w+):([^\s]+)/m', $data["content"], $uu)) {
         foreach ($uu[1] as $i=>$moni) {
            if (!isset($inter[$moni])) {
               $inter[$moni] .= $uu[2][$i];
            }
      }  }
      /*
         WONT_WORK
         $refs = explode("\n", trim($data["refs"]));
         for ($n=1; $n<count($refs); $n++) {
            if (strpos($refs[$n], "://")) {
               $moni = $refs[$n-1];
               if (!isset($inter[$moni])) {
                  $inter[$moni] .= $refs[$n];
               }
            }
         }
      */

      #-- enable _APPENDONLY part
      if ($data["flags"] & EWIKI_DB_F_APPENDONLY) {
         $ewiki_plugins["page"]["EditableInterMap"] = "ewiki_editable_intermap";
      }
   }
}


#-- provides an append-form to prevent total-editing (which
#   otherwise allowed random removal of existing entries)
function ewiki_editable_intermap($id, $data, $action) {

   global $ewiki_config;

   $o = "";

   if (($url = $_REQUEST["add_url"]) && ($moni = $_REQUEST["add_moniker"])) {
      if (!preg_match('#^http[sz]?://(\w{2,}\.)+\w{2,}(:\d+)?[^\[\]\"\s]+$#', $url) || strpos($url, "example")) {
         $o .= "URL was rejected.";
      }
      elseif (!preg_match('#^(['.EWIKI_CHARS_U.']+['.EWIKI_CHARS_L.']+){2,}['.EWIKI_CHARS.']+$#', $moni) || ($moni == "WikiName")) {
         $o .= "Choosen InterWiki moniker not acceptable.";
      }
      else {
         if ($ewiki_config["interwiki"][$moni]) {
            $o .= "(Note: eventually overriding earlier entry.)<br />";
         }
         $data["content"] =
             "\n" .
             trim($data["content"]) .
             "\n" .
             ":$moni:$url" .
             "\n";
         ewiki_data_update($data);
         $data["version"]++;
         if (ewiki_db::WRITE($data)) {
            $o .= "Map was updated.";
         } else {
            $o .= "Error occoured when saving your changes.";
         }
      }
      $o .= "<br />";
   }

   $o .= ewiki_make_title($id, $id, 2);
   $o .= ewiki_page_view($id, $data, $action, 0);

   $o .= ewiki_t(<<<EOT
    <form class="intermap-append" action="$_SERVER[REQUEST_URI]" method="POST" enctype="multipart/form-data">
      <hr>
      <input type="hidden" name="id" value="$id">
      :<input name="add_moniker" value="WikiName" size="16">:<input name="add_url" value="http://www.example.com/..." size="42">
      <br />
      <input type="submit" value="_{add}">
    </form>
EOT
   );

   return($o);
}

?>