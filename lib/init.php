<?php
/*
 * Created on 5.2.2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
require_once("FunctionsTools.php");
require_once("session.php");
require_once("db.php");
require_once("lang.php");

if (file_exists(dirname(__FILE__) . '/' . "config.php"))
	require_once ("config.php");
else
	bw_error("setup first! copy config.php.dist to config.php and edit it.");

function init() {
	global $MayBeDuplicate;

	SetupSession();
	DBConnect();

	// a duplicate use by several user has been detected
	if (!empty($MayBeDuplicate))
		LogStr($MayBeDuplicate); 

	LanguageChangeTest();
	
	// evaluate the events (messages received, keep uptodate whoisonline ...)
	EvaluateMyEvents(); 
	
	// Check if the navigation of the user must be logged
	if (HasFlag("RecordLogs")) {
	   $url= $_SERVER['PHP_SELF'];
		if (!empty($_SERVER['QUERY_STRING'])) 
		{
		   $url .="?".$_SERVER['QUERY_STRING'];
		}

	   LogStr("url=".$url,"RecordLogs");
	}
}

define("CV_def_lang","en"); // This is the short code for the default language

init();

?>
