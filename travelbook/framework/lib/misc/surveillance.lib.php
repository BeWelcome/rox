<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Surveillance class
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: surveillance.lib.php 68 2006-06-23 12:10:27Z kang $
 */
/**
 * Surveillance class
 * 
 * This class can set different time points to roughly compute a runtime
 *
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: surveillance.lib.php 68 2006-06-23 12:10:27Z kang $
 */
class PSurveillance {
    private static $_instance;
    private $_points = array();
    
    private function __construct() {
        $this->_points['startup'] = $this->_getTime();
    }

    public function __get($name) {
        $name = '_'.$name;
        if (!isset($this->$name))
            return false;
        return $this->$name;
    }
    
    public static function get() {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    } 
    
    public static function setPoint($name) {
        if (!is_array(self::$_instance->_points))
            throw new PException('D.i.e.!');
        self::$_instance->_points[$name] = self::$_instance->_getTime();
    }
    
    public static function getDiff($nameA, $nameB) {
        if (!is_array(self::$_instance->_points))
            throw new PException('D.i.e.!');
        if (!array_key_exists($nameA, self::$_instance->_points) || !array_key_exists($nameB, self::$_instance->_points))
            return false;
        $time = self::$_instance->_points[$nameB] - self::$_instance->_points[$nameA];
        return $time;
    }
    
    private function _getTime() {
        return microtime(true);
    }
}
?>