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
		echo "      <span><a href=\"", $langurl, "lang=",$ShortLang,"\"><img src=\"".bwlink("images/flags/".$png)."\" alt=\"",$title,"\" title=\"",$title,"\"></img></a></span>\n";
	else
		echo "      <a href=\"", $langurl, "lang=",$ShortLang,"\"><img src=\"".bwlink("images/flags/".$png)."\" alt=\"",$title,"\" title=\"",$title,"\"></img></a>\n";
} // end of DisplayFlag

//------------------------------------------------------------------------------
// bwlink converts a relative link to an absolute link
// It works from subdirectories too. Result is always relative
// to the root directory of the site. Works in local environment too.  
// e.g. "" -> "http://www.bewelcome.org/"
//      "layout/a.php" -> "http://www.bewelcome.org/layout/a.php"
function bwlink( $target )
{
	global $_SYSHCVOL;
	
	if (strlen($target) > 8)
	{
		if (substr_compare($target,"https://",0,8)==0 || 
		    substr_compare($target,"http://",0,7)==0)
			return $target;
	}
	
	$a = "http://".$_SYSHCVOL['SiteName'].$_SYSHCVOL['MainDir'].$target;
	
	return $a;
}

?>