<?php
require_once "lib/init.php";
require_once "layout/layouttools.php";
require_once "lib/findpeople.php";

$TList = buildresult();
$maxpos = $rCount->cnt;

header('Content-type: text/xml');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<markers>
";
foreach($TList as $TL) {
	$summary = xml_prep(LinkWithPicture($TL->Username, $TL->photo, 'map_style').'<a href="member.php?cid='.$TL->Username.'">'.$TL->Username.'</a><br>'.
							$TL->CityName.'<br>'.$TL->CountryName.'<br>');
	$detail = xml_prep(ShowMembersAjax($TL, GetParam("maxpos")));
	echo "<marker Latitude='$TL->Latitude' Longitude='$TL->Longitude' summary='$summary' detail='$detail'/>
";
}
$curpos=GetParam("start_rec",0) ; // find current pos (0 if not)
$width=GetParam("limitcount",10); // Number of records per page
$string = "<br><center>" ;
for ($ii=0; $ii<$maxpos; $ii=$ii+$width) {
	$i1=$ii ;
	$i2= min($ii + $width,$maxpos);
	if (($curpos>=$i1) and ($curpos<$i2)) $string .=  "<b>" ;
		$string .= "<a href=\"javascript: page_navigate($i1);\">".($i1+1)."..$i2</a> " ;
	if (($curpos>=$i1) and ($curpos<$i2)) $string .= "</b>" ;
}
$string .= "</center>" ;
echo "<page page='".xml_prep($string)."'/>";
echo "</markers>
";

function xml_prep($string)
{
	return preg_replace(array("/'/", '/</', '/>/'), array("&apos;", '&lt;', '&gt;'), $string);
}

function ShowMembersAjax($TM,$maxpos) {
	static $ii = 0;
	$IdCountry=GetParam("IdCountry",0) ;
	$IdCity=GetParam("IdCity",0) ;

	$info_styles = array(0 => "<tr class=\"blank\" align=\"left\" valign=\"center\">", 1 => "<tr class=\"highlight\" align=\"left\" valign=\"center\">");
	$string = $info_styles[($ii++%2)]; // this display the <tr>
	$string .= "<td class=\"memberlist\">" ;
	if (($TM->photo != "") and ($TM->photo != "NULL")) {
	    $string .= LinkWithPicture($TM->Username,$TM->photo);
	}
	$string .= "<br>".LinkWithUsername($TM->Username);
	if ($IdCountry ==0) $string .= "<br>".$TM->CountryName;
	if ($IdCity ==0) $string .= "<br>".$TM->CityName;
	$string .= "</td>" ;
	$string .= "<td class=\"memberlist\" valign=\"top\">" ;
	$string .= $TM->ProfileSummary ;
	$string .= "</td>";
	$string .= "<td class=\"memberlist\" align=\"center\">" ;
	$string .= ShowAccomadation($TM);
	$string .= "</td>" ;
	$string .= "<td class=\"memberlist\">" ;
	$string .= $TM->LastLogin ;
	$string .= "</td>" ;
	$string .= "<td class=\"memberlist\" align=center>" ;
	$string .= $TM->NbComment ;
	$string .= "</td>" ;
	$string .= "<td class=\"memberlist\" align=center>" ;
	$string .= $TM->Age ;
	$string .= "</td>" ;
	$string .="</tr>" ;

	return $string;
}
function ShowAccomadation($m) {
   if (strstr($m->Accomodation, "anytime"))
   return "<img src=\"images/yesicanhost.gif\"  title=\"".ww("CanOfferAccomodationAnytime")."\" width=\"30\" height=\"30\" alt=\"yesicanhost\" />";
   if (strstr($m->Accomodation, "yesicanhost"))
   return "<img src=\"images/yesicanhost.gif\" title=\"".ww("CanOfferAccomodation")."\" width=\"30\" height=\"30\" alt=\"yesicanhost\" />";
   if (strstr($m->Accomodation, "dependonrequest"))
   return "<img src=\"images/dependonrequest.gif\"  title=\"".ww("CanOfferdependonrequest")."\" width=\"30\" height=\"30\" alt=\"dependonrequest\" />";
   if (strstr($m->Accomodation, "neverask"))
   return "<img src=\"images/neverask.gif\" title=\"".ww("CannotOfferneverask")."\" width=\"30\" height=\"30\" alt=\"neverask\" />";
   if (strstr($m->Accomodation, "cannotfornow"))
   return "<img src=\"images/neverask.gif\"  title=\"". ww("CannotOfferAccomForNow")."\" width=\"30\" height=\"30\" alt=\"neverask\" />";
}
?>
