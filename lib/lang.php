<?php
/*
 * Created on 5.2.2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */

function LanguageChangeTest()
{
	$newlang = "";
	if (GetParam("lang") != "") {
		SwitchToNewLang(GetParam("lang"));
	}
	if (!isset ($_SESSION['lang'])) {
		SwitchToNewLang("eng");
	}
	
	// -----------------------------------------------------------------------------
	// test if member use the switchtrans switch to record use of words on its page 
	if ((isset ($_GET['switchtrans'])) and ($_GET['switchtrans'] != "")) {
		if (!isset ($_SESSION['switchtrans'])) {
			$_SESSION['switchtrans'] = "on";
		} else {
			if ($_SESSION['switchtrans'] == "on") {
				$_SESSION['switchtrans'] = "off";
			} else {
				$_SESSION['switchtrans'] = "on";
			}
		}
	} // end of switchtrans
	
	if (isset ($_GET['forcewordcodelink'])) { // use to force a linj to each word 
		//code on display
		$_SESSION['forcewordcodelink'] = $_GET['forcewordcodelink'];
	}
}
?>