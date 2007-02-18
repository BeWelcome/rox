<?php
require_once ("Menus.php");
function DisplayMyContactList($IdMember,$TData) {
	global $title;
	$title = ww('MyContactsPage');
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("mycontacts.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderWithColumns(); // Display the header

	echo "<center>" ;
	echo ww("MyContactListFor",fUsername($IdMember)) ;

	$iiMax = count($TData);
	$CurrentCategory="" ;
	echo "<table border=\"1\" rules=\"rows\">";
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
		echo "<a href=\"mycontacts.php?action=update&IdContact=$m->id\">",ww("UpdateContact"),"</a><br>" ;
		echo "<a href=\"mycontacts.php?action=delete&IdContact=$m->id onclick=\"return('Confirm delete ?');\"\">",ww("DeleteContact"),"</a><br>" ;
		echo "</td>" ;
		echo "</tr>\n";
	}
	echo "</table>\n";
	echo "</center>" ;

	include "footer.php";

}

function DisplayOneMyContact($m,$IdContact,$TContact,$TContactCategory) {
	global $title;
	$title = ww('MyContactsPage');
	include "header.php";

?>
<SCRIPT  TYPE="text/javascript">
function raz_Category(nameform) {
	document.forms[nameform].elements["Category"].value="" ;
}		
</SCRIPT>

<?php	
	Menu1(); // Displays the top menu
	Menu2("mycontacts.php", ww('MainPage')); // Displays the second menu

	// Header of the profile page
	require_once ("profilepage_header.php");

	menumember("mycontacts.php?IdContact=" . $m->id, $m->id, $m->NbComment);
	echo "	\n<div id=\"columns\">\n";

	echo "		\n<div id=\"columns-low\">\n";
	ShowActions(""); // Show the Actions
	ShowAds(); // Show the Ads

	echo "\n    <!-- middlenav -->\n";

	echo "     <div id=\"columns-middle\">\n";
	echo "					<div id=\"content\">";
	echo "						<div class=\"info\">";

	echo "<center>" ;
	echo ww("MyContactX",$m->Username) ;
	echo "<br>",ww("MyContactListExplanation",$m->Username) ;

	echo "<form method=post action=mycontacts.php name=choosecategory>\n" ;	
   echo "<input type=hidden name=IdContact value=",$m->id,">\n" ;
	echo "<table>\n" ;
	echo "<tr><td>" ;
	$iiMax=count($TContactCategory) ;
	if ($iiMax>0) {
	   echo ww("ContactListCategoryChooseOrAdd"),"</td><td>" ;
	   echo "<select name=iCategory OnChange=\"raz_Category('choosecategory');\">\n<option value=-1>",ww("MakeAChoice"),"</option>\n" ;
	   for ($ii=0;$ii<$iiMax;$ii++) {
	   	   echo "<option value=$ii" ;
		   if ($TContactCategory[$ii]->Category==$TContact->Category) echo " selected " ;
		   echo ">",$TContactCategory[$ii]->Category,"</option> ";
	   }
	   echo" </select>\n" ;
	   echo " <input type=text name=Category " ;
	   if (isset($TContact->Category)) {
	   	  echo "value=\"$TContact->Category\"" ;
	   }
	   echo ">" ;
	}
	else {
	  echo ww("ContactListCategory"),"</td><td><input type=text name=Category " ;
	   if (isset($TContact->Category)) {
	   	  echo "value=\"$TContact->Category\"" ;
	   }
	   echo ">" ;
	}
	if (isset($TContact->id)) {
	   echo "<input type=hidden name=ContactId value=",$TContact->id,">" ;
	   echo "<input type=hidden name=action value=doupdate>" ;
	}
	else {
	   echo "<input type=hidden name=action value=doadd>" ;
	}
	echo "</td>" ; 
	
	echo "<tr><td>",ww("ContactListText"),"</td><td><textarea rows=4 cols=60 name=Comment>" ;
	if (isset($TContact->Comment)) {
	   echo $TContact->Comment ;
	}
	echo "</textarea>" ;
	echo "</td>" ; 
	if (isset($TContact->id)) {
	   echo "<tr><td colspan=2 align=center><input type=submit value=\"",ww("UpdateContact"),"\"></td>\n" ;
	}
	else {
	   echo "<tr><td colspan=2 align=center><input type=submit value=\"",ww("AddContact"),"\"></td>\n" ;
	}
	echo "</table>\n</form>\n" ;

	echo "</center>" ;

	include "footer.php";

}

function DisplayResult($Group,$Title,$Message, $Result = "") {
	global $title;
	$title = ww('ContactGroupPage', $m->Username);
	include "header.php";

	Menu1("", ww('MainPage')); // Displays the top menu

	Menu2("contactgroup.php", ww('MainPage')); // Displays the second menu

	DisplayHeaderShortUserContent($title);

	echo "<center>";
	echo "<H1>Contact ", LinkWithGroup($Group), "</H1>\n";

	echo "<br><br><table width=50%>" ;
	echo "<tr><td><i>",$Title,"</i></td>" ;
	echo "<tr><td>",$Message,"</td>" ;
	echo "<tr><td><h4>";
	echo $Result;
	echo "</h4></td></table>\n";

	echo "					</div>\n"; // info
	echo "				</div>\n"; // content
	echo "			</div>\n"; // middle
	echo "		</div>\n"; // columns

	include "footer.php";

} // end of display result
?>
