<?php
require_once ("menus.php");


// this function return the date of a news
// it is based on the corrsponding words.created (english)
function newsdate($word) {
  $rr=LoadRow("select SQL_CACHE created from words where code='".$word."' and IdLanguage=0") ;
  return(date("F j, Y",strtotime($rr->created))) ;
} // end of newsdate

function DisplayMain($m, $mlast,$TVisits,$newscount=0) {
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
	echo "<td colspan=3> " ;
	echo ww("RecentVisitsOfyourProfile") ;
	echo "</td>" ;

// Display the last created members with a picture
	$m=$mlast ;
	echo "<tr>" ;
	echo "<td class=\"memberlist\" ";
   echo LinkWithPicture($m->Username,$m->photo);
	echo "<br>" ;
	echo LinkWithUsername($m->Username),"<br>" ;
	echo $m->countryname, "</td> ";
	
// Display the max last three visits
	for ($ii=0;$ii<count($TVisits);$ii++) {
	$m=$TVisits[$ii] ;
	echo "<td class=\"memberlist\" align=center>";
   echo LinkWithPicture($m->Username,$m->photo);
	echo "<br>" ;
	echo LinkWithUsername($m->Username), "<br>";
	echo $m->countryname, "</td> ";
	  
	} // end of for $ii on visits
	
	echo "</table>" ;
	


	// dispplay the hello xx
	$m=$me ;
	echo ww("HelloUsername",LinkWithUsername($m->Username)) ;
	
	// news
	echo "<br><br>",ww("News"),"<br>" ;
	echo "<table>" ;
	for ($ii=$newscount;$ii>0;$ii--) {
		echo "<tr><td>",ww("NewsTitle_".$ii),"</td><td>",newsdate("NewsTitle_".$ii),"</td>" ;
		echo "<tr><td colspan=2>",ww("NewsText_".$ii),"</td>" ;
	}
	echo "</table>" ;
	
	

	require_once "footer.php";
}
?>
