<?php

/*
   This plugin provides the database abstraction layer for SQL-compliant
   relational databases, for which interfaces in either ADOdb, PEAR::DB
   or PHP's dbx extension exist. It can access MySQL and Postgres without
   any of these wrappers, btw.
   You could establish a database connection via one of these db wrappers
   yourself and put it into the global $db var, but it is sometimes better
   to use the "anydb_connect()" function.

   Currently this plugin is mainly used (and only tested with) the
   PostgreSQL database. You should rather not use this with MySQL before
   4.1 (even if it still works with 3.x versions).

   Notes:
   - you should use the anydb_connect() when possible or else assign
     your PEAR::DB, ADOdb or dbx connection handle to the global '$db'
   - this interface also accepts native MY or PG connection handles
   - IMPORTANT: this newer OO-interface requires that the database
     connection is already established when you load this plugin - else
     you should put the anydb_connect() call herein
   - sqlite is only supported by PEAR::DB currently (but not tested)
   - dbx is rather memory exhaustive ("emalloc() unable to allocate
     1.7 gigabytes"...) - but maybe just a bug in my version(?)
   - dbx is otherwise a very good thing, but now not very suitable
     for the newer ewiki database layer
   - ADOdb does not work with PHP5
   - ewiki uses the Latin-1 charset exclusively, your database needs
     to know this (createdb -E LATIN1 wikidb for PostgreSQL)
   - else you could enable EWIKI_DB_UTF8 for Postgres "UNICODE" databases,
     where "SET NAMES" doesn't work
   - there is no _DB_F_BINARY support with PostgreSQL, so please use
     db/binary_store meanwhile or enable the EWIKI_DB_BIN64 workaround
     (minimally slower, most features remain, only irreal drawback is
     that _BINARY entries cannot be ::SEARCHed then)

   See also:
   - [http://php.weblogs.com/adodb] for ADOdb
   - [http://pear.php.net/] for PEAR::DB
   - [http://www.php.net/manual/en/ref.dbx.html] for dbx()
*/


#-- open db link here, if not already done, example:
/*
  include(".../adodb/adodb.inc.php")
  or include("DB.php")
  or dl("dbx.so");

  $db = anydb_connect("localhost", "root", "$password", "test", "mysql");
*/


#-- config
define("EWIKI_DB_UTF8", false);
define("EWIKI_DB_BIN64", false);    // cipher any _BINARY entry


#-- plugin registration
$ewiki_plugins["database"][0] = "ewiki_database_anydb";



#-- backend
class ewiki_database_anydb {

   var $table = EWIKI_DB_TABLE_NAME;

   function ewiki_database_anydb() {
      anydb_query("SELECT 1;", $GLOBALS["db"]);    // saves connection handle
   }


   function GET($id, $version) {
      if (EWIKI_DB_UTF8) $this->UTF8_ENCODE($id);
      $id = anydb_escape_string($id);
      if ($version) {
         $AND_VERSION = "AND (version=$version)";
      }
      $result = anydb_query("
          SELECT * FROM $this->table
          WHERE (pagename='$id') $AND_VERSION
          ORDER BY version DESC  LIMIT 1
      ");
      if ($result && ($r = anydb_fetch_array($result, "_ASSOC_ONLY=1"))) {
         $r["id"] = $r["pagename"];
         unset($r["pagename"]);
      }
      if (EWIKI_DB_UTF8) $this->UTF8_DECODE($r);
      if (EWIKI_DB_BIN64) $this->BIN64_DECODE($r);
      return($r);
   }



   function WRITE($hash, $overwrite=0) {
      if (EWIKI_DB_BIN64) $this->BIN64_ENCODE($hash);
      if (EWIKI_DB_UTF8) $this->UTF8_ENCODE($hash);

      #-- overwrite
      $id = anydb_escape_string($hash["id"]);
      $ver = $hash["version"];
      $current = "FROM $this->table WHERE (pagename='$id') AND (version=$ver)";
      if (($r = anydb_query("SELECT flags $current"))
      and anydb_fetch_array($r)) {
         if ($overwrite) {
            anydb_query("DELETE $current");
         } else {
            return;
         }
      }

      #-- build INSERT command      
      $hash["pagename"] = $hash["id"];
      unset($hash["id"]);
      $sql1 = $sql2 = "";
      foreach ($hash as $index => $value) {
         if (is_int($index)) {
            continue;
         }
         $a = ($sql1 ? ', ' : '');
         $sql1 .= $a . $index;
         $sql2 .= $a . "'" . anydb_escape_string($value) . "'";
      }

      $result = anydb_query(
          "INSERT INTO $this->table ($sql1) VALUES ($sql2)"
      );
       return($result ?1:0);
   }


   function HIT($id) {
      if (EWIKI_DB_UTF8) $this->UTF8_ENCODE($id);
      $id = anydb_escape_string($id);
      anydb_query("UPDATE $this->table SET hits=(hits+1) WHERE pagename='$id'");
   }


   function FIND($list) {
      if (EWIKI_DB_UTF8) $this->UTF8_ENCODE($list);
      $where = array();
      foreach ($list as $id) {
         if (strlen($id)) {
            $r[$id] = 0;
            $where[] = "(pagename='".anydb_escape_string($id)."')";
         }
      }
      $where = implode(" OR ", $where);
      if (strlen($where)) { $where = "WHERE $where"; }
      $result = anydb_query(
         "SELECT pagename AS id, meta, flags FROM $this->table $where"
      );
      $r = array();
      while ($result && ($row = anydb_fetch_array($result))) {
         $id = EWIKI_DB_UTF8 ? utf8_decode($row[0]) : $row[0];
         if ($row["meta"]) {
            $r[$id] = $row["meta"];
            $r[$id]["flags"] = $row["flags"];
         } else {
            $r[$id] = $row["flags"];
         }
      }
      if (EWIKI_DB_UTF8) $this->UTF8_DECODE($r);
      return($r);
   }


   function GETALL($fields, $mask=0, $filter=0) {
      $result = anydb_query("SELECT pagename AS id, flags, version, ".
         implode(", ", $fields) .
         " FROM $this->table " .
         " ORDER BY id, version DESC"
      );
      return $this->AS_DBQUERY_RESULT($result, $fields);
   }

   
   function AS_DBQUERY_RESULT(&$result, $fields) {
      $r = new ewiki_dbquery_result($fields);
      $last = "";
      if ($result) while ($row = anydb_fetch_array($result)) {
         if (EWIKI_DB_UTF8) $this->UTF8_DECODE($row);
         $drop = EWIKI_CASE_INSENSITIVE ? strtolower($row["id"]) : $row["id"];
         if (($last != $drop) && ($last = $drop)) {
            if (EWIKI_DB_UTF8) $this->UTF8_DECODE($row);
            if (EWIKI_DB_BIN64) $this->BIN64_DECODE($row);
            $r->add($row);
         }
      }
      return($r);
   }


   function SEARCH($field, $content, $ci="i", $regex=0, $mask=0, $filter=0) {
      global $anydb_type;
      if (EWIKI_DB_UTF8) $this->UTF8_ENCODE($content);
      // if (EWIKI_DB_BIN64 && ($field=="content") && ($flags&EWIKI_DB_F_BINARY)) $this->BIN64_ENCODE($content);
      if ($field != "id") { 
         $sqlfield = ", $field";
      }
      if ($regex) {
         if ($GLOBALS["anydb_type"] == ANYDB_MY) {
            $regex = "REGEXP";
         } else {
            $regex = ($ci ? "~": "~*");
         }
         $WHERE = "$field $regex '$content'";
      }
      elseif ($ci) {
         $content = strtolower($content);
         if ($anydb_type == ANYDB_PG && $field == 'id')
            $WHERE = "POSITION('$content' IN LOWER(pagename)) > 0";
         else 
            $WHERE = "POSITION('$content' IN LOWER($field)) > 0";
      }
      else {
         $WHERE="POSITION('$content' IN $field) > 0";
      }
      $content = anydb_escape_string($content);
      $result = anydb_query("
         SELECT pagename AS id, version, flags $sqlfield
           FROM $this->table
          WHERE ($WHERE)
          ORDER BY id, version DESC
      ");
      return $this->AS_DBQUERY_RESULT($result, array($field, "version", "flags"));
   }


   function DELETE($id, $version) {
      if (EWIKI_DB_UTF8) $this->UTF8_ENCODE($id);
      $id = anydb_escape_string($id);
      anydb_query("DELETE FROM $this->table WHERE pagename='$id' AND version=$version");
   }


   function INIT() {
      anydb_query("CREATE TABLE $this->table
       ( pagename VARCHAR(160)  NOT NULL,
         version INTEGER  DEFAULT 0  NOT NULL,
         flags INTEGER  DEFAULT 0,
         content TEXT  DEFAULT '',
         refs TEXT  DEFAULT '',
         meta TEXT  DEFAULT '',
         author VARCHAR(100)  DEFAULT 'ewiki',
         created INTEGER   DEFAULT ".time().",
         lastmodified INTEGER  DEFAULT 0,
         hits INTEGER  DEFAULT 0
       ) ");
      anydb_query("
         ALTER TABLE ONLY $this->table
            ADD CONSTRAINT internal_id PRIMARY KEY (pagename, version);
      ");
   }


   #-- for charset-aware databases
   function UTF8_ENCODE(&$a) {
      if (is_array($a)) foreach ($a as $i=>$v) {
         $a[$i] = is_array($v) ? $this->UTF8_ENCODE($v) : utf8_encode($v);
      }
      else {
         $a = utf8_encode($a);
      }
   }
   function UTF8_DECODE(&$a) {
      if (is_array($a)) foreach ($a as $i=>$v) {
         $a[$i] = is_array($v) ? $this->UTF8_DECODE($v) : utf8_decode($v);
      }
      else {
         $a = utf8_decode($a);
      }
   }


   #-- only engages if the EWIKI_DB_F_BINARY flag is set
   function BIN64_ENCODE(&$a) {
      if (!is_array($a)) {
         $a = base64_encode($a);
      }
      elseif ($a["flags"] & EWIKI_DB_F_BINARY) {
         $a["content"] = base64_encode($a["content"]);
      }
   }
   function BIN64_DECODE(&$a) {
      if (isset($a["content"]) && ($a["flags"] & EWIKI_DB_F_BINARY)) {
         $a["content"] = base64_decode($a["content"]);
      }
   }


}







#----------------------------------------------------------------------------



if (!function_exists("anydb_connect")) {
#############################################################################
###                                                                       ###
###   anydb access wrapper wrapper                                        ###
###                                                                       ###
#############################################################################


define("ANYDB_PEAR", 21);
define("ANYDB_ADO",  22);
define("ANYDB_DBX",  23);
define("ANYDB_PG",   51);   // Postgres
define("ANYDB_MY",   52);   // MySQL3.x
define("ANYDB_LI",   53);   // SQLite
define("ANYDB_MI",   54);   // MySQLi/4


function anydb_connect($host="localhost", $user="", $pw="", $dbname="test", $dbtype="mysql") {
   global $anydb_handle;
   class_exists("DB")
     and ($db = DB::connect("$dbtype://$user:$pw@$host/$dbname"))
     and (is_a($db, "db_common"))
     and ($db->setFetchMode(DB_FETCHMODE_ASSOC) or true)
   or function_exists("newadoconnection")
     and ($db = NewAdoConnection($dbtype))
     and ($db->connect($host, $user, $pw, $dbname))
     and ($db->setFetchMode(ADODB_FETCH_ASSOC) or true)
   or ($dbtype[0]=="p") and function_exists("pg_connect")
     and ($db = pg_connect("dbname=$dbname user=$user password=$pw"))
   or function_exists("mysql_connect")
     and ($db = mysql_connect($host, $user, $pw))
     and (mysql_query("USE $dbname"))
   or function_exists("dbx_connect")
     and ($db = dbx_connect($dbtype, $host, $dbname, $user, $pw))
   or ($db = false);

   if ($anydb_handle = $db) {
      $charset = EWIKI_DB_UTF8 ? "UTF8" : "ISO-8859-1";
      @anydb_query("SET NAMES '$charset'");  #-- not all databases support this
   }
   return($db);
}


function anydb_handle($db=NULL) {
   global $anydb_handle, $anydb_type;
   if (!empty($db)) {
      $anydb_handle = & $db;
      $anydb_type = anydb_type($anydb_handle);
   }
   return($anydb_handle);
}


function anydb_type(&$obj) {
   if (is_object($obj)) {
      if (is_a($obj, "db_common") || is_a($obj, "db_result")) {
         return(ANYDB_PEAR);
      }
      elseif (is_a($obj, "adoconnection") || is_a($obj, "adorecordset")) {
         return(ANYDB_ADO);
      }
      elseif (is_a($obj, "stdclass")) {
         return(ANYDB_DBX);
      }
   } 
   elseif (is_resource($obj) && ($type = strtok(get_resource_type($obj), " "))) {
      if ($type == "pgsql") {
         return(ANYDB_PG);
      }
      elseif ($type == "mysql") {
         return(ANYDB_MY);
      }
   }
}


function anydb_query($sql, $db="") {
   global $anydb_type;
   $db = anydb_handle($db);
   $res = false;
   if ($anydb_type == ANYDB_PEAR) {
      $res = $db->query($sql);
      if (DB::isError($res)) { $res = false; }
   }
   elseif ($anydb_type == ANYDB_ADO) {
      $res = $db->Execute($sql);
   }
   elseif ($anydb_type == ANYDB_DBX) {
      $res = dbx_query($db, $sql, DBX_RESULT_ASSOC);
   }
   elseif ($anydb_type == ANYDB_PG) {
      $res = pg_query($db, $sql);
   }
   elseif ($anydb_type == ANYDB_MY) {
      $res = mysql_query($sql, $db, MYSQL_ASSOC);
   }
   return($res);
}



function anydb_fetch_array(&$res, $assoc_only=0) {
   global $anydb_type;
   $anydb_type = anydb_type($res);
   $r = false;
   if ($anydb_type == ANYDB_PEAR) {
      $r = $res->fetchRow(DB_FETCHMODE_ASSOC);
      if (is_object($r)) {
         $r = false;
      }
   }
   elseif ($anydb_type == ANYDB_ADO) {
      $r = $res->FetchRow();
      #<ok>  $r = obj || false
   }
   elseif ($anydb_type == ANYDB_DBX) {
      $r = array_shift($res->data);
      #<ok>#  $r == obj || 1 || false
   }
   elseif ($anydb_type == ANYDB_PG) {
      $r = pg_fetch_assoc($res);
   }
   elseif ($anydb_type == ANYDB_MY) {
      $r = mysql_fetch_array($res, $db);
   }
   #-- make numeric indicies, if wanted
   $n = 0;
   if (!$assoc_only && is_array($r) && count($r)) {
      foreach ($r as $i=>$d) {
         if (!is_int($i)) {
            $r[$n++] = &$r[$i];
         }
      }
   }
   return($r);
}



function anydb_escape_string($s, $db="") {
   $db = anydb_handle($db);
   $type = anydb_type($db);
   if ($type == ANYDB_PEAR) {
      $s = $db->quoteString($s);
   }
   elseif ($type == ANYDB_ADO) {
      $s = $db->qStr($s);
      if ($s[0] = "'") {
         $s = substr($s, 1, strlen($s) - 2);
      }
   }
   elseif ($type == ANYDB_DBX) {
      $s = dbx_escape_string($db, (string)$s);
   }
   elseif ($type == ANYDB_PG) {
      $s = pg_escape_string((string)$s);
   }
   elseif ($type == ANYDB_MY) {
      $s = mysql_escape_string((string)$s);
   }
   else {
      $s = addslashes($s);
   }
   return($s);
}


#############################################################################
###                                                                       ###
#############################################################################
}


?>