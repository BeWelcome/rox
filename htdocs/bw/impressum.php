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

require_once "lib/init.php";
require_once "layout/menus.php";

	global $title;
	$title = ww('Impressum');
	require_once "layout/header.php";

	Menu1("", ""); // Displays the top menu

	Menu2("inviteafriend.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent(ww("Impressum")); // Display the header


echo "	<div id=\"col3\">\n";
echo "		<div id=\"col3_content\" class=\"clearfix\" >\n";
echo "			<div class=\"info\">\n";
echo "				<p>Anbieterkennzeichung nach &sect;6 Teledienstgesetz (information provider identification according to &sect;6 Teledienstgesetz) :<br />\n";
echo "				<br />\n";
echo "				BeVolunteer<br />\n";
echo "				c/o Jean-Yves Hegron<br />\n";
echo "				19 rue de Paris<br />\n";
echo "				35500 Vitre<br />\n";
echo "				France<br />\n";
echo "				<br />\n";
echo "				" . ww("SignupEmail") . ": info@bevolunteer.org<br /> </p>\n";
echo "				<br />";
echo "				<p>The layout is based on <a href=\"http://www.yaml.de/\">YAML</a> &copy; 2005-2006 by <a href=\"http://www.highresolution.info\">Dirk Jesse</a></p>";
echo "			</div> <!-- #info: - end -->\n";
echo "		</div> <!-- #col3_content: - end -->\n";
echo "		<div id=\"ie_clearing\">&nbsp;</div>\n";
echo "	</div> ";
echo "<!-- #col3: - End -->\n";

	require_once "layout/footer.php";



?>
