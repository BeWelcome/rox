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
chdir("..") ;
require_once "lib/init.php";
require_once "layout/header.php";

$lang = $_SESSION['lang']; // save session language
$this->getSession->set( 'lang', CV_def_lang )
$this->getSession->set( 'IdLanguage', 0 ) // force English for menu

$Schema="BW_MAIN" ;
$Schema="bewelcome" ;
echo "<H2 align=left>$Schema Schema</H2>";

echo "<table align=left><tr><td align=left>" ;
$s1 = "select TABLE_NAME,TABLE_COMMENT from information_schema.TABLES where TABLE_SCHEMA='".$Schema."' order by TABLE_NAME";
//$s1 = "select TABLE_NAME,TABLE_COMMENT from information_schema.TABLES where TABLE_SCHEMA='hcvoltest' order by TABLE_NAME";
echo "<br><br><br><br><br><br><br>\n";

$qry1 = sql_query($s1);
while ($r1 = mysql_fetch_object($qry1)) {
	echo "=== ", $r1->TABLE_NAME, " ===<br />\n ''", $r1->TABLE_COMMENT, "''<br />\n";
	$s2 = "select COLUMN_NAME,COLUMN_COMMENT,DATA_TYPE from information_schema.COLUMNS where COLUMNS.TABLE_NAME='" . $r1->TABLE_NAME . "' and TABLE_SCHEMA='".$Schema."'";
	$qry2 = sql_query($s2);

	while ($r2 = mysql_fetch_object($qry2)) {
		echo "&nbsp;'''", $r2->COLUMN_NAME, "''' ", $r2->DATA_TYPE, " ''", $r2->COLUMN_COMMENT, "''<br /><br />\n";
	}

	echo "<br />";
}
echo "</td></tr></table>" ;
require_once "../layout/footer.php";
?>
