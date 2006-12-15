<?php
require_once("Menus.php") ;

function DisplayMyPhotos($TData,$IdMember,$action,$lastaction) {

  global $title,$_SYSHCVOL ;
  $title=ww("MyPhotos") ;
  include "header.php" ;

  mainmenu("EditMyProfile.php",ww('MainPage'),$IdMember) ;
	if ($profilewarning!="") {
    echo "<center><H2>",$profilewarning,"</H2></center>\n" ;
	}
	
	$max=count($TData) ;

	echo "<table>" ;
	
	for ($ii=0;$ii<$max;$ii++) {
	  $rr=$TData[$ii] ;
		$text=FindTrad($rr->Comment) ;
	  echo "<tr>" ;
		echo "<td valign=center align=center>" ;
	  echo "<img src=\"".$rr->FilePath."\" height=20 alt=\"",$text,"\"><br>" ;
		echo "</td>" ;
	}
		
  echo "<input type=hidden name=cid value=",$IdMember,">" ;
  echo "</table>\n" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
