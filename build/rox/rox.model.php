<?php
/**
 * rox model
 *
 * @package rox
 * @author Felix van Hove <fvanhove@gmx.de>
 * @license http://opensource.org/licenses/gpl-license.php GNU General Public License Version 2
 */
class Rox extends PAppModel {
    protected $dao;
    
    // supported languages for translations; basis for flags in the footer
	private $_langs = array();
    
	/**
	 * @see /htdocs/bw/lib/lang.php
	 */
    public function __construct() {
        parent::__construct();
        
        // TODO: it is fun to offer the members the language of the volunteers, i.e. 'prog',
        // so I don't make any exceptions here; but we miss the flag - the BV flag ;-)
        // TODO: is it consensus we use "WelcomeToSignup" as the decision maker for languages?
        $query = '
SELECT `ShortCode`
FROM `words`
WHERE code = \'WelcomeToSignup\'';
        $result = $this->dao->query($query);
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $this->_langs[] = $row->ShortCode;
        }
        
    }
    
    /**
     * set defaults
     * TODO: check: how do we replace the files base.php and page.php? do we need a
     * replacement at all?
     * @see loadDefault in /build/mytravelbook/mytravelbook.model.ctrl
     * @see __construct in /build/rox/rox.model.ctrl
     * @param
     * @return true
     */
    public function loadDefaults() {
        if (!isset($_SESSION['lang'])) {
            $_SESSION['lang'] = 'en';
        }
        PVars::register('lang', $_SESSION['lang']);
        
        if (file_exists(SCRIPT_BASE.'text/'.PVars::get()->lang.'/base.php')) {
	        $loc = array();
	        require SCRIPT_BASE.'text/'.PVars::get()->lang.'/base.php';
	        setlocale(LC_ALL, $loc);
	        require SCRIPT_BASE.'text/'.PVars::get()->lang.'/page.php';
        }
        
        return true;
    }
    
    /**
     * @param string $lang short identifier (2 or 3 characters) for language
     * @return boolean if language is supported true, otherwise false
     */
    public function isValidLang($lang) {
        return in_array($lang, $this->_langs);
    }
    
    /**
     * @param
     * @return associative array mapping language abbreviations to 
     * 			long, English names of the language
     */
    public function getLangNames() {
        
        $l =  '';
		foreach ($this->_langs as $lang) {
		    $l .= '\'' . $lang . '\',';
		}
		$l = substr($l, 0, (strlen($l)-1));
		
        $query = '
SELECT `EnglishName`, `ShortCode`
FROM `languages`
WHERE `ShortCode` in (' . $l . ')
		';
        $result = $this->dao->query($query);
        
        $langNames = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $langNames[$row->ShortCode] = $row->EnglishName;
        }
        return $langNames;
    }

public function searchmembers(&$vars) {
	$TMember=array() ;

	$limitcount=$this->GetParam($vars, "limitcount",10); // Number of records per page
	if($limitcount > 50) $limitcount = 50;
	$vars['limitcount'] = $limitcount;

	$start_rec=$this->GetParam($vars, "start_rec",0); // Number of records per page
	$vars['start_rec'] = $start_rec;

	$order_by = $this->GetParam($vars, "OrderBy",0);
	$vars['order_by'] = $order_by;
	if ($order_by==0)  $OrderBy="members.created desc" ;
	elseif ($order_by==1)  $OrderBy="members.created asc" ;
	elseif ($order_by==2)  $OrderBy="LastLogin desc" ;
	elseif ($order_by==3)  $OrderBy="LastLogin asc" ;
	elseif ($order_by==4)  $OrderBy="Accomodation desc" ;
	elseif ($order_by==5)  $OrderBy="Accomodation asc" ;
	elseif ($order_by==6)  $OrderBy="HideBirthDate,BirthDate desc" ;
	elseif ($order_by==7)  $OrderBy="HideBirthDate,BirthDate asc" ;
	elseif ($order_by==8)  $OrderBy="NbComment desc" ;
	elseif ($order_by==9)  $OrderBy="NbComment asc" ;
	elseif ($order_by==10)  $OrderBy="countries.Name asc" ;
	elseif ($order_by==11)  $OrderBy="countries.Name desc" ;
	elseif ($order_by==12)  $OrderBy="cities.Name asc" ;
	elseif ($order_by==13)  $OrderBy="cities.Name desc" ;
	else $OrderBy="members.created desc" ; // by default find the last created members

	$OrderBy = " order by ".$OrderBy;

	$dblink="" ; // This will be used one day to query on another replicated database
	$tablelist=$dblink."members,".$dblink."cities,".$dblink."countries" ;

	if ($this->GetParam($vars, "IncludeInactive") == "on") {
		 $where=" where (members.Status='Active' or members.Status='ChoiceInActive' or members.Status='OutOfRemind')" ; // only active and inactive members
	}
	else {
		 $where=" where members.Status='Active'" ; // only active members
	}

	// Process typic Offer
	if(array_key_exists('TypicOffer', $vars)) {
		$TypicOffer = $vars['TypicOffer'];
		if(is_array($TypicOffer)) {
		 	foreach($TypicOffer as $value) {
		 	  if($value == '') continue;
	 		  $vars['TypicOffer'] = $value;
				$value = $this->GetParam($vars, 'TypicOffer');
				$where.=" and  FIND_IN_SET('".$value."',TypicOffer)" ;
			}
		}
	}

	// Process Username parameter if any
	if ($this->GetParam($vars, "Username","")!="") {
		$Username=$this->GetParam($vars, "Username") ; //
		if (strpos($Username,"*")!==false) {
			$Username=str_replace("*","%",$Username) ;
			$where.=" and Username like '".addslashes($Username)."'" ;
		}
		else {
	  	$Username=$this->fUserName($this->IdMember($this->GetParam($vars, "Username"))) ; // in case username was renamed, we do it only here to avoid problems with renamed people
		 	$where.=" and Username ='".addslashes($Username)."'" ;
		}
	}

	// Process TextToFind parameter if any
	if ($this->GetParam($vars, "TextToFind","")!="") {
	   	 $TextToFind=$this->GetParam($vars, "TextToFind") ;
		 // Special case where from the quicksearch the user is looking for a username
		 // in this case, if there is a username corresponding to TextToFind, we force to retrieve it
		 if (($this->GetParam($vars, "OrUsername",0)==1)and($this->IdMember($TextToFind)!=0)) { // in
		 	$where=$where." and Username='".addslashes($TextToFind)."'" ;
		 }
		 else {
		 	$tablelist=$tablelist.",".$dblink."memberstrads";
	 	 	$where=$where." and memberstrads.Sentence like '%".addslashes($TextToFind)."%' and memberstrads.IdOwner=members.id" ;
		 }
	}

	// Process IdRegion parameter if any
	if ($this->GetParam($vars, "IdRegion","")!="") {
	   $IdRegion=GetParam($vars, "IdRegion") ;
	 	 $where=$where." and cities.IdRegion=".$IdRegion ;
	}

	// Process Gender parameter if any
	if ($this->GetParam($vars, "Gender","0")!="0") {
   	 $Gender=$this->GetParam($vars, "Gender") ;
	 	 $where=$where." and Gender='".addslashes($Gender)."' and HideGender='No'" ;
	}

	// Process Age parameter if any
	if ($this->GetParam($vars, "Age","")!="") {
   	 $Age=$this->GetParam($vars, "Age") ;
		 if ($Age{0}==">") {
		 	$Age=substr($Age,1) ;
		 	$operation="BirthDate<(NOW() - INTERVAL ".$Age." YEAR)" ;
		 }
		 elseif ($Age{0}=="<") {
		 	$Age=substr($Age,1) ;
		 	$operation="BirthDate>(NOW() - INTERVAL ".$Age." YEAR)" ;
		 }
		 else {
			$Age1=$Age-1 ;
		 	$operation="BirthDate>(NOW()- INTERVAL ".$Age." YEAR) and BirthDate<(NOW() - INTERVAL ".$Age1." YEAR) " ;
		 }
	 	 $where=$where." and ".$operation." and HideBirthDate='No'" ;
	}
	if($order_by == 6 or $order_by == 7) $where=$where." and HideBirthDate='No'" ;


	if (!APP_User::login()) { // case user is not logged in
	   $where.=" and  memberspublicprofiles.IdMember=members.id" ; // muts be in the public profile list
	   $tablelist=$tablelist.",".$dblink."memberspublicprofiles" ;
	}

	if($this->GetParam($vars, "bounds_sw_lat") and $this->GetParam($vars, "bounds_sw_lng") and $this->GetParam($vars, "bounds_ne_lat") and $this->GetParam($vars, "bounds_ne_lng")) {
	  if($this->GetParam($vars, "bounds_sw_lng") > $this->GetParam($vars, "bounds_ne_lng")) {
		  $where .= " and ((cities.longitude >= ".$this->GetParam($vars, "bounds_sw_lng")." and cities.longitude <= 180) or (cities.longitude >= -180 and cities.longitude <= ".$this->GetParam($vars, "bounds_ne_lng")."))";
		}
		else {
		  $where .= " and (cities.longitude > ".$this->GetParam($vars, "bounds_sw_lng")." and cities.longitude < ".$this->GetParam($vars, "bounds_ne_lng").")";
		}
	  if($this->GetParam($vars, "bounds_sw_lat") > $this->GetParam($vars, "bounds_ne_lat")) {
		  $where .= " and ((cities.latitude >= ".$this->GetParam($vars, "bounds_sw_lat")." and cities.latitude <= 90) or (cities.latitude >= -90 and cities.latitude <= ".$this->GetParam($vars, "bounds_ne_lat")."))";
		}
		else {
		  $where .= " and (cities.latitude > ".$this->GetParam($vars, "bounds_sw_lat")." and cities.latitude < ".$this->GetParam($vars, "bounds_ne_lat").")";
		}
	}
	$where.=" and cities.id=members.IdCity and countries.id=cities.IdCountry" ;

	if ($this->GetParam($vars, "IdCountry",0)!= '0') {
 	   $where.=" and countries.isoalpha2='".$this->GetParam($vars, "IdCountry")."'" ;
	}
	if ($this->GetParam($vars, "IdCity",0)!=0) {
	   $where.=" and cities.id=".$this->GetParam($vars, "IdCity") ;
	}
	if ($this->GetParam($vars, "CityName","")!="") { // Case where a text field for CityName is provided
		$where.=" and (cities.Name='".$this->GetParam($vars, "CityName")."' or cities.OtherNames like '%".$this->GetParam($vars, "CityName")."%')" ;
	}
	// if a group is chosen
	if ($this->GetParam($vars, "IdGroup",0)!=0) {
		$tablelist=$tablelist.",".$dblink."membersgroups" ;
		$where.=" and membersgroups.IdGroup=".$this->GetParam($vars, "IdGroup")." and membersgroups.Status='In' and membersgroups.IdMember=members.id" ;
	}

	$str="select SQL_CALC_FOUND_ROWS count(comments.id) as NbComment,members.id as IdMember,members.BirthDate,members.HideBirthDate,members.Accomodation,members.Username as Username,members.LastLogin as LastLogin,cities.latitude as Latitude,cities.longitude as Longitude,cities.Name as CityName,countries.Name as CountryName,ProfileSummary,Gender,HideGender from (".$tablelist.") left join ".$dblink."comments on (members.id=comments.IdToMember) ".$where." group by members.id ".$OrderBy." limit ".$start_rec.",".$limitcount." /* Find people */";

//echo $str;

	$qry = $this->dao->query($str);
	$result = $this->dao->query("SELECT FOUND_ROWS() as cnt");
	$row = $result->fetch(PDB::FETCH_OBJ);
	$rCount= $row->cnt;

	$vars['rCount'] = $rCount;

	while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {

		$rr->ProfileSummary=$this->FindTrad($rr->ProfileSummary,true);
		$query = $this->dao->query("select SQL_CACHE * from ".$dblink."membersphotos where IdMember=" . $rr->IdMember . " and SortOrder=0");
		$photo = $query->fetch(PDB::FETCH_OBJ);

		if (isset($photo->FilePath)) $rr->photo=$photo->FilePath;
		else $rr->photo=$this->DummyPict($rr->Gender,$rr->HideGender) ;

		$rr->photo = $this->LinkWithPicture($rr->Username, $rr->photo, 'map_style');

		if ($rr->HideBirthDate=="No") $rr->Age=floor($this->fage_value($rr->BirthDate)+1) ;
    else $rr->Age=$this->ww("Hidden") ;

	  array_push($TMember, $rr);
	}

	return($TMember);
}

//------------------------------------------------------------------------------
// Get param returns the param value if any
private function GetParam($vars, $param, $defaultvalue = "") {

	if (isset ($vars[$param])) $m=$vars[$param];
	if (!isset($m)) return $defaultvalue;

	$m=mysql_real_escape_string($m);
	$m=str_replace("\\n","\n",$m);
	$m=str_replace("\\r","\r",$m);
	if ((stripos($m," or ")!==false)or (stripos($m," | ")!==false)) {
			//LogStr("Warning !  GetParam trying to use a <b>".addslashes($m)."</b> in a param $param for ".$_SERVER["PHP_SELF"], "alarm");
	}
	if (empty($m) and ($m!="0")){	// a "0" string must return 0 for the House Number for exemple
		return ($defaultvalue); // Return defaultvalue if none
	} else {
		return ($m);		// Return translated value
	}
} // end of GetParam

//------------------------------------------------------------------------------
// function IdMember return the numeric id of the member according to its username
// This function will TARNSLATE the username if the profile has been renamed.
// Note that a numeric username is provided no Username trnslation will be made
private function IdMember($username) {
	if (is_numeric($username)) { // if already numeric just return it
		return ($username);
	}
	$query = $this->dao->query("select SQL_CACHE id,ChangedId,Username,Status from members where Username='" . addslashes($username) . "'");
	$rr = $query->fetch(PDB::FETCH_OBJ);
	if ($rr->ChangedId > 0) { // if it is a renamed profile
		$qry = $this->dao->query("select SQL_CACHE id,Username from members where id=" . $rr->ChangedId);
		$rRenamed = $qry->fetch(PDB::FETCH_OBJ);
		$rr->id = $this->IdMember($rRenamed->Username); // try until a not renamde profile is found
	}
	if (isset ($rr->id)) {
	    // test if the member is the current member and has just bee rejected (security trick to immediately remove the current member in such a case)
		if (array_key_exists("IdMember", $_SESSION) and $rr->id==$_SESSION["IdMember"]) $this->TestIfIsToReject($rr->Status) ;
		return ($rr->id);
	}
	return (0);
} // end of IdMember

// THis TestIfIsToReject function check wether the status of the members imply an immediate logoff
// This for the case a member has just been banned
// the $Status of the member is the current status from the database
private function TestIfIsToReject($Status) {
	 if (($Status=='Rejected ')or($Status=='Banned')) {
		//LogStr("Force Logout GAMEOVER", "Login");
		Logout();
		die(" You can't use this site anymore") ;
	 }
} // end of funtion IsToReject

private function FindTrad($IdTrad,$ReplaceWithBr=false) {

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
	$query = $this->dao->query("select SQL_CACHE Sentence from memberstrads where IdTrad=" . $IdTrad . " and IdLanguage=" . $IdLanguage);
	$row = $query->fetch(PDB::FETCH_OBJ);
	if (isset ($row->Sentence)) {
		if (isset ($row->Sentence) == "") {
			//LogStr("Blank Sentence for language " . $IdLanguage . " with MembersTrads.IdTrad=" . $IdTrad, "Bug");
		} else {
		   return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
		}
	}
	// Try default eng
	$query = $this->dao->query("select SQL_CACHE Sentence from memberstrads where IdTrad=" . $IdTrad . " and IdLanguage=0");
	$row = $query->fetch(PDB::FETCH_OBJ);
	if (isset ($row->Sentence)) {
		if (isset ($row->Sentence) == "") {
			//LogStr("Blank Sentence for language 1 (eng) with memberstrads.IdTrad=" . $IdTrad, "Bug");
		} else {
		   return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
		}
	}
	// Try first language available
	$query = $this->dao->query("select  SQL_CACHE Sentence from memberstrads where IdTrad=" . $IdTrad . " order by id asc limit 1");
	$row = $query->fetch(PDB::FETCH_OBJ);
	if (isset ($row->Sentence)) {
		if (isset ($row->Sentence) == "") {
			//LogStr("Blank Sentence (any language) memberstrads.IdTrad=" . $IdTrad, "Bug");
		} else {
		   return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
		}
	}
	return ("");
} // end of FindTrad

private function ReplaceWithBR($ss,$ReplaceWith=false) {
		if (!$ReplaceWith) return ($ss);
		return(str_replace("\n","<br>",$ss));
}

//------------------------------------------------------------------------------
// fage_value return a  the age value corresponding to date
private function fage_value($dd) {
	$iDate = strtotime($dd);
	$age = (time() - $iDate) / (365 * 24 * 60 * 60);
	return ($age);
} // end of fage_value

//------------------------------------------------------------------------------
// THis function return a picture according to member gender if (any)
private function DummyPict($Gender="IDontTell",$HideGender="Yes") {
  if ($HideGender=="Yes") return ('memberphotos/' . "et.jpg") ;
  if ($Gender=="male") return ('memberphotos/' . "et_male.jpg") ;
  if ($Gender=="female") return ('memberphotos/' . "et_female.jpg") ;
  return ('memberphotos/' . "et.gif") ;
} // end of DummyPict

//------------------------------------------------------------------------------
// function LinkWithPicture build a link with picture and Username to the member profile
// optional parameter status can be used to alter the link
private function LinkWithPicture($Username, $ParamPhoto="", $Status = "") {
	$Photo=$ParamPhoto ;
	if ($Photo=="") {
		$query = $this->dao->query("select SQL_CACHE * from members where id=" . IdMember($Username));
		$rr = $query->fetch(PDB::FETCH_OBJ);
	  $Photo = $this->DummyPict($rr->Gender,$rr->HideGender) ;
	}
	// TODO: REMOVE THIS HACK:
	if (strstr($Photo,"memberphotos/")) $Photo = substr($Photo,strrpos($Photo,"/")+1);

	$thumb = $this->getthumb( 'bw/memberphotos/'.$Photo, 100, 100);
	if ($thumb === null) $thumb = "";

	return "<a href=\"".$this->bwlink("bw/member.php?cid=$Username").
		"\" title=\"" . $this->ww("SeeProfileOf", $Username) .
		"\"><img class=\"framed\" ".($Status == 'map_style' ? "style=\"float: left; margin: 4px\" " : "") . "src=\"". $this->bwlink($thumb)."\" height=\"50px\" width=\"50px\" alt=\"Profile\" /></a>";
} // end of LinkWithPicture

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
private function getthumb($file, $max_x, $max_y,$quality = 85, $thumbdir = 'thumbs',$mode = 'square')
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
// bwlink converts a relative link to an absolute link
// It works from subdirectories too. Result is always relative
// to the root directory of the site. Works in local environment too.
// e.g. "" -> "http://www.bewelcome.org/"
//      "layout/a.php" -> "http://www.bewelcome.org/layout/a.php"

private function bwlink( $target, $useTBroot = false )
{
	if (strlen($target) > 8)
	{
		if (substr_compare($target,"https://",0,8)==0 ||
		    substr_compare($target,"http://",0,7)==0)
			return $target;
	}

	if ( $useTBroot )
		$a = PVars::getObj('env')->baseuri . $target;
	else {
		$a = "http://".$_SERVER['HTTP_HOST'];
		if($_SERVER['HTTP_HOST'] == "localhost") $a .= '/bw/htdocs/';
		else $a .= '/';
		$a .= $target;
	}
	return $a;
}

//------------------------------------------------------------------------------
// function fUsername return the Username of the member according to its id
private function fUsername($cid) {
	if (!is_numeric($cid))
		return ($cid); // If cid is not numeric it is assumed to be already a username
	if (array_key_exists("IdMember", $_SESSION) and $cid == $_SESSION["IdMember"])
		return ($_SESSION["Username"]);
	$query = $this->dao->query("select SQL_CACHE username from members where id=" . $cid);
	$rr = $query->fetch(PDB::FETCH_OBJ);
	if (isset ($rr->username)) {
		return ($rr->username);
	}
	return ("");
} // end of fUsername


private function ww($str)
{
	return $str;
}

// sql_get_set returns in an array the possible set values of the colum of table name
public function sql_get_set($table, $column) {
	$query = $this->dao->query("SHOW COLUMNS FROM $table LIKE '$column'");
	$line = $query->fetch(PDB::FETCH_OBJ);
	$set = $line->Type;
	$set = substr($set, 5, strlen($set) - 7); // Remove "set(" at start and ");" at end
	return preg_split("/','/", $set); // Split into and array
} // end of sql_get_set($table,$column)

// rebuild the group list
public function sql_get_groups() {
	$query = $this->dao->query("select SQL_CACHE * from groups");
	$TGroup = array ();
	while ($rr = $query->fetch(PDB::FETCH_OBJ)) {
		array_push($TGroup, $rr);
	}
	return $TGroup;
}
}
?>