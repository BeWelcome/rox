<?php
echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n" ;
echo "<html>\n" ;
echo "<head>\n" ;
echo "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=ISO-8859-15/>\n";
echo "<LINK REL=\"SHORTCUT ICON\" HREF=\"favicon.ico\">\n" ;
if (isset($title)) {
  echo "\n<title>".$title."</title>" ;
}
else {
  echo "\n<title>",$_SYSHCVOL['SiteName'],"</title>" ;
}
//echo "<link href=\"stylesheets/screen.css\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n" ;
?>
<style type="text/css" media="screen">
<!--
			 
table {
     font:small Georgia,Serif;
     _font:x-small;
      font-size:90%;
}
td { 
     padding:2px;
}


body {
     background:#fff;
     margin:0;
     padding:20px;
     color:#000;
     font:small Georgia,Serif;
     _font:x-small;
      }
    
    #header {
      float:left;
      width:100%;
      background:#DAE0D2 url(stylesheets/bg.gif) repeat-x bottom;
      font-size:93%;
      line-height:normal;
      }
    #header ul {
      margin:0;
      padding:10px 10px 0;
      list-style:none;
      }
    #header li {
      float:left;
      background:url(stylesheets/left.gif) no-repeat left top;
      margin:0;
      padding:0 0 0 9px;
      }
    #header a {
      display:block;
      background:url(stylesheets/right.gif) no-repeat right top;
      padding:5px 15px 4px 6px;
      text-decoration:none;
      font-weight:bold;
      color:#765;
      }
    #header a:hover {
      color:#333;
      }
    #header #current {
      background-image:url(stylesheets/right_on.gif);
      }
    #header #current a {
      background-image:url(stylesheets/left_on.gif);
      color:#333;
      padding-bottom:5px;
      }
			
-->
</style>
<?php
echo "</head>\n" ;
echo "<html>\n" ;
echo "<body>\n" ;

if ($_SYSHCVOL['SiteStatus']=='Closed') {
  echo "<br><br>",$_SYSHCVOL['SiteCloseMessage'],"<br>"  ;
	echo "</body></html>" ;
	exit(0) ;
}
?>
