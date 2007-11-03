<?php

/*
   put this onto your "BannedLinks" page:
   <?plugin HoneyPot ?>
*/

$ewiki_plugins["mpi"]["honeypot"] = "ewiki_mpi_honeypot";

function ewiki_mpi_honeypot($action, $args, &$iii, &$s) {

   global $ewiki_data, $ewiki_config;

   $ewiki_config["@"] = 1;
   $ewiki_config["feedbots_badguys"] = "@" . str_replace("\n", ",@", trim($ewiki_data["refs"]));
   return ewiki_email_protect_feedbots(20);
}


?>