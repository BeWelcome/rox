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
/**
 * searchmembers model
 *
 * @package searchmembers
 * @author matrixpoint
 */
class Searchmembers extends PAppModel {
    
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
        $query =
            "
SELECT  ShortCode
FROM    words
WHERE   code = 'WelcomeToSignup'
            "
        ;
        $result = $this->dao->query($query);
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $this->_langs[] = $row->ShortCode;
        }
        
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
     *             long, English names of the language
     */
    public function getLangNames() {
        
        $l =  '';
        foreach ($this->_langs as $lang) {
            $l .= '\'' . $lang . '\',';
        }
        $l = substr($l, 0, (strlen($l)-1));
        
        $query =
            "
SELECT  EnglishName, ShortCode
FROM    languages
WHERE   ShortCode IN ($l)
            "
        ;
        $result = $this->dao->query($query);
        
        $langNames = array();
        while ($row = $result->fetch(PDB::FETCH_OBJ)) {
            $langNames[$row->ShortCode] = $row->EnglishName;
        }
        return $langNames;
    }
    
    public function quicksearch($searchtext)
    {
        $TList = array ();
        $dblink="" ; // This will be used one day to query on another replicated database
        
        if(strlen($searchtext) < 2) return $TList;
        
        // search for username
        $where = '';
        $tablelist = '';
        if (!APP_User::login()) { // case user is not logged in
            $where = "
AND memberspublicprofiles.IdMember=members.id"
            ; // must be in the public profile list
            $tablelist = ",memberspublicprofiles" ;
        }
        $str =
            "
SELECT
    members.id AS IdMember,
    Username,
    Gender,
    HideGender,
    ProfileSummary
FROM
    members $tablelist
WHERE
    Status = 'Active'   AND
    (Username LIKE '%" . mysql_real_escape_string($searchtext). "%')
    $where
LIMIT 20
            "
        ;
        $qry = $this->dao->query($str);
    
    
        while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
            $str =
                "
SELECT
    countries.Name AS CountryName,
    cities.Name as CityName
FROM
    countries,
    members,
    cities
WHERE
    members.IdCity=cities.id AND
    countries.id=cities.IdCountry AND
    members.id = $rr->IdMember
                "
            ;
            $result = $this->dao->query($str);
            $cc = $result->fetch(PDB::FETCH_OBJ);
            $rr->CountryName=$cc->CountryName ;
            $rr->CityName=$cc->CityName ;
            $rr->ProfileSummary = $this->ellipsis($this->FindTrad($rr->ProfileSummary), 100);
            $rr->result = '';
    
            $query = $this->dao->query(
                "
SELECT SQL_CACHE
    *
FROM
    ".$dblink."membersphotos
WHERE
    IdMember=" . $rr->IdMember . " AND
    SortOrder=0
                "
            );
            $photo = $query->fetch(PDB::FETCH_OBJ);
    
            if (isset($photo->FilePath)) $rr->photo=$photo->FilePath;
            else $rr->photo=$this->DummyPict($rr->Gender,$rr->HideGender) ;
            $rr->photo = MOD_layoutbits::linkWithPicture($rr->Username, $rr->photo);
            array_push($TList, $rr);
        }
    
        // search in MembersTrads
        $str =
            "
SELECT
    members.id AS IdMember,
    Username,
    Gender,
    HideGender,
    memberstrads.Sentence AS result,
    ProfileSummary
FROM
    members,
    memberstrads
WHERE
    memberstrads.IdOwner=members.id AND
    Status = 'Active' AND
    memberstrads.Sentence LIKE '%" . mysql_real_escape_string($searchtext) . "%'
ORDER BY username
LIMIT 20
            "
        ;
        $qry = $this->dao->query($str);
        while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
            $str =
                "
SELECT
    countries.Name AS CountryName,
    cities.Name AS CityName
FROM
    countries,
    members,
    cities
WHERE
    members.IdCity=cities.id AND
    countries.id=cities.IdCountry AND
    members.id= $rr->IdMember
                "
            ;
            $result = $this->dao->query($str);
            $cc = $result->fetch(PDB::FETCH_OBJ);
            $rr->CountryName=$cc->CountryName ;
            $rr->CityName=$cc->CityName ;
            $rr->result = $this->ellipsis($rr->result, 100);
            $rr->ProfileSummary = $this->ellipsis($this->FindTrad($rr->ProfileSummary), 100);
    
            $query = $this->dao->query(
                "
SELECT SQL_CACHE
    *
FROM
    ".$dblink."membersphotos
WHERE
    IdMember  = $rr->IdMember  AND
    SortOrder = 0
                "
            );
            $photo = $query->fetch(PDB::FETCH_OBJ);
    
            if (isset($photo->FilePath)) $rr->photo=$photo->FilePath;
            else $rr->photo=$this->DummyPict($rr->Gender,$rr->HideGender) ;
            $rr->photo = MOD_layoutbits::linkWithPicture($rr->Username, $rr->photo);
            array_push($TList, $rr);
        }
        return $TList;
    }
    
    private function ellipsis($str, $len)
    {
        $length = strlen($str);
        if($length <= $len) return $str;
        return substr($str, 0, $len).'...';
    }
    
    public function searchmembers(&$vars) {
        $TMember=array() ;
    
        $limitcount=$this->GetParam($vars, "limitcount",10); // Number of records per page
        if($limitcount > 100) $limitcount = 100;
        $vars['limitcount'] = $limitcount;
    
        $start_rec=$this->GetParam($vars, "start_rec",0); // Number of records per page
        $vars['start_rec'] = $start_rec;
    
        $order_by = $this->GetParam($vars, "OrderBy",0);
        $order_by_direction = $this->GetParam($vars, "OrderByDirection",0);
        
        if($order_by) {
            $OrderBy = "
ORDER BY $order_by $order_by_direction"
            ;
        } else {
            $OrderBy = "
ORDER BY members.created"
            ;
        }
    
        $dblink="" ; // This will be used one day to query on another replicated database
        $tablelist=$dblink."members,".$dblink."cities,".$dblink."countries" ;
    
        if ($this->GetParam($vars, "IncludeInactive", "0") == "1") {
            $where = "
WHERE (
    members.Status='Active' OR
    members.Status='ChoiceInActive' OR
    members.Status='OutOfRemind'
)"
            ; // only active and inactive members
        }
        else {
            $where = "
WHERE members.Status='Active'"
            ; // only active members
        }
    
        // Process Accomodation
        $where_accomodation = array();
        if(array_key_exists('Accomodation', $vars)) {
            $Accomodation = $vars['Accomodation'];
            if(is_array($Accomodation)) {
                foreach($Accomodation as $value) {
                    if($value == '') continue;
                    $vars['Accomodation'] = $value;
                    $value = $this->GetParam($vars, 'Accomodation');
                    $where_accomodation[] = "Accomodation='$value'" ;
                }
            }
        }
        if($where_accomodation) $where .= "
AND (".implode(" OR ", $where_accomodation).") "
        ;
    
        // Process typic Offer
        $where_typicoffer = array();
        if(array_key_exists('TypicOffer', $vars)) {
            $TypicOffer = $vars['TypicOffer'];
            if(is_array($TypicOffer)) {
                foreach($TypicOffer as $value) {
                    if($value == '') continue;
                    $vars['TypicOffer'] = $value;
                    $value = $this->GetParam($vars, 'TypicOffer');
                    $where_typicoffer[] = "FIND_IN_SET('$value',TypicOffer)" ;
                }
            }
        }
        if($where_typicoffer) $where .= "
AND (".implode(" AND ", $where_typicoffer).")";
    
        // Process Username parameter if any
        if ($this->GetParam($vars, "Username","")!="") {
            $Username=$this->GetParam($vars, "Username") ; //
            if (strpos($Username,"*")!==false) {
                $Username=str_replace("*","%",$Username) ;
                $where.="
AND Username LIKE '".mysql_real_escape_string($Username)."'"
                ;
            } else {
                $Username=$this->fUserName($this->IdMember($this->GetParam($vars, "Username"))) ; // in case username was renamed, we do it only here to avoid problems with renamed people
                $where.="
AND Username ='".mysql_real_escape_string($Username)."'"
                ;
            }
        }
    
        // Process TextToFind parameter if any
        if ($this->GetParam($vars, "TextToFind","")!="") {
            $TextToFind=$this->GetParam($vars, "TextToFind") ;
            // Special case where from the quicksearch the user is looking for a username
            // in this case, if there is a username corresponding to TextToFind, we force to retrieve it
            if (($this->GetParam($vars, "OrUsername",0)==1)and($this->IdMember($TextToFind)!=0)) { // in
                $where=$where." AND Username='".mysql_real_escape_string($TextToFind)."'" ;
            } else {
                $tablelist=$tablelist.",".$dblink."memberstrads";
                $where .= "
AND memberstrads.Sentence LIKE '%".mysql_real_escape_string($TextToFind)."%'
AND memberstrads.IdOwner=members.id"
                ;
            }
        }
    
        // Process IdRegion parameter if any
        if ($this->GetParam($vars, "IdRegion","")!="") {
            $IdRegion=GetParam($vars, "IdRegion") ;
            $where=$where." AND cities.IdRegion=".$IdRegion ;
        }
    
        // Process Gender parameter if any
        if ($this->GetParam($vars, "Gender","0")!="0") {
            $Gender=$this->GetParam($vars, "Gender") ;
            $where .= "
AND Gender='".mysql_real_escape_string($Gender)."'
AND HideGender='No'" ;
        }
    
        // Process Age parameters
        $operation = '';
        if ($this->GetParam($vars, "MinimumAge","0")!=0) {
            $operation .= "
AND members.BirthDate<=(NOW() - INTERVAL ".$this->GetParam($vars, "MinimumAge")." YEAR)"
            ;
        }
        if ($this->GetParam($vars, "MaximumAge","0")!=0) {
            $operation .= "
AND members.BirthDate >= (NOW() - INTERVAL ".$this->GetParam($vars, "MaximumAge")." YEAR)"
            ;
        }
        if($operation) $where .= "
$operation AND members.HideBirthDate='No'"
        ;
        
        // If the SortOrder is "BirthDate", hide the members that don't want to show their age.
        if($order_by == 'BirthDate') $where .= "
AND members.HideBirthDate='No'"
        ;
        
        if (!APP_User::login()) { // case user is not logged in
            $where .= "
AND memberspublicprofiles.IdMember=members.id"
            ; // must be in the public profile list
            $tablelist=$tablelist.",".$dblink."memberspublicprofiles" ;
        }
        
        if($this->GetParam($vars, "mapsearch")) {
            if($this->GetParam($vars, "bounds_sw_lng") > $this->GetParam($vars, "bounds_ne_lng")) {
                $where .= "
AND ((
    cities.longitude >= ".$this->GetParam($vars, "bounds_sw_lng")."
    AND
    cities.longitude <= 180
) OR (
    cities.longitude >= -180
    AND
    cities.longitude <= ".$this->GetParam($vars, "bounds_ne_lng")."
))"
                ;
            } else {
                $where .= "
AND (
    cities.longitude > ".$this->GetParam($vars, "bounds_sw_lng")."
    AND
    cities.longitude < ".$this->GetParam($vars, "bounds_ne_lng")."
)"
                ;
            }
            if($this->GetParam($vars, "bounds_sw_lat") > $this->GetParam($vars, "bounds_ne_lat")) {
                $where .= "
AND ((
    cities.latitude >= ".$this->GetParam($vars, 'bounds_sw_lat')."
    AND
    cities.latitude <= 90
) OR (
    cities.latitude >= -90
    AND
    cities.latitude <= ".$this->GetParam($vars, "bounds_ne_lat")."
))"
                ;
            } else {
                $where .= "
AND (
    cities.latitude > ".$this->GetParam($vars, "bounds_sw_lat")."
    AND
    cities.latitude < ".$this->GetParam($vars, "bounds_ne_lat")."
)"
                ;
            }
        }
        $where.="
AND cities.id=members.IdCity
AND countries.id=cities.IdCountry"
        ;
    
        if ($this->GetParam($vars, "IdCountry",0)!= '0') {
            $where.=" AND countries.isoalpha2='".$this->GetParam($vars, "IdCountry")."'" ;
        }
        if ($this->GetParam($vars, "IdCity",0)!=0) {
           $where.=" AND cities.id=".$this->GetParam($vars, "IdCity") ;
        }
        if ($this->GetParam($vars, "CityName","")!="") { // Case where a text field for CityName is provided
            $where=$where."
AND (
    cities.Name='".$this->GetParam($vars, "CityName")."'
    OR
    cities.OtherNames LIKE '%". $this->GetParam($vars, "CityName")."%'
)"
            ;
        }
		else {
			$where=" WHERE 1=0" ; // If there is no city, no search
		}
        // if a group is chosen
        if ($this->GetParam($vars, "IdGroup",0)!=0) {
            $tablelist=$tablelist.",".$dblink."membersgroups" ;
            $where.="
AND membersgroups.IdGroup=".$this->GetParam($vars, "IdGroup")."
AND membersgroups.Status='In'
AND membersgroups.IdMember=members.id"
            ;
        }
    
        $str=
           "
SELECT SQL_CALC_FOUND_ROWS
    count(comments.id) AS NbComment,
    members.id AS IdMember,
    members.BirthDate,
    members.HideBirthDate,
    members.Accomodation,
    members.Username AS Username,
    date_format(members.LastLogin,'%Y-%m-%d') AS LastLogin,
    cities.latitude AS Latitude,
    cities.longitude AS Longitude,
    cities.Name AS CityName,
    countries.Name AS CountryName,
    ProfileSummary,
    Gender,
    HideGender
FROM
    ($tablelist)
    LEFT JOIN $dblink comments ON (members.id=comments.IdToMember)
$where
GROUP BY members.id
$OrderBy
LIMIT $start_rec,$limitcount
            "
        ;
    
        //echo $str;
        MOD_log::get()->write("doing a [".$str."]", "Search");
    
        $qry = $this->dao->query($str);
        $result = $this->dao->query("SELECT FOUND_ROWS() as cnt");
        $row = $result->fetch(PDB::FETCH_OBJ);
        $rCount= $row->cnt;

        MOD_log::get()->write("result =".$rCount, "Search");
		
        $vars['rCount'] = $rCount;
        
        while ($rr = $qry->fetch(PDB::FETCH_OBJ)) {
            
            $rr->ProfileSummary=$this->ellipsis($this->FindTrad($rr->ProfileSummary,true), 200);
            $query = $this->dao->query("
SELECT SQL_CACHE
    *
FROM
    ".$dblink."membersphotos
WHERE
    IdMember=" . $rr->IdMember . " AND
    SortOrder=0
                "
            );
            $photo = $query->fetch(PDB::FETCH_OBJ);
            
            if (isset($photo->FilePath)) $rr->photo=$photo->FilePath;
            else $rr->photo=$this->DummyPict($rr->Gender,$rr->HideGender) ;
            
            $rr->photo = MOD_layoutbits::linkWithPicture($rr->Username, $rr->photo, 'map_style');
            
            if ($rr->HideBirthDate=="No") $rr->Age=floor($this->fage_value($rr->BirthDate)) ;
            else $rr->Age=$this->ww("Hidden") ;
            
            array_push($TMember, $rr);
        }
        
        return($TMember);
    }
    
    private static function test() {}
    
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
        if (empty($m) and ($m!="0")){    // a "0" string must return 0 for the House Number for exemple
            return ($defaultvalue); // Return defaultvalue if none
        } else {
            return ($m);        // Return translated value
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
        $query = $this->dao->query(
           "
SELECT SQL_CACHE
    id,
    ChangedId,
    Username,
    Status
FROM
    members
WHERE
    Username='" . mysql_real_escape_string($username) . "'
            "
        );
        $rr = $query->fetch(PDB::FETCH_OBJ);
        if(!$rr) return (0);
        if ($rr->ChangedId > 0) { // if it is a renamed profile
            $qry = $this->dao->query(
                "
SELECT SQL_CACHE
    id,
    Username
FROM
    members
WHERE
    id = $rr->ChangedId
                "
            );
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
        $query = $this->dao->query(
            "
SELECT SQL_CACHE
    Sentence
FROM
    memberstrads
WHERE
    IdTrad = $IdTrad AND
    IdLanguage= $IdLanguage
            "
        );
        $row = $query->fetch(PDB::FETCH_OBJ);
        if (isset ($row->Sentence)) {
            if (isset ($row->Sentence) == "") {
                //LogStr("Blank Sentence for language " . $IdLanguage . " with MembersTrads.IdTrad=" . $IdTrad, "Bug");
            } else {
               return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
            }
        }
        // Try default eng
        $query = $this->dao->query(
           "
SELECT SQL_CACHE
    Sentence
FROM
    memberstrads
WHERE
    IdTrad = $IdTrad  AND
    IdLanguage = 0
            "
        );
        $row = $query->fetch(PDB::FETCH_OBJ);
        if (isset ($row->Sentence)) {
            if (isset ($row->Sentence) == "") {
                //LogStr("Blank Sentence for language 1 (eng) with memberstrads.IdTrad=" . $IdTrad, "Bug");
            } else {
               return (strip_tags($this->ReplaceWithBr($row->Sentence,$ReplaceWithBr), $AllowedTags));
            }
        }
        // Try first language available
        $query = $this->dao->query(
            "
SELECT SQL_CACHE
    Sentence
FROM
    memberstrads
WHERE
    IdTrad = $IdTrad
ORDER BY id ASC
LIMIT 1
            "
        );
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
    // THis function return a picture according to member gender if (any)
    private function DummyPict($Gender="IDontTell",$HideGender="Yes")
    {
        return 'images/misc/empty_avatar_30_30.png';
        if ($HideGender=="Yes") return ('/memberphotos/et.jpg') ;
        if ($Gender=="male") return ('/memberphotos/et_male.jpg') ;
        if ($Gender=="female") return ('/memberphotos/et_female.jpg') ;
        return ('/memberphotos/et.gif') ;
    } // end of DummyPict
    
    //------------------------------------------------------------------------------
    // function LinkWithPicture build a link with picture and Username to the member profile
    // optional parameter status can be used to alter the link
    private function LinkWithPicture($Username, $ParamPhoto="", $Status = "")
    {
        $words = new MOD_words();
        $Photo=$ParamPhoto ;
    
        if ($Photo=="") {
            $query = $this->dao->query(
                "
SELECT SQL_CACHE
    *
FROM
    members
WHERE
    id = " . IdMember($Username)) . "
                "
            ;
            $rr = $query->fetch(PDB::FETCH_OBJ);
            $Photo = $this->DummyPict($rr->Gender,$rr->HideGender) ;
        }
    
        $thumb = $this->getthumb($Photo, 100, 100);
        if ($thumb === null) $thumb = "";
        
        if($Status == 'map_style')
            return "<a href=\"javascript:newWindow('$Username')\" title=\"" . $words->getBuffered("SeeProfileOf", $Username) .
                "\"><img class=\"framed\" style=\"float: left; margin: 4px\" src=\"". $this->bwlink($thumb)."\" height=\"50px\" width=\"50px\" alt=\"Profile\" /></a>";
    
        return "<a href=\"".$this->bwlink("bw/member.php?cid=$Username").
            "\" title=\"" . $words->getBuffered("SeeProfileOf", $Username) .
            "\"><img class=\"framed\" src=\"". $this->bwlink($thumb)."\" height=\"50px\" width=\"50px\" alt=\"Profile\" /></a>";
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
    private function getthumb($file = "", $max_x, $max_y, $quality = 85, $thumbdir = 'thumbs',$mode = 'square')
    {
        // TODO: analyze MIME-TYPE of the input file (not try / catch)
        // TODO: error analysis of wrong paths
        // TODO: dynamic prefix (now: /th/)
    
        if($file == "") return null;
    
        $filename = basename($file);
        $filename_noext = substr($filename, 0, strrpos($filename, '.'));
        $filepath = getcwd()."/bw/memberphotos";
        if($_SERVER['HTTP_HOST'] == 'localhost')
            $wwwpath = "http://".$_SERVER['HTTP_HOST']."/bw/htdocs/bw/memberphotos";
        else
            $wwwpath = "http://".$_SERVER['HTTP_HOST']."/bw/memberphotos";
        
        $thumbfile = $filename_noext.'.'.$mode.'.'.$max_x.'x'.$max_y.'.jpg';
    
        if(is_file("$filepath/$thumbdir/$thumbfile")) return "$wwwpath/$thumbdir/$thumbfile";
    
        // locate file
    
        if (!is_file("$filepath/$filename")) return null;
    
        // TODO: bw_error("get_thumb: no file found");
    
        if(!is_dir("$filepath/$thumbdir")) return null;
    
        // TODO: bw_error("get_thumb: no directory found");
    
        ini_set("memory_limit",'64M'); //jeanyves increasing the memory these functions need a lot
        // read image
        $image = false;
        if (!$image) $image = @imagecreatefromjpeg("$filepath/$filename");
        if (!$image) $image = @imagecreatefrompng("$filepath/$filename");
        if (!$image) $image = @imagecreatefromgif("$filepath/$filename");
    
        if($image == false) return null;
    
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
        imagejpeg($thumb, "$filepath/$thumbdir/$thumbfile", $quality);
        return "$wwwpath/$thumbdir/$thumbfile";
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
        $query = $this->dao->query(
            "
SELECT SQL_CACHE
    username
FROM
    members
WHERE
    id = $cid
            "
        );
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
        $query = $this->dao->query(
            "
SHOW COLUMNS
FROM $table
LIKE '$column'
            "
        );
        $line = $query->fetch(PDB::FETCH_OBJ);
        $set = $line->Type;
        $set = preg_replace("/.*\('(.*)'\).*/", "$1", $set);
        return preg_split("/','/", $set); // Split into and array
    } // end of sql_get_set($table,$column)
    
    // rebuild the group list
    public function sql_get_groups() {
        $query = $this->dao->query(
            "
SELECT SQL_CACHE
    *
FROM
    groups
            "
        );
        $TGroup = array ();
        while ($rr = $query->fetch(PDB::FETCH_OBJ)) {
            array_push($TGroup, $rr);
        }
        return $TGroup;
    }
    
    // result sort order == array of codes in the words table, keyed by sort field.
    public function get_sort_order()
    {
        return array(
            'members.created' => 'FindPeopleNewMembers',
            'BirthDate' => 'Age',
            'LastLogin' => 'Lastlogin',
            'NbComment' => 'Comments',
            'Accomodation' => 'Accomodation',
            'cities.Name' => 'City',
            'countries.Name' => 'Country'
        );
    }
}


?>
