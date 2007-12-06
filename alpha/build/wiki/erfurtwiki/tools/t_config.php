<?php
  # this is the configuration for the ewiki page and database tools
  #     (which may need to be distinct from your main ewiki config)
  #


  #-- injects password into fragments/funcs/auth.php
  $passwords = array(
//     "admin" => "...",
  );



  #-- tools/ are run standalone?
  if (!function_exists("ewiki_database")) {


     #-- enable readonly:test play account?
     $test = ($_SERVER["SERVER_NAME"] == "erfurtwiki.sourceforge.net")
          || ($_SERVER["REMOTE_ADDR"] == "127.0.0.1") && (!strpos($_SERVER["SERVER_NAME"], "."));
     if ($test && !$_REQUEST["test"]) {
        $passwords["readonly"] = "test";
     }


     #-- simplest authentication:
     require("../fragments/funcs/auth.php");
     
     if ($_a_user == "readonly") { 
        define("EWIKI_DB_LOCK", 1);  // disable write-access
     }


     #-- normalize cwd (stupid approach)
     if (!file_exists($LIB="ewiki.php")) {
        chdir("..");
        define("EWIKI_SCRIPT", "../?");
        define("EWIKI_SCRIPT_BINARY", "../?binary=");
     }


     #-- open db connection, load 'lib'
     include("./config.php");


     #-- PHP fixes
     include("plugins/lib/fix.php");
     include("plugins/lib/upgrade.php");

  }


  #-- we now seem to run from inside ewiki (via the StaticPages plugin e.g.)
  else {

     #-- this terminates ewiki from within the spages plugin
     if (!EWIKI_PROTECTED_MODE || !ewiki_auth($id, $data, $action, 0, 2) || ($ewiki_ring>0) || !isset($ewiki_ring)) {
        die("Only the administrator can use this function.");
     }

     #-- some tools/ must be excluded nevertheless (because they override security-related settings, like t_control.php does)
     define("CONCURRENT_INCLUDE", 1);  // just don't ask for an explaination ;)

  }



?>