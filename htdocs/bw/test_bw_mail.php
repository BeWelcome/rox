<?php

//CZ_070619: Testing the bw_mail function a bit

require_once "lib/init.php";
require_once "lib/FunctionsMessages.php";
require_once "layout/error.php";

$to = "lemon@east.de";
$replyto = "replytest@bewelcome.org";
$from = "bwtest@bewelcome.org";
$subject = "Ein �ml��t T�st
mit Umbruch";
$text = " Etwas Txt hier, auch mit �ml��ten,
und auch
ein paar
Zeilenumbr�chen.";

$mailSent = bw_mail($to, 
                 $subject, 
                 $text, 
                 "", 
                 $from, 
                 1, 
                 "yes", 
                 "", 
                 $replyto,
                 "");
if ($mailSent)                 
echo "Mail hopefully sent.";
else echo "mail not sent.";