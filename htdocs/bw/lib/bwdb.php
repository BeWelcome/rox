<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or 
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330, 
Boston, MA  02111-1307, USA.

*/

require_once "dbupdate.php";
 
function CheckDBParams()
{
	global $_SYSHCVOL;
	if (empty($_SYSHCVOL['MYSQLUsername'])||
		empty($_SYSHCVOL['MYSQLDB'])||
		empty($_SYSHCVOL['MYSQLServer']))
		{
			print_r( $_SYSHCVOL );
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

// Line to force use of UTF-8
// Natively dabase is sio latin1

	mysql_query("SET NAMES 'utf8'"); 
	mysql_query("SET CHARACTER SET 'utf8'"); 
	mysql_query("SET collation_connection='utf8_general_ci'"); 
    
	global $i_am_the_mailbot;
    if (
        'auto' == PVars::getObj('db')->dbupdate &&
        !(isset($_SYSHCVOL['NODBAUTOUPDATE']) ? $_SYSHCVOL['NODBAUTOUPDATE'] : true) &&
        !(isset($i_am_the_mailbot) ? $i_am_the_mailbot : false)
    ) {
        DBUpdateCheck();
    }
	
	// Adding a time limit
	set_time_limit(15) ; // No page must go longer than this number of seconds
	
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
	if ($this->_session->has( 'sql_query' )&&
		$this->_session->get('sql_query') == "AlreadyIn") {
		//	  die ("<br>recursive sql_query<br>".$ss_sql);
	}
	$this->_session->set( 'sql_query', "AlreadyIn" )
	
	$qry = mysql_query($ss_sql." /* ".$_SERVER["PHP_SELF"]." */");
	
	if ($qry) // No failure
	{
		$this->_session->set( 'sql_query', "" )
		return ($qry);
	}
	$error =  mysql_error();
	
	if ((HasRight("Debug")) or ($_SERVER['SERVER_NAME'] == 'localhost') ) {
		$this->_session->set( 'sql_query', "" );
		bw_error(debug("<br>query problem with<br><font color=red> $ss_sql mysql_error: ". $error. "</font><br>"));
	}
	else {
		error_log(debug("\nquery problem with\n $ss_sql mysql_error: ".$error. "\n"));
		LogStr("Pb with <b>" . $ss_sql . "</b>", "sql_query");
		die("query problem " . $_SERVER['REMOTE_ADDR'] . " " . date("F j, Y, g:i a"));
	}
	
} // end of sql_query

//------------------------------------------------------------------------------
// Just to read one row
//------------------------------------------------------------------------------
function LoadRow($str) {
	//  echo "str=$str<br>";
	$qry = sql_query($str);
	if (!$qry) {
		if ($_SERVER['SERVER_NAME'] == 'localhost') { // LocalHost will display debug message
			echo "<br><font color=red>Warning message for Admin (only)<br>";
			if (!mysql_num_rows())
				debug($_SERVER['PHP_SELF'] . "<br> : LoadRow failed:<br>mysql_error:" . mysql_error() . "<br>query:$str</b>");
			else
				debug($_SERVER['PHP_SELF'] . "<br> : LoadRow failed: No results! <br>query:$str</b>");
			echo "</font>";
		} else {
			error_log("LoadRow error in " . $_SERVER['PHP_SELF'] . "?" . $_SERVER['QUERY_STRING'] . " <br> str=[" . $str . "]<br>");
			//			LogStrTmp("LoadRow(".addslashes($str).") in ".$_SERVER['PHP_SELF'],"Debug"); // No need already done by sql_query
		}
		$row = null;
	} else {
		$row = mysql_fetch_object($qry);
	}
	return ($row);
}

?>
