<?php
/*
  If you are using an older database backend (one of the ewiki_database_*()
  functions), then you need to load this plugin wrapper _after_ it, so it
  still can be accessed by the core using the newer OO interface.
  
  Whenever it doesn't work out of the box, do this by hand:
    $ewiki_db = new ewiki_database_compat("");
    $ewiki_db->pf = "old_db_function_name";
    
  You can probably also use this file as template for converting your
  database backend to the new scheme.
*/


#-- enable it
if (!isset($ewiki_db) && isset($ewiki_plugins["database"])) {
   $ewiki_db = new ewiki_database_compat($ewiki_plugins["database"][0]);
}


#-- compatibility wrapper
class ewiki_database_compat {

   var $pf;

   function ewiki_database_compat($pf) {
      $this->pf = $pf;
   }
   
   function GET($id, $version=false) {
      return $this->pf("GET", array("id"=>$id, "version"=>$version));
   }

   function WRITE($data, $overwrite=0) {
      $FUNC = $overwrite ? "OVERWRITE" : "WRITE";
      return $this->pf($FUNC, $data);
   }

   function HIT($id) {
      return $this->pf("HIT", $id);
   }

   function FIND($list) {
      return $this->pf("FIND", $list);
   }

   function GETALL($fields) {
      return $this->pf("GETALL", $fields);
   }

   function SEARCH($field, $content, $ci=1, $regex=0, $mask=0, $filter=0) {
      return $this->pf("SEARCH", array($field=>$content));
   }

   function DELETE($id, $version) {
      return $this->pf("DELETE", array("id"=>$id, "version"=>$version));
   }

   function INIT() {
      return $this->pf("INIT", array());
   }

}


?>