<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * contains (global) variable handler
 * 
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: vars.lib.php 68 2006-06-23 12:10:27Z kang $
 */
/**
 * (global) variable handler
 * 
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: vars.lib.php 68 2006-06-23 12:10:27Z kang $
 */
class PVars {
    private static $_instance;
    private $_vars = array();

    private function __construct() {
    }

    public static function get() {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
    
    public static function register($name, $value) {
        if (!isset(self::$_instance)) {
            $c = self::get();
        } else {
            $c = self::$_instance;
        }
        $c->_vars[$name] = $value;
    }
    
    public function __get($name) {
        if (!isset(self::$_instance)) {
            $c = self::get();
        } else {
            $c = self::$_instance;
        }
        if (!array_key_exists($name, $c->_vars))
            return FALSE;
        return $c->_vars[$name];
    }
    
    public static function getObj($name) {
        if (!isset(self::$_instance)) {
            $c = self::get();
        } else {
            $c = self::$_instance;
        }
        if (!array_key_exists($name, $c->_vars)) {
            $c->_vars[$name] = array();
        }
        if (!is_array($c->_vars[$name]))
            return false;
        $obj = new PVarObj($c->_vars[$name]);
        return $obj;
    }
    
    /**
     * Get config section as an array
     *
     * @param string $name Name of config section, e.g. "db" ("[db]" in
     *                     rox.ini)
     * @return array|bool Config key/value pairs; false if config section does
     *                    not exist
     *
     * TODO: Reuse in getObj(), keeping same getObj() performance
     */
    public static function getArray($name) {
        if (!isset(self::$_instance)) {
            $c = self::get();
        } else {
            $c = self::$_instance;
        }
        if (array_key_exists($name, $c->_vars)) {
            return $c->_vars[$name];
        } else {
            return false;
        }
    }

}
?>
