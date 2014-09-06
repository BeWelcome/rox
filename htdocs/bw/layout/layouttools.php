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
		echo "      <span><a href=\"", $langurl, "lang=",$ShortLang,"\"><img src=\"".bwlink("images/flags/".$png)."\" alt=\"",$title,"\" title=\"",$title,"\"/></a></span>\n";
	else
		echo "      <a href=\"", $langurl, "lang=",$ShortLang,"\"><img src=\"".bwlink("images/flags/".$png)."\" alt=\"",$title,"\" title=\"",$title,"\"/></a>\n";
} // end of DisplayFlag

//------------------------------------------------------------------------------
// bwlink converts a relative link to an absolute link
// It works from subdirectories too. Result is always relative
// to the root directory of the site. Works in local environment too.  
// e.g. "" -> "http://www.bewelcome.org/"
//      "layout/a.php" -> "http://www.bewelcome.org/layout/a.php"
function bwlink( $relative_url, $omit_bw = false )
{
    $exploded = explode('/bw/', $relative_url);
    if (isset($exploded[1])) {
        $relative_url = $exploded[1];
    } else if (substr_compare($relative_url.'   ', 'bw/', 0, 3) == 0) {
        $relative_url = substr($relative_url, 3);
    } else if (substr_compare($relative_url.' ', '/', 0, 1) == 0) {
        $relative_url = substr($relative_url, 1);
    } else {
        // do nothing
    }
    
    if (class_exists('PVars')) {
        if (isset($_SERVER['HTTPS'])) {
            $baseuri = PVars::getObj('env')->baseuri_https;  // https://. 'bw/' . $relative_url;
        } else {
            $baseuri = PVars::getObj('env')->baseuri;  // http:// . 'bw/' . $relative_url;
        }
    } else {
        $protocol_exploded = explode('/', $_SERVER['SERVER_PROTOCOL']);
        $baseuri =
            strtolower($protocol_exploded[0]).'://'.
            $_SYSHCVOL['SiteName'].
            $_SYSHCVOL['MainDir']
        ;
        if (substr_compare($baseuri, '/bw/', -4)) {
            $baseuri = substr($baseuri, -4).'/';
        } else if (substr_compare($baseuri, '/bw', -3)) {
            $baseuri = substr($baseuri, -3).'/';
        } else if (substr_compare($baseuri, '/', -1)) {
            // do nothing
        } else {
            $baseuri = $baseuri.'/';
        }
    }
    
    return $baseuri . ($omit_bw ? '' : 'bw/') . $relative_url;
}

?>