<?php

# requires UNIX/Linux and the 'fortune' program (prints jokes)


$ewiki_plugins["page"]["Fortune"] = "ewiki_page_fortune";


function ewiki_page_fortune($id, $data, $action) {

   $LANGUAGE = trim(preg_replace('/[^a-z]*q=[^a-z]*|[^a-z]+/', ':', $_SERVER["HTTP_ACCEPT_LANGUAGE"].$_ENV["LANGUAGE"]." en"), ":");
   $LANG = strtok($LANGUAGE, ":");
   $ENV = "export LANGUAGE=$LANGUAGE ; export LANG=$LANG ;";
   
   $o = "<h2>$id</h2>\n";

   $o .= "<pre>" . shell_exec("$ENV /usr/games/fortune") . "</pre>\n";

   return($o);
}


?>