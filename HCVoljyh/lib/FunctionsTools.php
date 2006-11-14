<?php


// This function set the new language parameters
function SwitchToNewLang($newlang) {
	if ((!isset($_SESSION['lang']))or($_SESSION['lang']!=$newlang)) { // Update lang if url lang has changed
	  $RowLanguage=LoadRow("select id,ShortCode from Languages where ShortCode='".$newlang."'") ;
	  
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
  if (!isset($_SESSION['lang'])) {
	  $_SESSION['lang']="eng" ; 	  
		$_SESSION['IdLanguage']=0 ; 
	}
  if ($_SESSION['lang']=="") {
	  $_SESSION['lang']="eng" ; 	  
		$_SESSION['IdLanguage']=0 ;
	} 
	$res="" ;
	if (empty($code)) {
		return("Empty field \$code in ww function") ;
	}
	if ((int)$code>0) { // case code is the idword
	  	$rr=LoadRow("select SQL_CACHE Sentence from words where id=$code") ;
		$res=nl2br(stripslashes($rr->Sentence)) ;
	}
	if ($res=="") { // In case the word wasent in the session variable
		$rr=LoadRow("select  SQL_CACHE Sentence from words where code='$code' and IdLanguage='".$_SESSION['IdLanguage']."'") ;
    		$res=nl2br(stripslashes($rr->Sentence)) ;
	}
	if ($res=="") {
		if ((int)$code>0) { // id word case
		  if (IsAdmin()) {
				$res="<b>function ww() : idword #$code missing</b>" ;
			}
			else {
				$res=$code ;
			}
		}
		else {
			$rr=LoadRow("select SQL_CACHE Sentence from words where code='$code' and IdLanguage='".$_SESSION['IdLanguage']."'") ;
			$res=nl2br(stripslashes($rr->Sentence)) ;
			if (IsAdmin()) {
				$res.="<a  target=\"_new\" href=AdminWords.php?IdLangage=".$_SESSION['IdLangage']."&code=$code><font size=1 color=red>click to define the word <font color=blue><font size=2>$code</font></font> in </font><b>".$_SESSION['lang']."</b></a>" ;
			}
		}
		if (IsAdmin()) {
		  $res="<a  target=\"_new\" href=AdminWords.php?IdLangage=".$_SESSION['IdLangage']."&code=$code><font size=1 color=red>click to define the word <font color=blue><font size=2>$code</font></font> in </font><b>".$_SESSION['lang']."</b></a>" ;
		}
//		$res="<a href=AdminWords.php?search_lang=fr&search=$str&generate=check>click here to define $str</a>"
	}
	$res=sprintf($res,$p1,$p2,$p3,$p4,$p5,$p6,$p7,$p8,$p9,$p10,$p11,$p12,$p13) ;
	return ($res) ;
} // end of ww


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
			echo $_SERVER['PHP_SELF']," : LoadRow error [".mysql_error()."]for <b>[",$str,"]</b></font>" ;
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
// This function display the main menu
//------------------------------------------------------------------------------
function mainmenu($link="",$tt="") {
  global $title ;
	if ($tt!="") $title=$tt ;
  echo "\n<div align=\"center\" id=\"header\">" ;
  echo "\n<ul>\n" ;

  if (IsLogged()) {	
    echo "<li><a" ;
	  if ($link=="Main.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"Main.php\" ";
	  }
	  echo " title=\"first page.\">",ww('Welcome'),"</a></li>\n" ;
	}
	
  echo "<li><a" ;
	if ($link=="MembersByCountries.php") {
	  echo " id=current " ;
	}
	else {
	  echo " href=\"MembersByCountries.php\" ";
	}
	echo " title=\"Members by countries\">",ww('MembersByCountries'),"</a></li>\n" ;

  echo "<li><a" ;
	if ($link=="Search.php") {
	  echo " id=current " ;
	}
	else {
	  echo " href=\"Search.php\" ";
	}
	echo " title=\"Search Page\">",ww('SearchPage'),"</a></li>\n" ;

  echo "<li><a" ;
	if ($link=="Faq.php") {
	  echo " id=current " ;
	}
	else {
	  echo " href=\"Faq.php\" ";
	}
	echo " title=\"Frequently asked questions.\">",ww('faq'),"</a></li>\n" ;

  echo "<li><a" ;
	if ($link=="Feedback.php") {
	  echo " id=current " ;
	}
	else {
	  echo " href=\"Contact.php\" ";
	}
	echo " title=\"Contact us\">",ww('ContactUs'),"</a></li>\n" ;

  if (IsLogged()) {	
    echo "<li><a" ;
	  if ($link=="Main.php?action=Logout") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"Main.php?action=logout\" method=post ";
	  }
	  echo " title=\"Logout\">",ww('Logout'),"</a></li>\n" ;

    echo "<li><a" ;
	  if ($link=="MyPreferences.php") {
	    echo " id=current " ;
	  }
	  else {
	    echo " href=\"MyPreferences.php\" method=post ";
	  }
	  echo " title=\"My preferences\">",ww('MyPreferences'),"</a></li>\n" ;

	}
	

  echo "</ul>\n</div>\n" ;
	
	// anomalie : les 2 ligne ssuivantes sont nécéssaires pour provoquer un retour à la ligne
  echo "\n<table width=100%><tr><td align=left>&nbsp;</td></table>" ;
  echo "\n<table width=100%><tr><td align=left>&nbsp;</td></table>" ;
} // end of mainmenu


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
	$str="insert delayed into Logs(IpAddress,IdMember,Str,Type) values(".ip2long( $_SERVER['REMOTE_ADDR']).",".$IdMember.",'".addslashes($stext)."','".$stype."')" ;
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
  $row=LoadRow("select Sentence from MembersTrads where IdTrad=".$IdTrad." and IdLanguage=".$_SESSION['IdLanguage']) ;
	if (isset($row->Sentence)) {
	  if (isset($row->Sentence)=="") {
		  LogStr("Blank Sentence for language ".$_SESSION['IdLanguage']." with MembersTrads.IdTrad=".$IdTrad,"Bug") ;
		}
		else {
		  return($row->Sentence) ;
		}
	}
// Try default eng
  $row=LoadRow("select Sentence from MembersTrads where IdTrad=".$IdTrad." and IdLanguage=1") ;
	if (isset($row->Sentence)) {
	  if (isset($row->Sentence)=="") {
		  LogStr("Blank Sentence for language 1 (eng) with MembersTrads.IdTrad=".$IdTrad,"Bug") ;
		}
		else {
		  return($row->Sentence) ;
		}
	}
// Try first language available
  $row=LoadRow("select Sentence from MembersTrads where IdTrad=".$IdTrad." order by id asc limit 1") ;
	if (isset($row->Sentence)) {
	  if (isset($row->Sentence)=="") {
		  LogStr("Blank Sentence (any language) MembersTrads.IdTrad=".$IdTrad,"Bug") ;
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
  if (!isset($_SESSION['IdMember'])) return(0) ; // No ned to search for right if no memebr logged
  $IdMember=$_SESSION['IdMember'] ;
  if ((!isset($_SESSION['Right_'.$RightName]))or ($_SYSHCVOL['ReloadRight']=='True')) {
	  $str="select Scope,Level from RightsVolunteers,Rights where IdMember=$IdMember and Rights.id=RightsVolunteers.IdRight and Rights.Name='$RightName'" ;
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
