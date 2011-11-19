<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Contains the application class
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: apps.lib.php 68 2006-06-23 12:10:27Z kang $
 */
/**
 * Application class
 * 
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 */
class PApps 
{
    /**
     * Singleton instance
     * 
     * @var PApps
     * @access private
     */
    private static $_instance;
    /**
     * all required includes
     * 
     * @var array
     * @access private
     */
    private $_includes = array();
    /**
     * all apps
     * 
     * @var array
     * @access private
     */
    private $_apps = array();
    
    /**
     * @param void
     */
    private function __construct() 
    {
    }
    
    /**
     * load all app files for given path and build.xml document
     * 
     * @param string $path app base path
     * @param DOMDocument $build build.xml
     * @return boolean
     */
    private function _doBuild($path, DOMDocument $build) 
    {
        $Classes = Classes::get();
        $x = new DOMXPath($build);
        $app = $x->query('/build/app');
        if ($app->length != 1) {
            throw new PException('App error!');
        }
        $app = $app->item(0);
        if (!$app->hasAttribute('name')) {
            throw new PException('App name error!');
        }
        $this->_apps[$app->getAttribute('name')] = $build;
        $files = $x->query('/build/files/file');
        foreach ($files as $file) {
            if ($file->hasAttribute('class')) {
                $Classes->addClass($file->getAttribute('class'), $path.$file->nodeValue);
                continue;
            }
            if ($file->hasAttribute('include')) {
                if (!file_exists($path.$file->nodeValue))
                    continue;
                $this->_includes[] = $path.$file->nodeValue;
                continue;
            }
        }
        return true;
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
    
    /**
     * returns the build dir
     * 
     * may be called statically
     * 
     * @param void
     * @return string
     */
    public static function getBuildDir() {
        return BUILD_DIR;
    }
    
    /**
     * Start to load all apps
     * 
     * @param voud
     * @return boolean
     */
    public function build() 
    {
        $buildDir = dir(BUILD_DIR);
        while ($d = $buildDir->read()) {
            if ($d == '.' || $d == '..' || ! is_dir($buildDir->path.'/'.$d))
                continue;
            if (!file_exists($buildDir->path.'/'.$d.'/build.xml'))
                continue;
            $path = $buildDir->path.'/'.$d.'/';
            $build = DOMDocument::load($path.'build.xml');
            $this->_doBuild($path, $build);
        }
        return true;
    }
    
    /**
     * returns all loaded app names
     * 
     * @param void
     * @return array
     */
    public function getAppNames() 
    {
        return array_keys($this->_apps);
    }
    
    /**
     * returns an array with app name as a key and the build.xml doc as a value
     * 
     * @param void
     * @return array
     */
    public function getApps() 
    {
        return $this->_apps;
    }
    
    /**
     * returns all files, which shall be included in an indexed array
     * 
     * @param void
     * @return array
     */
    public function getIncludes() 
    {
        if (count($this->_includes) == 0)
            return false;
        return $this->_includes;
    }

    /**
     * returns the default controller class name for given app name
     * 
     * @param string $app
     * @return mixed either "false" or the class name
     */
    public function getAppName($app) 
    {
        $app = ucfirst($app);
        $app = $app.'Controller';
        if (!class_exists($app)) {
            return false;
        }
        if (get_parent_class($app) != 'PAppController') {
            return false;
        }
        return $app;
    }
}
