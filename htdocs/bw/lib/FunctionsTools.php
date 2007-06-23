<?php
require_once "FunctionsCrypt.php";
require_once("rights.php");
require_once("mailer.php");

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
		
	if (isset($_SESSION['IdLanguage'])) {
		 $IdLanguage=$_SESSION['IdLanguage'] ;
	}
	else {
		 $IdLanguage=0 ; // by default laguange 0
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
	return ($rr->Name);
}

//------------------------------------------------------------------------------
// This function return the name of a city according to the IdCity parameter
function getcityname($IdCity) {
	$rr = LoadRow("select  SQL_CACHE Name from cities where id=" . $IdCity);
	return ($rr->Name);
}

//------------------------------------------------------------------------------
// This function return the name of a country according to the IdCountry parameter
function getcountryname($IdCountry) {
	$rr = LoadRow("select  SQL_CACHE Name from countries where id=" . $IdCountry);
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
		$str = "select SQL_CACHE cities.id,cities.Name,cities.OtherNames,regions.name as RegionName from (cities) left join regions on (cities.IdRegion=regions.id) where  cities.IdCountry=" . $IdCountry . " and ActiveCity='True' and cities.Name like '".$CityName."%' order by cities.population desc";
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
// Get param returns the param value (in get or post) if any it intented to return an int
function GetParam($param, $defaultvalue = "") {
	if (isset ($_GET[$param])) {
	    $m=$_GET[$param];
	}
	if (isset ($_POST[$param])) {
	    $m=$_POST[$param];
	}


	$m=mysql_real_escape_string($m);
	$m=str_replace("\\n","\n",$m);
	$m=str_replace("\\r","\r",$m);
	if ((stripos($m," or ")!==false)or (stripos($m," | ")!==false)) {
			LogStr("Warning ! trying to use a <b>".addslashes($m)."</b> in a param $param for ".$_SERVER["PHP_SELF"], "alarm");
	}
	if (empty($m) and ($m!="0")){	// a "0" string must return 0 for the House Number for exemple 
		return ($defaultvalue); // Return defaultvalue if none
	} else {
		return ($m);		// Return translated value
	}
} // end of GetParam


//----------------------------------------------------------------------------------------- 
// GetParamStr returns the param value (in get or post) if any it intented to return a string
function GetStrParam($param, $defaultvalue = "") {
	if (isset ($_GET[$param])) {
	    $m=$_GET[$param];
	}
	if (isset ($_POST[$param])) {
	    $m=$_POST[$param];
	}


	$m=mysql_real_escape_string($m);
	$m=str_replace("\\n","\n",$m);
	$m=str_replace("\\r","\r",$m);
	if ((stripos($m," or ")!==false)or (stripos($m," | ")!==false)) {
			LogStr("Warning ! trying to use a <b>".addslashes($m)."</b> in a param $param for ".$_SERVER["PHP_SELF"], "alarm");
	}
	if (empty($m) and ($m!="0")){	// a "0" string must return 0 for the House Number for exemple 
		return ($defaultvalue); // Return defaultvalue if none
	} else {
		return ($m);		// Return translated value
	}
} // end of GetStrParam


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
	
	global $_SYSHCVOL;
	
	// TODO: REMOVE THIS HACK:
	if (strstr($Photo,"memberphotos/"))
		$Photo = substr($Photo,strrpos($Photo,"/")+1);
		
	$orig = $_SYSHCVOL['IMAGEDIR']."/".$Photo;
		
	$thumb = getthumb( $_SYSHCVOL['IMAGEDIR']."/".$Photo, 100, 100);
	if ($thumb === null)
		$thumb = "";
	$thumb = str_replace( $_SYSHCVOL['IMAGEDIR'],$_SYSHCVOL['WWWIMAGEDIR'],$thumb );

	return "<a href=\"".bwlink("member.php?cid=$Username").
		"\" title=\"" . ww("SeeProfileOf", $Username) . 
		"\">\n<img class=\"framed\" src=\"". bwlink($thumb)."\" height=\"50px\" width=\"50px\" alt=\"Profile\" /></a>\n";
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
	$rr = LoadRow("select SQL_CACHE id,ChangedId,Username from members where Username='" . addslashes($username) . "'");
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
	return("<img height=\"11px\" width=\"16px\"src=\"images/flags/".$flag."\" alt=\"".$flag."\" title=\"".$title."\" />");
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
	
  if ($IdMember==0) return("") ;

	$rr = LoadRow("select SQL_CACHE * from membersphotos where IdMember=" . $IdMember . " and SortOrder=0");
	if (isset($rr->FilePath)) return($rr->FilePath) ;
	else return("") ;
} // end of MyPict

//------------------------------------------------------------------------------
// THis function retrun true if the member is in the status list
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
	if (in_array($rr->Status,$tt)) return (true) ;
	return (false) ;
} // end of LogVisit




// to solve the double name for this function 
// todo really solve this problem (only one name shall rename)
//function prepareProfileHeader($IdMember,null,$photorank) {
//   prepare_profile_header($IdMember,null,$photorank) ;
//}
