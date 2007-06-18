<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Contains the module handler
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: modules.lib.php 68 2006-06-23 12:10:27Z kang $
 */
/**
 * Module handler
 * 
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: modules.lib.php 68 2006-06-23 12:10:27Z kang $
 */
class PModules {
    private static $_instance;
    private $_moduleDir;
    private $_modules = array();
    
    private function __construct() {
    }
    
    public static function get() {
        if (!isset(self::$_instance)) {
            $c = __CLASS__;
            self::$_instance = new $c;
        }
        return self::$_instance;
    }
    
    public function loadModules() {
        if (!isset($this->_moduleDir))
            return false;
        $Classes = Classes::get();
        $dir = dir($this->_moduleDir);
        while ($d = $dir->read()) {
            if ($d == '.' || $d == '..')
                continue;
            $dp = $dir->path.'/'.$d;
            if (!is_dir($dp))
                continue;
            if (!file_exists($dp.'/module.xml'))
                continue;
            $d = @DOMDocument::load($dp.'/module.xml');
            if (!$d)
                continue;
            $d->XPath = new DOMXPath($d);
            $moduleName = $d->XPath->query('/module/moduleName');
            if ($moduleName->length != 1) {
                throw new PException('Module load error in "'.$dp.'/module.xml"!');
            }
            $moduleFiles = $d->XPath->query('/module/moduleFiles');
            if ($moduleFiles->length != 1) {
                throw new PException('Module load error in "'.$dp.'/module.xml"!');
            }
            foreach ($moduleFiles->item(0)->childNodes as $node) {
                if (!is_a($node, 'DOMElement'))
                    continue;
                foreach ($node->childNodes as $file) {
                    if (!is_a($file, 'DOMElement'))
                        continue;
                    if ($file->hasAttribute('class')) {
                        $Classes->addClass($file->getAttribute('class'), $dp.'/'.$node->nodeName.'/'.$file->nodeValue);
                    }
                }
            }

            $this->_modules[$moduleName->item(0)->nodeValue] = $d;
        }
    }
    
    public static function setModuleDir($moduleDir) {
        if (!file_exists($moduleDir) || !is_dir($moduleDir)) {
            throw new PException('Module directory error!');
        }
        self::$_instance->_moduleDir = $moduleDir;
    }
    
    public static function moduleLoaded($moduleName) {
        if (!is_array(self::$_instance->_modules))
            throw new PException('Internal module error!');
        return array_key_exists($moduleName, self::$_instance->_modules);
    }
}
?>