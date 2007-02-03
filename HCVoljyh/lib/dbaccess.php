<?php

require_once ("HCVol_Config.php");

if (empty($_SYSHCVOL['MYSQLUsername'])||
	empty($_SYSHCVOL['MYSQLDB'])||
	empty($_SYSHCVOL['MYSQLServer'])||
	empty($_SYSHCVOL['MYSQLPassword']))
	{
		die("Setup database connection first!");
	}

$db = mysql_connect($_SYSHCVOL['MYSQLServer'], $_SYSHCVOL['MYSQLUsername'], $_SYSHCVOL['MYSQLPassword']) or die("localhost bad connection with dbname=" . $dbname . " and mysqlusername=" . $mysqlusername . " " . mysql_error()); // remote on old server

if (!$db) {
	$str = "bad mysql_connect " . mysql_error();
	error_log($str . $_SYSHCVOL['MYSQLServer']);
	die($str);
}

if (!mysql_select_db($_SYSHCVOL['MYSQLDB'], $db)) {
	$str = "bad mysql_select_db " . mysql_error();
	error_log($str . " select db ${_SYSHCVOL['MYSQLDB']}");
	die($str);
}

require_once ("FunctionsTools.php");
require_once ("session.php");
EvaluateMyEvents(); // evaluate the events (messages received, keep uptodate whoisonline ...)
?>
