<?php
/*
   This plugin provides utility and access code for NearLinks, SisterPages
   and other InterWiki plugins. It depends upon the MetaWikiDatabase file
   being available (not distributed with ewiki, but cron can download it).
   You need to have it inside of the EWIKI_VAR directory with a filename
   of "metadb" (maybe .gz compressed).
*/


define("EWIKI_METADB_FN", "metadb");


#-- utility code class (static)
class ewiki_metadb {


   #-- read-in metadb file
   function LOAD() {
      global $ewiki_metadb;
      $ewiki_metadb = array();

      if (file_exists($fn = EWIKI_VAR."/".EWIKI_METADB_FN)
      or (file_exists($fn .= ".gz"))) {

         $f = gzopen($fn, "r");
         $line = gzread($f, 1<<24);
         gzclose($f);

         foreach (explode("\n", $line) as $line) {
            $real = strtok($line, " ");
            $ci = strtok(" ");
            $where = strtok(" \n\r");

            $ewiki_metadb[$ci] = array($real, $where);
         }
      }
      return(count($ewiki_metadb));
   }
   function UNLOAD() {
      global $ewiki_metadb;
      $ewiki_metadb = array();
   }
   
   #-- search for listed page names in InterWiki namespace
   function FIND($list) {
      global $ewiki_metadb, $ewiki_config;
      $r = array();
      
      foreach ($list as $id) {
         $ci = strtolower($id);
         if ($uu = $ewiki_metadb[$ci]) {
            $real = $uu[0];
            $r[$id] = array();
            foreach (explode("|", trim($uu[1], "|")) as $iw) {
               if ($ewiki_config["interwiki"][$iw]) {
                  $r[$id][] = "$iw:$real";
               }
            }
         }
         else {
            $r[$id] = 0;
         }
      }
      
      return($r);
   }

}



?>