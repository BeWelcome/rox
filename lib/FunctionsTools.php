<?php
require_once "FunctionsCrypt.php";
require_once("rights.php");

//------------------------------------------------------------------------------
function LogVisit() {
	if (!isset ($_SESSION['idvisitor'])) {
		$idtext = "Agent=[" . $_SERVER['HTTP_USER_AGENT'] . "] lang=[" . $_SERVER['HTTP_ACCEPT_LANGUAGE'] . "]";
		$intip = ip2long($_SERVER['REMOTE_ADDR']);
		$rr = LoadRow("select * from visites where ip=" . $intip . " and idtext='" . addslashes($idtext) . "'");
		if ($rr) {
			$_SESSION['idvisitor'] = $rr->id;
			LogStr("Nouvelle Identification, Nouvelle session", "log");
		} else {
			$HTTP_REFERER = $_SERVER['HTTP_REFERER'];
			$qry = sql_query("insert into visites(ip,idtext,HTTP_REFERER) values($intip,'" . addslashes($idtext) . "','" . $HTTP_REFERER . "')");
			$_SESSION['idvisitor'] = mysql_insert_id();
			LogStr("Identification retrouv�e, Nouvelle session", "log");
		}

	}
} // end of LogVisit

//------------------------------------------------------------------------------
function LogStr($stext, $stype = "Log") {
	global $_SYSHCVOL;
	//  if (!isset($_SESSION['IdMember'])) LogVisit();
	if (!empty($_SESSION['IdMember']))
		$IdMember = $_SESSION['IdMember'];
	else
		$IdMember = 0; // Zeromember if no member in session
	if (isset ($_SERVER['REMOTE_ADDR']))
		$ip = $_SERVER['REMOTE_ADDR'];
	else
		$ip = "127.0.0.1"; // case its local host 
	$str = "insert into " . $_SYSHCVOL['ARCH_DB'] . ".logs(IpAddress,IdMember,Str,Type) values(" . ip2long($ip) . "," . $IdMember . ",'" . addslashes($stext) . "','" . $stype . "')";
	$qry = mysql_query($str);
	if (!$qry) {
		if (IsAdmin())
			echo "problem : LogStr \$str=$str<br>";
	}
} // end of LogStr

function ReplaceWithBR($ss,$ReplaceWith=false) {
		if (!$ReplaceWith) return ($ss);
		return(str_replace("\n","<br>",$ss));
}

// -----------------------------------------------------------------------------
// the trad corresponding to the current language of the user, or english, 
// or the one the member has set
function FindTrad($IdTrad,$ReplaceWithBr=false) {


	$AllowedTags = "<b><i><br>";
	if ($IdTrad == "")
		return ("");
	// Try default language
	$row = LoadRow("select SQL_CACHE Sentence from memberstrads where IdTrad=" . $IdTrad . " and IdLanguage=" . $_SESSION['IdLanguage']);
	if (isset ($row->Sentence)) {
		if (isset ($row->Sentence) == "") {
			LogStr("Blank Sentence for language " . $_SESSION['IdLanguage'] . " with MembersTrads.IdTrad=" . $IdTrad, "Bug");
		} else {
		   return (strip_tags(ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
		}
	}
	// Try default eng
	$row = LoadRow("select SQL_CACHE Sentence from memberstrads where IdTrad=" . $IdTrad . " and IdLanguage=0");
	if (isset ($row->Sentence)) {
		if (isset ($row->Sentence) == "") {
			LogStr("Blank Sentence for language 1 (eng) with memberstrads.IdTrad=" . $IdTrad, "Bug");
		} else {
		   return (strip_tags(ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
		}
	}
	// Try first language available
	$row = LoadRow("select  SQL_CACHE Sentence from memberstrads where IdTrad=" . $IdTrad . " order by id asc limit 1");
	if (isset ($row->Sentence)) {
		if (isset ($row->Sentence) == "") {
			LogStr("Blank Sentence (any language) memberstrads.IdTrad=" . $IdTrad, "Bug");
		} else {
		   return (strip_tags(ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
		}
	}
	return ("");
} // end of FindTrad



// -----------------------------------------------------------------------------
function HasFlag($FlagName, $_Scope = "", $OptionalIdMember = 0) 
{
	global $_SYSHCVOL;

	$rlevel=0; // by default no flag
	if (!IsLoggedIn())
		return (0); // No need to search for right if no member logged
	if ($OptionalIdMember != 0) {
		$IdMember = $OptionalIdMember;
	} else {
		$IdMember = $_SESSION['IdMember'];
	}

	$Scope = $_Scope;
	if ($Scope != "") {
		if ($Scope {
			0 }
		!= "\"")
		$Scope = "\"" . $Scope . "\""; // add the " " if they are missing 
	}

	if ((!isset ($_SESSION['FlagLevel_' . $FlagName])) or ($_SYSHCVOL['ReloadRight'] == 'True') or ($OptionalIdMember != 0)) {
		$str = "select SQL_CACHE Scope,Level from flagsmembers,flags where IdMember=$IdMember and flags.id=flagsmembers.IdFlag and flags.Name='$FlagName'";
		$qry = mysql_query($str) or die("function HasFlag");
		$Flag = mysql_fetch_object(mysql_query($str)); // LoadRow not possible because of recusivity
		if (!isset ($Flag->Level))
			return (0); // Return false if the Flag does'nt exist for this member in the DB
		$rlevel = $Flag->Level;
		$rscope = $Flag->Scope;
		if ($OptionalIdMember == 0) { // if its current member cache for next research 
			$_SESSION['FlagLevel_' . $FlagName] = $rlevel;
			$_SESSION['FlagScope_' . $FlagName] = $rscope;
		}
	}
	else {
		$rlevel=$_SESSION['FlagLevel_' . $FlagName];
	}
	if ($Scope != "") { // if a specific scope is asked
		if ((!(strpos($rscope, $Scope) === false)) or ($Scope == $rscope)) {
			return ($rlevel);
		} else
			return (0);
	} else {
		return ($rlevel);
	}
} // end of HasFlag


//------------------------------------------------------------------------------
// This function return the name of a country according to the IdCountry parameter
function getcountryname($IdCountry) {
	$rr = LoadRow("select  SQL_CACHE Name from countries where id=" . $IdCountry);
	return ($rr->Name);
}

//------------------------------------------------------------------------------
// This function return the name of a city according to the IdCity parameter
function getcityname($IdCity) {
	$rr = LoadRow("select  SQL_CACHE Name from cities where id=" . $IdCity);
	return ($rr->Name);
}

//------------------------------------------------------------------------------
// This function return the name of a region according to the IdRegion parameter
function getregionname($IdRegion) {
	if (empty($IdRegion)) { // let consider that in some case members can have a city without region 
	   return(ww("NoRegionDefined")) ;
	}
	$rr = LoadRow("select  SQL_CACHE Name from regions where id=" . $IdRegion);
	return ($rr->Name);
}

//------------------------------------------------------------------------------
// This function return the id of a region according to the IdCity parameter
function GetIdRegionForCity($IdCity) {
	$rr = LoadRow("select  SQL_CACHE IdRegion from cities where id=". $IdCity);
	return ($rr->IdRegion);
}

//------------------------------------------------------------------------------
function ProposeCountry($Id = 0, $form = "signup") {
	$ss = "";
	$str = "select  SQL_CACHE id,Name from countries order by Name";
	$qry = sql_query($str);
	$ss = "\n<select name=IdCountry onChange=\"change_country('" . $form . "');\">\n";
	$ss .= "<option value=0>" . ww("MakeAChoice") . "</option>\n";
	while ($rr = mysql_fetch_object($qry)) {
		$ss .= "<option value=" . $rr->id;
		if ($rr->id == $Id)
			$ss .= " selected";
		$ss .= ">";
		$ss .= $rr->Name;
		//			if ($rr->OtherNames!="")	$ss.=" (".$rr->OtherNames.")";
		$ss .= "</option>\n";
	}
	$ss .= "\n</select>\n";

	return ($ss);
} // end of ProposeCountry

//------------------------------------------------------------------------------
function ProposeRegion($Id = 0, $IdCountry = 0, $form = "signup") {
	if ($IdCountry == 0) {
		return ("\n<input type=hidden name=IdRegion Value=0>\n");
	}
	$ss = "";
	$str = "select SQL_CACHE id,Name,OtherNames,NbCities from regions where IdCountry=" . $IdCountry . " and NbCities>0 order by Name";
	$qry = sql_query($str);
	$ss = "\n<select name=IdRegion onChange=\"change_region('" . $form . "')\">\n";
	$ss .= "<option value=0>" . ww("MakeAChoice") . "</option>\n";
	while ($rr = mysql_fetch_object($qry)) {
		$ss .= "<option value=" . $rr->id;
		if ($rr->id == $Id)
			$ss .= " selected";
		$ss .= ">";
		$ss .= $rr->Name;
		if (IsAdmin()) $ss.="(".$rr->NbCities.")";
//		if ($rr->OtherNames!="")	$ss.=" (".$rr->OtherNames.")";
		$ss .= "</option>\n";
	}
	$ss .= "\n</select>\n";

	return ($ss);
} // end of ProposeRegion

//------------------------------------------------------------------------------
// this function propose a city according to preselected region
// or to CityName and preselected country if any 
function ProposeCity($Id = 0, $IdRegion = 0,$form="signup",$CityName="",$IdCountry=0) {
	$ss="\n<input type=hidden name=IdCity Value=0>\n";
	if ($CityName!="") {
//	    $str = "select SQL_CACHE id,Name,OtherNames from cities where IdRegion=" . $IdRegion . " and ActiveCity='True' order by Name";
//		$str = "select SQL_CACHE cities.id,cities.Name,cities.OtherNames,regions.name as RegionName from (cities) left join regions on (cities.IdRegion=regions.id) where  cities.IdCountry=" . $IdCountry . " and ActiveCity='True' and cities.Name like '".$CityName."%' order by cities.population desc";
		$str = "select SQL_CACHE cities.id,cities.Name,cities.OtherNames,IdRegion from cities where  cities.IdCountry=" . $IdCountry . " and ActiveCity='True' and cities.Name like '".$CityName."%' order by cities.population desc";
	}
	else {
		return($ss) ;
	}
//	if (IsAdmin()) echo "<br>".$str."<br>" ;
	$qry = sql_query($str);
	$ss = "\n<br><select name=IdCity>\n";
	if ($CityName == "") {
	    $ss .= "<option value=0>" . ww("MakeAChoice") . "</option>\n";
	}
	while ($rr = mysql_fetch_object($qry)) {
	  if ($rr->IdRegion>0) {
			 $rr->RegionName=getregionname($rr->IdRegion) ;
		}
		else {
			 $rr->RegionName="" ;
		}
		$ss .= "<option value=" . $rr->id;
		if ($rr->id == $Id)
			$ss .= " selected";
		$ss .= ">";
		$ss .= $rr->Name;
//		if ($rr->OtherNames!="")	$ss.=" (".$rr->OtherNames.")";
		if (isset($rr->RegionName)) $ss.=" ".$rr->RegionName ;
		$ss .= "</option>\n";
	}
	$ss .= "\n</select>\n";

	return ($ss);
} // end of ProposeCity

//------------------------------------------------------------------------------
// CheckEmail return true if the email looks valid
function CheckEmail($email) {
	if (!ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+' .
		'@' .
		'[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.' .
		'[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $email)) {
		return (false);

	} else {
		return (true); // email ok
	}

}

// -----------------------------------------------------------------------------
// hc_mail is a function to centralise all mail send thru HC 
function bw_mail($to, $the_subject, $text, $hh = "", $FromParam = "", $IdLanguage = 0, $PreferenceHtmlEmail = "yes", $LogInfo = "", $replyto = "",$Greetings="") {
	return bw_sendmail($to, $the_subject, $text, "", $hh, $FromParam, $IdLanguage, $PreferenceHtmlEmail, $LogInfo, $replyto,$Greetings);
}

// -----------------------------------------------------------------------------
// bw_sendmail is a function to centralise all mail send thru HC with more feature 
// $to = email of receiver
// $mail_subject=subject of mail
// $text = text of mail
// $textinhtml = text in html will be use if user preference are html
// $From= from mail (will also be the reply to)
// $deflanguage : d�fault language of receiver
// $PreferenceHtmlEmail : if set to yes member will receive mail in html format, note that it will be force to html if text contain ";&#"
// $LogInfo = used for debugging

function bw_sendmail($to, $mail_subject, $text, $textinhtml = "", $hh = "", $_FromParam = "", $IdLanguage = 0, $PreferenceHtmlEmail = "yes", $LogInfo = "", $replyto = "",$ParamGreetings="") {
	global $_SYSHCVOL;
	if (isset($_SESSION['verbose'])) {
	   $verbose=$_SESSION['verbose'];
	}
	else {
	   $verbose = false;
	}
//	if (IsAdmin())  $verbose=1; // set to one for a verbose function
	$FromParam = $_FromParam;
	if ($_FromParam == "")
		$FromParam = $_SYSHCVOL['MessageSenderMail'];

	$From = $FromParam;

	$text = str_replace("<br />", "", $text);

	//	nl2br_inv($text);	// neutralize the nl2br() of ww() and wwinlang()
	$text = str_replace("\r\n", "\n", $text); // solving the century-bug: NO MORE DAMN TOO MANY BLANK LINES!!!

	$use_html = $PreferenceHtmlEmail;
	if ($use_html=="html") $use_html="yes";
	if ($verbose)
		echo "<br>use_html=[" . $use_html . "] mail to $to<br>\n\$_SERVER['SERVER_NAME']=", $_SERVER['SERVER_NAME'], "<br>\n";
	if (stristr($text, ";&#") != false) { // if there is any non ascii file, force html
		if ($verbose)
			echo "<br>1 <br>\n";
		if ($use_html != "yes") {
			if ($verbose)
				echo "<br> no html 2<br>\n";
			$use_html = "yes";
			if ($LogInfo == "") {
				LogStr("Forcing HTML for message to $to", "hcvol_mail");
			} else {
				LogStr("Forcing HTML <b>$LogInfo</b>", "hchcvol_mail");
			}
		}
	}

	$headers = $hh;
	if (!(strstr($headers, "From:")) and ($From != "")) {
		$headers = $headers . "From:" . $From . "\n";
	}
	$headers .= "MIME-Version: 1.0\nContent-type: text/html; charset=utf-8\n";
	if (($use_html == "yes") or (strpos($text, "<html>") !== false)) { // if html is forced or text is in html then add the MIME header
		if ($verbose)
			echo "<br>3<br>";
		$use_html = "yes";
	}

	//	$headers .= "To: $to\n";
	//	$headers .= "Subject: $mail_subject\n";
	//	$headers .= "Return-Path: $From\n";
	//	$headers .= "Organization: " . $_SYSHCVOL['SiteName']."\n";

	if ($replyto != "") {
		$headers = $headers . "Reply-To:" . $replyto;
	}
	if (!(strstr($headers, "Reply-To:")) and ($From != "")) {
		$headers = $headers . "Reply-To:" . $From;
	}
	elseif (!strstr($headers, "Reply-To:")) {
		$headers = $headers . "Reply-To:" . $_SYSHCVOL['MessageSenderMail'];
	}
	$headers .= "\nX-Mailer:PHP"; // mail of client			

	if ($ParamGreetings=="") {
		$Greetings=wwinlang('HCVolMailSignature', $IdLanguage);
	}
	else {
		$Greetings=$ParamGreetings;
	}
	if ($use_html == "yes") {
		if ($verbose)
			echo "<br>4<br>\n";
		if ($textinhtml != "") {
			if ($verbose)
				echo "<br>5 will use text in html paramameter<br>";
			$texttosend = $textinhtml;
		} else {
			if ($verbose)
				echo "<br>6<br>\n";
			$texttosend = $text;
		}
		if (strpos($texttosend, "<html>") === false) { // If not allready html
			if ($verbose)
				echo "<br>7<br>";
			$realtext = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n" . "<html>\n<head>\n<title>" . $mail_subject . "</title>\n</head>\n<body bgcolor=#ffffcc>\n" . str_replace("\n", "<br>", $texttosend) .
			$realtext .= "<br>\n<font color=blue>" . $ParamGreetings . "</font>";
			$realtext .= "\n</body>\n</html>";
		} else {
			if ($verbose)
				echo "<br>8<br>\n";
			$realtext = "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.0 Transitional//EN\">\n" . $texttosend; // In this case, its already in html
		}
	} else {
		if ($verbose)
			echo "<br>9 <br>\n";
		$text .= "\n" .$ParamGreetings;
		$realtext = str_replace("<br>", "\n", $text);
	}

	if ($verbose)
		echo "<br>10 " . nl2br($realtext) . "<br>\n";

	if ($verbose)
		echo "<br>11 " . nl2br($realtext) . "<br>\n";
	if ($verbose)
		echo "<br>12 " . $realtext . "<br>\n";

	// Debugging trick	
	if ($verbose) {
		echo "<table bgcolor=#ffff99 cellspacing=3 cellpadding=3 border=2><tr><td>";
		echo "\$From:<font color=#6633ff>$From</font> \$To:<font color=#6633ff>$to</font><br>";
		echo "\$mail_subject:<font color=#6633ff><b>", $mail_subject, "</b></font></td>";
		$ss = $headers;
		echo "<tr><td>\$headers=<font color=#ff9933>";
		for ($ii = 0; $ii < strlen($ss); $ii++) {
			//			echo "\$ss[$ii]=",ord($ss{$ii})," [",$ss{$ii},"]<br>";
			$jj = ord($ss {
				$ii });
			if ($jj == 10) {
				echo "\\n<br>";
			}
			elseif ($jj == 13) {
				echo "\\r";
			} else {
				echo chr($jj);
			}
		}
		echo "</font></td>";
		echo "<tr><td><font color=#6633ff>", htmlentities($realtext), "</font></td>";
		if ($use_html == "yes")
			echo "<tr><td>$realtext</td>";
		echo "</table><br>";
	} // end of for $ii
	// end of debugging trick

	// remove new line in $mail_subject because it is not accepted
	if ($verbose)
		echo "<br>13 removing extra \\n from \$mail_subject<br>\n";
	for ($ii = 0; $ii < strlen($mail_subject); $ii++) {
		//	  echo $ii,"-->",$mail_subject{$ii}," ",ord($mail_subject{$ii}),"<br>";;
		if ((ord($mail_subject {
			$ii }) < 32) or (ord($mail_subject {
			$ii }) > 255)) {
			$mail_subject {
				$ii }
			= " ";
			if ($verbose) echo "One weird char removed in subject at ", $ii, " position<br>\n";
		}
	}

	if ($_SERVER['SERVER_NAME'] == 'localhost') { // Localhost don't send mail
		return ("<br><b><font color=blue>" . $mail_subject . "</font></b><br><b><font color=blue>" . $realtext . "</font></b><br>" . " not sent<br>");
	}
	elseif (($_SERVER['SERVER_NAME'] == 'ns20516.ovh.net') or (($_SERVER['SERVER_NAME'] == 'www.hcvolunteers.org')) or (($_SERVER['SERVER_NAME'] == 'www.bewelcome.org'))) {
		$ret = mail($to, $mail_subject, $realtext, $headers, "-" . $_SYSHCVOL['ferrorsSenderMail']);
		//	  $ret=mail($to,$mail_subject,$realtext,$headers) ;
		if ($verbose) {
			echo "<br>14 <br>\n";
			echo "headers:\n";
			print_r($headers);
			echo "\n<br>to=", $to, "<br>\n";
			echo "subj=", $mail_subject, "<br>";
			echo "text :<i>", htmlentities($realtext), "</i><br>\n";
			echo " \$ret=", $ret, "<br>\n";
		}
		//		echo "Mail sent to $to<br>";
		return ($ret);
	}
} // end of hcvol_sendmail

//------------------------------------------------------------------------------
//
function debug($s1 = "", $s2 = "", $s3 = "", $s4 = "", $s5 = "", $s6 = "", $s7 = "", $s8 = "", $s9 = "", $s10 = "", $s11 = "", $s12 = "") {
	debug_print_backtrace();
	echo $s1 . $s2 . $s3 . $s4 . $s5 . $s6 . $s7 . $s8 . $s9 . $s10 . $s11 . $s12 . "<br>";
}

//------------------------------------------------------------------------------
// InsertInMTrad allow to insert a string in MemberTrad table
// It returns the IdTrad of the created record 
function InsertInMTrad($ss, $_IdMember = 0, $_IdLanguage = -1, $IdTrad = -1) {
	if ($_IdMember == 0) { // by default it is current member
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $_IdMember;
	}

	if ($_IdLanguage == -1)
		$IdLanguage = $_SESSION['IdLanguage'];
	else
		$IdLanguage = $_IdLanguage;

	if ($IdTrad == -1) { // if a new IdTrad is needed
		// Compute a new IdTrad
		$rr = LoadRow("select max(IdTrad) as maxi from memberstrads");
		if (isset ($rr->maxi)) {
			$IdTrad = $rr->maxi + 1;
		} else {
			$IdTrad = 1;
		}
	}

	$IdOwner = $IdMember;
	$IdTranslator = $_SESSION['IdMember']; // the recorded translator will always be the current logged member
	$Sentence = $ss;
	$str = "insert into memberstrads(IdLanguage,IdOwner,IdTrad,IdTranslator,Sentence,created) ";
	$str .= "Values(" . $IdLanguage . "," . $IdOwner . "," . $IdTrad . "," . $IdTranslator . ",\"" . $Sentence . "\",now())";
	sql_query($str);
	//	echo "::InsertInMTrad IdTrad=",$IdTrad," str=",$str,"<hr>";
	return ($IdTrad);
} // end of InsertInMTrad

//------------------------------------------------------------------------------
// ReplaceInMTrad insert or replace the value corresponding to $IdTrad in member Trad
// if ($IdTrad==0) then a new record is inserted
// It returns the IdTrad of the created record 
function ReplaceInMTrad($ss, $IdTrad = 0, $IdOwner = 0) {
	if ($IdOwner == 0) {
		$IdMember = $_SESSION['IdMember'];
	} else {
		$IdMember = $IdOwner;
	}
	//  echo "in ReplaceInMTrad \$ss=[".$ss."] \$IdTrad=",$IdTrad," \$IdOwner=",$IdMember,"<br>";
	$IdLanguage = $_SESSION['IdLanguage'];
	if ($IdTrad == 0) {
		return (InsertInMTrad($ss, $IdMember)); // Create a full new translation
	}
	$IdTranslator = $_SESSION['IdMember']; // the recorded translator will always be the current logged member
	$str = "select * from memberstrads where IdTrad=" . $IdTrad . " and IdOwner=" . $IdMember . " and IdLanguage=" . $IdLanguage;
	$rr = LoadRow($str);
	if (!isset ($rr->id)) {
		//	  echo "[$str] not found so inserted <br>";
		return (InsertInMTrad($ss, $IdMember, $IdLanguage, $IdTrad)); // just insert a new record in memberstrads in this new language
	} else {
		if ($ss != addslashes($rr->Sentence)) { // Update only if sentence has changed
			MakeRevision($rr->id, "memberstrads"); // create revision
			$str = "update memberstrads set IdTranslator=" . $IdTranslator . ",Sentence='" . $ss . "' where id=" . $rr->id;
			sql_query($str);
		}
	}
	return ($IdTrad);
} // end of ReplaceInMTrad



//------------------------------------------------------------------------------ 
// Get param returns the param value (in get or post) if any
function GetParam($param, $defaultvalue = "") {
	if (isset ($_GET[$param])) {
	    $m=$_GET[$param];
	}
	if (isset ($_POST[$param])) {
	    $m=$_POST[$param];
	}

	if ((empty($m)) and ($m!="0")) // a "0" string must return 0 for the House Number for exemple 
		return ($defaultvalue); // Return defaultvalue if none

	$m=mysql_real_escape_string($m);
	$m=str_replace("\\n","\n",$m);
	$m=str_replace("\\r","\r",$m);
	if (strpos($m," or ")) {
			LogStr("Warning ! trying to use a <b>".addslashes($m)."</b> in a param $param for ".$_SERVER["PHP_SELF"], "alarm");
	}
	if (empty($m)) 
		return ($defaultvalue); // Return defaultvalue if none
	else  return ($m); // Return translated value
} // end of GetParam


//------------------------------------------------------------------------------ 
// this function return the count of whoisonline members
function CountWhoIsOnLine() {
	global $_SYSHCVOL;
	$rr = LoadRow("select count(*) as cnt from online where online.updated>DATE_SUB(now(),interval " . $_SYSHCVOL['WhoIsOnlineDelayInMinutes'] . " minute) and online.Status='Active'");
	$_SESSION['WhoIsOnlineCount'] = $rr->cnt;
	return ($_SESSION['WhoIsOnlineCount']);
} // end of CountWhoIsOnLine

//------------------------------------------------------------------------------ 
// function EvaluateMyEvents()  evaluate several events :
// - not read message
function EvaluateMyEvents() {
	global $_SYSHCVOL;
	if (!IsLoggedIn())
		return; // if member not identified, no evaluation needed
	if ($_SYSHCVOL['EvaluateEventMessageReceived'] == "Yes") {
		$IdMember = $_SESSION['IdMember'];
		$str = "select count(*) as cnt from messages where IdReceiver=" . $IdMember . " and WhenFirstRead='0000-00-00 00:00:00' and (not FIND_IN_SET('receiverdeleted',DeleteRequest))  and Status='Sent'";
		//		echo "str=$str<br>";
		$rr = LoadRow($str);

		$_SESSION['NbNotRead'] = $rr->cnt;
	} else {
		$_SESSION['NbNotRead'] = 0;
	}

	if ($_SYSHCVOL['WhoIsOnlineActive'] == "Yes") { // Keep upto date who is online if it is active
		$str = "replace into online set IdMember=" . $IdMember . ",appearance='" . fUsername($IdMember) . "',lastactivity='" . $_SERVER["PHP_SELF"] . "',Status='" . $_SESSION["Status"] . "'";
		sql_query($str);
		CountWhoIsOnLine();
		// Check if record was beaten
		$params = LoadRow("select SQL_CACHE * from params");
		if ($_SESSION['WhoIsOnlineCount'] > $params->recordonline) {
			LogStr("New record broken " . $_SESSION['WhoIsOnlineCount'] . " members online !", "Record");
			$str = "update params set recordonline=" . $_SESSION['WhoIsOnlineCount'];
			sql_query($str);
		}
	} else {
		$_SESSION['WhoIsOnlineCount'] = "###"; // Not activated
	}
	return;
} // end of EvaluateMyEvents()

//------------------------------------------------------------------------------ 
// function LinkWithUsername build a link with Username to the member profile 
// optional parameter status can be used to alter the link
function LinkWithUsername($Username, $Status = "") {
	return ("<a href=\"".bwlink("member.php?cid=$Username")."\">$Username</a>");
} // end of LinkWithUsername

//------------------------------------------------------------------------------ 
// function LinkWithGroup build a link with Group to the goup page 
// optional parameter status can be used to alter the link
function LinkWithGroup($groupname, $Status = "") {
	$IdGroup=IdGroup($groupname);
	if (is_numeric($groupname)) {
	   $rr=LoadRow("select SQL_CACHE * from groups where id=".$groupname);
	   $groupname=$rr->Name;
	}
	return ("<a href=\"".bwlink("group.php?action=ShowMembers&IdGroup=".$IdGroup."")."\">".ww("Group_" .$groupname)."</a>");
} // end of LinkWithGroup

//------------------------------------------------------------------------------ 
// function LinkWithPicture build a link with picture and Username to the member profile 
// optional parameter status can be used to alter the link
function LinkWithPicture($Username, $Photo, $Status = "") {
	return "<a href=\"".bwlink("member.php?cid=$Username").
		"\" title=\"" . ww("SeeProfileOf", $Username) . 
		"\">\n<img src=\"". bwlink($Photo). "\" height=\"100px\" ></a>\n";
} // end of LinkWithPicture

//------------------------------------------------------------------------------ 
// function CreateKey compute a nearly unique key according to parameters 
function CreateKey($s1, $s2, $IdMember = "", $ss = "default") {
	$key = sprintf("%X", crc32($s1 . " " . $s2 . " " . $IdMember . "_" . $ss)); // compute a nearly unique key
	return ($key);
} // end of CreateKey

//------------------------------------------------------------------------------ 
// function LinkEditWord display a link to edit the word $code in language $IdLanguage
// if $ll is not specified then default language will be used  
function LinkEditWord($code, $_IdLanguage = -1) {
	$IdLanguage = $_IdLanguage;
	if ($IdLanguage == -1) {
		$IdLanguage = $_SESSION["IdLanguage"];
	}
	$str = "<a href=\"".bwlink("admin/adminwords.php?IdLanguage=" . $IdLanguage . "&code=$code")."\">edit</a>";
	return ($str);
} // end of LinkEditWord

//------------------------------------------------------------------------------ 
// function IdMember return the numeric id of the member according to its username
// This function will TARNSLATE the username if the profile has been renamed.
// Note that a numeric username is provided no Username trnslation will be made
function IdMember($username) {
	if (is_numeric($username)) { // if already numeric just return it
		return ($username);
	}
	$rr = LoadRow("select SQL_CACHE id,ChangedId,Username from members where Username='" . $username . "'");
	if ($rr->ChangedId > 0) { // if it is a renamed profile
		$rRenamed = LoadRow("select SQL_CACHE id,Username from members where id=" . $rr->ChangedId);
		$rr->id = IdMember($rRenamed->Username); // try until a not renamde profile is found
	}
	if (isset ($rr->id)) {
		return ($rr->id);
	}
	return (0);
} // end of IdMember

//------------------------------------------------------------------------------ 
// function IdGroup return the numeric id of the group according to its parameter
// Note that a numeric IdGroup is provided no IdGroup translation will be made
function IdGroup($IdGroup) {
	if (is_numeric($IdGroup)) { // if already numeric just return it
		return ($IdGroup);
	}
	$rr = LoadRow("select SQL_CACHE id from groups where Name='" . $IdGroup . "'");
	if (isset ($rr->id)) {
		return ($rr->id);
	}
	return (0);
} // end of IdGroup

//------------------------------------------------------------------------------ 
// function fUsername return the Username of the member according to its id
function fUsername($cid) {
	if (!is_numeric($cid))
		return ($cid); // If cid is not numeric it is assumed to be already a username
	if ($cid == $_SESSION["IdMember"])
		return ($_SESSION["Username"]);
	$rr = LoadRow("select SQL_CACHE username from members where id=" . $cid);
	if (isset ($rr->username)) {
		return ($rr->username);
	}
	return ("");
} // end of fUsername

//------------------------------------------------------------------------------
// MakeRevision this function save a copy of current value of record Id in table
// TableName for member IdMember with DoneBy reason
function MakeRevision($Id, $TableName, $IdMemberParam = 0, $DoneBy = "DoneByMember") {
	global $_SYSHCVOL;
	$IdMember = $IdMemberParam;
	if ($IdMember == 0)
		$IdMember = $_SESSION["IdMember"];
	$qry = sql_query("select * from " . $TableName . " where id=" . $Id);
	$count = mysql_num_fields($qry);
	$rr = mysql_fetch_object($qry);

	$XMLstr = "";
	for ($ii = 0; $ii < $count; $ii++) {
		$field = mysql_field_name($qry, $ii);
		$XMLstr .= "<field>" . $field . "</field>\n";
		$XMLstr .= "<value>" . $rr-> $field . "</value>\n";
	}
	$str = "insert into " . $_SYSHCVOL['ARCH_DB'] . ".previousversion(IdMember,TableName,IdInTable,XmlOldVersion,Type) values(" . $IdMember . ",'" . $TableName . "'," . $Id . ",'" . addslashes($XMLstr) . "','" . $DoneBy . "')";
	sql_query($str);
} // end of MakeRevision

//------------------------------------------------------------------------------
// Local date return the local date according to preference
// parameter $tt is a timestamp
function localdate($ttparam, $formatparam = "") {
	// todo apply local offset to $tt
	$tt = strtotime($ttparam);
	$format = $formatparam;
	if ($format == "") {
		$format = "%c";
	}
	return (strftime($format, $tt));
} // end of localdate

//------------------------------------------------------------------------------
// fage return a string describing the age correcponding to date 
function fage($dd, $hidden = "No") {
	if ($hidden != "No") {
		return (ww("AgeHidden"));
	}
	return (ww("AgeEqualX", fage_value($dd)));
} // end of fage

//------------------------------------------------------------------------------
// fage_value return a  the age value corresponding to date 
function fage_value($dd) {
	$iDate = strtotime($dd);
	$age = (time() - $iDate) / (365 * 24 * 60 * 60);
	return ($age);
} // end of fage_value

//------------------------------------------------------------------------------
// function fFullName return the FullName of the member with a special layout if some fields are crypted 
function fFullName($m) {
	return (PublicReadCrypted($m->FirstName, "*") . " " . PublicReadCrypted($m->SecondName, "*") . " " . PublicReadCrypted($m->LastName, "*"));
} // end of fFullName

//------------------------------------------------------------------------------
function GetPreference($namepref,$idm=0) {
	$IdMember=0;
   if ($idm==0) {
	   if ($_SESSION['IdMember']!="") $IdMember=$_SESSION['IdMember'];
	   
	}
	else {
		$IdMember=$idm;
	}
	if ($IdMember==0) {
	   $rr=LoadRow("select DefaultValue SQL_CACHE from preferences where codeName='".$namepref."'");
	   return($rr->DefaultValue);
	}
	else {
	   $rr = LoadRow("select SQL_CACHE Value from memberspreferences,preferences where preferences.codeName='$namepref' and memberspreferences.IdPreference=preferences.id and IdMember=" . $IdMember);
	   if (isset ($rr->Value))
		  $def = $rr->Value;
		else {
	   	  $rr=LoadRow("select DefaultValue SQL_CACHE from preferences where codeName='".$namepref."'");
	   	  if (isset($rr->DefaultValue))
	      	return($rr->DefaultValue);
	      else
	      	return NULL;
		}
	   return ($def);
	}
	
} // end of GetPreference

//------------------------------------------------------------------------------
// function GetDefaultLanguage return the default language of member $IdMember 
function GetDefaultLanguage($IdMember) {
	return(GetPreference("PreferenceLanguage"));
} // end of GetDefaultLanguage

//------------------------------------------------------------------------------
// function GetEmail return the email of member $IdMember (or current member if 0) 
function GetEmail($IdMemb = 0) {
	if ($IdMemb == 0)
		$IdMember = $_SESSION["IdMember"];
	else
		$IdMember = $IdMemb;
	$rr = LoadRow("select SQL_CACHE Email from members where id=" . $IdMember);
	if ($rr->Email > 0)
		return (AdminReadCrypted($rr->Email));
	else
		return "";
} // end of GetEmail

//------------------------------------------------------------------------------
// function GetEmail return the email of member $IdMember (or current member if 0) 
function LanguageName($IdLanguage) {
	$rr = LoadRow("select SQL_CACHE EnglishName,ShortCode from languages where id=" . $IdLanguage);
	return ($rr->EnglishName);
} // end of LanguageName

// return eng for english, ru for russian etc
function ShortLangSentence($IdLanguage) {
	$rr = LoadRow("select SQL_CACHE EnglishName,ShortCode from languages where id=" . $IdLanguage);
	return ($rr->ShortCode);
}

//------------------------------------------------------------------------------
// return the id of member ship in group $IdGroup for member $IdMember, or 0
function IdMemberShip($IdGroup, $IdMemb = 0) { // find the membership of the member

	if ($IdMemb == 0)
		$IdMember = $_SESSION["IdMember"];
	else
		$IdMember = $IdMemb;
	if (empty($IdMember)) return (0);
	$rr = LoadRow("select SQL_CACHE * from membersgroups where IdMember=" . $IdMember . " and IdGroup=" . $IdGroup);
	if (isset ($rr->id))
		return ($rr->id);
	else
		return (0);
} // end of IdMemberShip

//------------------------------------------------------------------------------
// Return true if the profile of the member is a public profile
function IsPublic($IdMember=0) {
   $rr=LoadRow("select * from memberspublicprofiles where  memberspublicprofiles.IdMember=".$IdMember);
	if (isset($rr->id)) return(true);
	else  return(false);
} // end of IsPublic

//------------------------------------------------------------------------------
// Return the number of minutes,jours,days,month or year since the parameter date
function fSince($dd) {
	$tt = time()-strtotime($dd);
	if ($tt<3600) {
	   $res=ceil($tt/60);
	   return ($res." minutes");
	}
	elseif ($tt<(3600*24)) {
	   $res=ceil($tt/3600);
	   return ($res." hours");
	}
	elseif ($tt<(3600*24*7)) {
	   $res=ceil($tt/(3600*24));
	   return ($res." days");
	}
	elseif ($tt<(3600*24*30.5)) {
	   $res=ceil($tt/(3600*24*7));
	   return ($res." weeks");
	}
	elseif ($tt<(3600*24*365)) {
	   $res=ceil($tt/(3600*24*30.5));
	   return ($res." months");
	}
   $res=ceil($tt/(3600*24*365));
   return ($res." years");
} // end of fSince

//------------------------------------------------------------------------------
// This function return a flag with the language
function FlagLanguage($IdLang=-1,$title="") {
	if (($IdLang==-1)or ($IdLang==$_SESSION["IdLanguage"])) {
	   $flag=$_SESSION['lang'].".png";
	}
	else {
		$rr=LoadRow("select SQL_CACHE * from languages where id=".$IdLang);
		$flag=$rr->ShortCode.".png";
	}
	return("<img height=\"11px\" src=\"images/".$flag."\" title=\"".$title."\">");
} // end of FlagLanguage

/**
 * print the error and die
 * @param string $errortext error text to be printed 
 */
function bw_error( $errortext )
{
	die("System error: ".$errortext);
}


// Thumbnail creator. (by markus5, Markus Hutzler 25.02.2007)
// tested with GD Version: bundled (2.0.28 compatible)
// with GIF Read Support: Enabled
// with JPG Support: Enabled
// with PNG Support: Enabled

// this function creates a thumbnail of a JPEG, GIF or PNG image
// file: path (with /)!!!
// max_x / max_y delimit the maximal size. default = 100 (it keeps the ratio)
// the quality can be set. default = 85
// this function returns an array. ['state','message']
// state: successful = true / error = false
// message: the error / success message
function create_thumb($file, $max_x = 100, $max_y = 100 ,$quality = 85)
{

  // TODO: analyze MIME-TYPE of the input file (not try / catch)
  // TODO: error analysis of wrong paths
  // TODO: dynamic prefix (now: /th/)
  
  $file = str_replace("\\","/",$file);
  
  $prefix = '/th/';
 
  // seperating the filename and path
	$slash_pos = strrpos($file, '/');
	if ($slash_pos === false)
	{
		$filename = $file;
		$path = '.';
	}
	else
	{
		$filename = substr($file,$slash_pos+1);
		$path = substr($file,0,$slash_pos);
	}
  		
  // seperating the filename and extention
  $dot_pos = strrpos($filename, '.');
	if ($dot_pos === false)
   		return array("state" => false, "message" => '"'.$filename.'" has no extension... I\'m confused!?!?!');
   	else
  		$filename_noext = substr($filename,0,$dot_pos);

  		
  // locate file
  if ( !is_file($file) )
  	return array("state" => false, "message" => 'no such file found'); 
  
  
  // read image
  $image = false;
  if (!$image) $image = @imagecreatefromjpeg($file);
  if (!$image) $image = @imagecreatefrompng($file);
  if (!$image) $image = @imagecreatefromgif($file);
  if($image == false)
  	return array("state" => false, "message" => 'file is not a supported image type');

  // calculate ratio
  $size_x = imagesx($image);
  $size_y = imagesy($image);
  if($size_x == 0 or $size_y == 0)
  	return array("state" => false, "message" => 'bad image size (0)');
  if (($max_x / $size_x) >= ($max_y / $size_y))
  	$ratio = $max_x / $size_x; 
  else
  	$ratio = $max_y / $size_y;
  	 
  $th_size_x = $size_x * $ratio;
  $th_size_y = $size_y * $ratio;
  
  // creating thumb
  $thumb = imagecreatetruecolor($th_size_x,$th_size_y);
  imagecopyresampled($thumb,$image,0,0,0,0,$th_size_x,$th_size_y,$size_x,$size_y);
  
  // try to write the new image 
  // TODO: dynamic prefix!!!!
  if(!is_dir('./th'))
  	return array("state" => false, "message" => 'no folder ./th!');         
  if(is_file($path.$prefix.$filename_noext.'.jpg'))
  	return array("state" => false, "message" => 'thumbnail-image already exists...');         

  imagejpeg( $thumb,$path.'/th/'.$filename_noext.'.jpg',$quality);
  return array("state" => true, "message" => 'output: '.$path.$prefix.$filename_noext.'.jpg');         

}