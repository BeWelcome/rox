<?php
echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n" ;
echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">" ;

echo "<head>\n" ;
echo "  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n" ;
if (isset($title)) {
  echo "  <title>",$title,"</title>\n" ;
}
else {
  echo "\n<title>",$_SYSHCVOL['SiteName'],"</title>\n" ;
}
echo "<LINK REL=\"SHORTCUT ICON\" HREF=\"favicon.ico\">\n" ;

$stylesheet="stylesheets1" ;

// If is logged try to load appropriated style sheet
if (IsLogged()) {
  // todo set a cache for this
  $rrstylesheet=LoadRow("select Value from memberspreferences where IdMember=".$_SESSION['IdMember']." and IdPreference=6") ;
  if (isset($rrstylesheet->Value)) {
    $stylesheet=$rrstylesheet->Value ;
  } 
}
echo"  <link href=\"",$stylesheet,"/undohtml.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />" ;
echo"  <link href=\"",$stylesheet,"/screen_micha.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />" ;
echo "</head>\n" ;
echo "<html>\n" ;
echo "<body>\n" ;

if ($_SYSHCVOL['SiteStatus']=='Closed') {
  echo "<br><br>",$_SYSHCVOL['SiteCloseMessage'],"<br>\n"  ;
	echo "</body>\n</html>\n" ;
	exit(0) ;
}
?>
