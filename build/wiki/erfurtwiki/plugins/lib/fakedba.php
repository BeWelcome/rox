<?php
/*
   Load this with plugins/db/dba.php if your PHP provides only the
   older dbm_*() functions.
*/


#-- fake dba_* using dbm_* functions
if (!function_exists("dba_open") && function_exists("dbm_open")) {

   function dba_open($path, $mode, $handler, $a1=0) {
      if ($handler == "dbm") {
         return(dbmopen($path, $mode));
      }
      else return(false);
   }

   function dba_popen($a, $b, $c, $d=0) {
      return(dba_open($a, $b, $c));
   }

   function dba_exists($key, $handle) {
      return(dbmexists($handle, $key));
   }

   function dba_fetch($key, $handle) {
      return(dbmfetch($handle, $key));
   }

   function dba_insert($key, $string, $handle) {
      return(dbminsert($handle, $key, $string));
   }

   function dba_replace($key, $string, $handle) {
      return(dbmreplace($handle, $key, $string));
   }

   function dba_delete($key, $handle) {
      return(dbmdelete($handle, $key));
   }

   function dba_firstkey($handle) {
      return($GLOBALS["dbm_lastkey"] = dbmfirstkey($handle));
   }

   function dba_nextkey($handle) {
      return(dbmnextkey($handle, $GLOBALS["dbm_lastkey"]));
   }

   function dba_close($handle) {
      return(dbmclose($handle));
   }

   function dba_handlers() {
      return(array("dbm"));
   }

}


?>