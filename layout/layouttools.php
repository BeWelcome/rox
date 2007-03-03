<?php

function DisplayFlag($ShortLang,$png,$title)
{
	$langurl = $_SERVER['PHP_SELF'] . "?";
	if ($_SERVER['QUERY_STRING'] != "") {
		$QS = explode('&', $_SERVER['QUERY_STRING']);
		for ($ii = 0; $ii < count($QS); $ii++) {
			if (strpos($QS[$ii], "lang=") === false)
				$langurl = $langurl . $QS[$ii] . "&";
		}
	}
	
	if ($_SESSION['lang'] == $ShortLang)
		echo "  <span><a href=\"", $langurl, "lang=",$ShortLang,"\"><img src=\"images/",$png,"\" title=\"",$title,"\"></a></span>\n";
	else
		echo "  <a href=\"", $langurl, "lang=",$ShortLang,"\"><img src=\"images/",$png,"\" title=\"",$title,"\"></a>\n";
} // end of DisplayFlag

?>