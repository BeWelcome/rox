<?php
/*
 * Created on 5.2.2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

require_once ("FunctionsTools.php");
require_once ("session.php");
require_once ("db.php");
require_once ("lang.php");
 
if (file_exists(dirname(__FILE__).'/'."config.php"))
	require_once ("config.php");
else
	die("setup first! copy config.php.dist to config.php and edit it.");

SetupSession();

if ($_SESSION['testvar'] != 'testtest')
	die("session setup failed!");
else
	unset( $_SESSION['testvar'] );

DBConnect();

global $MayBeDuplicate ;
if ($MayBeDuplicate!="") LogStr($MayBeDuplicate); // a duplicate use bys sevral user has been detected


LanguageChangeTest();
EvaluateMyEvents(); // evaluate the events (messages received, keep uptodate whoisonline ...)
?>
