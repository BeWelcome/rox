<?php
require_once ("Menus.php");
function DisplayMyRelationsList($IdMember,$TData) {
	global $title;
	$title = ww('MyContactsPage');
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("myrelations.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderWithColumns("myrelations.php","","<li><a href=\"mycontacts.php\">" . ww('DisplayAllContacts') . "</a></li>"); // Display the header

	echo "<center>" ;

	$iiMax = count($TData);
	$CurrentCategory="" ;
	echo "<table border=\"1\" rules=\"rows\" cellspacing=4>";
	for ($ii = 0; $ii < $iiMax; $ii++) {
		$m = $TData[$ii];
		if ($m->Category!=$CurrentCategory) {
		   echo "<tr><td colspan=3 align=left>",$m->Category,"</td></tr>\n" ;
		   $CurrentCategory=$m->Category ;
		}
		echo "<tr align=left>";
		echo "<td valign=center align=left>";
		if (($m->photo != "") and ($m->photo != "NULL")) {
//			echo "<div id=\"topcontent-profile-photo\">\n";
            echo LinkWithPicture($m->Username,$m->photo),"<br>" ;
//			echo "</div>";
		}
		echo LinkWithUsername($m->Username),"<br><br>";
		echo "</td>";
		echo "<td valign=center align=left>";
		echo $m->Comment;
		echo "</td>";
		echo "<td>" ;
		echo "<a href=\"mycontacts.php?action=update&IdContact=$m->Username\">",ww("UpdateContact"),"</a><br>" ;
		echo "<a href=\"mycontacts.php?action=delete&IdContact=$m->Username\" onclick=\"return confirm('Confirm delete ?');\">",ww("DeleteContact"),"</a><br>" ;
		echo "</td>" ;
		echo "</tr>\n";
	}
	echo "</table>\n";
	echo "</center>" ;

	include "footer.php";

}

function DisplayOneRelation($m,$IdRelation,$TRelation) {
	global $title;
	$title = ww('MyRelationsPage');
	include "header.php";

?>
<SCRIPT  TYPE="text/javascript">
function raz_Category(nameform) {
	document.forms[nameform].elements["type"].value="" ;
}		
</SCRIPT>

<?php	
	Menu1(); // Displays the top menu
	Menu2("myrelations.php", ww('MainPage')); // Displays the second menu

	// Header of the profile page
	require_once ("profilepage_header.php");

	menumember("myrelations.php?IdRelation=" . $m->id, $m->id, $m->NbComment);
	echo "	\n<div id=\"columns\">\n";

	echo "		\n<div id=\"columns-low\">\n";
	ShowActions(""); // Show the Actions
	ShowAds(); // Show the Ads

	echo "\n    <!-- middlenav -->\n";

	echo "     <div id=\"columns-middle\">\n";
	echo "					<div id=\"content\">";
	echo "						<div class=\"info\">";

	echo "<center>" ;

	echo "<form method=post action=myrelations.php name=choosecategory>\n" ;	
   echo "<input type=hidden name=IdRelation value=",$m->id,">\n" ;
	echo "<table>\n" ;
	echo "<tr><td colspan=3>" ;
	echo "<br>",ww("MyRelationListExplanation",$m->Username,$m->Username) ;
	echo "</td><tr>" ;
	echo "<tr><td>" ;
  	echo ww("RelationListCategory"),"</td><td>" ;

  	$tt=mysql_get_set("specialrelations","Type") ;
	$max=count($tt) ;
	for ($ii = 0; $ii < $max; $ii++) {
		echo "<input type=checkbox name=\"Type_" . $tt[$ii] . "\"";
		if (strpos(" ".$TRelation->Type,$tt[$ii] )!=0)
		echo " checked ";
		echo "> ",ww("Relation_Type_" . $tt[$ii]),"<br>";
	}
	echo "</td>" ;
	if (isset($TRelation->id)) {
	   echo "<input type=hidden name=RelationId value=",$TRelation->id,">" ;
	   echo "<input type=hidden name=action value=doupdate>" ;
	}
	else {
	   echo "<input type=hidden name=action value=doadd>" ;
	}
	echo "</td>" ; 
	
	echo "<tr><td>",ww("RelationText",$m->Username),"</td><td><textarea rows=4 cols=60 name=Comment>" ;
	if (isset($TRelation->Comment)) {
	   echo $TRelation->Comment ;
	}
	echo "</textarea>" ;
	echo "</td>" ; 
	if (isset($TRelation->id)) {
	   echo "<tr><td colspan=2 align=center><input type=submit value=\"",ww("UpdateRelation"),"\"></td>\n" ;
	}
	else {
	   echo "<tr><td colspan=2 align=center><input type=submit value=\"",ww("AddRelation"),"\"></td>\n" ;
	}
	echo "</table>\n</form>\n" ;
	echo "<br><br>" ;
	if ($TRelation->Confirmed) echo ww("RelationConfirmedByXX",LinkWithUsername($m->Username)) ;
	else  echo ww("RelationNotConfirmedByXX",LinkWithUsername($m->Username)) ;

	echo "</center>" ;

	include "footer.php";
}
?>
