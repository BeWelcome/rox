<?php
echo "<h1>HHHHHHHHHHHHHHHHHALLO";
$i18n = new MOD_i18n('apps/rox/searchmembers.php');
$searchmembersText = $i18n->getText('searchmembersText');

header('Content-type: text/xml');
echo "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<markers>
";
$maxpos = $vars['rCount'];
foreach($TList as $TL) {
	$summary = xml_prep($TL->photo.'<a href="bw/member.php?cid='.$TL->Username.'">'.$TL->Username.'</a><br />'.$TL->CityName.'<br />'.$TL->CountryName.'<br />');
	$detail = xml_prep(ShowMembersAjax($TL, $maxpos));
	echo "<marker Latitude='$TL->Latitude' Longitude='$TL->Longitude' summary='$summary' detail='$detail'/>
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
echo "<header header='".xml_prep("<table><tr><th>".$searchmembersText['country']."</th><th>".$searchmembersText['about_me']."</th><th>".$searchmembersText['accomodation']."</th><th>".$searchmembersText['last_login']."</th><th>".$searchmembersText['comments']."</th><th>".$searchmembersText['age']."</th></tr>")."'/>";
echo "<footer footer='".xml_prep("</table>")."'/>";
echo "<page page='".xml_prep($string)."'/>";
echo "</markers>
";

function xml_prep($string)
{
	return preg_replace(array("/'/", "/&/", '/</', '/>/'), array("&apos;", "&amp;", '&lt;', '&gt;'), $string);
}

function ShowMembersAjax($TM,$maxpos) {
	static $ii = 0;

	$info_styles = array(0 => "<tr class=\"blank\" align=\"left\" valign=\"center\">", 1 => "<tr class=\"highlight\" align=\"left\" valign=\"center\">");
	$string = $info_styles[($ii++%2)]; // this display the <tr>
	$string .= "<td class=\"memberlist\">" ;
	if (($TM->photo != "") and ($TM->photo != "NULL")) $string .= $TM->photo;
	$string .= "<br />".'<a href="bw/member.php?cid='.$TM->Username.'">'.$TM->Username.'</a>';
	$string .= "<br />".$TM->CountryName;
	$string .= "<br />".$TM->CityName;
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
   return "<img src=\"bw/images/yesicanhost.gif\"  title=\"".ww("CanOfferAccomodationAnytime")."\" width=\"30\" height=\"30\" alt=\"yesicanhost\" />";
   if (strstr($m->Accomodation, "yesicanhost"))
   return "<img src=\"bw/images/yesicanhost.gif\" title=\"".ww("CanOfferAccomodation")."\" width=\"30\" height=\"30\" alt=\"yesicanhost\" />";
   if (strstr($m->Accomodation, "dependonrequest"))
   return "<img src=\"bw/images/dependonrequest.gif\"  title=\"".ww("CanOfferdependonrequest")."\" width=\"30\" height=\"30\" alt=\"dependonrequest\" />";
   if (strstr($m->Accomodation, "neverask"))
   return "<img src=\"bw/images/neverask.gif\" title=\"".ww("CannotOfferneverask")."\" width=\"30\" height=\"30\" alt=\"neverask\" />";
   if (strstr($m->Accomodation, "cannotfornow"))
   return "<img src=\"bw/images/neverask.gif\"  title=\"". ww("CannotOfferAccomForNow")."\" width=\"30\" height=\"30\" alt=\"neverask\" />";
}
function ww($str)
{
	return $str;
}
?>
