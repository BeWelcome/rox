<?php

require_once("tbinit.php");
require_once("FunctionsTools.php");
require_once("session.php");
require_once("bwdb.php");
require_once("lang.php");

if (file_exists(dirname(__FILE__) . '/' . "config.php"))
	require_once ("config.php");
else
	bw_error("Setup first! In /htdocs/bw/lib/: copy config.php.dist to config.php and edit it.");
	
function init() {
	global $MayBeDuplicate;

	if (get_magic_quotes_gpc ())
		bw_error("The software is not meant to work with PHP magic quotes gpc ON, Please turn it off.");
	
	$phpexts = get_loaded_extensions();
	if (!in_array("gd",$phpexts))
		bw_error("Install GD module in PHP before going on.");
	
	$apacheexts = apache_get_modules();
	if (!in_array("mod_rewrite",$apacheexts))
		bw_error("Install mod_rewrite module in apache before going on.");
	
	if (version_compare(phpversion(), "5.0.0")<0)
		bw_error("PHP version is lower than 5.0.0. Please update. ");

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
