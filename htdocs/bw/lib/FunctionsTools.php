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
require_once "FunctionsCrypt.php";
require_once("rights.php");
require_once("mailer.php");

if (defined('SCRIPT_BASE')) {
	require_once(SCRIPT_BASE."/modules/i18n/lib/words.lib.php") ;
}
else {
	require_once ("../../modules/i18n/lib/words.lib.php")  ;
}


$words_for_BW=new MOD_words() ;

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
			echo "problem : LogStr \$str=$str<br />";
	}
} // end of LogStr

function ReplaceWithBR($ss,$ReplaceWith=false) {
		if (!$ReplaceWith) return ($ss);
		return(str_replace("\n","<br \>",$ss));
}

// -----------------------------------------------------------------------------
// the trad corresponding to the current language of the user, or English, 
// or the one the member has set
// The rox function for this function is in MOD_WORD and it is call mTrad($IdTrad) 
function FindTrad($IdTrad,$ReplaceWithBr=false) {


	$AllowedTags = "<b><i><br>";
	if ($IdTrad == "")
		return ("");
		
	if (isset($_SESSION['IdLanguage'])) {
		 $IdLanguage=$_SESSION['IdLanguage'] ;
	}
	else {
		 $IdLanguage=0 ; // by default language 0
	} 
	// Try default language
	$row = LoadRow("select SQL_CACHE Sentence from memberstrads where IdTrad=" . $IdTrad . " and IdLanguage=" . $IdLanguage);
	if (isset ($row->Sentence)) {
		if (isset ($row->Sentence) == "") {
			LogStr("Blank Sentence for language " . $IdLanguage . " with MembersTrads.IdTrad=" . $IdTrad, "Bug");
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
// This function return the name of a region according to the IdRegion parameter
function getregionname($IdRegion) {
	if (empty($IdRegion)) { // let consider that in some case members can have a city without region 
	   return(ww("NoRegionDefined")) ;
	}
	$rr = LoadRow("select  SQL_CACHE Name from regions where id=" . $IdRegion);
	if (!isset($rr->Name)) {
	   return(ww("NoRegionDefined")) ;
	}
	else {
	   return ($rr->Name);
	}
}

//------------------------------------------------------------------------------
// This function return the name of a city according to the IdCity parameter
function getcityname($IdCity) {
	$rr = LoadRow("select  SQL_CACHE Name from cities where id=" . $IdCity);
	if (isset($rr->Name)) return ($rr->Name);
	else  return("unknown city") ;
}

//------------------------------------------------------------------------------
// This function return the name of a country according to the IdCountry parameter
function getcountryname($IdCountry) {
	$rr = LoadRow("select  SQL_CACHE Name from geonames_cache where geonameid=" . $IdCountry);
	if (isset($rr->Name)) return ($rr->Name);
	else  return("unknown country") ;
}

//------------------------------------------------------------------------------
// This function return the name of a country according to the isoalpha2 parameter
function getcountrynamebycode($isoalpha2) {
	$rr = LoadRow("select  SQL_CACHE Name from geonames_countries where iso_alpha2='$isoalpha2'");
	return ($rr->Name);
}

//------------------------------------------------------------------------------
// This function return the id of a region according to the IdCity parameter
function GetIdRegionForCity($IdCity) {
	$rr = LoadRow("select  SQL_CACHE parentAdm1Id as IdRegion from geonames_cache where geonameid=". $IdCity);
	return ($rr->IdRegion);
}

//------------------------------------------------------------------------------
function ProposeCountry($Id = 0, $form = "signup", $isoalpha2 = false) {
	$ss = "";
	if($isoalpha2) $str = "select  SQL_CACHE isoalpha2 as id,Name from countries order by Name";
	else $str = "select  SQL_CACHE id,Name from countries order by Name";
	$qry = sql_query($str);
	$ss = "\n<select id=\"IdCountry\" name=\"IdCountry\"";
	if(!$isoalpha2) $ss .= " onchange=\"change_country('" . $form . "')\"";
	$ss .= ">\n";
	$ss .= "  <option value=\"0\">" . ww("MakeAChoice") . "</option>\n";
	while ($rr = mysql_fetch_object($qry)) {
		$ss .= "  <option value='" . $rr->id. "'";
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
		return ("\n<input type=\"hidden\" id=\"IDRegion\" name=\"IdRegion\" Value=\"0\" />\n");
	}
	$ss = "";
	$str = "select SQL_CACHE id,Name,OtherNames,NbCities from regions where IdCountry=" . $IdCountry . " and NbCities>0 order by Name";
	$qry = sql_query($str);
	$ss = "\n<select name=\"IdRegion\" onchange=\"change_region('" . $form . "')\">\n";
	$ss .= "<option value=\"0\">" . ww("MakeAChoice") . "</option>\n";
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

/**
 * this function proposes a city according to preselected region
 * or to CityName and preselected country if any
 */ 
function ProposeCity($Id = 0, $IdRegion = 0, $form="signup", $CityName="", $IdCountry=0)
{
    $hiddenIdCity = "\n<input type=\"hidden\" name=\"IdCity\" value=\"0\" />\n";
    if ($CityName!="") {
        $str = "select SQL_CACHE cities.id, cities.Name, cities.OtherNames, regions.name as RegionName ".
            "from (cities) left join regions on (cities.IdRegion=regions.id) ".
            "where cities.IdCountry=" . $IdCountry . " and ActiveCity='True' and (cities.Name like '".$CityName."%' or cities.OtherNames like '%".$CityName."%') ".
            "order by cities.population desc";
    } else {
        if ($form!="findpeopleform") {
            return "$hiddenIdCity";
        }
        $str = "select SQL_CACHE cities.id, cities.Name, cities.OtherNames, regions.name as RegionName ".
            "from (cities) left join regions on (cities.IdRegion=regions.id) ".
            "where cities.IdCountry=" . $IdCountry . " and ActiveCity='True' and cities.IdCountry=".$IdCountry." ".
            "order by cities.population desc";
    }
    
    $qry = sql_query($str);

    $selectBox = "\n<ul><li><select name=\"IdCity\">\n";
    if ($CityName == "") {
        $selectBox .= '<option value="0">' . ww("MakeAChoice") . "</option>\n";
		}
    $zeroHits = true;
    while ($rr = mysql_fetch_object($qry)) {
        $zeroHits = false;
        $selectBox .= '<option value="' . $rr->id . '"';
        if ($rr->id == $Id) {
            $selectBox .= " selected";
        }
        $selectBox .= ">";
        $selectBox .= $rr->Name;
//		if ($rr->OtherNames!="") $selectBox.=" (".$rr->OtherNames.")";
        if (isset($rr->RegionName)) {
            $selectBox.=" ".$rr->RegionName;
        }
        $selectBox .= "</option>\n";
    } // end of while
    $selectBox .= "\n</select></li></ul>\n";

  	if ($zeroHits) {
        return $hiddenIdCity;
  	} // end if $zeroHits
    
  return $selectBox;
} // end of ProposeCity

//------------------------------------------------------------------------------
// CheckEmail return true if the email looks valid
function CheckEmail($email) {
	if	(filter_var($email,FILTER_VALIDATE_EMAIL) === FALSE) {
		return (false);
	} else {
		return (true); // email ok
	}

}

//------------------------------------------------------------------------------
//
function debug($s1 = "", $s2 = "", $s3 = "", $s4 = "", $s5 = "", $s6 = "", $s7 = "", $s8 = "", $s9 = "", $s10 = "", $s11 = "", $s12 = "") {
	debug_print_backtrace();
	echo $s1 . $s2 . $s3 . $s4 . $s5 . $s6 . $s7 . $s8 . $s9 . $s10 . $s11 . $s12 . "<br />";
}

//------------------------------------------------------------------------------
// InsertInMTrad allow to insert a string in MemberTrad table
// It returns the IdTrad of the created record
// This function is deprecated, use NewInsertInMTrad instead 
function InsertInMTrad($ss, $_IdMember = 0, $_IdLanguage = -1, $IdTrad = -1) {
	return(NewInsertInMTrad($ss,"NotSet",0,$_IdMember = 0, $_IdLanguage = -1, $IdTrad = -1)) ;
} // end of InsertInMTrad

//------------------------------------------------------------------------------
// NewInsertInMTrad allow to insert a string in MemberTrad table
// It returns the IdTrad of the created record
// it also allow to define the concerned table and its the Record id
// @$TableComumn must be in the form "members.ProfileSummary"
// @$Idrecord is to be the id of the record in the corresponding $TableColumn, 
// This is not normalized but needed for mainteance
function NewInsertInMTrad($ss,$TableColumn,$IdRecord, $_IdMember = 0, $_IdLanguage = -1, $IdTrad = -1) {
	$words_for_BW=new MOD_words() ;
	return($words_for_BW->InsertInMTrad($ss,$TableColumn,$IdRecord, $_IdMember, $_IdLanguage, $IdTrad))  ;
} // end of NewInsertInMTrad

//------------------------------------------------------------------------------
// ReplaceInMTrad insert or replace the value corresponding to $IdTrad in member Trad
// if ($IdTrad==0) then a new record is inserted
// It returns the IdTrad of the created record 
// This function is deprecated, use NewReplaceInMTrad instead 
function ReplaceInMTrad($ss, $IdTrad = 0, $IdOwner = 0) {
	return(NewReplaceInMTrad($ss,"NotSet",0, $IdTrad, $IdOwner)) ;
} // end of ReplaceInMTrad

//------------------------------------------------------------------------------
// NewReplaceInMTrad insert or replace the value corresponding to $IdTrad in member Trad
// if ($IdTrad==0) then a new record is inserted
// It returns the IdTrad of the created record 
// @$TableComumn must be in the form "members.ProfileSummary"
// @$Idrecord is to be the id of the record in the corresponding $TableColumn, 
// This is not normalized but needed for mainteance
function NewReplaceInMTrad($ss,$TableColumn,$IdRecord, $IdTrad = 0, $IdOwner = 0) {
	$words_for_BW=new MOD_words() ;
	return($words_for_BW->ReplaceInMTrad($ss,$TableColumn,$IdRecord, $IdTrad, $IdOwner)) ;
} // end of NewReplaceInMTrad



//------------------------------------------------------------------------------ 
// Get param returns the param value (in get or post) if any it intented to return an int
function GetParam($param, $defaultvalue = "") {
	return GetStrParam( $param, $defaultvalue );
} // end of GetParam


//----------------------------------------------------------------------------------------- 
// GetStrParam returns the param value (in get or post) if any it intented to return a string
function GetStrParam($param, $defaultvalue = "") {

	if (isset ($_GET[$param])) {
	    $m=$_GET[$param];
	}
	if (isset ($_POST[$param])) {
	    $m=$_POST[$param];
	}

	if (!isset($m))
		return $defaultvalue;
	
	$m=mysql_real_escape_string($m);
	$m=str_replace("\\n","\n",$m);
	$m=str_replace("\\r","\r",$m);
	if ((stripos($m," or ")!==false)or (stripos($m," | ")!==false)) {
			LogStr("Warning !  GetStrParam trying to use a <b>".addslashes($m)."</b> in a param $param for ".$_SERVER["PHP_SELF"], "alarm");
	}
	if (empty($m) and ($m!="0")){	// a "0" string must return 0 for the House Number for exemple 
		return ($defaultvalue); // Return defaultvalue if none
	} else {
		return ($m);		// Return translated value
	}
} // end of GetStrParam


//----------------------------------------------------------------------------------------- 
// GetArrayParam returns the param value (in get or post) if any it intented to return an array
function GetArrayParam($param, $defaultvalue = "") {
	$colarray = array();
	if ((isset ($_GET[$param]))and(!empty($_GET[$param]))){
		 $colarray=unserialize($_GET[$param]) ; // Beware at calling this parameter must be serialized
	}
	if ((isset ($_POST[$param]))and(!empty($_POST[$param]))) {
		 $colarray=$_POST[$param] ;
	}

	return($colarray) ;

	// to do a mysql escape string to argument before returning 
	$m=mysql_real_escape_string($m);
	$m=str_replace("\\n","\n",$m);
	$m=str_replace("\\r","\r",$m);
	if ((stripos($m," or ")!==false)or (stripos($m," | ")!==false)) {
			LogStr("Warning !  GetArrayParam trying to use a <b>".addslashes($m)."</b> in a param $param for ".$_SERVER["PHP_SELF"], "alarm");
	}
	if (empty($m) and ($m!="0")){	// a "0" string must return 0 for the House Number for exemple 
		return ($defaultvalue); // Return defaultvalue if none
	} else {
		return ($m);		// Return translated value
	}
} // end of GetArrayParam


//------------------------------------------------------------------------------ 
// function EvaluateMyEvents()  evaluate several events :
// - not read message
function EvaluateMyEvents() {

    global $_SYSHCVOL;

    if (isset($_SESSION['IdMember'])) {
        $memberId = $_SESSION['IdMember'];
    } else {
        $memberId = false;
    }

    // REMOTE_ADDR is not set when run via CLI
    if (isset($_SERVER['REMOTE_ADDR'])) {
        $ipAsInt = intval(ip2long($_SERVER['REMOTE_ADDR']));
    } else {
        $ipAsInt = intval(ip2long('127.0.0.1'));
    }
    MOD_online::get()->iAmOnline($ipAsInt, $memberId);

	if (!IsLoggedIn()) {
		return; // if member not identified, no more evaluation needed
	}
	if ($_SYSHCVOL['EvaluateEventMessageReceived'] == "Yes") {
		$IdMember = $_SESSION['IdMember'];
		$str = "select count(*) as cnt from messages where IdReceiver=" . $IdMember . " and WhenFirstRead='0000-00-00 00:00:00' and (not FIND_IN_SET('receiverdeleted',DeleteRequest))  and Status='Sent'";
		//		echo "str=$str<br> /";
		$rr = LoadRow($str);

		$_SESSION['NbNotRead'] = $rr->cnt;
	} else {
		$_SESSION['NbNotRead'] = 0;
	}

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
function LinkWithPicture($Username, $ParamPhoto="", $Status = "") {
	
	global $_SYSHCVOL;
    return "<a href='/members/{$Username} title='" . ww("SeeProfileOf", $Username) . "'><img class='framed' ".($Status == 'map_style' ? "style=\"float: left; margin: 4px\" " : "") . "src='/members/avatar/{$Username}' height=\"50px\" width=\"50px\" alt='Profile'/></a>";
    die();

//	echo "\$Username=",$Username." \$ParamPhoto=",$ParamPhoto ;
	$Photo=$ParamPhoto ;
	if (empty($Photo)or ($Photo=='NULL')) {
		if (is_numeric($Username)) {
			$rr = LoadRow("select SQL_CACHE * from members where id=" . $Username);
		}
		else {
			$rr = LoadRow("select SQL_CACHE * from members where Username='" . $Username."'");
		}
		$Photo=DummyPict($rr->Gender,$rr->HideGender) ;
		return "<a href=\"".bwlink("member.php?cid=$Username").
		"\" title=\"" . ww("SeeProfileOf", $Username) . 
		"\"><img class=\"framed\" ".($Status == 'map_style' ? "style=\"float: left; margin: 4px\" " : "") . "src=\"". $Photo."\" height=\"50px\" width=\"50px\" alt=\"Profile without pict (".$rr->Gender.")\" /></a>";
	}
	// TODO: REMOVE THIS HACK:
	if (strstr($Photo,"memberphotos/"))
		$Photo = substr($Photo,strrpos($Photo,"/")+1);
		
	
		
	$orig = $_SYSHCVOL['IMAGEDIR']."/".$Photo;

	$thumb = getthumb( $_SYSHCVOL['IMAGEDIR'].$Photo, 100, 100);
	if ($thumb === null)
		$thumb = "";
	$thumb = str_replace( $_SYSHCVOL['IMAGEDIR'],$_SYSHCVOL['WWWIMAGEDIR'].'/',$thumb );

	return "<a href=\"".bwlink("member.php?cid=$Username").
		"\" title=\"" . ww("SeeProfileOf", $Username) . 
		"\"><img class=\"framed\" ".($Status == 'map_style' ? "style=\"float: left; margin: 4px\" " : "") . "src=\"". bwlink($thumb)."\" height=\"50px\" width=\"50px\" alt=\"Profile\" /></a>";
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

// THis TestIfIsToReject function check wether the status of the members imply an immediate logoff
// This for the case a member has just been banned
// the $Status of the member is the current status from the database
function TestIfIsToReject($Status) {
	 if (($Status=='Rejected ')or($Status=='Banned')) { 
		LogStr("Force Logout GAMEOVER", "Login");
		APP_User::get()->logout();
		die(" You can't use this site anymore") ;
	 }
} // end of funtion IsToReject 

//------------------------------------------------------------------------------ 
// function IdMember return the numeric id of the member according to its username
// This function will TARNSLATE the username if the profile has been renamed.
// Note that a numeric username is provided no Username trnslation will be made
function IdMember($username) {
	if (is_numeric($username)) { // if already numeric just return it
		return ($username);
	}
	$rr = LoadRow("select SQL_CACHE id,ChangedId,Username,Status from members where Username='" . addslashes($username) . "'");
	if (!isset($rr->id)) return(0) ; // Return 0 if no username match
	if ($rr->ChangedId > 0) { // if it is a renamed profile
		$rRenamed = LoadRow("select SQL_CACHE id,Username from members where id=" . $rr->ChangedId);
		$rr->id = IdMember($rRenamed->Username); // try until a not renamde profile is found
	}
	if (isset ($rr->id)) {
	    // test if the member is the current member and has just bee rejected (security trick to immediately remove the current member in such a case)
		if (array_key_exists("IdMember", $_SESSION) and $rr->id==$_SESSION["IdMember"]) TestIfIsToReject($rr->Status) ;
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
	if (array_key_exists("IdMember", $_SESSION) and $cid == $_SESSION["IdMember"])
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
	$words_for_BW=new MOD_words() ;
	if ($hidden != "No") {
		return ($words_for_BW->getFormatted("AgeHidden"));
	}
	return ($words_for_BW->getFormatted("AgeEqualX", fage_value($dd)));
} // end of fage

//------------------------------------------------------------------------------
// fage_value return a  the age value corresponding to date
function fage_value($dd) {
    $pieces = explode("-",$dd);
    if(count($pieces) != 3) return 0;
    list($year,$month,$day) = $pieces;
    $year_diff = date("Y") - $year;
    $month_diff = date("m") - $month;
    $day_diff = date("d") - $day;
    if ($month_diff < 0) $year_diff--;
    elseif (($month_diff==0) && ($day_diff < 0)) $year_diff--;
    return $year_diff;
} // end of fage_value

//------------------------------------------------------------------------------
// function fFullName return the FullName of the member with a special layout if some fields are crypted 
function fFullName($m) {
	return (PublicReadCrypted($m->FirstName, "*") . " " . PublicReadCrypted($m->SecondName, "*") . " " . PublicReadCrypted($m->LastName, "*"));
} // end of fFullName

//------------------------------------------------------------------------------
function GetPreference($namepref,$idm=0) {
	$IdMember=$idm;
   if ($idm==0) {
	   if (isset($_SESSION['IdMember'])) $IdMember=$_SESSION['IdMember'];
	   
	}
	if ($IdMember==0) {
	   $rr=LoadRow("select SQL_CACHE DefaultValue  from preferences where codeName='".$namepref."'");
	   return($rr->DefaultValue);
	}
	else {
	   $rr = LoadRow("select SQL_CACHE Value from memberspreferences,preferences where preferences.codeName='$namepref' and memberspreferences.IdPreference=preferences.id and IdMember=" . $IdMember);
	   if (isset ($rr->Value))
		  $def = $rr->Value;
		else {
	   	  $rr=LoadRow("select SQL_CACHE DefaultValue  from preferences where codeName='".$namepref."'");
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
function GetDefaultLanguage($IdMember=0) {
	return(GetPreference("PreferenceLanguage",$IdMember));
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
	$ss="select SQL_CACHE EnglishName,ShortCode from languages where id=" . $IdLanguage ;
	$rr = LoadRow($ss);
	if (!isset($rr->EnglishName)) {
		if (HasRight("Debug")) {
			echo " in FunctionsTools::LanguageName failed for ".$ss ;
		}
		else {
			LogStr(" in FunctionsTools::LanguageName failed for ".$ss,"Debug") ;
		}
	}
	return ($rr->EnglishName);
} // end of LanguageName

// return eng for English, ru for Russian etc
function ShortLangSentence($IdLanguage) {
	$rr = LoadRow("select SQL_CACHE EnglishName,ShortCode from languages where id=" . $IdLanguage);
	return ($rr->ShortCode);
}

//------------------------------------------------------------------------------
// return the id of member ship in group $IdGroup for member $IdMember, or 0
function IdMemberShip($IdGroup, $IdMemb = 0) { // find the membership of the member

	if ($IdMemb == 0) {
		if(array_key_exists("IdMember", $_SESSION)) $IdMember = $_SESSION["IdMember"];
	}
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
//@$IdMember : id or username of the member to bechecked as a public profile
function IsPublic($IdMember=0) {
   $rr=LoadRow("select SQL_CACHE * from memberspublicprofiles where  memberspublicprofiles.IdMember=".IdMember($IdMember));
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
	return("<img height=\"11px\" width=\"16px\"src=\"images/flags/".$flag."\" alt=\"".$flag."\" title=\"".$title."\" />");
} // end of FlagLanguage

/**
 * print the error and die
 * @param string $errortext error text to be printed
 * this function write data in php_log
 * according to member right (Debug) this function will also display error on screen 
 */
function bw_error( $errortext, $showalways = false ) {
	
	global $_SYSHCVOL;	

   	$serr="" ;
	$tt=time() ; // save a timestant which will be used in the log to retrieve error reference
	if (isset($_SESSION["Username"])) {
	   $serr="[".$tt."] bw_error for :".$_SESSION["Username"]." :\n" ;
	}
	else {
	   $serr="[".$tt."] bw_error for unknownmember :\n" ;
	} 
	$serr.=$_SERVER["PHP_SELF"] ;
	if ((isset($_SERVER["QUERY_STRING"])) and ($_SERVER["QUERY_STRING"]!="")) {
	    $serr=$serr."?".$_SERVER["QUERY_STRING"] ;
	}
	$serr.="\n" ; 

   error_log($serr.$errortext) ;
	if (/*HasRight("Debug") || */$showalways || (isset($_SYSHCVOL['DISABLEERRORS']) and !$_SYSHCVOL['DISABLEERRORS'])) {
	   die("System error: ".$serr.": ".$errortext."<br />");
	}
	die("System error, please report the following timestamp along the error: [".$tt."]");
} // end of bw error


// Thumbnail creator. (by markus5, Markus Hutzler 25.02.2007)
// tested with GD Version: bundled (2.0.28 compatible)
// with GIF Read Support: Enabled
// with JPG Support: Enabled
// with PNG Support: Enabled

// this function creates a thumbnail of a JPEG, GIF or PNG image
// file: path (with /)!!!
// max_x / max_y delimit the maximal size. default = 100 (it keeps the ratio)
// the quality can be set. default = 85
// this function returns the thumb filename or null

// modified by Fake51
// $mode specifies if the new image is based on a cropped and resized version of the old, or just a resized
// $mode = "square" means a cropped version
// $mode = "ratio" means merely resized
function getthumb($file, $max_x, $max_y,$quality = 85, $thumbdir = 'thumbs',$mode = 'square')
{
	// TODO: analyze MIME-TYPE of the input file (not try / catch)
	// TODO: error analysis of wrong paths
	// TODO: dynamic prefix (now: /th/)
	
	if (empty($file))
		return null;

	$file = str_replace("\\","/",$file);
  
	 
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
	$prefix = "$path/$thumbdir/";
	// seperating the filename and extension
	
	$dot_pos = strrpos($filename, '.');
	if ($dot_pos === false)
		return null;
		//return array("state" => false, "message" => '"'.$filename.'" has no extension... I\'m confused!?!?!');
	else
		$filename_noext = substr($filename,0,$dot_pos);

	// locate file
	if ( !is_file($file) )
		return null;
		// TODO: bw_error("get_thumb: no $file found");

	if(!is_dir($prefix))
		bw_error("no folder $prefix!");         
	
	$thumbfile = $prefix.$filename_noext.'.'.$mode.'.'.$max_x.'x'.$max_y.'.jpg';

	if(is_file($thumbfile))
		return $thumbfile;

   ini_set("memory_limit",'64M'); //jeanyves increasing the memory these functions need a lot
	// read image
	$image = false;
	if (!$image) $image = @imagecreatefromjpeg($file);
	if (!$image) $image = @imagecreatefrompng($file);
	if (!$image) $image = @imagecreatefromgif($file);

	if($image == false)
		return null;

	// calculate ratio
	$size_x = imagesx($image);
	$size_y = imagesy($image);
	
	if($size_x == 0 or $size_y == 0){
		bw_error("bad image size (0)");
	}

	switch($mode){
		case "ratio":
			if (($max_x / $size_x) >= ($max_y / $size_y)){
				$ratio = $max_y / $size_y; 
			} else {
			  	$ratio = $max_x / $size_x;
			}
			$startx = 0;
			$starty = 0;
			break;
		default:
			if ($size_x >= $size_y){
				$startx = ($size_x - $size_y) / 2;
				$starty = 0;
				$size_x = $size_y;
			} else {
				$starty = ($size_y - $size_x) / 2;
				$startx = 0;
				$size_y = $size_x;
			}
		
			if ($max_x >= $max_y){
				$ratio = $max_y / $size_y;
			} else {
				$ratio = $max_x / $size_x;
			}
			break;
	}
	  	 
	$th_size_x = $size_x * $ratio;
	$th_size_y = $size_y * $ratio;
	



	// creating thumb
	$thumb = imagecreatetruecolor($th_size_x,$th_size_y);
	imagecopyresampled($thumb,$image,0,0,$startx,$starty,$th_size_x,$th_size_y,$size_x,$size_y);

	// try to write the new image 
	imagejpeg($thumb,$thumbfile,$quality);
	return $thumbfile;         
}

//------------------------------------------------------------------------------
// function MyPict() return the path of the picture for the member
function MyPict($paramIdMember=0) {
  if ($paramIdMember==0) {
		 $IdMember=$_SESSION["IdMember"] ;
	}
	else {
		 $IdMember=$paramIdMember ;
	}
	
   if ($IdMember==0) return(DummyPict()) ; 

	$rr = LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $IdMember . " and SortOrder=0");
	if (isset($rr->FilePath)) return($rr->FilePath) ;
	else {
	  $rr = LoadRow("select SQL_CACHE * from members where id=" . $IdMember);
	  return(DummyPict($rr->Gender,$rr->HideGender)) ;
	}
} // end of MyPict

//------------------------------------------------------------------------------
// THis function return true if the member is in the status list
// for example $Status="Active,ActiveHidden" ;
function CheckStatus($Status,$paramIdMember=0) {
  if ($paramIdMember==0) {
		 $IdMember=$_SESSION["IdMember"] ;
	}
	else {
		 $IdMember=$paramIdMember ;
	}
   if ($IdMember==0) return(False) ;
	
	$tt=explode(",",$Status) ;
	$rr=LoadRow("select SQL_CACHE * from members where id=".$IdMember) ;
	if ($IdMember==$_SESSION["IdMember"]) {
		 $_SESSION["Status"]=$rr->Status ; // update status in case it has changed
		 TestIfIsToReject($rr->Status) ;
	}
	if (in_array($rr->Status,$tt)) return (true) ;
	return (false) ;
} // end of CheckStatus

//------------------------------------------------------------------------------
// THis function return a picture according to member gender if (any)
function DummyPict($Gender="IDontTell",$HideGender="Yes") {
	global $_SYSHCVOL;

    // return this automatically, because memberphotos won't be available
  return "http://www.bewelcome.org/images/misc/empty_avatar_30_30.png" ;
/*	
  if ($HideGender=="Yes") return ($_SYSHCVOL['IMAGEDIR'] . "et.jpg") ;
  if ($Gender=="male") return ($_SYSHCVOL['IMAGEDIR'] . "et_male.jpg") ;
  if ($Gender=="female") return ($_SYSHCVOL['IMAGEDIR'] . "et_female.jpg") ;
 */
  
  if ($HideGender=="Yes") return ($_SYSHCVOL['IMAGEDIR'] . "et.jpg") ;
  if ($Gender=="male") return ("http://www.bewelcome.org/bw/memberphotos/thumbs/et_male.square.50x50.jpg") ;
  if ($Gender=="female") return ("http://www.bewelcome.org/bw/memberphotos/thumbs/et_female.square.50x50.jpg") ;
  
  return ("http://www.bewelcome.org/bw/memberphotos/thumbs/et.square.50x50.jpg") ;
} // end of DummyPict


// Here is a Class which will manage a volunteer left menu 
	class CVolMenu {
				var $link ; // the link for this volmenu
				var $text ; // the text of the link for this volmenu
				var $help ; // the help text of the link for this volmenu
        function __construct($l,$t,$h) {
								 $this->link=$l ;
								 $this->text=$t ;
								 $this->help=$h ;
				} // end of constructor CVolMenu
	} // end of class CVolMenu


//------------------------------------------------------------------------------
// This build the specific menu for volunteers
// It is build with all the option a volunteer as, an empty array is retruned if they are no option
// @ output an array of class ResVolMenu with three string attributes ->link and ->text and ->help
function BuildVolMenu() {

	$res=array();

	if (HasRight("Grep")) {
		 array_push($res,new CVolMenu("admin/admingrep.php","AdminGrep","Grepping files")) ;
	}

	if (HasRight("Group")) {
		 array_push($res,new CVolMenu("admin/admingroups.php","AdminGroup","Group managment")) ;
	}

	if (HasRight("Flags")) {
		 array_push($res,new CVolMenu("admin/adminflags.php","AdminFlags","administration of members flags")) ;
	}

	if (HasRight("Rights")) {
		 array_push($res,new CVolMenu("admin/adminrights.php","AdminRights","administration of members rights")) ;
	}

	if (HasRight("Logs")) {
		 array_push($res,new CVolMenu("admin/adminlogs.php","AdminLogs","logs of activity")) ;
	}

	if (HasRight("Comments")) {
		 array_push($res,new CVolMenu("admin/admincomments.php","AdminComments","managing comments")) ;
	}

	if (HasRight("Pannel")) {
		 array_push($res,new CVolMenu("admin/adminpanel.php","AdminPanel","managing panel (may be obsolete)")) ;
	}


	if (HasRight("Checker")) {
	  $rr=LoadRow("SELECT COUNT(*) AS cnt FROM messages WHERE Status='ToCheck' AND messages.WhenFirstRead='0000-00-00 00:00:00'");
		$rrSpam=LoadRow("SELECT COUNT(*) AS cnt FROM messages,members AS mSender, members AS mReceiver WHERE mSender.id=IdSender AND messages.SpamInfo='SpamSayMember' AND mReceiver.id=IdReceiver AND (mSender.Status='Active' or mSender.Status='Pending')");
		
		$text ="AdminChecker"."(".$rr->cnt."/".$rrSpam->cnt.")";
		array_push($res,new CVolMenu("admin/adminchecker.php",$text,"Mail Checking")) ;
	}

	if (HasRight("Debug","ShowErrorLog")) {
		 array_push($res,new CVolMenu("admin/phplog.php?showerror=10","php error log","php error log")) ;
	}

	if (HasRight("Debug","ShowSlowQuery")) {
		 array_push($res,new CVolMenu("admin/phplog.php?ShowSlowQuery=10","Slow queries","Mysql Slow queries")) ;
	}

	if (HasRight("MassMail")) {
		 array_push($res,new CVolMenu("admin/adminmassmails.php","mass mails","Broadcast messages")) ;
	}

	return ($res);
} // end of VolMenu



// to solve the double name for this function 
// todo really solve this problem (only one name shall rename)
//function prepareProfileHeader($IdMember,null,$photorank) {
//   prepare_profile_header($IdMember,null,$photorank) ;
//}
