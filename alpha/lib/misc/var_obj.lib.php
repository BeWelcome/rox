<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Var object
 * 
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: var_obj.lib.php 68 2006-06-23 12:10:27Z kang $
 */
/**
 * (global) variable var object
 * 
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: var_obj.lib.php 68 2006-06-23 12:10:27Z kang $
 */
class PVarObj {
    private $_vars;
    
    public function __construct(&$vars) {
        $this->_vars =& $vars;
    }
    
    public function __get($name) {
        if (!is_array($this->_vars))
            return false;
        if (!array_key_exists($name, $this->_vars))
            return false;
        return $this->_vars[$name];
    }
    
    public function __set($name, $val) {
        $this->_vars[$name] = $val;
        return true;
    }
}
?>