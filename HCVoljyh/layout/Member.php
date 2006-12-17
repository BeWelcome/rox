<?php
require_once("Menus.php") ;

function DisplayMember($m,$photo="",$phototext="",$photorank=0,$cityname,$regionname,$countryname,$profilewarning="",$TGroups) {
  global $title ;
  $title=ww('ProfilePageFor',$m->Username) ;
  include "header.php" ;

  ProfileMenu("Member.php",ww('MainPage'),$m->id) ;
	if ($profilewarning!="") {
    echo "<center><H1>",$profilewarning,"</H1></center>\n" ;
	}
	else {
    echo "<center><H1>",$m->Username,"</H1></center>\n" ;
	}
  echo "\n<center>\n" ;
  echo "<table width=50%>\n" ;

  echo "<tr><td>" ;
  echo ww('Name') ;
  echo "</td>" ;
  echo "<td>" ;
	echo PublicReadCrypted($m->FirstName)," " ;
	echo PublicReadCrypted($m->SecondName)," " ;
	echo PublicReadCrypted($m->LastName) ;
  echo "</td>" ;

  echo "<tr><td>" ;
  echo ww('Location') ;
  echo "</td>" ;
  echo "<td>" ;
	echo $cityname,"<br>" ;
	echo $regionname,"<br>" ;
	echo $countryname,"<br>" ;
  echo "</td>" ;
  echo "<td align=center  bgcolor=#ffffcc >" ;
	if ($photo!="") {
	  echo "photo<br>" ;
	  echo "<img src=\"".$photo."\" height=200 alt=\"$phototext\"><br>" ;
		echo "<table bgcolor=#ffffcc width=60%>" ;
		echo "<tr>" ;
		echo "<td align=left>" ;
		if ($photorank>0) {
		  echo "<a href=\"".$_SERVER['PHP_SELF']."?action=previouspicture&photorank=".$photorank."&cid=".$m->id."\">" ;
		  echo "<img border=0 height=10 src=\"images/moveleft.gif\" alt=\"previous picture \"></a>" ;
		}
		echo "</td>" ;
		echo "<td align=right>" ;
		echo "<a href=\"".$_SERVER['PHP_SELF']."?action=nextpicture&photorank=".$photorank."&cid=".$m->id."\">" ;
		echo "<img border=0 height=10 src=\"images/moveright.gif\" alt=\"next picture \"></a>" ;
		
		echo "</td>" ;
		echo "<tr><td cosplan=2 align=center><font size=1>",$phototext,"</font></td>" ;
		echo "</table><br>" ;
	}
  echo "</td>" ;

  echo "<tr><td>" ;
  echo ww('ProfileSummary') ;
  echo ":</td>" ;
  echo "<td colspan=2>" ;
  if ($m->ProfileSummary>0) echo FindTrad($m->ProfileSummary) ;
  echo "</td>" ;

	if ($m->Organizations>0) {
    echo "<tr><td>" ;
    echo ww('ProfileOrganizations') ;
    echo ":</td>" ;
    echo "<td colspan=2>" ;
    echo FindTrad($m->Organizations) ;
    echo "</td>" ;
	}

	if ($m->Accomodation!="") {
    echo "<tr><td>" ;
    echo ww('ProfileAccomodation') ;
    echo ":</td>" ;
    echo "<td colspan=2>" ;
	  $tt=explode(",",$m->Accomodation) ;
	  $max=count($tt) ;
    echo "<table valign=center style=\"font-size:12;\">" ;
	  for ($ii=0;$ii<$max;$ii++) {
	    echo "<tr><td>",ww("Accomodation_".$tt[$ii]),"</td>" ;
	  }
	  echo "</table></td>" ;
    echo "</td>" ;
	}

	if ($m->AdditionalAccomodationInfo>0) {
    echo "<tr><td>" ;
    echo ww('ProfileAdditionalAccomodationInfo') ;
    echo ":</td>" ;
    echo "<td colspan=2>" ;
    echo FindTrad($m->AdditionalAccomodationInfo) ;
    echo "</td>" ;
	}

	$max=count($TGroups) ;
	if ($max>0) {
    echo "<tr><td colspan=3></td>" ;
    echo "<tr><th colspan=3>",ww("xxBelongsToTheGroups",$m->Username),"</th>" ;
	  for ($ii=0;$ii<$max;$ii++) {
		  echo "<tr><td>",ww("Group_".$TGroups[$ii]->Name),"</td>";
			echo"<td  colpsan=2>" ;
      if ($TGroups[$ii]->Comment>0) echo FindTrad($TGroups[$ii]->Comment) ;
		  echo "</td>" ;
		}
	}
	
  echo "</table>\n" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
