<?php

require_once("layouttools.php");

echo "<!DOCTYPE html PUBLIC \"-//W3C//DTD XHTML 1.0 Strict//EN\" \"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd\">\n";
echo "<html xmlns=\"http://www.w3.org/1999/xhtml\">\n";
global $_SYSHCVOL;
echo "<head>\n";
if (isset ($title)) {
	echo "  <title>", $title, "</title>\n";
} else {
	echo "\n<title>", $_SYSHCVOL['SiteName'], "</title>\n";
}
echo "  <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\" />\n";
if (empty($meta_description)) $meta_description=ww("default_meta_description") ;
echo "  <meta name=\"description\" content=\"",$meta_description,"\" />\n" ;
if (empty($meta_keyword)) $meta_keyword=ww("default_meta_keyword") ;
echo "  <meta name=\"keywords\" content=\"",$meta_keyword,"\" />\n" ;
echo "  <link rel=\"shortcut icon\" href=\"".bwlink("favicon.ico")."\" />\n";

$stylesheet = "YAML"; // this is the default style sheet

// If is logged try to load appropriated style sheet
if (IsLoggedIn()) {
	if (!isset($_SESSION["stylesheet"]))  { // cache in session to avoid a reload at each new page
		 $rrstylesheet = LoadRow("select Value from memberspreferences where IdMember=" . $_SESSION['IdMember'] . " and IdPreference=6");
		 if (isset ($rrstylesheet->Value)) {
		 		$_SESSION["stylesheet"]=$stylesheet = $rrstylesheet->Value;
		 }
	}
	else {
		 $stylesheet=$_SESSION["stylesheet"] ;
	}
}
echo "  <link href=\"".bwlink("styles/". $stylesheet. "/bw_yaml.css")."\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
echo "<!--[if lte IE 7]>";
echo "  <link href=\"".bwlink("styles/". $stylesheet. "/explorer/iehacks_3col_vlines.css")."\" rel=\"stylesheet\" type=\"text/css\" media=\"screen\" />\n";
echo "<![endif]-->\n";

echo "</head>\n";
echo "<body>\n";

if ($_SYSHCVOL['SiteStatus'] == 'Closed') {
	echo "<p>", $_SYSHCVOL['SiteCloseMessage'], "</p>\n";
	echo "</body>\n</html>\n";
	exit (0);
}
?>
