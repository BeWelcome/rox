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
        
        if(!is_file(getcwd().'/bw'.$picfile)) {
            // get a picture by username
            $thumburl = self::smallUserPic_username($username);
        } else {
            $thumburl = self::_getThumb($picfile, 100, 100);
            if ($thumburl === null) $thumburl = "bw/";
        }
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
                    'href="bw/member.php?cid='.$username.'" '.
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
     * 100x100 avatar picture for forums etc
     *
     * @param string $username
     * 
     */
    public static function smallUserPic_userId($userId)
    {
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
        $thumbfile = self::_getThumb($picfile, 100, 100, 100);
        return $thumbfile;
    }
    
    
    
    
    public static function userPic_userId($userId)
    {
        // check if user is logged in
        if (!APP_User::isBWLoggedIn()) {
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
    }
    
    
    
    public static function userPic_username($username)
    {
        // get the user id
        $row = self::get()->dao->query(
            'SELECT SQL_CACHE id '.
            'FROM members '.
            "WHERE Username='$username' "
        )->fetch(PDB::FETCH_OBJ);
        if ($row) {
            return self::userPic_userId($row->id);
        } else {
            // username not found..
            return self::_memberNotFoundPic();
        }
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
    
        if($file == "") return null;
        
        $filename = basename($file);
        $filename_noext = substr($filename, 0, strrpos($filename, '.'));
        $filepath = getcwd()."/bw/memberphotos";
        $wwwpath = PVars::getObj('env')->baseuri."bw/memberphotos";
        
        $thumbfile = $filename_noext.'.'.$mode.'.'.$max_x.'x'.$max_y.'.jpg';
        
        // look if thumbnail already exists
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
        return '/memberphotos/et.jpg';
    }
    
    
    /**
     * The pic that is shown if the username or id is not found in the database
     * (which means, something has gone wrong)
     *
     * @return string relative picture url
     */
    private static function _memberNotFoundPic()
    {
        return '/memberphotos/et.jpg';
    }
}

?>