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
    use \Rox\RoxTraits\SessionTrait;

    /**
     * Quasi-constant functions for userthumbnails
     *
     */
    public static function PIC_100_100($username,$picfile='',$style="framed") {
        return self::linkWithPictureVar($username, $height = 100, $width = 100, $style);
    }
    public static function PIC_75_75($username,$picfile='',$style="framed") {
        return self::linkWithPictureVar($username, $height = 75, $width = 75, $style);
    }
    public static function PIC_50_50($username,$picfile='',$style="framed memberpic") {
        return self::linkWithPictureVar($username, $height = 50, $width = 50, $style);
    }
    public static function PIC_40_40($username,$picfile='',$style="framed") {
        return self::linkWithPictureVar($username, $height = 40, $width = 40, $style);
    }
    public static function PIC_30_30($username,$picfile='',$style="framed") {
        return self::linkWithPictureVar($username, $height = 30, $width = 30, $style);
    }
    public static function PIC_15_15($username,$picfile='',$style="framed") {
        return self::linkWithPictureVar($username, $height = 15, $width = 15, $style);
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
        $this->setSession();
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


    protected static function member_pic_url() {
        return '/members/avatar/';
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
        $words = new MOD_words(self::$_instance->_session);
        $thumburl = self::smallUserPic_username($username);
        return
            '<a '.
                'href="/members/'.$username.'" '.
                'title="'.$words->getBuffered('SeeProfileOf', $username).'" '.
            '><img '.
                'class="framed" '.
                'src="'.$thumburl.'" '.
                'height="50" '.
                'width="50" '.
                'alt="Profile" '.
            '/></a>'
        ;
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
     * @param string $style css-class for the image-tag
     * @return string html-formatted link with picture
     * @throws PException
     */
    public static function linkWithPictureVar($username, $height, $width, $style)
    {
        $words = new MOD_words();
        $thumburl = self::member_pic_url().$username.'?size='.$height;

        return
            '<a '.
                'href="/members/'.$username.'" '.
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
    public static function smallUserPic_username($username)
    {
        $picfile = self::userPic_username($username);
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
    }


    public static function userPic_userId($userId)
    {
        return self::member_pic_url().$userId.'/';
    }



    public static function userPic_username($username)
    {
        return self::member_pic_url().$username;
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
        $periods[$j]=$periods[$j]."_ago";
        $text = $words->getSilent($periods[$j],$difference);
        return $text;

    }

    // Returns a qualifier for how long ago the timestamp is
    public function ago_qualified($timestamp) {
        // test if the given timestamp could be a unix timestamp, otherwise try to make it one
        $timestamp = ((is_string($timestamp) && intval($timestamp) == $timestamp) ? intval($timestamp) : $timestamp);
        if (!is_int($timestamp)) $timestamp = strtotime($timestamp);

        $difference = time() - $timestamp;

        $periods = array ('second','minute','hour','day','week','month','year','decade' );
        $lengths = array ("60","60","24","7","4.35","12","10");
        for($j = 0; $j < count($lengths) && $difference >= $lengths[$j]; $j++) {
            $difference /= $lengths[$j];
        }
        $difference = round($difference);
        $qualified = 3;
        switch ($periods[$j]) {
            case "second":
            case "minute":
            case "hour":
            case "day":
                $qualified = 0;
                break;
            case "week":
                if ($difference > 4) {
                    $qualified = 1;
                } else {
                    $qualified = 0;
                }
                break;
            case "month":
                if ($difference <= 6) {
                    $qualified = 1;
                } else {
                    $qualified = 2;
                }
                break;
            default:
                $qualified = 2;
        }

        return $qualified;
    }

    // COPIED FROM OLD BW
    function getParams($Param) {

		return(self::$_instance->_session->get('Param[' . $Param . ']'));
    }

    // COPIED FROM OLD BW
    // fage_value return a  the age value corresponding to date
    public function fage_value($dd, $nn = false) {
        $pieces = explode("-",$dd);
        if(count($pieces) != 3) return 0;
        if (!$nn) {
            $nn = date('Y-m-d');
        }
        $npieces = explode("-",$nn);
        if(count($npieces) != 3) return 0;
        list($year,$month,$day) = $pieces;
        list($nyear,$nmonth,$nday) = $npieces;
        $year_diff = $nyear - $year;
        $month_diff = $nmonth - $month;
        $day_diff = $nday - $day;
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
    public static function truncate_words($text, $limit, $ellipsis = ' ...') {
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
        return $gender;

        $words = new MOD_words(self::$_instance->_session);
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

    public static function GetPreference($namepref,$idm=0) {
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

}
