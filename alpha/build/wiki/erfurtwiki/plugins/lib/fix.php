<?php
/*
   Outdated and bogus PHP settings (register_globals and magic_quotes) are
   defended by this script, so code cannot be negatively impacted. It can
   always be loaded as it doesn't cause problems or speed disadvantages on
   correctly configured servers. THE "PHP.INI" SHOULD BE FIXED PREFERABLY.
*/

 #-- strike register_globals (injected variables)
 if (ini_get("register_globals") == "1") {
    ewiki_recursive_unset($GLOBALS, $_REQUEST);
    ini_set("register_globals", 0);
 }

 #-- strip any \'s if magic_quotes (variable garbaging) is still enabled
 if (ini_get("magic_quotes_gpc") && get_magic_quotes_gpc()) {
    ewiki_recursive_stripslashes($_REQUEST);
    ewiki_recursive_stripslashes($_GET);
    ewiki_recursive_stripslashes($_POST);
    ewiki_recursive_stripslashes($_COOKIE);
    ewiki_recursive_stripslashes($_ENV);
    ewiki_recursive_stripslashes($_SERVER);
    ini_set("magic_quotes_gpc", 0);
 }

 #-- now that one is really dumb
 set_magic_quotes_runtime(0);


 #-- implementation
 function ewiki_recursive_unset(&$TO, $FROM) {
    foreach ($FROM as $var=>$value) {
       if (isset($TO[$var]) && ($TO[$var]==$FROM[$var])) {
          unset($TO[$var]);
       }
    }
 }
 function ewiki_recursive_stripslashes(&$var) {
    if (is_array($var)) {
       foreach ($var as $key=>$item) {
          ewiki_recursive_stripslashes($var[$key]);
       }
    }
    else {
       $var = stripslashes($var);
    }
 }

?>