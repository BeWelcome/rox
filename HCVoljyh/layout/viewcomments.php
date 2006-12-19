<?php
require_once("Menus.php") ;
function DisplayComments($TCom,$Username) {
  global $title ;
  $title=ww('ViewComments') ;
  include "header.php" ;

  ProfileMenu("viewcomments.php",ww('MainPage'),$Username) ;
  echo "<center><H1>",ww('commentsfor',$Username),"</H1></center>\n" ;

  echo "\n<center>\n" ;
  echo "<table>\n" ;
	echo "<tr><th colspan=3>",$UserName,"</th>" ;


	$iiMax=count($TCom) ;
	$tt=array() ;
	for ($ii=0;$ii<$iiMax;$ii++) {
	  $color="black" ;
	  if ($TCom[$ii]->Quality=="Good") {
		  $color="#808000" ;
		}
	  if ($TCom[$ii]->Quality=="Bad") {
		  $color="red" ;
		}
    echo "<tr><td >" ;
    echo "<b>",$TCom[$ii]->Commenter,"</b><br>" ;
    echo "<i>",$TCom[$ii]->TextWhere,"</i>" ;
    echo "<br><font color=$color>",$TCom[$ii]->TextFree,"</font>" ;
    echo "</td>" ;
		$tt=explode(",",$TCom[$ii]->Lenght) ;
		echo "<td>" ;
		for ($jj=0;$jj<count($tt);$jj++) {
		  echo ww("Comment_".$tt[$jj]),"<br>" ;
		} 
		
		echo "</td>" ;
	}
  
  echo "</table>\n" ;
	
  echo "</center>\n" ;
  include "footer.php" ;
}

?>
