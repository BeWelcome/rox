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
	$ListOfActions.= VolMenu();
	DisplayHeaderWithColumns( "<br>&nbsp;&nbsp;&nbsp;". ww('MainPage'), "", $ListOfActions);


   echo "          <div class=\"info\">\n";
	
	
	echo "<table>" ;
	echo "<tr><td>" ;
	echo ww("RecentMember") ;
	echo "</td>" ;
	echo "<td colspan=3 align=left> " ;
	echo ww("RecentVisitsOfyourProfile") ;
	echo "</td>" ;

// Display the last created members with a picture
	$m=$mlast ;
	echo "<tr>" ;
	echo "<td class=\"memberlist\">";
   echo LinkWithPicture($m->Username,$m->photo);
	echo "<br>" ;
	echo LinkWithUsername($m->Username),"<br>" ;
	echo $m->countryname, "</td> ";
	
// Display the max last three visits
	for ($ii=0;$ii<count($TVisits);$ii++) {
	$m=$TVisits[$ii] ;
	echo "<td class=\"memberlist\" align=left>";
   echo LinkWithPicture($m->Username,$m->photo);
	echo "<br>" ;
	echo LinkWithUsername($m->Username), "<br>";
	echo $m->countryname, "</td> ";
	  
	} // end of for $ii on visits
	
	echo "</table>" ;
	


	// dispplay the hello xx
	echo ww("HelloUsername",LinkWithUsername($me->Username)) ;
	
	// news

	echo "				<div class=\"subcolumns main_posts\">\n"; 
	echo "				  <div class=\"c62l\">\n"; 
	echo "				    <div class=\"subcl\">\n"; 
	echo "						<div id=\"content\">\n"; 
	echo "						<h3>",ww("News"),"</h3>\n"; 
	for ($ii=$newscount;$ii>0;$ii--) {
		echo "							<p class=\"news\"><a href=\"#\">",ww("NewsTitle_".$ii),"</a><span class=\"small grey\">&nbsp;&nbsp;  |&nbsp; ",newsdate("NewsTitle_".$ii),"</span></p><p>",ww("NewsText_".$ii),"</p>\n"; 
		echo "				    </div>\n"; 
	}
	echo "				    </div>\n"; 
	echo "				  </div>\n"; 
	echo "\n"; 

	/*
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
