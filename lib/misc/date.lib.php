<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Contains the date library
 *
 * @package date
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: date.lib.php 68 2006-06-23 12:10:27Z kang $
 */
/**
 * Static date functions
 *
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: date.lib.php 68 2006-06-23 12:10:27Z kang $
 */
class PDate {
    /**
     * returns the timestamp for the beginning of given week/year 
     */
    public static function weekToTime($week, $year) {
        $startofyear = mktime(0,0,0,1,1,$year);
        $startdate = $startofyear;
        while(date('N',$startdate) != 1) {
            $startdate = mktime(0,0,0,date('m',$startdate),date('d',$startdate)-1, date('Y',$startdate));     
        }
        while (date('W', $startdate) != $week) {
            $startdate += 3600*24*7;
        }
        return $startdate;
    }
    
    public static function strftime_utf8($format, $t = false) {
        if (!$t)
            $t = time();
        $t = strftime($format, $t);
        if (PFunctions::isUTF8($t)) {
            return $t;
        } else {
            return utf8_encode($t);
        }
    }

    public static function intlDate($format, $timestamp, $lang) {
        $formats = array(
'long'=>array(
),
'medium'=>array(
),
'short'=>array(
),
        );
    }
}
?>