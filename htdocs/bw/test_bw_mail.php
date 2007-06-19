<?php

//Load in the files we'll need
require_once "swift/Swift.php";
require_once "swift/Swift/Connection/NativeMail.php";

//CZ_070619: Testing the bw_mail function a bit

require_once "lib/init.php";
require_once "lib/FunctionsMessages.php";
require_once "layout/error.php";

$to = "lemon@east.de";
$replyto = "replytest@bewelcome.org";
$from = "bwtest@bewelcome.org";
$subject = "Ein Ümläüt Tést
mit Umbruch";
$text = " Etwas Txt hier, auch mit Ümläüten,
und auch
ein paar
Zeilenumbrüchen.";


	//Start Swift with php's mail()
	$swift =& new Swift(new Swift_Connection_NativeMail());
	 
	 //Create a message
	$message =& new Swift_Message($subject,$text);

	 
	//Now check if Swift actually sends it
	if ($swift->send($message, $to, $from)) echo "Sent";
	else echo "Failed";


/*
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
echo "Mail hopefully sent.".$mailSent;
else 
echo "mail not sent.".$mailSent;
*/