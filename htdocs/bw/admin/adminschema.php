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
require_once "../lib/init.php";
require_once "../layout/header.php";

$lang = $_SESSION['lang']; // save session language
$_SESSION['lang'] = CV_def_lang;
$_SESSION['IdLanguage'] = 0; // force english for menu
echo "<H2>BW_MAIN Schema</H2>";

$s1 = "select TABLE_NAME,TABLE_COMMENT from information_schema.TABLES where TABLE_SCHEMA='BW_MAIN' order by TABLE_NAME";
//$s1 = "select TABLE_NAME,TABLE_COMMENT from information_schema.TABLES where TABLE_SCHEMA='hcvoltest' order by TABLE_NAME";
echo "<br><br><br><br><br><br><br>\n";

$qry1 = sql_query($s1);
while ($r1 = mysql_fetch_object($qry1)) {
	echo "<b>", $r1->TABLE_NAME, "</b> <i>", $r1->TABLE_COMMENT, "</i><br>";
	$s2 = "select COLUMN_NAME,COLUMN_COMMENT,DATA_TYPE from information_schema.COLUMNS where COLUMNS.TABLE_NAME='" . $r1->TABLE_NAME . "' and TABLE_SCHEMA='BW_MAIN'";
	$qry2 = sql_query($s2);
	while ($r2 = mysql_fetch_object($qry2)) {
		echo "&nbsp;&nbsp;&nbsp;<b>", $r2->COLUMN_NAME, "</b> ", $r2->DATA_TYPE, " <i>", $r2->COLUMN_COMMENT, "</i><br>\n";
	}
	echo "<br>";
}

require_once "../layout/footer.php";
?>
