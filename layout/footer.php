<?php


function DisplayFlag($ShortLang,$gif,$title) {
$langurl = $_SERVER['PHP_SELF'] . "?";
if ($_SERVER['QUERY_STRING'] != "") {
	$QS = explode('&', $_SERVER['QUERY_STRING']);
	for ($ii = 0; $ii < count($QS); $ii++) {
		if (strpos($QS[$ii], "lang=") === false)
			$langurl = $langurl . $QS[$ii] . "&";
	}
}

if ($_SESSION['lang'] == $ShortLang)
	echo "  <span><a href=\"", $langurl, "lang=",$ShortLang,"\"><img height=\"10px\" src=\"images/",$gif,"\" title=\"",$title,"\" width=16></a></span>\n";
else
	echo "  <a href=\"", $langurl, "lang=",$ShortLang,"\"><img height=\"10px\" src=\"images/",$gif,"\" title=\"",$title,"\" width=16></a>\n";
} // end of DisplayFlag

global $DisplayHeaderWithColumnsIsSet;
global $DisplayHeaderShortUserContentIsSet;

//echo "\n         </div>\n"; // ??? 
//echo "       </div>\n"; // main

echo "\n<div id=\"footer\">\n"; // footer
echo "<p>&copy;2007 <strong>BeWelcome</strong> - The hospitality network</p>";
echo "<p>To change the language in which the website is displayed, click on one of the flags below.";
echo "</p>";
echo "<p>&nbsp;</p>";

// Just add add the bottom the language switch trick
DisplayFlag("eng","en.gif","English") ;
DisplayFlag("fr","fr.gif","French") ;
DisplayFlag("esp","esp.gif","Español") ;
DisplayFlag("de","de.gif","Deutsh") ;
DisplayFlag("it","it.gif","Italian") ;
DisplayFlag("ru","ru.gif","Russian") ;
DisplayFlag("espe","esper.gif","Esperanto") ;
DisplayFlag("pl","pl.gif","Polish") ;
DisplayFlag("tr","tr.gif","Turkish") ;
DisplayFlag("lt","lt.gif","Lithuanian") ;
DisplayFlag("nl","nl.gif","Dutch") ;
DisplayFlag("dk","dk.gif","Danish") ;
DisplayFlag("cat","cat.gif","Catalan") ;

//if ($_SESSION['switchtrans']!='on') echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"switch to translation mode\" width=16></a>&nbsp;" ;
if ($_SESSION['switchtrans'] == 'on') {
	//  echo "<a href=\"",$langurl,"switchtrans=off\"><img border=0 height=10 src=\"images/showtransarray.gif\" alt=\"remove translation mode\" width=16></a>&nbsp;" ;
	$pagetotranslate = $_SERVER['PHP_SELF'];
	if ($pagetotranslate { 0 }	== "/")
	   $pagetotranslate { 0 }= "_";
	echo "  <a href=\"adminwords.php?showtransarray=1&pagetotranslate=" . $pagetotranslate . "\" target=new><img border=0 height=10 src=\"images/switchtrans.gif\" title=\"go to current translation list for " . $_SERVER['PHP_SELF'] . "\" width=16></a>\n";
}
echo "\n</div>\n"; // footer

if ($DisplayHeaderWithColumnsIsSet == true) { // if this header was displayed
	echo "     </div>\n"; // columns-low
	echo "   </div>\n"; // columns
	echo " </div>\n"; // main-content

} // end of if  a header was displayed

if ($DisplayHeaderShortUserContentIsSet == true) { // if this header was displayed
	echo "          </div>\n"; // user-content
}

echo "</body>\n";
echo "</html>\n";
?>