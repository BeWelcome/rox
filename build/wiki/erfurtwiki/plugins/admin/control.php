<?php

/*
  This plugin provides per-page administrative functions, for easier access
  to some settings and tools. Currently supports page renaming and page flag
  setting.
  requires _PROTECTED_MODE, see ewiki_auth() in README, and $ewiki_ring==0

  The functions have following ring permission level equirements:
     delete: ring<=1 moderators
     rename: ring<=1 moderators
     meta:   ring=0 admins
     flags:  ring=0 admins, moderators may change just some flags

  For styling purposes following CSS selectors could be used:
    .wiki.control  .flags  {...}
    .wiki.control  .rename  {...}
    .wiki.control  .meta  {...}
    .wiki.control  .delete  {...}
*/


#-- which flags moderators may change
define("EWIKI_DB_F_MODERATORFLAGS",  0x0070 | 0x0004 | 0x0008);
                             # == EWIKI_DB_F_ACCESS | EWIKI_DB_F_DISABLED | EWIKI_DB_F_HTML


#-- glue
$ewiki_plugins["action"]["control"] = "ewiki_action_control_page";
$ewiki_config["action_links"]["view"]["control"] = "page control";


#-- implementation
function ewiki_action_control_page($id, &$data, $action) {
   global $ewiki_ring, $ewiki_config, $ewiki_plugins;

   $a_flagnames = array(
      "_TEXT", "_BINARY", "_DISABLED", "_HTML", "_READONLY", "_WRITEABLE",
      "_APPENDONLY", "_SYSTEM", "_PART", "_MINOR", "_HIDDEN", "_ARCHIVE",
      "_UU12", "_UU13", "_UU14", "_UU15", "_UU16", "_EXEC", "_UU18", "_UU19",
   );
   

   $o = ewiki_make_title($id, "control $id", 2);

   #-- admin requ. ---------------------------------------------------------
   if (!ewiki_auth($id,$data,$action, $ring=0, "_FORCE_LOGIN=1") || !isset($ewiki_ring) || ($ewiki_ring > 1)) {

      if (is_array($data)) {
         $data = "You'll need to be admin. See ewiki_auth() and _PROTECTED_MODE in the README.";
      }
      $o .= $data;
     
   }

   #-- page flags ---------------------------------------------------------
   elseif (@$_REQUEST["pgc_setflags"]) {

      #-- setted new flags
      $new_f = 0;
      foreach ($_REQUEST["sflag"] as $n=>$b) {
         if ($b) {
            $new_f |= (1 << $n);
         }
      }
      #-- administrator may change all flags
      if ($ewiki_ring==0) {
         $data["flags"] = $new_f;
      }
      #-- moderators only a few
      else {
         $data["flags"] = ($data["flags"] & ( ~ EWIKI_DB_F_MODERATORFLAGS))
                        | ($new_f & EWIKI_DB_F_MODERATORFLAGS);
      }
      $data["lastmodified"] = time();
      $data["version"]++;

      if (ewiki_db::WRITE($data)) {
         $o .= "Page flags were updated correctly.";
         ewiki_log("page flags of '$id' were set to $data[flags]");
      }
      else {
         $o .= "A database error occoured.";
      }
   }

   #-- renaming -----------------------------------------------------------
   elseif  (@$_REQUEST["pgc_rename"] && strlen($new_id = $_REQUEST["mv_to"])) {

      $old_id = $id;
      $report = "";

      $preg_id = "/". addcslashes($old_id, ".+*?|/\\()$[]^#") ."/"
                 . ($_REQUEST["mv_cr1"] ? "i" : "");

      #-- check if new name does not already exist in database
      $exists = ewiki_db::GET($new_id);
      if ($exists || !empty($exists)) {
         return($o .= "Cannot overwrite an existing database entry.");
      }

      #-- copy from old name to new name
      $max_ver = $data["version"];
      $data = array();
      for ($v=1; $v<=$max_ver; $v++) {

         $row = ewiki_db::GET($old_id, $v);
         $row["id"] = $new_id;
         $row["lastmodified"] = time();
         $row["content"] = preg_replace($preg_id, $new_id, $row["content"]);
         ewiki_scan_wikiwords($row["content"], $links, "_STRIP_EMAIL=1");
         $row["refs"] = "\n\n".implode("\n", array_keys($links))."\n\n";
         $row["author"] = ewiki_author("control/");

         if (!ewiki_db::WRITE($row)) {
            $report .= "error while copying version $v,<br />\n";
              
         }
      }

      #-- proceed if previous actions error_free
      if (empty($report)) {

         #-- deleting old versions
         for ($v=1; $v<=$max_ver; $v++) {
            ewiki_db::DELETE($old_id, $v);
         }

         #-- adjust links/references to old page name
         if ($_REQUEST["mv_cr0"]) {

            $result = ewiki_db::SEARCH("refs", $old_id);
            while ($result && ($row = $result->get())) {

               $row = ewiki_db::GET($row["id"]);

               if (preg_match($preg_id, $row["content"], $uu)) {

                  $row["content"] = preg_replace($preg_id, $new_id, $row["content"]);
                  $row["lastmodified"] = time();
                  $row["version"]++;
                  ewiki_scan_wikiwords($row["content"], $links, "_STRIP_EMAIL=1");
                  $row["refs"] = "\n\n".implode("\n", array_keys($links))."\n\n";
                  $row["author"] = ewiki_author("control/");

                  if (!ewiki_db::WRITE($row)) {
                     $report .= "could not update references in ".$row['id'].",<br />\n";
                  } 
                  else {
                     $report .= "updated references in ".$row['id'].",<br />\n";
                  }
               }

            }

         }

         $o .= "This page was correctly renamed from '$old_id' to '$new_id'.<br /><br />\n$report";
         ewiki_log("page renamed from '$old_id' to '$new_id'", 2);

      }
      else {

         $o .= "Some problems occoured while processing your request, therefor the old page still exists:<br />\n" . $report;
      }

   }

   #-- meta data -----------------------------------------------------------
   elseif (@$_REQUEST["pgc_setmeta"] && ($ewiki_ring==0) && ($set = explode("\n", $_REQUEST["pgc_meta"]))) {

      $new_meta = array();
      foreach ($set as $line) {
         if (($line=trim($line)) && ($key=trim(strtok($line, ":"))) && ($value=trim(strtok("\000"))) ) {
            $new_meta[$key] = $value;
         }
      }

      $data["meta"] = $new_meta;
      $data["lastmodified"] = time();
      $data["version"]++;

      if (ewiki_db::WRITE($data)) {
         $o .= "The {meta} field was updated.";
      }
      else {
         $o .= "A database error occoured.";
      }
   }

   #-- deletion -----------------------------------------------------------
   elseif (@$_REQUEST["pgc_purge"] && $_REQUEST["pgc_purge1"]) {

      $loop = 3;
      do {
         $verZ = $data["version"];
         while ($verZ > 0) {
            ewiki_db::DELETE($id, $verZ);
            $verZ--;
         }
      } while ($loop-- && ($data = ewiki_db::GET($id)));

      if (empty($data)) {
         $o .= "Page completely removed from database.";
         ewiki_log("page '$id' was deleted from db", 2);
      }
      else {
         $o .= "Page still here.";
      }
   }

   #-- function list -------------------------------------------------------
   else {
      $o .= '<form action="'.ewiki_script("$action",$id).'" method="POST" enctype="text/html">'
          . '<input type="hidden" name="id" value="'."$action/$id".'">';

      #-- flags
      $o .= '<div class="flags">';
      $o .= "<h4>page flags</h4>\n";
      foreach ($a_flagnames as $n=>$s) {
         $disabled = (($ewiki_ring==1) && !((1<<$n) & EWIKI_DB_F_MODERATORFLAGS)) ? ' disabled="disabled"' : "";
         $checked = $data["flags"] & (1<<$n) ? ' checked="checked"': "";
         $a[$n] = '<input type="checkbox" name="sflag['.$n.']" value="1"'.
               $checked . $disabled .'> ' . $s;
      }
      $o .= '<table border="0" class="list">' . "\n";
      for ($n=0; $n<count($a_flagnames); $n++) {
         $y = $n >> 2;
         $x = $n & 0x03;
         if ($x==0) $o .= "<tr>";
         $o .= "<td>" . $a[4*$y + $x] . "</td>";
         if ($x==3) $o .= "</tr>\n";
      }
      $o .= '</table>';
      $o .= '<input type="submit" class="button" name="pgc_setflags" value="chmod">';
      $o .= "\n<br /><br /><hr></div>\n"; 

      #-- rename
      $o .= '<div class="rename">';
      $o .= "<h4>rename page</h4>\n";
      $o .= 'new page name: <input type="text" size="30" name="mv_to" value="'.htmlentities($id).'">'
          . '<br />'
          . '<input type="checkbox" name="mv_cr0" value="1" checked> also try to change all references from other pages accordingly '
          . '(<input type="checkbox" name="mv_cr1" value="1" checked> and act case-insensitive when doing so) ';
      $o .= '<br /><input type="submit" class="button" name="pgc_rename" value="mv">';
      $o .= "\n<br /><br /><hr></div>\n"; 

      #-- meta
      if (isset($ewiki_ring) && ($ewiki_ring==0)) {
      $o .= '<div class="meta">';
      $o .= "<h4>meta data</h4>\n";
      $o .= '<textarea cols="40" rows="6" name="pgc_meta">';
      if (($uu = @$data["meta"]) && is_array($uu))
      foreach ($uu as $key=>$value) {
         if (is_array($value)) { $value = serialize($array); }
         $o .= htmlentities($key.": ".trim($value)) . "\n";
      }
      $o .= "</textarea>\n";
      $o .= '<br /><input type="submit" class="button" name="pgc_setmeta" value="set">';
      $o .= "\n<br /><br /><hr></div>\n"; 
      }

      #-- delete
      $o .= '<div class="delete">';
      $o .= "<h4>delete page</h4>\n";
      $o .= '<input type="checkbox" name="pgc_purge1" value="1"> I\'m sure';
      $o .= '<br /><input type="submit" class="button" name="pgc_purge" value="rm">';
      $o .= "\n<br /><br /><hr></div>\n"; 

      $o .= '</form>';
   }

   return($o);
}



?>