<?php

/*
   Using this extension you get access to the data from multiple backends
   (the requests will be dispatched and query results merged from
   individual database implementations). It is HIGHLY EXPERIMENTAL and
   it's typically safer to simply merge all page data into one database
   backend/implementation instead.
   
   You should set this up by hand as follows, because order is important:
   <!php
     include("plugins/db/any.php");
     include("plugins/db/dzf2.php");
     include("plugins/db/ext_multi.php");
     #-- create
     $ewiki_db = & new ewiki_database_multi(false);
     #-- register databases
     $ewiki_db->all[] = & new ewiki_database_anydb();
     $ewiki_db->all[] = & new ewiki_database_dzf2();
   !>
   
   If you only load the plugins, the order may not be preserved, so that
   new page entries go to the wrong backend (typically the first was the
   active/main database).
#this should be configureable!
   
   If you also want subwiki support, then you should load that plugin
   after _this_ one.
#both features could be merged

*/

define("EWIKI_MULTIDB_GUESS", 0);  // which database to use for WRITE calls, if disabled this will always fallback to the first backend


#-- registration
$ewiki_plugins["database"][0] = "ewiki_database_multi";


#-- backend wrapper
class ewiki_database_multi {

   var $all = array();

   function ewiki_database_multi($auto=true) {
      if ($auto) {
         foreach (get_declared_classes() as $classname) {
            if ((strpos($name, "ewiki_database_") === 0) && !strpos($name, "_multi")) {
               $this->all = & new $classname;
      }  }  }
   }


   function GET($id, $version=false) {
      for ($i=0; $i<count($this->all); $i++) {
         $r = $this->all[$i]->GET($id, $version);
         if ($r) {
            $r["db"] = $i;
            return $r;
         }
      }
   }


   function WRITE($hash, $overwrite=0) {
      $i = $hash["db"];
      if (!$i) {
         $i = 0;
      }
      return $this->all[$i]->WRITE($hash, $overwrite);
   }


   function HIT($id) {
   }


   function FIND($list) {
   }


   function GETALL($fields, $mask=0, $filter=0) {
   }


   function SEARCH($field, $content, $ci="i", $regex=0, $mask=0, $filter=0) {
   }


   function DELETE($id, $version) {
   }


   function INIT() {
      for ($i=0; $i<count($this->all); $i++) {
         $this->all[$i]->INIT();
      }
   }
   
   
   function WHICH($id) {
      
   }


} // end of class


?>