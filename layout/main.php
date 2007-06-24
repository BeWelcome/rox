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

	DisplayHeaderWithColumns( "<br />&nbsp;&nbsp;&nbsp;". ww("HelloUsername",LinkWithUsername($me->Username)), "<br />", $ListOfActions);


  echo "          <div class=\"info\">\n";
	echo "            <table border=\"0\" cellspacing=\"5\">\n" ;
	echo "              <tr>\n";
	echo "                <td><h3>\n" ;
	echo ww("RecentMember") ;
	echo "</h3></td>\n" ;
	echo "                <td colspan=\"3\" align=\"left\"><h3> " ;
	echo ww("RecentVisitsOfyourProfile") ;
	echo "</h3></td>\n" ;
	echo "              </tr>\n" ;

// Display the last created members with a picture
	$m=$mlast ;
	echo "<tr>\n" ;
	echo "<td class=\"memberlist\">";
	echo "<p class=\"floatbox\" style=\"vertical-align: bottom;\"><span class=\"float_left\">";
  echo LinkWithPicture($m->Username,$m->photo);
  echo "</span>\n";
	echo LinkWithUsername($m->Username),"<br>" ;
	echo $m->countryname, "</p></td>\n ";
	
// Display the max last three visits
	for ($ii=0;$ii<count($TVisits);$ii++) {
	$m=$TVisits[$ii] ;
	echo "<td class=\"memberlist\" align=left>";
	echo "<p class=\"floatbox\" style=\"vertical-align: bottom;\"><span class=\"float_left\">";
  echo LinkWithPicture($m->Username,$m->photo);
  echo "</span>\n";
	echo LinkWithUsername($m->Username), "<br>";
	echo $m->countryname, "</p></td> ";
	  
	} // end of for $ii on visits
	
	echo "</table>" ;
	
	// news
	echo "<br>" ;
 
	echo "						  <h3>",ww("News"),"</h3>\n"; 
	for ($ii=$newscount;$ii>0;$ii--) {
		echo "              <h4>",ww("NewsTitle_".$ii),"</h4>\n";
		echo "							<p class=\"news\">\n";
		echo "              <span style=\"font-size:11px; color:#666666; \">",newsdate("NewsTitle_".$ii),"</span>\n";
		echo "              </p>\n";
		echo "              <p>",ww("NewsText_".$ii),"</p>\n"; 
		 
	}
	echo "		  </div>\n"; 
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
