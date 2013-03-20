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
 * Collection of functions that create elements for a page.
 *
 * An example for its use:
 * $layoutbits = MOD_layoutbits::get();  // get the singleton instance
 * $id = $geo->getCityID($cityname);
 *
 * @author Andreas (bw/cs:lemon-head)
 */
class MOD_layoutbits
{

    /**
     * Quasi-constant functions for userthumbnails
     *
     */
    public static function PIC_100_100($username,$picfile='',$style="framed") {
        return self::linkWithPictureVar($username,$height=100,$width=100,$quality=85,$picfile,$style);
    }
    public static function PIC_50_50($username,$picfile='',$style="framed") {
        return self::linkWithPictureVar($username,$height=50,$width=50,$quality=85,$picfile,$style);
    }
    public static function PIC_40_40($username,$picfile='',$style="framed") {
        return self::linkWithPictureVar($username,$height=40,$width=40,$quality=100,$picfile,$style);
    }
    public static function PIC_30_30($username,$picfile='',$style="framed") {
        return self::linkWithPictureVar($username,$height=30,$width=30,$quality=100,$picfile,$style);
    }
    public static function PIC_15_15($username,$picfile='',$style="framed") {
        return self::linkWithPictureVar($username,$height=15,$width=15,$quality=100,$picfile,$style);
    }

    /**
     * Singleton instance
     *
     * @var MOD_layoutbits
     * @access private
     */
    private static $_instance;

    public function __construct()
    {
        $db = PVars::getObj('config_rdbms');
        if (!$db) {
            throw new PException('DB config error!');
        }
        $dao = PDB::get($db->dsn, $db->user, $db->password);
        $this->dao =& $dao;
    }

    /**
     * singleton getter
     *
     * @param void
     * @return PApps
     */
    public static function get()
    {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }


    protected function member_pic_url() {
        return 'members/avatar/';
    }

    public static function test() {}
    /**
     * function LinkWithPicture build a link with picture and Username to the member profile.
     * Optional parameter status can be used to alter the link.
     * Old version found in FunctionsTools.php
     *
     * Usually it is more convenient to use
     * smallUserPic_userId($userId)
     * or
     * smallUserPic_username($username)
     *
     * @param string $username
     * @param string $pic alternative picture path
     * @param string $mode can be used to enable 'map_style'
     * @return string html-formatted link with picture
     */
    public static function linkWithPicture($username, $picfile="", $mode="")
    {
        $words = new MOD_words();
/* disabling references to memberphotos
        if(!is_file(getcwd().'/bw'.$picfile)) {
            // get a picture by username */
            $thumburl = self::smallUserPic_username($username);
/*
        } else {
            $thumburl = self::_getThumb($picfile, 100, 100);
            if ($thumburl === null) $thumburl = "bw/";
        }
        */
        if ($mode == 'map_style') {
            // TODO: why return a window with "$username" ??
            return
                '<a '.
                    'href="javascript:newWindow('."'".$username."'".')" '.
                    'title="' . $words->getBuffered('SeeProfileOf', $username).'"'.
                '><img '.
                    'class="framed" '.
                    'style="float: left; margin: 4px" '.
                    'src="'.$thumburl.'" '.
                    'height="50px" '.
                    'width="50px" '.
                    'alt="Profile" '.
                '/></a>'
            ;
        } else {
            return
                '<a '.
                    'href="members/'.$username.'" '.
                    'title="'.$words->getBuffered('SeeProfileOf', $username).'" '.
                '><img '.
                    'class="framed" '.
                    'src="'.$thumburl.'" '.
                    'height="50px" '.
                    'width="50px" '.
                    'alt="Profile" '.
                '/></a>'
            ;
        }
    }

    /**
     * function LinkWithPicture build a link with picture and Username to the member profile.
     * Optional parameter status can be used to alter the link.
     * Old version found in FunctionsTools.php
     *
     * Usually it is more convenient to use
     * smallUserPic_userId($userId)
     * or
     * smallUserPic_username($username)
     *
     * @param string $username
     * @param string $height of picture
     * @param string $width of picture
     * @param string $quality of picture
     * @param string $picfile alternative picture path
     * @param string $style css-class for the image-tag
     * @return string html-formatted link with picture
     */
    public static function linkWithPictureVar($username,$height,$width,$quality,$picfile,$style)
    {
        $words = new MOD_words();
        $thumburl = self::member_pic_url().$username.'?'.$height.'_'.$width;
        /*
        if(!is_file(getcwd().'/bw'.$picfile)) {
            // get a picture by username
            $thumburl = self::smallUserPic_usernameVar($username,$height,$width,$quality) ;
        } else {
            $thumburl = self::_getThumb($picfile,$height,$width,$quality);
            if ($thumburl === null) $thumburl = "bw/";
        }
        */
            return
                '<a '.
                    'href="members/'.$username.'" '.
                    'title="'.$words->getBuffered('SeeProfileOf', $username).'" '.
                '><img height="'.$height.'" width="'.$width.'" '.
                    'class="'.$style.'" '.
                    'src="'.$thumburl.'" '.
                    'alt="Profile" '.
                '/></a>'
            ;
    }

    /**
     * 100x100 avatar picture for forums etc
     *
     * @param string $username
     *
     */
    public static function smallUserPic_userId($userId)
    {
        //first of all, check if a pic in the new data folder is available
        $avatarDir = new PDataDir('user/avatars');
        if($avatarDir->fileExists((int)$userId.'_xs'))
            return 'members/avatar/'.$userId.'/?xs';
        
        $picfile = self::userPic_userId($userId);
        $thumbfile = self::_getThumb($picfile, 100, 100, 100);
        return $thumbfile;
    }



    /**
     * 100x100 avatar picture for forums etc
     *
     * @param string $username
     *
     */
    public static function smallUserPic_username($username)
    {
        $picfile = self::userPic_username($username);
        //$thumbfile = self::_getThumb($picfile, 100, 100, 100);
        //return $thumbfile;
        return $picfile;
    }


    /**
     * XxX avatar picture for all over the website
     *
     * @param string $username
     *
     */
    public static function smallUserPic_usernameVar($username,$height,$width,$quality)
    {
        return self::member_pic_url().$username.'/?xs';
        /*
        $picfile = self::userPic_username($username);
        $thumbfile = self::_getThumb($picfile,$height,$width,$quality);
        return $thumbfile;
        */
    }


    public static function userPic_userId($userId)
    {
        return self::member_pic_url().$userId.'/';
        /*
        // check if user is logged in
        if (!APP_User::isBWLoggedIn('NeedMore,Pending')) {
            // check if pic owner has a public profile
            if (!( self::get()->dao->query(
                'SELECT SQL_CACHE IdMember '.
                'FROM memberspublicprofiles '.
                "WHERE IdMember='$userId'"
            )->fetch(PDB::FETCH_OBJ))) {
                // hide the pic
                return self::_incognitoPic_userId($userId);
            }
        }

        // now we can safely display the user pic

        $sql_result = self::get()->dao->query(
            'SELECT SQL_CACHE FilePath '.
            'FROM membersphotos '.
            "WHERE IdMember='$userId' ".
            'ORDER BY membersphotos.SortOrder'
        );

        // look if any of the pics exists
        while ($row = $sql_result->fetch(PDB::FETCH_OBJ)) {
            if(is_file(getcwd().'/bw'.$row->FilePath)) {
                return $row->FilePath;
            }
        }
        return self::_dummyPic_userId($userId);
        */
    }



    public static function userPic_username($username)
    {
        return self::member_pic_url().$username;
    }



    /**
     *
     * Thumbnail creator. (by markus5, Markus Hutzler 25.02.2007)
     * tested with GD Version: bundled (2.0.28 compatible)
     * with GIF Read Support: Enabled
     * with JPG Support: Enabled
     * with PNG Support: Enabled
     *
     * this function creates a thumbnail of a JPEG, GIF or PNG image
     * file: path (with /)!!!
     * max_x / max_y delimit the maximal size. default = 100 (it keeps the ratio)
     * the quality can be set. default = 85
     * this function returns the thumb filename or null
     *
     * modified by Fake51
     * $mode specifies if the new image is based on a cropped and resized version of the old, or just a resized
     * $mode = "square" means a cropped version
     * $mode = "ratio" means merely resized
     */
    private static function _getThumb(
                                    $file,
                                    $max_x, $max_y,
                                    $quality = 85,
                                    $thumbdir = 'thumbs',
                                    $mode = 'square'
                                )
    {
        // TODO: analyze MIME-TYPE of the input file (not try / catch)
        // TODO: error analysis of wrong paths
        // TODO: dynamic prefix (now: /th/)

        // method appears to work in old memberphotos folder, so I'm disabling it for now
        /* if($file == "") */ return null;

        $filename = basename($file);
        $filename_noext = substr($filename, 0, strrpos($filename, '.'));
        $filepath = getcwd()."/bw/memberphotos";
        $wwwpath = PVars::getObj('env')->baseuri."bw/memberphotos";
    	$avatarDir = new PDataDir('user/avatars');
        
        $thumbfile = $filename_noext.'.'.$mode.'.'.$max_x.'x'.$max_y.'.jpg';

        if(is_file("$filepath/$thumbdir/$thumbfile")) return "$wwwpath/$thumbdir/$thumbfile";

        // look if original file exists
        if (!is_file($filepath.'/'.$filename)) return 'bw/';

        // TODO: bw_error("get_thumb: no file found");

        // look if thumbnail directory exists
        if(!is_dir("$filepath/$thumbdir")) return 'bw/';

        // TODO: bw_error("get_thumb: no directory found");

        ini_set("memory_limit",'64M'); //jeanyves increasing the memory these functions need a lot

        // read image - try different image types
        $image = false;
        if (!$image) $image = @imagecreatefromjpeg("$filepath/$filename");
        if (!$image) $image = @imagecreatefrompng("$filepath/$filename");
        if (!$image) $image = @imagecreatefromgif("$filepath/$filename");

        // look if reading the image was successful
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


    /**
     * This function return a picture according to member gender if (any).
     * It is used when no personal picture is found.
     *
     * @param integer $userId
     * @return string path+filename of the dummy picture
     */
    private static function _dummyPic_userId($userId)
    {
        return 'images/misc/empty_avatar.png';
        $row = self::get()->dao->query(
            'SELECT SQL_CACHE Gender, HideGender '.
            'FROM members '.
            "WHERE id='$userId'"
        )->fetch(PDB::FETCH_OBJ);
        if(!$row) return '/memberphotos/et.jpg';
        if ($row->HideGender=="Yes") return '/memberphotos/et.jpg';
        else if ($row->Gender=="male") return '/memberphotos/et_male.jpg';
        else if ($row->Gender=="female") return '/memberphotos/et_female.jpg';
        else return '/memberphotos/et.jpg';
    }


    /**
     * Picture for members with non-public profile.
     * TODO: make a nice picture dedicated for this case!
     * TODO: allow users to upload separate avatars for the public!
     *
     * @param int $userId
     * @return string relative picture url
     */
    private static function _incognitoPic_userId($userId)
    {
        return 'images/misc/empty_avatar.png';
        if(is_file(getcwd().'/bw/memberphotos/not_found.jpg')) {
            return '/memberphotos/incognito.jpg';
        } else {
            return '/memberphotos/et.jpg';
        }
    }


    /**
     * The pic that is shown if the username or id is not found in the database
     * (which means, something has gone wrong)
     *
     * @return string relative picture url
     */
    private static function _memberNotFoundPic()
    {
        return 'images/misc/empty_avatar.png';
        if(is_file(getcwd().'/bw/memberphotos/not_found.jpg')) {
            return '/memberphotos/not_found.jpg';
        } else {
            return '/memberphotos/et.jpg';
        }
    }

    /**
     * returns a sentence based on a given timestamp
     * like: 20 minutes ago
     *
     * @param int $timestamp unix timestamp
     * @access public
     * @return string
     */
    public function ago($timestamp)
    {
        // test if the given timestamp could be a unix timestamp, otherwise try to make it one
        $timestamp = ((is_string($timestamp) && intval($timestamp) == $timestamp) ? intval($timestamp) : $timestamp);
        if (!is_int($timestamp)) $timestamp = strtotime($timestamp);
        
        $words = new MOD_words();
        $difference = time() - $timestamp;

        $periods = array('second','minute','hour','day','week','month','year','decade');
        $lengths = array("60","60","24","7","4.35","12","10");
        for ($j = 0; $j < count($lengths) && $difference >= $lengths[$j]; $j++)
        {
            $difference /= $lengths[$j];
        }
        $difference = round($difference);
        if($difference != 1) $periods[$j].= "s";
        $periods[$j]=$periods[$j]."_ago" ;
	
        $text = $words->get($periods[$j],$difference);
        return $text;
        
    }


    // COPIED FROM OLD BW - to improve
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
        $row = self::get()->dao->query("select SQL_CACHE Sentence from memberstrads where IdTrad=" . $IdTrad . " and IdLanguage=" . $IdLanguage)->fetch(PDB::FETCH_OBJ);
      if (isset ($row->Sentence)) {
        if (isset ($row->Sentence) == "") {
          LogStr("Blank Sentence for language " . $IdLanguage . " with MembersTrads.IdTrad=" . $IdTrad, "Bug");
        } else {
           return (strip_tags($row->Sentence, $AllowedTags));
        }
      }
      // Try default eng
        $row = self::get()->dao->query("select SQL_CACHE Sentence from memberstrads where IdTrad=" . $IdTrad . " and IdLanguage=0")->fetch(PDB::FETCH_OBJ);
      if (isset ($row->Sentence)) {
        if (isset ($row->Sentence) == "") {
          LogStr("Blank Sentence for language 1 (eng) with memberstrads.IdTrad=" . $IdTrad, "Bug");
        } else {
           return (strip_tags($row->Sentence, $AllowedTags));
        }
      }
      // Try first language available
        $row = self::get()->dao->query("select  SQL_CACHE Sentence from memberstrads where IdTrad=" . $IdTrad . " order by id asc limit 1")->fetch(PDB::FETCH_OBJ);
      if (isset ($row->Sentence)) {
        if (isset ($row->Sentence) == "") {
          LogStr("Blank Sentence (any language) memberstrads.IdTrad=" . $IdTrad, "Bug");
        } else {
           return (strip_tags($row->Sentence, $AllowedTags));
        }
      }
      return ("");
    } // end of FindTrad

    // COPIED FROM OLD BW
    function GetPreference($namepref,$idm=0) {
      $IdMember=$idm;
       if ($idm==0) {
         if (isset($_SESSION['IdMember'])) $IdMember=$_SESSION['IdMember'];

      }
      if ($IdMember==0) {
           $row = self::get()->dao->query("select SQL_CACHE DefaultValue  from preferences where codeName='".$namepref."'")->fetch(PDB::FETCH_OBJ);
         if (!empty($row))
         return($row->DefaultValue);
      }
      else {
           $row = self::get()->dao->query("select SQL_CACHE Value from memberspreferences,preferences where preferences.codeName='$namepref' and memberspreferences.IdPreference=preferences.id and IdMember=" . $IdMember)->fetch(PDB::FETCH_OBJ);
         if (isset ($row->Value))
          $def = $row->Value;
        else {
           $row = self::get()->dao->query("select SQL_CACHE DefaultValue  from preferences where codeName='".$namepref."'")->fetch(PDB::FETCH_OBJ);
            if (isset($row->DefaultValue))
              return($row->DefaultValue);
            else
              return NULL;
        }
         return ($def);
      }

    } // end of GetPreference

    // COPIED FROM OLD BW
    function getParams($Param) {

//		echo "$Param=".$_SESSION["Param"]->$Param ;
//		die(0) ;
		return($_SESSION["Param"]->$Param) ;
	/* 
	
	// Removed by JeanYves Params are to be retrieve in $_SESSION["Param"]
	// which is only to be fetched from database only once but at EACH page refresh
	
	
        // get the user id
        $row = self::get()->dao->query(
            "SELECT *".
            'FROM params '
        )->fetch(PDB::FETCH_OBJ);
        return $row->$Param;
	*/
	
    }
    
    // COPIED FROM OLD BW
    // fage_value return a  the age value corresponding to date
    public function fage_value($dd) {
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

    /**
     * Truncate a string
     *
     * @param string $string Input string
     * @param int $length Maximum length of truncated version
     * @param string $omission Trailing characters indicating omission,
     *                         default: '...'
     *
     * @return string Truncated version of string
     */
    public static function truncate($string, $length, $omission = '...') {
        if (strlen($string) > $length) {
            $truncated = substr($string, 0, $length) . $omission;
        } else {
            $truncated = $string;
        }
        return $truncated;
    }

    /**
     * Truncate a string with full words
     *
     * @param string $text Input string
     * @param int $limit length of truncated version
     * @param string $ellipsis Trailing characters indicating ellipsis,
     *                         default: ' ...'
     *
     * @return string Truncated version of string
     */
    function truncate_words($text, $limit, $ellipsis = ' ...') {
        $words = preg_split("/[\n\r\t ]+/", $text, $limit + 1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_OFFSET_CAPTURE);
        if (count($words) > $limit) {
            end($words); //ignore last element since it contains the rest of the string
            $last_word = prev($words);
               
            $text =  substr($text, 0, $last_word[1] + strlen($last_word[0])) . $ellipsis;
        }
        return $text;
    }
    
    /**
     * Returns a string with the gender if that isn't hidden. Translated of the 
     * form 'Gender: male/female/other'
     * 
     * @return string 'Gender: male/female/other/ translated or empty string
     */
    public static function getGenderTranslated($gender, $hideGender, $addGenderText = true) {
        $words = new MOD_words();
        $string = '';
        if (($hideGender == 'No') && ($gender != 'IDontTell')) {
            if ($addGenderText) {
                $string .= $words->getFormatted('Gender'). ": ";
            }
            if ($gender != 'other') {
                $string .= $words->getFormatted($gender);
            } else {
                $string .= $words->getFormatted('GenderOther');
            } 
        }
        return $string;
    }
}

?>
