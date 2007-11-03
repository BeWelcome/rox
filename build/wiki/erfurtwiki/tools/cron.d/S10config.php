<?php
/*
   If not already present, this script part loads your main "config.php"
   and ewiki core script / database interface. It does not make use of the
   tools/t_config, because that would terminate immediately if it wasn't
   run with a correctly set HTTP Basic auth environment.
   
   You can also change any defines/config settings in here, but we
   recommended that you instead create something like "S07yourconfig.php"
   and pack them in there (better when upgrading). Keep the Snn-number
   below 10 so it really is executed before this config script.
   
   Btw, everything will be executed in the ewiki installations base
   directory from here.   
*/


#-- load ewiki config, if it isn't alread present
if (!class_exists("ewiki_db")) {

   #-- we leave the cron.d/ directory
   echo "[$cron]: loading main 'config.php' from ewiki base directory\n";
   chdir("../..");
   include("config.php");
}

#-- that's ok for us, too
else {
   echo "[$cron]: we're appeareantly running as trail/shutdown code on real ewiki\n";
}


#-- pre-define a few settings for the following cron.d/ parts
//
// define("PREPARE_AUTOLINKING", 1);



#-- define start time, if not already done
@define("EWIKI_CRON", time());

?>