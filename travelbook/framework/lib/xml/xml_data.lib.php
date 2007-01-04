<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * contains XML Data class
 * 
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: xml_data.lib.php 68 2006-06-23 12:10:27Z kang $
 */
/**
 * XML Data class
 * 
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: xml_data.lib.php 68 2006-06-23 12:10:27Z kang $
 */
class PData extends DOMDocument {
    private static $_instance;
    
    public $namespace = 'http://www.w3.org/1999/xhtml';
    public $XPath;
    
    public function __construct() {
        parent::__construct('1.0', 'utf-8');
        $data = $this->createElementNS($this->namespace, 'data');
        $this->appendChild($data);
        $this->XPath = new DOMXPath($this);
        $this->XPath->registerNamespace('default', $this->namespace);    
    }
    
    public static function get() {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
}
?>