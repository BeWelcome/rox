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

require_once("tbinit.php");
require_once("FunctionsTools.php");
require_once("session.php");
require_once("bwdb.php");
require_once("lang.php");

function init() {
	global $MayBeDuplicate;

	if (get_magic_quotes_gpc ())
		bw_error("The software is not meant to work with PHP magic_quotes_gpc = On. Please turn it Off (probably in php.ini).");
	
	$phpexts = get_loaded_extensions();
	if (!in_array("gd",$phpexts))
		bw_error("Install GD module in PHP before going on.");
	
	$apacheexts = apache_get_modules();
	if (!in_array("mod_rewrite",$apacheexts))
		bw_error("Install mod_rewrite module in Apache before going on.");
	
	if (version_compare(phpversion(), "5.0.0")<0)
		bw_error("PHP version is lower than 5.0.0. Please update. ");

	SetupSession();
	DBConnect();

	// a duplicate use by several users has been detected
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

init();

?>
