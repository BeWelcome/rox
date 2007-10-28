<?php
/*
   This plugin allows to use an existing SQL database table with usernames
   and passwords for authentication purposes. Works with MySQL or the
   anydb backend.

   Prior usage you must configure, which database table and field names
   to retrieve the user information from. Additionally it is possible to
   substitute the "$ewiki_ring" level (= admin or moderator privileges)
   from there, if a column exists with that purpose.

   You also need:
   + EWIKI_PROTECTED_MODE=1
   + one auth_perm_* plugin
   + one auth_method_* plugin
*/



$ewiki_plugins["auth_userdb"] = "ewiki_auth_userdb_sql";


function ewiki_auth_userdb_sql($username) {

   #-- function names (don't change)
   $sql_query = "mysql_query";
   $sql_fetch = "mysql_fetch_array";
#   $sql_query = "anydb_query";
#   $sql_fetch = "anydb_fetch_array";

   #-- set your CMS' user table and column names here
   $TABLE = "users";
   $COL_USER = "name";
   $COL_PW = "password";
   #-- you can OPTIONALLY map privilege level strings to ewiki ring level ints:
   $COL_PRIV = "privilege";
   $MAP_PRIV = array("guest"=>3, "user"=>2, "moderator"=>1, "admin"=>0);

   #-- examples for some common CMS and dynamic web site systems
   # PostNuke
  //$TABLE="nuke_users";  $COL_USER="pn_name";  $COL_PW="pn_pass";
  //$COL_PRIV="";
   # pSlash
  //$TABLE="ps_users";  $COL_USER="uname";  $COL_PW="pass";
  //$COL_PRIV="status";  $MAP_PRIV("member"=>2, "Admin"=>0);
   # coWiki
  //$TABLE="cowiki_user";  $COL_USER="name";  $COL_PW="passwd";
  //$COL_PRIV="";
   # e107
  //$TABLE="user";  $COL_USER="user_name";  $COL_PW="user_password";
  //$COL_PRIV="user_admin";  $MAP_PRIV(0=>2, 1=>0);
   # Geeklog
  //$TABLE="gl_user";  $COL_USER="username";  $COL_PW="passwd";
  //$COL_PRIV="";


   #-- proceed
   $ret = array();
   $username = addslashes($username);  # (not really necessary here anymore)
   
   #-- search username and password
   if (($result = $sql_query("select $COL_PW from $TABLE where $COL_USER='$username'")) && ($row = $sql_fetch($result))) {

      #-- return values
      $ret[0] = $row[$COL_PW];
      $ret[1] = 2;  // default ring level

      #-- fetch privilege level, if there is a table row for it
      if ($COL_PRIV && ($result = $sql_query("select $COL_PRIV from $TABLE where $COL_USER='$username'")) && ($row = $sql_fetch($result))) {
         $i = $row[$COL_PRIV];
         #-- map priviliege names/values to ewikis' ring level integers
         if (isset($i) && isset($MAP_PRIV[$i])) {
            $ret[1] = $MAP_PRIV[$i];
         }
      }
   }

   #-- done
   return($ret);
}


?>