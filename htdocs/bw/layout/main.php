<?php
require_once ("menus.php");


// this function return the date of a news
// it is based on the corrsponding words.created (english)
function newsdate($word) {
  $rr=LoadRow("select SQL_CACHE created from words where code='".$word."' and IdLanguage=0") ;
  return(date("F j, Y",strtotime($rr->created))) ;
} // end of newsdate

function DisplayMain($me, $mlast,$TVisits,$newscount=0) {
	global $title;
	$title = ww('WelcomePage' . " " . $_POST['Username']);
	require_once "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("main.php",ww('MainPage')); // Displays the second menu

	$ListOfActions="<li><a href=\"editmyprofile.php\">" . ww('EditMyProfile') . "</a></li>\n";
	if ($me->NbContacts>0) {
	   $ListOfActions.= "<li><a href=\"mycontacts.php\">" . ww('DisplayAllContacts') . "</a></li>\n" ;
	}
	DisplayHeaderMainPage( "". ww('MainPage'), "", $ListOfActions);

	// middle column
	echo "\n";
	echo "      <div id=\"col3\"> \n"; 
	echo "        <div id=\"col3_content\" class=\"clearfix\"> \n"; 
	
	echo "<table>" ;
	echo "<tr><td class=\"info\">" ;

	echo "		<div class=\"subcolumns main_preposts\">\n"; 
// Display the last created members with a picture
	$m=$mlast ;
	echo "			  <div class=\"c33l\">\n"; 
	echo "			    <div class=\"subc\">\n"; 
	echo "				<h3>",ww("RecentMember"),"</h3>\n"; 
	echo "				<p class=\"floatbox\">";
	echo LinkWithPicture($m->Username,$m->photo), LinkWithUsername($m->Username),"<br />",$m->countryname ;
	echo "				</p>\n"; 
	echo "			    </div>\n"; 
	echo "			  </div>\n"; 
	echo "			  <div class=\"c66r\">\n"; 
	echo "			  <h3>",ww("RecentVisitsOfyourProfile"),"</h3>\n"; 
	
	$DivForVisit[0]='c33l' ;
	$DivForVisit[1]='c33l' ;
	$DivForVisit[2]='c33r' ;
// /*###   NEW   To be programmed: show the first visitor, then the second. !! Different div's (c50l, c50r)!  ###
	for ($ii=0;$ii<count($TVisits);$ii++) {
			$m=$TVisits[$ii] ;
			echo "				  <div class=\"",$DivForVisit[$ii],"\">\n"; 
			echo "				    <div class=\"subc\">\n"; 
			echo "					<p class=\"floatbox\">";
			echo LinkWithPicture($m->Username,$m->photo), LinkWithUsername($m->Username),"<br />",$m->countryname ;
			echo "				</p>\n"; 
			echo "					</div>\n"; 
			echo "				  </div>\n"; 
	} // end of for $ii on visits
	/* 
	echo "				  <div class=\"c50r\">\n"; 
	echo "				    <div class=\"subcr\">\n"; 
	echo "					 <p class=\"floatbox\"><img src=\"images/et.gif\" width=\"50\" height=\"50\" border=\"0\" alt=\"\" align=\"top\"  class=\"float_left framed\"><a href=\"#\" class=\"username\">maplefanta</a><br />from Oberschwanbach in USA:<br> <q>I love BeWelcome</q></p>		\n"; 
	echo "					</div>\n"; 
	echo "				  </div>\n"; 
*/
	echo "			  </div>\n"; 
	echo "		</div>\n";

// OLD DEACTIVATED Display the max last three visits
/*
	for ($ii=0;$ii<count($TVisits);$ii++) {
	$m=$TVisits[$ii] ;
	echo "<td class=\"memberlist\" align=center>";
   echo LinkWithPicture($m->Username,$m->photo);
	echo "<br>" ;
	echo LinkWithUsername($m->Username), "<br>";
	echo $m->countryname, "</td> ";
	  
	} // end of for $ii on visits
*/	

	// news	
	echo "				<div class=\"subcolumns main_posts\">\n"; 
	echo "				  <div class=\"c62l\">\n"; 
	echo "				    <div class=\"subcl\">\n"; 
	echo "						<div id=\"content\">\n"; 
	echo "						<h3>",ww("News"),"</h3>\n"; 
	for ($ii=$newscount;$ii>0;$ii--) {
		echo "							<p class=\"news\"><a href=\"#\">",ww("NewsTitle_".$ii),"</a><span class=\"small grey\">&nbsp;&nbsp;  |&nbsp; ",newsdate("NewsTitle_".$ii),"</span></p><p>",ww("NewsText_".$ii),"</p>\n"; 
	}
	echo "				    </div>\n"; 
	echo "				    </div>\n"; 
	echo "				  </div>\n"; 
	echo "\n"; 
	echo "				  <div class=\"c38r\">\n"; 
	echo "				    <div class=\"subcr\">\n"; 
	echo "					<h3>Next visitors in town:</h3>\n"; 
	echo "							 <ul>\n"; 
	echo "							 	<li><a href=\"#\" class=\"username\">member1</a><span class=\"small grey\"> / 4 June 2007</span> </li>\n"; 
	echo "							 	<li><a href=\"#\" class=\"username\">maplefanta</a><span class=\"small grey\"> / 14 June 2007</span> </li>\n"; 
	echo "							 	<li><a href=\"#\" class=\"username\">autoseeker23</a><span class=\"small grey\"> / 23 June 2007</span> </li>\n"; 
	echo "							 </ul>									\n"; 
	echo "				    </div>\n"; 
	echo "				  </div>\n"; 
	echo "				</div>\n"; 
	echo "				\n"; 
/*	echo "				<p><a href=\"#\">",ww("MoreNews"),"</a></p>\n";  */
	echo "</td>\n"; 
	echo "</tr>\n"; 
	echo "</table>\n";

	
	// news
/* OLD DEACTIVATED
	echo "<br><br>",ww("News"),"<br><br>" ;
	echo "<table cellspacing=5 cellspadding=5>" ;
	for ($ii=$newscount;$ii>0;$ii--) {
		echo "<tr><td><i>",ww("NewsTitle_".$ii),"</i></td><td><font color=gray>",newsdate("NewsTitle_".$ii),"</font></td>" ;
		echo "<tr><td colspan=2>",ww("NewsText_".$ii),"<br> </td>" ;
	}
	echo "</table>" ;

*/
	

	require_once "footer.php";
}
?>