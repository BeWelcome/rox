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
if(isset($vars['queries']) and $vars['queries']) {
    if(PVars::get()->debug) {
        $R = MOD_right::get();
        if($R->HasRight("Debug","DB_QUERY")) {
            $query_list = PVars::get()->query_history;
            foreach($query_list as $key=>$query) {
                echo ($key + 1).": $query<br />\n";
            }
        }
    }
    return;
}

$words = new MOD_words();
$Accomodation = array();
$Accomodation['anytime'] = $words->getBuffered('Accomodation_anytime');
$Accomodation['dependonrequest'] = $words->getBuffered('Accomodation_dependonrequest');
$Accomodation['neverask'] = $words->getBuffered('Accomodation_neverask');
$mapstyle = $_SESSION['SearchMapStyle'];

header('Content-type: text/xml');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<markers>
";
$maxpos = $vars['rCount'];

foreach($TList as $TL) {
	$summary = xml_prep($TL->photo.'<a href="javascript:newWindow(\''.$TL->Username.'\')">'.$TL->Username.'</a><br />'.$TL->CityName.'<br />'.$TL->CountryName.'<br />');
	$detail = xml_prep(ShowMembersAjax($TL, $maxpos, $Accomodation));
	echo "<marker Latitude='$TL->Latitude' Longitude='$TL->Longitude' accomodation='$TL->Accomodation' summary='$summary' detail='$detail'/>
";
}
$curpos = $vars['start_rec'];
$width = $vars['limitcount'];
$string = "<br /><center>" ;
for ($ii=0; $ii<$maxpos; $ii=$ii+$width) {
	$i1=$ii ;
	$i2= min($ii + $width,$maxpos);
	if (($curpos>=$i1) and ($curpos<$i2)) $string .=  "<b>" ;
		$string .= "<a href=\"javascript: page_navigate($i1);\">".($i1+1)."..$i2</a> " ;
	if (($curpos>=$i1) and ($curpos<$i2)) $string .= "</b>" ;
}
$string .= "</center>" ;
if(sizeof($TList) > 0) echo "<header header='".
    xml_prep("<h3>".$words->getFormatted("searchResults")."</h3>").
    xml_prep("<table><tr><th></th><th></th><th>".$words->getFormatted('ProfileSummary')."</th><th>".$words->getFormatted('Host')."</th><th>".$words->getFormatted('LastLogin')."</th><th>".$words->getFormatted('Comments')."</th><th align=\"right\">".$words->getFormatted('Age')."</th></tr>").
    "'/>";
else echo "<header header='".
    xml_prep("NoResults").
    "'/>";
echo "<footer footer='".xml_prep("".$words->flushBuffer())."'/>";

echo "<page page='".xml_prep($string)."'/>";
echo "<num_results num_results='".$maxpos."'/>";
echo "</markers>
";


// Set session variables for use at another time.
$_SESSION['SearchMapStyle'] = $mapstyle;
$_SESSION['SearchMembersVars'] = $vars;
$_SESSION['SearchMembersTList'] = $TList;

function xml_prep($string)
{
	return preg_replace(array("/&/", '/</', '/>/', "/'/"), array("&amp;", '&lt;', '&gt;', "&apos;"), $string);
}

function ShowMembersAjax($TM,$maxpos, $Accomodation) {
	static $ii = 0;

	$info_styles = array(0 => "<tr class=\"blank\" align=\"left\" valign=\"center\">", 1 => "<tr class=\"highlight\" align=\"left\" valign=\"center\">");
	$string = $info_styles[($ii++%2)]; // this display the <tr>
	$string .= "<td class=\"memberlist\">" ;
	if (($TM->photo != "") and ($TM->photo != "NULL")) $string .= $TM->photo;
	$string .= "</td>" ;
	$string .= "<td class=\"memberlist\" valign=\"top\">" ;
	$string .= '<a href="javascript:newWindow(\''.$TM->Username.'\')">'.$TM->Username.'</a>';
	$string .= "<br />".$TM->CountryName;
	$string .= "<br />".$TM->CityName;
	$string .= "</td>" ;
	$string .= "<td class=\"memberlist\" valign=\"top\">" ;
	$string .= $TM->ProfileSummary ;
	$string .= "</td>";
	$string .= "<td class=\"memberlist\" align=\"center\">" ;
	$string .= ShowAccomodation($TM->Accomodation, $Accomodation);
	$string .= "</td>" ;
	$string .= "<td class=\"memberlist\">" ;
	$string .= $TM->LastLogin ;
	$string .= "</td>" ;
	$string .= "<td class=\"memberlist\" align=center>" ;
	$string .= $TM->NbComment ;
	$string .= "</td>" ;
	$string .= "<td class=\"memberlist\" align=\"right\">" ;
	$string .= $TM->Age ;
	$string .= "</td>" ;
	$string .="</tr>" ;

    
	return $string;
}
function ShowMembersAjaxShort($TM,$maxpos, $Accomodation) {
	static $ii = 0;

	$info_styles = array(0 => "<div class=\"blank floatbox\" align=\"left\" valign=\"center\">", 1 => "<div class=\"highlight floatbox\" align=\"left\" valign=\"center\">");
	$string = $info_styles[($ii++%2)]; // this display the <tr>
	$string .= "<table><tr><td class=\"memberlist\">" ;
	if (($TM->photo != "") and ($TM->photo != "NULL")) $string .= $TM->photo;
	$string .= "</td>" ;
	$string .= "<td class=\"memberlist\" valign=\"top\">" ;
	$string .= ShowAccomodation($TM->Accomodation, $Accomodation);
	$string .= '<p><a href="javascript:newWindow(\''.$TM->Username.'\')">'.$TM->Username.'</a><br />';
	$string .= "<span class=\"small\">".$TM->CityName.", ".$TM->CountryName;
	$string .= "</span></td></tr></table>" ;
	$string .="</div>" ;

    
	return $string;
}

function ShowAccomodation($accom, $Accomodation)
{
    if ($accom == "anytime")
       return "<img src=\"images/misc/yesicanhost_small.gif\"  title=\"".$Accomodation['anytime']."\" width=\"20\" height=\"20\" alt=\"yesicanhost\" class=\"float_right\"/>";
    if ($accom == "dependonrequest")
       return "<img src=\"images/misc/dependonrequest_small.gif\" title=\"".$Accomodation['dependonrequest']."\" width=\"20\" height=\"20\" alt=\"dependonrequest\"  class=\"float_right\" />";
    if ($accom == "neverask")
       return "<img src=\"images/misc/neverask_small.gif\" title=\"".$Accomodation['neverask']."\" width=\"20\" height=\"20\" alt=\"neverask\"  class=\"float_right\" />";
}

?>
