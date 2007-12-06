<?php
/*
   If spammers and bots only target a few of your pages, you can use
   this extension to disable most of the antispam plugins for anything
   but a few pages.
   
   List attacked pages on "WatchSpam" or add them to the config array
   here. Can be combined with other trigger or anti-trigger plugins.
*/


#-- config
//$ewiki_config["watchspam"][] = "AttackedPage";
//$ewiki_config["watchspam"][] = "YouSillyBotPleaseEditMe";
//...

$ewiki_plugins["handler"] = "ewiki_trigger_spam_watchonly";
function ewiki_trigger_spam_watchonly($id, &$data, &$action, $pf_i) {

   #-- fetch list of tracked pages
   if ($d = ewiki_db::GET("WatchSpam")) {
      (array)$ewiki_config["watchspam"] += explode("\n", trim($d["refs"]));
   }
   
   #-- disable all bot-blocking plugins?
   if (!ewiki_in_array($id, $ewiki_config["watchspam"])) {
      $GLOBALS["ewiki_no_bot"] = 1;
   }
   elseif ($action == "edit") {
      ewiki_log("someone is {$action}ing specifically watched page '$id'", 2);
   }
}


?>