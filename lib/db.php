<?php
/*
 * Created on 5.2.2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
function CheckDBParams()
{
	global $_SYSHCVOL;
	if (empty($_SYSHCVOL['MYSQLUsername'])||
		empty($_SYSHCVOL['MYSQLDB'])||
		empty($_SYSHCVOL['MYSQLServer'])||
		empty($_SYSHCVOL['MYSQLPassword']))
		{
			die("Setup database connection first!");
		}	
}

function DBConnect()
{
	global $_SYSHCVOL;
	CheckDBParams();
	$db = mysql_connect($_SYSHCVOL['MYSQLServer'], $_SYSHCVOL['MYSQLUsername'], $_SYSHCVOL['MYSQLPassword']); 
	
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
	
	mysql_query("SET CHARACTER SET 'utf8'", $db );
}

?>
