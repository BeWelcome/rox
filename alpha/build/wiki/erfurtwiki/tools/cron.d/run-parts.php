<?php
/*
   The ewiki/tools/cron.d/ run-parts wrapper looks at all the files in
   its own directory and simply includes() one after another. Scripts
   found herein typically accomplish admistrative tasks that require no
   human observation.
   
   All S** parts are run in ascending order, then Z** and any K** parts
   get run in reverse order. The former is stopped when one of the
   scripts sets the $HALT or $STOP variables. The latter can be prevented
   only with $HALT >= 2.
   There is also a $GOTO variable available, which allows to overstep a
   few scripts by numeric id (the anacron snippets use that).

   Please see the HOWTO on more notes on how to activate this.
*/


#-- read in current directory
$dir = dirname(__FILE__);
$startparts = array();
$killparts = array();
if ($dh = opendir($dir)) {
   while ($fn = readdir($dh)) {
      if (preg_match("/^[SKZ]\d\d.+\.php$/", $fn)) {
         if ($fn[0]=="S") {
            $startparts[] = "$dir/$fn";
         }
         else {
            $killparts[] = "$dir/$fn";
         }
      }
   }
   closedir($dh);
   $dh = NULL;
}

#-- run 'S'tart-scripts
$STOP=$HALT=false;
if ($startparts) {
   asort($startparts);
   foreach ($startparts as $fn) { 

      #-- make script id string from filename
      $cron = strtok(substr($fn, strrpos($fn, "/") + 1), ".");

      #-- overstep a few scripts, if instructed to do so
      $num = substr($cron, 1, 2);
      if ($GOTO && ($num<$GOTO)) {
         continue;
      }

      #-- run current
      include($fn);      

      #-- stop processing
      if ($STOP |= $HALT) {
         break;
      }
   }
}

#-- reverse order for 'K'ill and 'Z'leep -scripts
if ($killparts && ($HALT < 2)) {
   arsort($killparts);
   foreach ($killparts as $fn) { 
      $cron = strtok(substr($fn, strrpos($fn, "/") + 1), ".");
      include($fn);      
   }
}


#-- fin
$startparts=$killparts=$cron=$fn=$STOP=$HALT=NULL;
// something else may follow here

?>