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
<content>
";
$maxpos = $vars['rCount'];
$maxLoggedIn = $vars['rCountFull'];

// Check wether there is a specific list type set or not
if ($mapstyle == 'mapon')
{
    $ShowMemberFunction = 'ShowMembersAjaxShort';
}
else
{
    $ShowMemberFunction = 'ShowMembersAjax';
}
$ii = 0;
$curpos = $vars['start_rec'];
$width = $vars['limitcount'];
foreach($TList as $TL) {
    $ii++;
    $Nr = $ii;

    $accomodationIcon = ShowAccomodation($TL->Accomodation, $Accomodation);
    $profileSummary = nl2br($TL->ProfileSummary);

    $string = <<<HTML
    <div class="avatar">
        <a href="members/{$TL->Username}" target="_blank"><img width="50" height="50" src="members/avatar/{$TL->Username}?xs"/></a>
    </div>
    <div class="username">
        <a href="members/{$TL->Username}" target="_blank"><b>{$TL->Username}</b></a>
    </div>
    <div class="details">
        {$words->getFormatted('YearsOld',$TL->Age)}, {$words->getFormatted('from')} {$TL->CityName}, {$TL->CountryName}
    </div>
    <div class="summary">
        {$profileSummary}
    </div>
    <div class="zoom-buttons">
        <a class="button" href="javascript: geosearchMapBuilder.zoomIn($TL->Latitude, $TL->Longitude);">Zoom In</a>
        <a class="button" href="javascript: geosearchMapBuilder.zoomOut(-4);">Zoom Out</a>
    </div>
HTML;
    $summary = htmlspecialchars($string, ENT_QUOTES);
    $string = '';
    $detail = htmlspecialchars($ShowMemberFunction($TL, $maxpos, $Accomodation,$Nr), ENT_QUOTES);

    echo "<marker username='$TL->Username' Latitude='$TL->Latitude' Longitude='$TL->Longitude' accomodation='$TL->Accomodation' summary='$summary' detail='$detail' abbr='$Nr' />
";
}
/* pagination should NOT be inside the results sent back
   as they won't be visible until scrolled to
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
*/
$pagination = $pagination_attr = '';
if (count($TList))
{
    $start = isset($vars['start_rec']) ? $vars['start_rec'] : 0;
    $params->strategy = new HalfPagePager('left');
    $params->items_per_page = $vars['limitcount'];
    $params->items = $maxpos;
    $params->active_page = floor($start / $vars['limitcount']) + 1;
    $pager = new PagerWidget($params);
    $pagination = str_replace('&nbsp;', ' ', $pager->getHtml());
    $pagination_attr = htmlspecialchars($pager->getHtml(), ENT_QUOTES);
}
echo <<<XML
<pager per_page='{$vars['limitcount']}' paging='{$pagination_attr}'>{$pagination}</pager>
XML;
if ($ShowMemberFunction == 'ShowMembersAjaxShort')
{
    echo "<header header='".
    "'/>";
}
else
{
    if(sizeof($TList) > 0) echo "<header header='".
        htmlspecialchars("<table class=\"full\">
            <tr>
                <th colspan=\"2\">".$words->getFormatted('Member')."</th>
                <th>".$words->getFormatted('ProfileSummary')."</th>
                <th>".$words->getFormatted('Host')."</th>
                <th>".$words->getFormatted('MemberSince')."</th>
                <th>".$words->getFormatted('LastLogin')."</th>
                <th>".$words->getFormatted('Comments')."</th>
                <th align=\"right\">".$words->getFormatted('Age')."<br />" . $words->getFormatted('Gender') . "</th>
            </tr>", ENT_QUOTES).
        "'/>";
    else echo "<header header='".
        htmlspecialchars($words->getFormatted("searchmembersNoSearchResults"), ENT_QUOTES).
        "'/>";
}
echo "<footer footer='".htmlspecialchars("".$words->flushBuffer() ."</table>" , ENT_QUOTES)."'/>";
echo "<num_results num_results='".$maxpos."' num_all_results='".$maxLoggedIn."'/>";
echo "</content>
";


// Set session variables for use at another time.
$_SESSION['SearchMapStyle'] = $mapstyle;
$_SESSION['SearchMembersVars'] = $vars;
$_SESSION['SearchMembersTList'] = $TList;

function ShowMembersAjax($TM,$maxpos, $Accomodation) {
    static $ii = 0;
    $layoutbits = new MOD_layoutbits();

    $info_styles = array(0 => "<tr class=\"blank\" align=\"left\" valign=\"center\">", 1 => "<tr class=\"highlight\" align=\"left\" valign=\"center\">");
    $string = $info_styles[($ii++%2)]; // this display the <tr>
    $string .= "<td class=\"memberlist\">" ;
    $string .= "<img src=\"members/avatar/".$TM->Username."?xs\" class=\"framed\">";
    $string .= "</td>" ;
    $string .= "<td class=\"memberlist\" valign=\"top\">" ;
    $string .= '<a href="members/'.$TM->Username.'" target="_blank">'.$TM->Username.'</a>';
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
    $string .= date('d M y', strtotime($TM->created));
    $string .= "</td>" ;
    $string .= "<td class=\"memberlist\">" ;
    $string .= $TM->LastLogin == '0000-00-00' ? 'Never' : $layoutbits->ago(strtotime($TM->LastLogin));
    $string .= "</td>" ;
    $string .= "<td class=\"memberlist\" align=center>" ;
    $string .= $TM->NbComment ;
    $string .= "</td>" ;
    $string .= "<td class=\"memberlist\" align=\"right\">" ;
    $string .= $TM->Age . '<br />' . $layoutbits->getGenderTranslated($TM->Gender, $TM->HideGender, false);
    $string .= "</td>" ;
    $string .="</tr>" ;


    return $string;
}
function ShowMembersAjaxShort($TM,$maxpos, $Accomodation,$Nr) {
    static $ii = 0;
    $words = new MOD_words();
    $layoutbits = new MOD_layoutbits();
    $memberProfileLink = "members/".$TM->Username;

    $ago = ($TM->LastLogin == 0) ? $layoutbits->ago($TM->LastLogin) : $layoutbits->ago(strtotime(implode('/',explode('-',$TM->LastLogin))));
    if ($TM->Accomodation == '') $TM->Accomodation = 'dependonrequest';
    $info_styles = array(0 => "<div class=\"blank \" align=\"left\" valign=\"center\">", 1 => "<div class=\"highlight \" align=\"left\" valign=\"center\">");
    $string = $info_styles[($ii++%2)]; // this display the <tr>
    $string .= "<table id=\"memberDetail".$Nr."\" class=\"profileLinkArea full\"";
    // highlight marker on member list mouse over: $string .= " onmouseover=\"mapBuilder.highlightMarker(".$Nr.");\" onmouseout=\"mapBuilder.unhighlightMarker(".$Nr.");\"";
    $string .= " ><tr><td valign=\"top\" class=\"memberlist\">" ;
    $string .= "<a class=\"profileLink\" href=".$memberProfileLink." target=\"_blank\">";
    $string .= "<img src=\"members/avatar/".$TM->Username."?xs\" class=\"framed\">";
    $string .= "</a>";
    $string .= "</td>" ;
    $string .= "<td class=\"memberlist\" valign=\"top\">" ;
    $string .= '<p><a href="members/'.$TM->Username.'" target="_blank"><b>'.$TM->Username.'</b></a><br />';
    $string .= "<span class=\"small\">". $words->getFormatted('YearsOld',$TM->Age).", ";
    $strGender = $layoutbits->getGenderTranslated($TM->Gender, $TM->HideGender, false);
    if (!empty($strGender)) {
       $string .= $strGender . ", ";
    }
    $string .= $TM->CityName.", ".$TM->CountryName. "<br />";
    $string .= $words->getFormatted('LastLogin').": <span title=".$TM->LastLogin."><strong>".$ago."</strong></span><br />";
    $string .= $words->getFormatted('MemberSince').": <span title=".$TM->created."><strong>".date('d M y', strtotime($TM->created))."</strong><br />";
    $string .= $words->getFormatted('Comments').": <span title=".$TM->NbComment."><strong>".$TM->NbComment."</strong><br />";
    $string .= "</span></td><td align=\"right\" class=\"accommodation\">";
    $string .= "<div class=\"markerLabelList ".$TM->Accomodation."\"><a href=\"javascript:geosearchMapBuilder.openMarker(".$Nr.");\" title=\"".$words->getBuffered('Accomodation').": ".$Accomodation[$TM->Accomodation]."\">".$Nr."</a></div>";
    $string .= "</td></tr></table>" ;
    $string .="</div>" ;

    return $string;
}

// Not needed anymore...
function ShowAccomodation($accom, $Accomodation)
{
    if ($accom == "anytime")
       return "<img src=\"images/icons/gicon1_a.png\" title=\"".$Accomodation['anytime']."\"  alt=\"yesicanhost\" />";
    if (($accom == "dependonrequest") || ($accom == ""))
       return "<img src=\"images/icons/gicon2_a.png\" title=\"".$Accomodation['dependonrequest']."\"  alt=\"dependonrequest\"   />";
    if ($accom == "neverask")
       return "<img src=\"images/icons/gicon3_a.png\" title=\"".$Accomodation['neverask']."\"  alt=\"neverask\" />";
}
