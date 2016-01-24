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



require_once("layouttools.php");

echo "<!DOCTYPE html>\n";
echo "<html " ;
if (isset($_SESSION["lang"])) echo " lang=\"".($_SESSION["lang"])."\"" ;
echo ">\n";
global $_SYSHCVOL;
echo "<head>\n";
if (isset ($title)) {
    echo "  <title>", $title, "</title>\n";
} else {
    echo "\n<title>", $_SYSHCVOL['SiteName'], "</title>\n";
}
echo "  <meta charset=\"utf-8\">\n";
echo "  <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">\n";
echo "  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n";
if (empty($meta_description)) $meta_description=ww("default_meta_description") ;
echo "  <meta name=\"description\" content=\"",$meta_description,"\" />\n" ;
if (empty($meta_keyword)) $meta_keyword=ww("default_meta_keyword") ;
echo "  <meta name=\"keywords\" content=\"",$meta_keyword,"\" />\n" ;
echo "  <meta name=\"ROBOTS\" content=\"INDEX, FOLLOW\" />\n" ;
echo "  <link rel=\"shortcut icon\" href=\"".PVars::getObj("env")->baseuri."favicon.ico\" />\n";

$stylesheet = "minimal"; // this is the default style sheet

// If is logged try to load appropriated style sheet
if (IsLoggedIn()) {
    if (!isset($_SESSION["stylesheet"]))  { // cache in session to avoid a reload at each new page
         $rrstylesheet = LoadRow("select Value from memberspreferences where IdMember=" . $_SESSION['IdMember'] . " and IdPreference=6");
         if (isset ($rrstylesheet->Value)) {
                $_SESSION["stylesheet"]=$stylesheet = $rrstylesheet->Value;
         }
    }
    $stylesheet = "minimal"; // force YAML also for logged member (for now, todo several layout)
}
echo '  <link href="/styles/css/' . $stylesheet. '/minimal.css?3" rel="stylesheet" type="text/css" media="screen" />';
echo '<!--[if lte IE 7]>';
echo '  <link href="/styles/css/' . $stylesheet. '/patches/iehacks_3col_vlines.css" rel="stylesheet" type="text/css" media="screen" />';
echo '<![endif]-->';
echo '<!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->';
echo '<!--[if lt IE 9]>';
echo '<script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>';
echo '<script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>';
echo '<![endif]-->';
echo '</head>';

if (isset($onLoadAction)) {
    echo "<body onload='$onLoadAction'>";
}
else {
    echo "<body>";
}

if ($_SYSHCVOL['SiteStatus'] == 'Closed') {
    echo "<p>", $_SYSHCVOL['SiteCloseMessage'], "</p>\n";
    echo "</body>\n</html>\n";
    exit (0);
}
?>
