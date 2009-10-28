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

// Check wether there is a specific list type set or not
if ($mapstyle == 'mapoff') {
$ShowMemberFunction = 'ShowMembersAjax';
} elseif ($mapstyle == 'mapon') {
$ShowMemberFunction = 'ShowMembersAjaxShort';
} else {
$ShowMemberFunction = 'ShowMembersAjax';
}
$ii = 0;
$curpos = $vars['start_rec'];
$width = $vars['limitcount'];
foreach($TList as $TL) {
    $ii++;
    $Nr = $ii;
    $string = '';
	$string .= "<table style=\"width: 200px\"><tr><td class=\"memberlist\">" ;
	$string .= "<img src=\"members/avatar/".$TL->Username."?xs\" >";
	$string .= "</td>" ;
	$string .= "<td class=\"memberlist\" valign=\"top\">" ;
	$string .= '<p><a href="members/\''.$TL->Username.'\'" target="_blank"><b>'.$TL->Username.'</b></a><br />';
	$string .= "<span class=\"small\">". $words->getFormatted('YearsOld',$TL->Age).", ". $words->getFormatted('from')." ".$TL->CityName.", ".$TL->CountryName."<br>".$TL->ProfileSummary;
	$string .= "</span><br /><a class=\"button\" href=\"javascript: map.setZoom((map.getZoom())+4);\">Zoom In</a> <a class=\"button\" href=\"javascript: map.setZoom((map.getZoom())-4);\">Zoom Out</a></td></tr></table>" ;
    $summary = xml_prep($string);
    $string = '';
	$detail = xml_prep($ShowMemberFunction($TL, $maxpos, $Accomodation,$Nr));
    
	echo "<marker Latitude='$TL->Latitude' Longitude='$TL->Longitude' accomodation='$TL->Accomodation' summary='$summary' detail='$detail' abbr='$Nr' />
";
}

$rr=0;
$string = "<div class='pages center'><ul style='float:none'>" ;
for ($ii=0; $ii<$maxpos; $ii=$ii+$width) {
    $rr++;
	$i1=$ii ;
	$i2= min($ii + $width,$maxpos);
	$add = (($curpos>=$i1) and ($curpos<$i2)) ? 'current' : '';
    $string .= "<li class=\"$add\"><a href=\"javascript: page_navigate($i1);\" class=\"off\">".$rr."</a></li> " ;
}
$string .= "</ul></div>" ;
if ($ShowMemberFunction == 'ShowMembersAjaxShort') {
    echo "<header header='".
    xml_prep('').
    "'/>";
} else {        
    if(sizeof($TList) > 0) echo "<header header='".
        xml_prep("<h2>".$words->getFormatted("searchResults")."</h2>").
        xml_prep("<table  style=\"width: 100%\"><tr><th>".$words->getFormatted('Member')."</th><th></th><th>".$words->getFormatted('ProfileSummary')."</th><th>".$words->getFormatted('Host')."</th><th>".$words->getFormatted('LastLogin')."</th><th>".$words->getFormatted('Comments')."</th><th align=\"right\">".$words->getFormatted('Age')."</th></tr>").
        "'/>";
    else echo "<header header='".
        xml_prep($words->getFormatted("searchmembersNoSearchResults")).
        "'/>";
}
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
	$string .= "<img src=\"members/avatar/".$TM->Username."?xs\" class=\"framed\">";
	$string .= "</td>" ;
	$string .= "<td class=\"memberlist\" valign=\"top\">" ;
	$string .= '<a href="members/\''.$TM->Username.'\'" target="_blank">'.$TM->Username.'</a>';
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
function ShowMembersAjaxShort($TM,$maxpos, $Accomodation,$Nr) {
	static $ii = 0;
    $words = new MOD_words();
    $layoutbits = new MOD_layoutbits();
    
    $ago = ($TM->LastLogin == 0) ? $layoutbits->ago($TM->LastLogin) : $layoutbits->ago(strtotime(implode('/',explode('-',$TM->LastLogin))));
    if ($TM->Accomodation == '') $TM->Accomodation = 'dependonrequest';
	$info_styles = array(0 => "<div class=\"blank \" align=\"left\" valign=\"center\">", 1 => "<div class=\"highlight \" align=\"left\" valign=\"center\">");
	$string = $info_styles[($ii++%2)]; // this display the <tr>
	$string .= "<table style='width:100%'><tr><td class=\"memberlist\">" ;
	$string .= "<img src=\"members/avatar/".$TM->Username."?xs\" class=\"framed\">";
	$string .= "</td>" ;
	$string .= "<td class=\"memberlist\" valign=\"top\">" ;
	$string .= '<p><a href="members/\''.$TM->Username.'\'" target="_blank"><b>'.$TM->Username.'</b></a><br />';
	$string .= "<span class=\"small\">". $words->getFormatted('YearsOld',$TM->Age).", ". $words->getFormatted('from')." ".$TM->CityName.", ".$TM->CountryName."<br /> ". $words->getFormatted('LastLogin').": <span title=".$TM->LastLogin."><strong>".$ago."</strong></span>";
	$string .= "</span></td><td>";
    $string .= "<div class=\"markerLabelList ".$TM->Accomodation."\"><a href=\"javascript:GEvent.trigger(gmarkers[".$Nr."], 'click');\" title=\"".$words->getBuffered('Accomodation').": ".$Accomodation[$TM->Accomodation]."\">".$Nr."</a></div>";
    $string .= "<span class=\"small\">".$Accomodation[$TM->Accomodation]."</span>";
    $string .= "</td></tr></table>" ;
	$string .="</div>" ;

    
	return $string;
}

// Not needed anymore...
function ShowAccomodation($accom, $Accomodation)
{
    if ($accom == "anytime")
       return "<img src=\"images/icons/gicon1.png\" title=\"".$Accomodation['anytime']."\"  alt=\"yesicanhost\" />";
    if (($accom == "dependonrequest") || ($accom == ""))
       return "<img src=\"images/icons/gicon3.png\" title=\"".$Accomodation['dependonrequest']."\"  alt=\"dependonrequest\"   />";
    if ($accom == "neverask")
       return "<img src=\"images/icons/gicon2.png\" title=\"".$Accomodation['neverask']."\"  alt=\"neverask\" />";
}

?>
