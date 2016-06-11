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
require_once "lib/FunctionsLogin.php";
require_once "layout/error.php";
require_once "layout/admingrep.php";

$action = GetParam("action");

$countmatch = 0;

$RightLevel = HasRight('Grep'); // Check the rights
if ($RightLevel < 1) {
	echo "This requires the sufficient <strong>Grep</strong> rights<br />";
	exit (0);
}

$scope = RightScope('Grep');

if ($nbligne == "")
	$nbligne = "3";
if (isset ($_POST['repertoire']))
	$repertoire = $this->_session->get('repertoire');
if ($RightLevel >= 5) { // rigcht level 5 allow to overwrite scope
	if (GetParam("scope") != "")
		$scope = GetParam("scope");
}
if (GetParam("s1") != "")
	$s1 = GetParam("s1");
if (GetParam("s2") != "")
	$s2 = GetParam("s2");
if (GetParam("stringnot") != "")
	$stringnot = GetParam("stringnot");

$previousres = ""; // will receive the result if any

switch ($action) {
	case "logout" :
		Logout();
		exit (0);
	case "grep" :
		$ext = $scope;

		$arrext = explode(";", $scope);
		foreach ($arrext as $ext) {
			$previousres .= "<tr><td><br /><br /><hr />scoping in  <b>$ext</b></td>";
			foreach (glob($repertoire . $ext) as $filename) {
				$previousres .= analyse($filename, stripslashes($s1), $nbligne, stripslashes($s2), stripslashes($stringnot));
			}
		}
		$this->_session->set( directory, $repertoire )
		break;
}

DisplayGrepForm($s1, $s2, $stringnot, $scope, $RightLevel, $previousres); // call the layout

//------------------------------------------------------------------------------	
// Analyse function
function analyse($fname, $searchstr, $nbligne, $searchstr2, $searchnot) {
	$res = "";
	//  echo "analyse $fname for $searchstr<br />";
	if (is_dir($fname)) {
		//    $res.="<tr><td>digging in dir <b>$fname</b></td>";
		$lines = @ file($fname);
		$count = count($lines);
		if (($count == 0) or (!isset ($line))) {
			//		  $res.="<tr><td>pb with file ".$fname."</td>";
		}
		for ($ii = 0; $ii < $count; $ii++) {
			$res .= analyse($line[$ii], $searchstr, $nbligne, $searchstr2, $searchnot);
		}
	} else {
		$res .= showfile($fname, $searchstr, $nbligne, $searchstr2, $searchnot);
	}
	return ($res);
} // 
?>