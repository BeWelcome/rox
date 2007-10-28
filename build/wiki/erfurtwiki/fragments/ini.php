<?php
/*
   This plugin parses the "ewiki.ini" file and sets ewiki variables
   and constants accordingly, then loads plugins. Such a configuration
   file could be prepared using the SetupWizard.

   Note: PHPs parse_ini_file() is insufficient for our .ini file,
   because it lacks recognizing repeated entry names.
*/


#-- get file
error_reporting(error_reporting() & 0xFFFE);
if ($ini = ewiki_parse_ini_file("ewiki.ini")) {

   #-- init database
   if ($v = $ini["db"]["init"][0]) {
      foreach (split('&&|;', $v) as $v) {
         $i = strtok(preg_replace("/[\"\s\']/", "", $v), "(");
         $v = explode(",", strtok(")"));
         if ($v && $i) {
            call_user_func_array($i, $v); // auto function_exists() check
         }
      }
   }

   #-- set options
   foreach ($ini["config"] as $i=>$v) {
      $v = $v[0];
      if ($i[0] == "\$") {
         $i = preg_replace("/[\s\"\'\$\]]/", "", $i);
         $i = explode("[", $i);
         switch (count($i) + (strlen($i[count($i)-1]) ? 0 : 10)) {
            case 1: $GLOBALS[$i[0]] = $v; break;
            case 2: $GLOBALS[$i[0]][$i[1]] = $v; break;
           case 12: $GLOBALS[$i[0]][] = $v; break;
            case 3: $GLOBALS[$i[0]][$i[1]][$i[2]] = $v; break;
           case 13: $GLOBALS[$i[0]][$i[1]][] = $v; break;
            case 4: $GLOBALS[$i[0]][$i[1]][$i[2]][$i[3]] = $v; break;
           case 14: $GLOBALS[$i[0]][$i[1]][$i[2]][] = $v;
         }
      }
      else {
         @define($i, $v);
      }
   }

   #-- load plugins
   foreach ($ini["plugins"]["load"] as $v) {
      include_once($v);
   }
   $i = $v = $ini = NULL;
}

#-- add core scripts
include_once("ewiki.php");



#-- load and decipher .ini files
function ewiki_parse_ini_file($fn) {
   return ewiki_parse_ini_str(@implode("",file($fn)));
}
function ewiki_parse_ini_str($s)
{
   $r = array();
   $sect = "global";
   foreach (explode("\n", $s) as $line) {
      $line = trim($line);
      if ($line[0] == "[") {
         $sect = trim(strtok(substr($line, 1), "]"));
      }
      elseif (($line[0] == ";") || ($line[0] == "#")) {
      }
      elseif (strpos($line, "=")) {
         $opt = trim(strtolower(strtok($line, "=")));
         $val = trim(strtok("\r\n"));
         $r[$sect][$opt][] = $val;
      }
   }
   return($r);
}


?>