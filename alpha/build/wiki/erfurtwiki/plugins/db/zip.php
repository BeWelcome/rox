<?php

/*
   Beware, this is a fun plugin!  It is supposed to work, but not
   recommended for seriously big installations.  This database backend
   stores all your pages in a ZIP file, it requires the standard util
   "zip" ('pkzip.exe' may not work).

   You must set EWIKI_DB_ZIP and EWIKI_TMP to writable locations (the
   directory in which the single ZIP file resides must be world-
   writable).
   This database plugin is _CASE_INSENSITIVE always, hit counting isn't
   done. Eventually you even have to create an empty ZIP file yourself.
   And this will only run in UNIX environments!
*/

define("EWIKI_DB_ZIP", "/tmp/database.zip");
define("EWIKI_CASE_INSENSITIVE", "always");

#-- reg
$ewiki_plugins["database"][0] = "ewiki_db_zip";

#-- backend
class ewiki_db_zip {

   var $tmp = EWIKI_TMP;
   var $util = "zip ";
   var $util_un = "unzip ";
   var $util_get = "unzip -q -C -p ";
   var $util_add = "zip -j -q -u ";
   var $zip = EWIKI_DB_ZIP;
   var $QUIET = "2>/dev/null";


   function ewiki_db_zip() {
   }


   function GET($id, $version=false) {
      $fn = $this->FN($id);
      if (!$version) {
         $version = 0 + trim(`$this->util_get $this->zip $fn $this->QUIET`);
      }
      if ($version) {
         $fn .= ".$version";
         $r = `$this->util_get $this->zip $fn `;
         $r = unserialize($r);
      }
      return($r);
   }


   function WRITE($hash, $overwrite=0) {
      $fn = "$this->tmp/" . $this->FN($hash["id"]);
      $fn2 = "$fn." . $hash["version"];
      if ($f = fopen($fn2, "w")) {
         fwrite($f, serialize($hash));     // unsafe, to say mildly
         fclose($f);
         if ($f = fopen($fn, "w")) {
            fwrite($f, $hash["version"]);
            fclose($f);
            #-- add to zip
            $r = `$this->util_add $this->zip $fn2 $fn $this->QUIET`;
            $r = !$r;
            @unlink($fn);
         }
         @unlink($fn2);
      }
      return($r);
   }
   
   
   function HIT($id) {
      // nop
   }
   
   
   function FIND($list) {
      $r = array();
      foreach ($list as $id) if ($id) {
         $r[$id] = 0;
         $fn = $this->FN($id);
         if ($ver = `$this->util_get $this->zip $fn $this->QUIET`) {
            $r[$id] = 1;
         }
      }
      return($r);
   }


   function GETALL($fields, $mask=0, $filter=0) {
      $r = new ewiki_dbquery_result($fields);
      foreach (explode("\n", `$this->util_un -l $this->zip | cut -b 29-290`) as $id) {
         if (!strpos($id, ".") || !preg_match('/\.\d+$/', $id)) {
            $r->entries[] = rawurldecode($id);
         }
      }
      return($r);
   }


   function SEARCH($field, $content, $ci="i", $regex=0, $mask=0, $filter=0) {
      $r = new ewiki_dbquery_result($args);
      if ($ci && !$regex) {
         $content = strtolower($content);
      }
      $ALL = $this->GETALL(array($field), $mask, $filter);
      while ($row = $ALL->get()) {
         if ($regex) {
            $match = preg_match("\007$content\007$ci", $row["field"]);
         }
         elseif ($ci) {
            $match = strpos(strtolower($row[$field]), $content) !== false;
         }
         else {
            $match = strpos($row[$field], $content) !== false;
         }
         if ($match) {
            $r->add($row);
         }
      }
      return($r);
   }


#   function DELETE($id, $version) {
#   }


   function INIT() {
      if (!file_exists($this->zip) || !filesize($this->zip)) {
         $f = fopen($this->zip, "wb");
         fwrite($f, "PK\005\006".str_repeat("\000", 18));
         fclose($f);
      }
      if (!is_writeable($this->zip)) {
         echo "error db_zip: $this->zip is not writeable!\n";
      }
   }


   function FN($id) {
      $id = ewiki_lowercase($id);
      return(rawurlencode($id));
   }


} // end of class


?>