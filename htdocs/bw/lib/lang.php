<?php
/*

Copyright (c) 2007 BeVolunteer

This file is part of BW Rox.

BW Rox is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

BW Rox is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, see <http://www.gnu.org/licenses/> or
write to the Free Software Foundation, Inc., 59 Temple Place - Suite 330,
Boston, MA  02111-1307, USA.

*/
/*
 * Created on 5.2.2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */


define("CV_def_lang","en"); // This is the short code for the default language


function LanguageChangeTest()
{
	$newlang = "";
	if (GetStrParam("lang") != "") {
		SwitchToNewLang(GetStrParam("lang"));
	}
	if (!isset ($_SESSION['lang'])) {
		if (!empty($_COOKIE['LastLang'])) { // If there is already a cookie ide set, we are going try it as language
			 SwitchToNewLang($_COOKIE['LastLang']);
		}
		else {
			 SwitchToNewLang(); // Switch lang will choose the default language
		}
	}

	// -----------------------------------------------------------------------------
	// test if member use the switchtrans switch to record use of words on its page
	if ((isset ($_GET['switchtrans'])) and ($_GET['switchtrans'] != "")) {
		if (!isset ($_SESSION['switchtrans'])) {
			$this->getSession->set( 'switchtrans', "on" )
		} else {
			if ($_SESSION['switchtrans'] == "on") {
				$this->getSession->set( 'switchtrans', "off" )
			} else {
				$this->getSession->set( 'switchtrans', "on" )
			}
		}
	} // end of switchtrans

	if (isset ($_GET['forcewordcodelink'])) { // use to force a linj to each word
		//code on display
		$this->getSession->set( 'forcewordcodelink', $_GET['forcewordcodelink'] )
	}
}

// This function sets the new language parameters
function SwitchToNewLang($para_newlang="") {

	//echo $_SERVER["HTTP_ACCEPT_LANGUAGE"],"\$para_newlang=",$para_newlang;
	$newlang=$para_newlang;
	if (empty($newlang))
	{
		if (!empty($_COOKIE['LastLang']))
		{ // If there is already a cookie ide set, we are going try it as language
		   $newlang = $_COOKIE['LastLang'];
		}
		else {
			$newlang = CV_def_lang; // use the default one

			if (isset($_SERVER["HTTP_ACCEPT_LANGUAGE"])) { // To avoid a notice error
				 // Try to look in the default browser settings
				 $TLang = explode(",",$_SERVER["HTTP_ACCEPT_LANGUAGE"]);
				 for ($ii=0;$ii<count($TLang);$ii++) {
				 		 $rr=LoadRow("SELECT languages.id AS id FROM languages,words WHERE languages.ShortCode='".$TLang[$ii]."' and languages.id=words.Idlanguage and words.code='WelcomeToSignup'");
						 if (isset($rr->id)) { // if valid language found
				 		 		$newlang=$TLang[$ii];
								break;
						 }
				}
			}
			// end Try to look in the default browser settings
		}
	}

	if (!$this->_session->has( 'lang' ) ||
		$_SESSION['lang'] != $newlang ||
		!$this->_session->has( 'IdLanguage' )
	{
		// Update lang if url lang has changed
		$RowLanguage = LoadRow("SELECT SQL_CACHE id,ShortCode FROM languages WHERE ShortCode='" . $newlang . "'");

		if (isset($RowLanguage->id))
		{
			if ($this->_session->has( 'IdMember' ))
				LogStr("change to language from [" . $_SESSION['lang'] . "] to [" . $newlang . "]", "SwitchLanguage");
			$this->getSession->set( 'lang', $RowLanguage->ShortCode );
			$this->getSession->set( 'IdLanguage', $RowLanguage->id );
		}
		else
		{
			LogStr("problem : " . $newlang . " not found after SwitchLanguage", "Bug");
			$this->getSession->set( 'lang', CV_def_lang );
			$this->getSession->set( 'IdLanguage', 0 );
		}
		setcookie('LastLang',$_SESSION['lang'],time()+3600*24*300); // store it as a cookie for 300 days
	}

	if (IsLoggedIn())
	{ // if member is logged in set language preference
		$rPrefLanguage = LoadRow("SELECT * FROM memberspreferences WHERE IdMember=" . $_SESSION['IdMember'] . " and IdPreference=1");
		if (isset($rPrefLanguage->id))
		{
			$str = "UPDATE memberspreferences SET Value='" . $_SESSION['IdLanguage'] . "' WHERE id=" . $rPrefLanguage->id;
		}
		else
		{
			$str = "INSERT INTO memberspreferences(IdPreference,IdMember,Value,created) VALUES(1," .$_SESSION['IdMember'] . ",'" . $_SESSION['IdLanguage'] . "',now() )";
		}
		sql_query($str) ;
	} // end if Is Logged in

	if (!$this->_session->has( 'IdLanguage' ))
	{
		bw_error("SwitchToNewLang internal failure. IdLanguage still not set.");
	}

} // end of SwitchToNewLang

//------------------------------------------------------------------------------
// ww function will display the translation according to the code and the default language
function ww($code, $p1 = NULL, $p2 = NULL, $p3 = NULL, $p4 = NULL, $p5 = NULL, $p6 = NULL, $p7 = NULL, $p8 = NULL, $p9 = NULL, $pp10 = NULL, $pp11 = NULL, $pp12 = NULL, $pp13 = NULL) {
	global $Params;

	// If no language set default language
	if (!$this->_session->has( 'IdLanguage' ))
	{
	   SwitchToNewLang();
	}

	if (!$this->_session->has( 'IdLanguage' ))
	{
		bw_error("Lang select internal failure");
	}

	return (wwinlang($code, $_SESSION['IdLanguage'], $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9, $pp10, $pp11, $pp12, $pp13));
} // end of ww

//------------------------------------------------------------------------------
// ww function will display the translation according to the code and the default language
function wwinlang($code, $IdLanguage = 0, $p1 = NULL, $p2 = NULL, $p3 = NULL, $p4 = NULL, $p5 = NULL, $p6 = NULL, $p7 = NULL, $p8 = NULL, $p9 = NULL, $pp10 = NULL, $pp11 = NULL, $pp12 = NULL, $pp13 = NULL) {
	if ((isset ($_SESSION['switchtrans'])) and ($_SESSION['switchtrans'] == "on")) { // if user as choosen to build a translation list to use in AdminWords
		if (!isset ($_SESSION['TranslationArray'])) {
			$this->getSession->set( 'TranslationArray', array () ) // initialize $_SESSION['TranslationArray'] if it wasent existing yet
		}
		if (!in_array($code, $_SESSION['TranslationArray'])) {
			array_push($_SESSION['TranslationArray'], $code);
		}
	}

	$res = "";
	if (empty ($code)) {
		return ("Empty field \$code in ww function");
	}
	if (is_numeric($code)) { // case code is the idword in numeric form
		$rr = LoadRow("select SQL_CACHE Sentence,donottranslate from words where id=$code");
		$res = nl2br(stripslashes($rr->Sentence));
	} else { // In case the code wasnt a numeric id
		$rr = LoadRow("select SQL_CACHE Sentence,donottranslate from words where code='$code' and IdLanguage='" . $IdLanguage . "'");
		if (isset ($rr->Sentence)){
			$res = nl2br(stripslashes($rr->Sentence));
		}		//		echo "ww('",$code,"')=",$res,"<br>";
	}

	if ($res == "") { // If not translation found
		if (is_numeric($code)) { // id word case (code is numeric)
			if (HasRight("Words", ShortLangSentence($IdLanguage))) {
				$res = "<b>function ww() : idword #$code missing</b>";
			} else {
				$res = $code;
			}
			return ($res);
		} else { // Normal case (code is a string)
			$rEnglish = LoadRow("select SQL_CACHE Sentence,donottranslate from words where code='$code' and IdLanguage=0");
			if (!isset ($rEnglish->Sentence)) { // If there is no default language correspondance
			   $res = $code; // The code of the word will be return
			    if (HasRight("Words") >= 10) { // IF the user has translation right mark the word has missing
				   $res = "<a target=\"_new\" href=admin/adminwords.php?IdLanguage=" . $IdLanguage . "&code=$code style=\"background-color:#ff6699;color:#660000;\" title=\"click to translate in " . ShortLangSentence($IdLanguage) . "\">Missing words : $code</a>";
				}
			} else { // There is a default language so propose it as a result
				$res = nl2br(stripslashes($rEnglish->Sentence));
			}

			// If member has translation rights in this language and that the word is translatable propose a link to translate
			if (
			   	 (HasRight("Words", ShortLangSentence($IdLanguage))) and
				 (HasRight("Words") >= 10) and
				 ( (!isset($rEnglish->donottranslate)or($rEnglish->donottranslate == "no") ) )
				) { // if members has translation rights
				$res = "<a target=\"_new\" href=admin/adminwords.php?IdLanguage=" . $IdLanguage . "&code=$code style=\"background-color:#ff6699;color:#660000;\" title=\"click to translate in " . ShortLangSentence($IdLanguage) . "\">$res</a>";
			}
		}

	} // end  If no translation found

	// Apply the parameters if any
	$res = sprintf($res, $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9, $pp10, $pp11, $pp12, $pp13);
	//	debug("code=<font color='red'>".$code."</font> IdLanguage=".$IdLanguage."<br> res=[<b>".$res."</b>]");
	return ($res);
} // end of wwinlang

function getBroadCastElement($wordCode, $languageId, $username = false, $email = false)
{
    $sentence = "";
    $rr = LoadRow("select SQL_CACHE Sentence,donottranslate from words where code='$wordCode' and IdLanguage='" . $languageId . "'");
    if (isset ($rr->Sentence)){
        $sentence = stripslashes($rr->Sentence);
    }

    if ($sentence == "") {
        $rEnglish = LoadRow("select SQL_CACHE Sentence,donottranslate from words where code='$wordCode' and IdLanguage=0");
        if (!isset ($rEnglish->Sentence)) {
            $sentence = $wordCode; // The code of the word will be return
        } else {
            $sentence = stripslashes($rEnglish->Sentence);
        }
    }
    if ($username) {
        // we prepare to send or display the send mail therefore change nls to <br>
        $sentence = nl2br($sentence);

        // backwards compatibility replace %s with username and %% with % (just in case someone
        // wants to send an old newsletter again
        $sentence = str_replace('%s', $username, $sentence);
        $sentence = str_replace('%%', '%', $sentence);

        // replace %username% with real username. allow some different writings.
        $sentence = str_replace('%UserName%', $username, $sentence);
        $sentence = str_replace('%username%', $username, $sentence);
        $sentence = str_replace('%Username%', $username, $sentence);
    }
    if ($email) {
        $sentence = str_replace('%emailaddress%', $email, $sentence);
        $sentence = str_replace('%Emailaddress%', $email, $sentence);
        $sentence = str_replace('%EmailAddress%', $email, $sentence);
    }
    return $sentence;
}
?>
