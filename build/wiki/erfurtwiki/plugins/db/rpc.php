<?php
/*
   Remote ewiki databases can be accessed using the PHP-RPC protocol
   (more lightweight and magnitudes faster than XML-RPC). But you must
   first create an interface on the remote server:
   
      <?php
         include("config.php");
         include("plugins/lib/phprpc.php");
         $passwords = array("accessname" => "password");
         include("fragments/funcs/auth.php");
         // define("EWIKI_DB_LOCK", 0);
         phprpc_server(array("ewiki_db"));
      ?>

   Give this a name of "z-db.php" or so, and give its absolute URL in
   the EWIKI_DB_RPC constant here. You must load plugins/lib/phprpc.php
   of course.
*/

define("EWIKI_DB_RPC", "http://name:pw@rpc.example.com/ewiki/z-db.php");


class ewiki_database_rpc {

   function ewiki_ddatabase_rpc($url=EWIKI_DB_RPC) {
      $this->api = $url;
   }
   

   function GET($id, $version) {
      return phprpc($this->url, "ewiki_db::GET", array($id, $version));
   }

   function WRITE($hash, $overwrite) {
      return phprpc($this->url, "ewiki_db::WRITE", array($hash, $overwrite));
   }

   function HIT($id) {
      // stub
   }

   function FIND($list) {
      return phprpc($this->url, "ewiki_db::FIND", array($list));
   }

   function GETALL($fields, $msk, $filt) {
      return phprpc($this->url, "ewiki_db::GETALL", array($fields, $msk, $filt));
   }

   function SEARCH($field, $content, $ci, $regex, $mask, $filter) {
      return phprpc($this->url, "ewiki_db::SEARCH", array($field, $content, $ci, $regex, $mask, $filter));
   }

   function DELETE($id, $version) {
      return phprpc($this->url, "ewiki_db::DELETE", array($id, $version));
   }

   function INIT() {
      return phprpc($this->url, "ewiki_db::INIT", array());
   }

}


?>