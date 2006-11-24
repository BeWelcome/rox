<?php


// This function set the new language parameters
function SwitchToNewLang($newlang) {
	if ((!isset($_SESSION['lang']))or($_SESSION['lang']!=$newlang)) { // Update lang if url lang has changed
	  $RowLanguage=LoadRow("select id,ShortCode from languages where ShortCode='".$newlang."'") ;
	  
		if (isset($RowLanguage->id)) {
	    LogStr("change to language from [".$_SESSION['lang']."] to [".$newlang."]","SwitchLanguage") ;
      $_SESSION['lang']=$RowLanguage->ShortCode ;
      $_SESSION['IdLanguage']=$RowLanguage->id ;
		}
		else {
	    LogStr("problem : ".$newlang." not found after SwitchLanguage","Bug") ;
      $_SESSION['lang']="eng" ;
      $_SESSION['IdLanguage']=0 ;
		}
	}
} // end of SwitchToNewLang



//------------------------------------------------------------------------------
// ww function will display the translation according to the code and the default language
Function ww($code, $p1=NULL, $p2=NULL, $p3=NULL, $p4=NULL, $p5=NULL, $p6=NULL, $p7=NULL, $p8=NULL, $p9=NULL, $pp10=NULL, $pp11=NULL, $pp12=NULL, $pp13=NULL) {
  global $Params ;

// If no language set default language
  if (!isset($_SESSION['IdLanguage'])) {
	  $_SESSION['lang']="eng" ; 	  
		$_SESSION['IdLanguage']=0 ; 
	}
  if ($_SESSION['lang']=="") {
	  $_SESSION['lang']="eng" ; 	  
		$_SESSION['IdLanguage']=0 ;
	}
	return(wwinlang ($code,$_SESSION['IdLanguage'], $p1, $p2, $p3, $p4, $p5, $p6, $p7, $p8, $p9, $pp10, $pp11, $pp12, $pp13));
} // end of ww

//------------------------------------------------------------------------------
// ww function will display the translation according to the code and the default language
Function wwinlang($code,$IdLanguage=0, $p1=NULL, $p2=NULL, $p3=NULL, $p4=NULL, $p5=NULL, $p6=NULL, $p7=NULL, $p8=NULL, $p9=NULL, $pp10=NULL, $pp11=NULL, $pp12=NULL, $pp13=NULL) {
	if ((isset($_SESSION['switchtrans'])) and ($_SESSION['switchtrans']=="on")) { // if user as choosen to build a translation list to use in AdminWords
//	  if (!$_SERVER['PHP_SELF']=="AdminWords") {
      if (!isset($_SESSION['TranslationArray'])) {
        $_SESSION['TranslationArray']=array() ; // initialize $_SESSION['TranslationArray'] if it wasent existing yet
		  }
	    if (!in_array($code,$_SESSION['TranslationArray'])) {
		    array_push($_SESSION['TranslationArray'],$code) ;
		  } 
//		}
	}

	$res="" ;
	if (empty($code)) {
		return("Empty field \$code in ww function") ;
	}
	if ((int)$code>0) { // case code is the idword
	  $rr=LoadRow("select SQL_CACHE Sentence from words where id=$code") ;
		$res=nl2br(stripslashes($rr->Sentence)) ;
	}
	if ($res=="") { // In case the word wasnt in the session variable
		$rr=LoadRow("select  SQL_CACHE Sentence from words where code='$code' and IdLanguage='".$IdLanguage."'") ;
    $res=nl2br(stripslashes($rr->Sentence)) ;
	}
	if ($res=="") {
		if ((int)$code>0) { // id word case
		  if (HasRight("Words",$IdLanguage)) {
				$res="<b>function ww() : idword #$code missing</b>" ;
			}
			else {
				$res=$code ;
			}
			return($res) ;
		}
		else {
			$rr=LoadRow("select SQL_CACHE Sentence from words where code='$code' and IdLanguage='".$IdLanguage."'") ;
			$res=nl2br(stripslashes($rr->Sentence)) ;
			if (HasRight("Words",$IdLanguage)) {
			  $rLang=LoadRow("select * from languages where id=".$IdLanguage) ; $Language=$rLang->ShortCode ; 
				$res.="<a  target=\"_new\" href=AdminWords.php?IdLanguage=".$IdLanguage."&code=$code><font size=1 color=red>click to define the word <font color=blue><font size=2>$code</font></font> in </font><b>".$Language."</b></a>" ;
			}
		}
		if (HasRight("Words",$IdLanguage)) {
		  $rLang=LoadRow("select * from languages where id=".$IdLanguage) ; $Language=$rLang->ShortCode ; 
		  $res="<a  target=\"_new\" href=AdminWords.php?IdLanguage=".$IdLanguage."&code=$code><font size=1 color=red>click to define the word <font color=blue><font size=2>$code</font></font> in </font><b>".$Language."</b></a>" ;
		}
		else {
		  if ($_SESSION['forcewordcodelink']==1) $res="<a  target=\"_new\" href=AdminWords.php?IdLanguage=".$IdLanguage."&code=$code><font size=1 color=red>click to define the word <font color=blue><font size=2>$code</font></font> </font></a>" ;
		  else $res=$code ;
		}
//		$res="<a href=AdminWords.php?search_lang=fr&search=$str&generate=check>click here to define $str</a>"
	}
	$res=sprintf($res,$p1,$p2,$p3,$p4,$p5,$p6,$p7,$p8,$p9,$p10,$p11,$p12,$p13) ;
//	debug("code=<font color=red>".$code."</font> IdLanguage=".$IdLanguage."<br> res=[<b>".$res."</b>]");
	return ($res) ;
} // end of wwinlang


//------------------------------------------------------------------------------
function IsAdmin() {
  return (HasRight('Admin')) ;
} // end of IsAdmin()


//------------------------------------------------------------------------------
// Just to read one row
//------------------------------------------------------------------------------
function LoadRow($str) {
//  echo "str=$str<br>" ;
	$qry=mysql_query($str) ;
	if (!$qry) {
		if (IsAdmin()) {
			echo "<br><font color=red>Warning message for Admin (only)<br>" ;
			debug ($_SERVER['PHP_SELF']."<br> : LoadRow error [".mysql_error()."]for <b>[".$str."]</b></font>") ;
		}
		else {
			error_log("LoadRow error in ".$_SERVER['PHP_SELF']."?".$_SERVER['QUERY_STRING']." <br> str=[".$str."]<br>") ;
//			LogStrTmp("LoadRow(".addslashes($str).") in ".$_SERVER['PHP_SELF'],"Debug") ; // No need already done by hc_mysl_query
		}
		$row=$str;
	}
	else{
		$row=mysql_fetch_object($qry) ;
	}
	return($row) ;
}




//------------------------------------------------------------------------------
function LogVisit() {
  if (!isset($_SESSION['idvisitor'])) {
	  $idtext="Agent=[".$_SERVER['HTTP_USER_AGENT']."] lang=[".$_SERVER['HTTP_ACCEPT_LANGUAGE']."]" ;
		$intip=ip2long( $_SERVER['REMOTE_ADDR']) ;
		$rr=LoadRow("select * from visites where ip=".$intip." and idtext='".addslashes($idtext)."'") ;
		if ($rr) {
		  $_SESSION['idvisitor']=$rr->id ;
			LogStr("Nouvelle Identification, Nouvelle session","log") ;
		}
		else {
		  $HTTP_REFERER=$_SERVER['HTTP_REFERER'] ;
		  $qry=mysql_query("insert into visites(ip,idtext,HTTP_REFERER) values($intip,'".addslashes($idtext)."','".$HTTP_REFERER."')") ;
		  $_SESSION['idvisitor']=mysql_insert_id() ;
			LogStr("Identification retrouvée, Nouvelle session","log") ;
		}

	}
} // end of LogVisit

//------------------------------------------------------------------------------
function LogStr($stext,$stype="Log") {
//  if (!isset($_SESSION['IdMember'])) LogVisit() ;
  if (isset($_SESSION['IdMember'])) $IdMember=$_SESSION['IdMember'] ;
	else $_SESSION['IdMember']=0 ; // Zeromember if no member in session 
	$str="insert delayed into logs(IpAddress,IdMember,Str,Type) values(".ip2long( $_SERVER['REMOTE_ADDR']).",".$IdMember.",'".addslashes($stext)."','".$stype."')" ;
  $qry=mysql_query($str) ;
	if (!$qry) {
  	if (IsAdmin()) echo "problem : LogStr \$str=$str<br>" ;
  }
} // end of LogStr


// -----------------------------------------------------------------------------
// Test if member as requested to change language
$newlang="" ;
if ((isset($_GET['lang'])) and ($_GET['lang']!='')) {
  SwitchToNewLang($_GET['lang']) ;
}
else if ((isset($_POST['lang'])) and ($_POST['lang']!='')) {
  SwitchToNewLang($_POST['lang']) ;
}
if (!isset($_SESSION['lang'])) {
  SwitchToNewLang("eng") ;
}	

// -----------------------------------------------------------------------------
// test if member use the switchtrans switch to record use of words on its page 
if ((isset($_GET['switchtrans'])) and ($_GET['switchtrans']!="")) {
  if (!isset($_SESSION['switchtrans'])) {
	  $_SESSION['switchtrans']="on" ;
	}
	else {
	  if ($_SESSION['switchtrans']=="on") {
	    $_SESSION['switchtrans']="off" ;
		}
		else {
	    $_SESSION['switchtrans']="on" ;
		}
	}
} // end of switchtrans

if (isset($_GET['forcewordcodelink'])) { // use to force a linj to each word 
                                         //code on display
  $_SESSION['forcewordcodelink']=$_GET['forcewordcodelink'] ;
}

// end of Test if member as requested to change language
// -----------------------------------------------------------------------------





// -----------------------------------------------------------------------------
// return true is the member is logged
function IsLogged() {

  if (!isset($_SESSION['IdMember']) or isset($_SESSION['IdMember'])==0) {
	  return(false) ;
	}

  if (!isset($_SESSION['MemberCryptKey']) or isset($_SESSION['MemberCryptKey'])=="") {
	  LogStr("IsLogged() : Anomaly with MemberCryptKey","Bug") ;
	  return(false) ;
	}

	if ($_SESSION['LogCheck']!=Crc32($_SESSION['MemberCryptKey'].$_SESSION['IdMember'])) {
	  LogStr("Anomaly with Log Check","Hacking") ;
		require_once("Login.php") ;
		Logout() ;
		exit(0) ;
	}
	return(true) ;
} // end of IsLogged

// -----------------------------------------------------------------------------
// the trad corresponding to the current language of the user, or english, 
// or the one the member has set
function FindTrad($IdTrad) {

// Try default language
  $row=LoadRow("select Sentence from memberstrads where IdTrad=".$IdTrad." and IdLanguage=".$_SESSION['IdLanguage']) ;
	if (isset($row->Sentence)) {
	  if (isset($row->Sentence)=="") {
		  LogStr("Blank Sentence for language ".$_SESSION['IdLanguage']." with MembersTrads.IdTrad=".$IdTrad,"Bug") ;
		}
		else {
		  return($row->Sentence) ;
		}
	}
// Try default eng
  $row=LoadRow("select Sentence from memberstrads where IdTrad=".$IdTrad." and IdLanguage=1") ;
	if (isset($row->Sentence)) {
	  if (isset($row->Sentence)=="") {
		  LogStr("Blank Sentence for language 1 (eng) with memberstrads.IdTrad=".$IdTrad,"Bug") ;
		}
		else {
		  return($row->Sentence) ;
		}
	}
// Try first language available
  $row=LoadRow("select Sentence from memberstrads where IdTrad=".$IdTrad." order by id asc limit 1") ;
	if (isset($row->Sentence)) {
	  if (isset($row->Sentence)=="") {
		  LogStr("Blank Sentence (any language) memberstrads.IdTrad=".$IdTrad,"Bug") ;
		}
		else {
		  return($row->Sentence) ;
		}
	}
	return("Empty MembersTrads for IdTrad=".$IdTrad)  ;
} // end of FindTrad

// -----------------------------------------------------------------------------
// return the RightLevel if the members has the Right RightName 
// optional Scope value can be send if the RightScope is set to All then Scope
// will alawys match if not, the sentence in Scope must be find in RightScope
// The funsction will use a cache in session
//   $_SYSHCVOL['ReloadRight']=='True' is used to force RightsReloading
//  fro scope beware to the "" which must exist in the mysal table but NOT in 
// the $Scope parameter 
function HasRight($RightName,$Scope="") {
  if (!isset($_SESSION['IdMember'])) return(0) ; // No need to search for right if no memebr logged
  $IdMember=$_SESSION['IdMember'] ;
  if ((!isset($_SESSION['Right_'.$RightName]))or ($_SYSHCVOL['ReloadRight']=='True')) {
	  $str="select Scope,Level from rightsvolunteers,rights where IdMember=$IdMember and rights.id=rightsvolunteers.IdRight and rights.Name='$RightName'" ;
    $qry=mysql_query($str) or die("function HasRight : Sql error for ".$str) ;
	  $right=mysql_fetch_object(mysql_query($str)) ; // LoadRow not possible because of recusivity
		if (!isset($right->Level)) return(0) ; // Return false if the Right does'nt exist for this member in the DB 
	  $_SESSION['RightLevel_'.$RightName]=$right->Level ;
	  $_SESSION['RightScope_'.$RightName]=$right->Scope ;
	}
	if ($Scope!="") { // if a specific scope is asked
	  if ($_SESSION['RightScope_'.$RightName]=="All") return($_SESSION['RightLevel_'.$RightName]) ;
		else {
		  if (strpos($_SESSION['RightScope_'.$RightName],"\"".$RightScope."\""))  return($_SESSION['RightLevel_'.$RightName]) ;
			else return(0) ;
		} 
	}
	else {
	  return($_SESSION['RightLevel_'.$RightName]) ;
	}
} // enf of HasRight

// -----------------------------------------------------------------------------
// return the Scope in the specific right 
// The funsction will use a cache in session
//   $_SYSHCVOL['ReloadRight']=='True' is used to force RightsReloading
//  fro scope beware to the "" which must exist in the mysal table but NOT in 
// the $Scope parameter 
function RightScope($RightName,$Scope="") {
  if (!isset($_SESSION['IdMember'])) return(0) ; // No ned to search for right if no memebr logged
  $IdMember=$_SESSION['IdMember'] ;
  if ((!isset($_SESSION['Right_'.$RightName]))or ($_SYSHCVOL['ReloadRight']=='True')) {
	  $str="select Scope,Level from rightsvolunteers,rights where IdMember=$IdMember and rights.id=rightsvolunteers.IdRight and rights.Name='$RightName'" ;
    $qry=mysql_query($str) or die("function HasRight : Sql error for ".$str) ;
	  $right=mysql_fetch_object(mysql_query($str)) ; // LoadRow not possible because of recusivity
		if (!isset($right->Level)) return(0) ; // Return false if the Right does'nt exist for this member in the DB 
	  $_SESSION['RightLevel_'.$RightName]=$right->Level ;
	  $_SESSION['RightScope_'.$RightName]=$right->Scope ;
	}
	return($_SESSION['RightScope_'.$RightName]) ;
} // enf of Scope

//------------------------------------------------------------------------------
function ProposeCountry($Id=0) {
  $ss="" ;
	$str="select id,Name from countries order by Name" ;
	$qry=mysql_query($str) ;
	$ss="<select name=IdCountry>\n" ;
	while ($rr=mysql_fetch_object($qry)) {
	  $ss.="<option value=".$rr->id ;
		if ($rr->id==$Id) $ss.=" selected" ;
		$ss.=">" ;
		$ss.=$rr->Name ;
//			if ($rr->OtherNames!="")	$ss.=" (".$rr->OtherNames.")" ;
		$ss.="</option>" ;
	}
	$ss.="\n</select>" ;
		
	return($ss) ;
} // end of ProposeCountry
//------------------------------------------------------------------------------
function ProposeRegion($Id=0,$IdCountry=0) {
  if ($IdCountry==0) {
	  $ss="<input type=submit name=action value=\"".ww('SubmitChooseRegion')."\">" ;
		return($ss) ;
	}
  $ss="" ;
	$str="select id,Name,OtherNames from regions where IdCountry=".$IdCountry." order by Name" ;
	$qry=mysql_query($str) ;
	$ss="<select name=IdRegion>\n" ;
	while ($rr=mysql_fetch_object($qry)) {
	  $ss.="<option value=".$rr->id ;
		if ($rr->id==$Id) $ss.=" selected" ;
		$ss.=">" ;
		$ss.=$rr->Name ;
//		if ($rr->OtherNames!="")	$ss.=" (".$rr->OtherNames.")" ;
		$ss.="</option>" ;
	}
	$ss.="\n</select>" ;
		
	return($ss) ;
} // end of ProposeRegion
//------------------------------------------------------------------------------
function ProposeCity($Id=0,$IdRegion=0) {
  if ($IdRegion==0) {
	  $ss="<input type=submit name=action value=\"".ww('SubmitChooseCity')."\">" ;
		return($ss) ;
	}
  $ss="" ;
	$str="select id,Name,OtherNames from cities where IdRegion=".$IdRegion." order by Name" ;
	$qry=mysql_query($str) ;
	$ss="<select name=IdCity>\n" ;
	while ($rr=mysql_fetch_object($qry)) {
	  $ss.="<option value=".$rr->id ;
		if ($rr->id==$Id) $ss.=" selected" ;
		$ss.=">" ;
		$ss.=$rr->Name ;
//		if ($rr->OtherNames!="")	$ss.=" (".$rr->OtherNames.")" ;
		$ss.="</option>" ;
	}
	$ss.="\n</select>" ;
		
	return($ss) ;
} // end of ProposeCity

//------------------------------------------------------------------------------
// CheckEmail return true if the email looks valid
function CheckEmail($email) {
  if (!ereg('^[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+'.
		'@'.
		'[-!#$%&\'*+\\/0-9=?A-Z^_`a-z{|}~]+\.'.
		'[-!#$%&\'*+\\./0-9=?A-Z^_`a-z{|}~]+$', $email)) {
		   return(false) ;
			 
  }
	else {
    return(true) ; // email ok
	}

}


// -----------------------------------------------------------------------------
// hc_mail is a function to centralise all mail send thru HC 
function hvol_mail($to,$subj,$text,$hh="",$_FromParam="",$IdLanguage=0,$PreferenceHtmlEmail="",$LogInfo="",$replyto="") {
  if ($_FromParam=="") $FromParam=$_SYSHCVOL['MessageSenderMail'] ;
  return hcvol_sendmail($to,$subj,$text,"",$hh,$FromParam,$deflanguage,$PreferenceHtmlEmail="",$LogInfo="",$replyto) ;
}


// -----------------------------------------------------------------------------
// hc_sendmail is a function to centralise all mail send thru HC with more feature 
// $to = email of receiver
// $subj=subject of mail
// $text = text of mail
// $textinhtml = text in html will be usee if user preference are html
// $From= from mail (will also be the reply to)
// $deflanguage : défault language of receiver
// $PreferenceHtmlEmail : if set to yes member will receive mail in html format, note that it will be force to html if text contain ";&#"
// $LogInfo = used for debugging

function hcvol_sendmail($to,$subj,$text,$textinhtml="",$hh="",$_FromParam="",$IdLanguage=0,$PreferenceHtmlEmail="",$LogInfo="",$replyto="") {
  if ($_FromParam=="") $FromParam=$_SYSHCVOL['MessageSenderMail'] ;

	$From=$FromParam ;

	$text=str_replace("<br />","",$text) ;
	
//	nl2br_inv($text) ;	// neutralize the nl2br() of ww() and wwinlang()
	$text=str_replace("\r\n","\n",$text) ; // solving the century-bug: NO MORE DAMN TOO MANY BLANK LINES!!!

	$use_html=$PreferenceHtmlEmail ;
  if ($verbose) echo "<br>".$use_html."<br>";
	if (stristr($text,";&#")!=false) { // if there is any non ascii file, force html
  if ($verbose) echo "<br>1<br>";
		if ($use_html!="yes") {
  if ($verbose) echo "<br>2<br>";
			$use_html="yes" ;
			if ($LogInfo=="") {
				LogStr("Forcing HTML for message to $to","hcvol_mail") ;
			}
			else {
				LogStr("Forcing HTML <b>$LogInfo</b>","hchcvol_mail") ;
			}
		}
	}

	$headers = $hh;
	if (($use_html=="yes")or(strpos($text,"<html>")!==false)) { // if html is forced or text is in html then add the MIME header
  if ($verbose) echo "<br>3<br>";
		if ((ord($headers{0})==13)and(ord($headers{1})==10)) { // case a terminator is allready set
			echo "stripping \\r and \\n<br>" ;
			$headers .= "MIME-Version: 1.0\r\nContent-type: text/html; charset=\"iso-8859-1\"".$headers;
		}
		else {
			$headers = "MIME-Version: 1.0\nContent-type: text/html; charset=\"iso-8859-1\"\n";
			$headers .= "X-Sender:<$From>\n";
			$headers .= "X-Mailer:PHP\n".$hh; // mail of client			
		}
		$use_html="yes" ;
	}

	if ($replyto!="") {
		$headers=$headers."Reply-To:".$replyto."\r\n" ;
	}
	if (!(strstr($headers,"From:"))and($From!="")) {
		$headers=$headers."From:".$From."\r\n" ;
	}
	if (!(strstr($headers,"Reply-To:"))and($From!="")) {
		$headers=$headers."Reply-To:".$From."\r\n" ;
	}
	elseif (!strstr($headers,"Reply-To:")) {
		$headers=$headers."Reply-To:".$_SYSHCVOL['MessageSenderMail']."\r\n" ;
	}

//	$headers.="To: $to\r\n";
//	$headers.="Subject: $subj\r\n";
//	$headers.="Return-Path: $From\r\n";


	$headers=$headers."Organization: http://www.openhc.org" ;
	
	if ($use_html=="yes") {
    if ($verbose) echo "<br>4<br>";
		if ($textinhtml!="") { 
    if ($verbose) echo "<br>5<br>";
			$texttosend=$textinhtml ;
		}
		else {
      if ($verbose) echo "<br>6<br>";
			$texttosend=$text ;
		}
		if (strpos($texttosend,"<html>")===false) { // If not allready html
    if ($verbose) echo "<br>7<br>";
			$realtext="<html><head><title>".$subj."</title></head><body bgcolor=#ffffcc>".str_replace("\n","<br>",$texttosend).
			$realtext.="<br><font color=blue>".wwinlang('HCVolMailSignature',$IdLanguage)."</font>" ;
			$realtext.="</body></html>" ;
		}
		else {
      if ($verbose) echo "<br>8<br>";
			$realtext=$texttosend ; // In this case, its already in html
		}
	}
	else {
  if ($verbose) echo "<br>9<br>";
		$text.=wwinlang('HCVolMailSignature',$IdLanguage) ;
		$realtext=str_replace("<br>","\n",$text) ;
	}

  if ($verbose) echo "<br>10".nl2br($realtext)."<br>" ;

  if ($verbose) echo "<br>11".nl2br($realtext)."<br>" ;
  if ($verbose) echo "<br>12".$realtext."<br>" ;

	if ((IsAdmin()) and ($verbose)) {
		echo "<table bgcolor=#ffff99 cellspacing=3 cellpadding=3 border=2><tr><td>" ;
		echo "\$From:<font color=#6633ff>$From</font> \$To:<font color=#6633ff>$to</font><br>" ;
		echo "\$subj:<font color=#6633ff>$subj</font></td>" ;
		$ss=$headers;
		echo "<tr><td>\$headers=<font color=#ff9933>" ;
		for ($ii=0;$ii<strlen($ss);$ii++) {
//			echo "\$ss[$ii]=",ord($ss{$ii})," [",$ss{$ii},"]<br>" ;
			$jj=ord($ss{$ii}) ;
			if ($jj==10) {
				echo "\\n<br>" ;
			}
			elseif ($jj==13) {
				echo "\\r" ;
			}
			else {
				echo chr($jj) ;
			}
		}
		echo "</font></td>"  ;
		echo "<tr><td><font color=#6633ff>",htmlentities($realtext),"</font></td>" ;
		if ($use_html=="yes") echo "<tr><td>$realtext</td>" ;
		echo "</table><br>" ;
	}
		
  if ($_SERVER['SERVER_NAME']=='localhost') { // Localhost don't send mail
	  return("<br><b><font color=blue>".$subj."</font></b><br><b><font color=blue>".$realtext."</font></b><br>"." not sent<br>");
	}
  elseif ($_SERVER['SERVER_NAME']=='ns20516.ovh.net') {
	  return(mail($to,$subj,$realtext,$headers,"-".$_SYSHCVOL['ferrorsSenderMail']))  ;
	}
} // end of hc_mail


//------------------------------------------------------------------------------
//
function debug($s1="",$s2="",$s3="",$s4="",$s5="",$s6="",$s7="",$s8="",$s9="",$s10="",$s11="",$s12="") {
  debug_print_backtrace() ;
	echo  $s1.$s2.$s3.$s4.$s5.$s6.$s7.$s8.$s9.$s10.$s11.$s12."<br>" ;
}


//------------------------------------------------------------------------------
// InsertInCrypted allow to insert a string in Crypted table
// It returns the ID of the created record 
function InsertInCrypted($ss,$_IdMember="") {
  if ($_IdMember=="") { // by default it is current member
	  $IdMember=$_SESSION['IdMember'] ;
	}
	else {
	  $IdMember=$_IdMember ;
	}
	
	$str="insert into cryptedfields(AdminCryptedValue,MemberCryptedValue,IdMember) values(\"".$ss."\",\"".$ss."\",".$IdMember.")" ;
	mysql_query($str) or die("InsertInCrypted:: problem inserting") ;
	return(mysql_insert_id()) ;
} // end of InsertInCrypted

//------------------------------------------------------------------------------
// InsertInMTrad allow to insert a string in MemberTrad table
// It returns the IdTrad of the created record 
function InsertInMTrad($ss,$_IdMember=0,$_IdLanguage=-1,$IdTrad=-1) {
  if ($_IdMember==0) { // by default it is current member
	  $IdMember=$_SESSION['IdMember'] ;
	}
	else {
	  $IdMember=$_IdMember ;
	}

	if ($_IdLanguage==-1) $IdLanguage=$_SESSION['IdLanguage'] ;
	else $IdLanguage=$_IdLanguage ;

	if ($IdTrad==-1) { // if a new IdTrad is needed
  // Compute a new IdTrad
	  $rr=LoadRow("select max(IdTrad) as maxi from memberstrads") ;
	  if (isset($rr->maxi)) { 
	    $IdTrad=$rr->maxi+1 ;
	  }
	  else {
	    $IdTrad=1 ;
	  }
	}
	
	$IdOwner=$IdMember ;
	$IdTranslator=$_SESSION['IdMember'] ; // the recorded translator will always be the current logged member
	$Sentence=$ss ;
	$str="insert into memberstrads(IdLanguage,IdOwner,IdTrad,IdTranslator,Sentence,created) " ; 
	$str.="Values(".$IdLanguage.",".$IdOwner.",".$IdTrad.",".$IdTranslator.",\"".$Sentence."\",now())" ;
	mysql_query($str) or die("InsertInMTrad:: problem inserting <br>error=".$str) ;
//	echo "::InsertInMTrad IdTrad=",$IdTrad," str=",$str,"<hr>" ;
	return($IdTrad) ;
} // end of InsertInMTrad

//------------------------------------------------------------------------------
// ReplaceInMTrad insert or replace the value corresponding to $IdTrad in member Trad
// if ($IdTrad==0) then a new record is inserted
// It returns the IdTrad of the created record 
function ReplaceInMTrad($ss,$IdTrad=0,$IdOwner=0) {
  if ($IdOwner==0) {
	  $IdMember=$_SESSION['IdMember'] ;
	}
	else {
	  $IdMember=$IdOwner ;
	}
//  echo "in ReplaceInMTrad \$ss=[".$ss."] \$IdTrad=",$IdTrad," \$IdOwner=",$IdMember,"<br>" ;
	$IdLanguage=$_SESSION['IdLanguage'] ;
	if ($IdTrad==0) {
	  return(InsertInMTrad($ss,$IdMember)) ; // Create a full new translation
	}
	$IdTranslator=$_SESSION['IdMember'] ; // the recorded translator will always be the current logged member
	$str="select * from memberstrads where IdTrad=".$IdTrad." and IdOwner=".$IdMember." and IdLanguage=".$IdLanguage ;
	$rr=LoadRow($str) ;
	if (!isset($rr->id)) {
//	  echo "[$str] not found so inserted <br>" ;
	  return(InsertInMTrad($ss,$IdMember,$IdLanguage,$IdTrad)) ; // just insert a new record in memberstrads in this new language
	}
	else {
//	  echo "replacing \"$str\" #".$rr->id," rr->IdTrad=",$rr->IdTrad,"<br>" ;
	  $str="update memberstrads set IdTranslator=".$IdTranslator.",Sentence='".$ss."' where id=".$rr->id ;
	  mysql_query($str) or die("ReplaceInMTrad:: problem replacing <br>error=".$str) ;
	}
	return($IdTrad) ;
} // end of ReplaceInMTrad


// 
// mysql_get_set returns in an array the possible set values of the colum of table name
function mysql_get_set($table,$column) {
    $sql = "SHOW COLUMNS FROM $table LIKE '$column'";
    if (!($ret = mysql_query($sql)))
        die("Error: Could not show columns");

    $line = mysql_fetch_assoc($ret);
    $set  = $line['Type'];
    $set  = substr($set,5,strlen($set)-7); // Remove "set(" at start and ");" at end
    return preg_split("/','/",$set); // Split into and array
}

