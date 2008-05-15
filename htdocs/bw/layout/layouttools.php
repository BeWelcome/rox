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



function DisplayFlag($ShortLang,$png,$title)
{
	$langurl = $_SERVER['PHP_SELF'] . "?";
	if ($_SERVER['QUERY_STRING'] != "") {
		$QS = explode('&', $_SERVER['QUERY_STRING']);
		for ($ii = 0; $ii < count($QS); $ii++) {
			if (strpos($QS[$ii], "lang=") === false)
				$langurl = $langurl . $QS[$ii] . "&";
		}
	}
	
	if ($_SESSION['lang'] == $ShortLang)
		echo "      <span><a href=\"", $langurl, "lang=",$ShortLang,"\"><img src=\"".bwlink("images/flags/".$png)."\" alt=\"",$title,"\" title=\"",$title,"\"></img></a></span>\n";
	else
		echo "      <a href=\"", $langurl, "lang=",$ShortLang,"\"><img src=\"".bwlink("images/flags/".$png)."\" alt=\"",$title,"\" title=\"",$title,"\"></img></a>\n";
} // end of DisplayFlag

//------------------------------------------------------------------------------
// bwlink converts a relative link to an absolute link
// It works from subdirectories too. Result is always relative
// to the root directory of the site. Works in local environment too.  
// e.g. "" -> "http://www.bewelcome.org/"
//      "layout/a.php" -> "http://www.bewelcome.org/layout/a.php"
define('USE_TBRoot_DEFAULT', class_exists('PVars'));
function bwlink( $target, $useTBroot = USE_TBRoot_DEFAULT)
{
	global $_SYSHCVOL;
	
	if (strlen($target) > 8)
	{
		if (substr_compare($target,"https://",0,8)==0 || 
		    substr_compare($target,"http://",0,7)==0)
			return $target;
	}
	
	if ( $useTBroot )
		$a = PVars::getObj('env')->baseuri . $target;
	else
		$a = "http://".$_SYSHCVOL['SiteName'].$_SYSHCVOL['MainDir'].$target;
	
	return $a;
}

?>