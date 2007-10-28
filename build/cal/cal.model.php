<?php
/**
 * Cal model
 *
 * @package cal
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: cal.model.php 69 2006-06-23 12:40:37Z kang $
 */
class Cal extends PAppModel {
    private $_dao;
    
    public function __construct() {
        parent::__construct();
    }

    /**
     * returns a nice array for calendars :)
     * 
     * array representing
     * 
     * array([ISOWk]=>  
     *   M Tu W Thu F Sa Su
     * 
     * @param int $y year 
     * @param int $m month 
     */
    public function calcCalMonth($y, $m) {
        $weeks = array();
        $curDay = 1;
        
        // add the remaining days from previous month
        $t = mktime(0, 0, 0, $m, $curDay, $y);
        // only if the current month does not start with monday
        if (date('N', $t) != 1) {
            for ($i = date('N', $t); $i >= 1; $i--) {
                $newT = $t - (24*3600*($i-1));
                $weeks[date('W', $newT)][idate('d', $newT)] = $newT;
            }
        }
        // set all days
        while (checkdate($m, $curDay, $y)) {
            $t = mktime(0, 0, 0, $m, $curDay, $y);
            $weeks[date('W', $t)][idate('d', $t)] = $t;
            $curDay++;
        }
        // and the first days of the following month
        if (date('N', $t) != 7) {
            $diff = 7 - date('N', $t);
            for ($i = 1; $i <= $diff; $i++) {
                $newT = $t + (24*3600*($i));
                $weeks[date('W', $newT)][idate('d', $newT)] = $newT;
            }
        }
        return $weeks;
    }
}
?>