<?php
require_once("Menus.php") ;

function DisplayMyPhotos($TData,$action,$IdMember,$lastaction) {

  global $title,$_SYSHCVOL ;
  $title=ww("MyPhotos") ;
  include "header.php" ;

  mainmenu("EditMyProfile.php",ww('MainPage'),$IdMember) ;
	if ($profilewarning!="") {
    echo "<center><H2>",$profilewarning,"</H2></center>\n" ;
	}
	
	$max=count($TData) ;
	

	echo "<br><center>\n" ;
	$rCurLang=LoadRow("select * from languages where id=".$_SESSION['IdLanguage']) ;
  echo "<table width=50%><tr><td bgcolor=#ffff66>",ww("WarningYouAreWorkingIn",$rCurLang->Name,$rCurLang->Name),"</td></table>\n" ;
	echo "<table>\n" ;
	
	for ($ii=0;$ii<$max;$ii++) {
	  $rr=$TData[$ii] ;
		$text=FindTrad($rr->Comment) ;
	  echo "<tr>" ;
		echo "<td valign=center align=center>" ;
	  if ($ii>0) echo "<a href=\"",$_SERVER["PHP_SELF"],"?action=moveup&iPos=",$ii,"&IdPhoto=",$rr->id,"&cid=",$IdMember,"\" title=\"move picture up \"><img border=0 height=10 src=\"images/up.gif\" alt=\"move picture up \"></a>&nbsp;" ;
		echo "<br>\n" ;
    echo "<img src=\"".$rr->FilePath."\" height=50 alt=\"",$text,"\">" ;
		echo "<br>" ;
	  if (($ii+1)<$max) echo "<a href=\"",$_SERVER["PHP_SELF"],"?action=movedown&iPos=",$ii,"&IdPhoto=",$rr->id,"&cid=",$IdMember,"\" title=\"move picture down \"><img border=0 height=10 src=\"images/down.gif\" alt=\"move picture down \"></a>&nbsp;" ;
		echo "</td>" ;
		echo "<td valign=center>" ;
		echo "\n<form method=post style=\"display:inline\"><input type=hidden name=action value=updatecomment><input type=hidden name=IdPhoto value=",$rr->id,">" ;
		echo "<textarea name=Comment cols=70 row=6  style=\"display:inline\">" ;
		echo FindTrad($rr->Comment) ;
		echo "</textarea>" ;
		echo "</td>" ;
		echo "<td valign=center align=center>" ;
		echo "<input type=submit value=\"",ww("updatepicturecomment"),"\">" ;
    echo "<input type=hidden name=cid value=",$IdMember,">" ;
		echo "</form><br>" ;
		echo "\n<form method=post style=\"display:inline\"><input type=hidden name=action value=deletephoto><input type=hidden name=IdPhoto value=",$rr->id,">" ;
		echo "\n<input type=submit value=\"",ww("deletepicture"),"\" onclick=\"return confirm('",ww("confirmdeletepicture"),"');\">" ;
		echo "</form>" ;
		echo "</td>\n" ;
	}
	
	echo "<tr>" ;
  echo "<td colspan=3 align=center>" ;
	echo "<hr>\n";

	echo "<table class=s bgColor=#ffffcc cellSpacing=2 cellPadding=3 width=100% valign=top border=0>";
	echo "<tr bgcolor=#99ccff><th colspan=2>",ww('uploadphotorules'),"</th>\n";
	echo "\n<FORM ENCTYPE=\"multipart/form-data\" ACTION=".$_SERVER["PHP_SELF"]," METHOD=POST>\n";
	echo "<INPUT TYPE=hidden name=MAX_FILE_SIZE value=",$_SYSHCVOL['UploadPictMaxSize'],">\n"; // Test of file size is done later
	echo "<INPUT TYPE=hidden name=action value=UpLoadPicture>\n";
  echo "<input type=hidden name=cid value=",$IdMember,">" ;
	echo "<tr><td>",ww("commentforthispicture"),"</td><td>" ;
	echo "<textarea name=Comment cols=70 row=6  style=\"display:inline\">" ;
	echo "</textarea>" ; 
	echo "</td>" ;
	echo "<tr><td align=center>",ww('uploadselectpicture'),"</td><td><INPUT NAME=\"userfile\" TYPE=file style=font-size=12>\n";
	echo "<tr><td colspan=2 align=center>" ;
	echo "<br><INPUT TYPE=\"submit\" VALUE=\"",ww('uploadsubmit'),"\" style=font-size=12><br>\n";
	echo "</td>";
	echo "</FORM>\n";
	echo "</table>";

	echo "</td>" ;
		
  echo "</table>\n" ;
  echo "</center>\n" ;
  include "footer.php" ;
}
?>
