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
			bw_error("Setup database connection first!");
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
		bw_error($str);
	}
	
	if (!mysql_select_db($_SYSHCVOL['MYSQLDB'], $db)) {
		$str = "bad mysql_select_db " . mysql_error();
		error_log($str . " select db ${_SYSHCVOL['MYSQLDB']}");
		bw_error($str);
	}
	
	// mysql_query("SET CHARACTER SET 'utf8'", $db );
}

// 
// sql_get_set returns in an array the possible set values of the colum of table name
function sql_get_set($table, $column) {
	$sql = "SHOW COLUMNS FROM $table LIKE '$column'";
	if (!($ret = sql_query($sql)))
		die("Error: Could not show columns $column");

	$line = mysql_fetch_assoc($ret);
	$set = $line['Type'];
	$set = substr($set, 5, strlen($set) - 7); // Remove "set(" at start and ");" at end
	return preg_split("/','/", $set); // Split into and array
} // end of sql_get_set($table,$column) 

// 
// sql_get_enum returns in an array the possible set values of the colum of table name
function sql_get_enum($table, $column) {
	$sql = "SHOW COLUMNS FROM $table LIKE '$column'";
	if (!($ret = sql_query($sql)))
		die("Error: Could not show columns $column");

	$line = mysql_fetch_assoc($ret);
	$set = $line['Type'];
	$set = substr($set, 6, strlen($set) - 8); // Remove "enum(" at start and ");" at end
	return preg_split("/','/", $set); // Split into and array
} // end of sql_get_enum($table,$column) 

// 
// sql query execute a mysql_query but logs errors if any, and 
// dump on screen if member has right Debug
function sql_query($ss_sql) {
	if ($_SESSION['sql_query'] == "AlreadyIn") {
		//	  die ("<br>recursive sql_query<br>".$ss_sql);
	}
	$_SESSION['sql_query'] = "AlreadyIn";
	
	$qry = mysql_query($ss_sql);
	
	if ($qry) // No failure
	{
		$_SESSION['sql_query'] = "";
		return ($qry);
	}
	$error =  mysql_error();
	
	if ((HasRight("Debug")) or ($_SERVER['SERVER_NAME'] == 'localhost') or (GetParam("debugjy","empty")!="empty")) {
		$_SESSION['sql_query'] = "";
		bw_error(debug("<br>query problem with<br><font color=red> $ss_sql mysql_error: ". $error. "</font><br>"));
	}
	else {
		error_log(debug("\nquery problem with\n $ss_sql mysql_error: ".$error. "\n"));
		LogStr("Pb with <b>" . $ss_sql . "</b>", "sql_query");
		die("query problem " . $_SERVER['REMOTE_ADDR'] . " " . date("F j, Y, g:i a"));
	}
	
} // end of sql_query

?>
