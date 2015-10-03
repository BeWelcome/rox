<?php
//**************************************************
// This project incorporates code, provided by
// Philipp Hunstein <hunstein@respice.de> and
// Seong-Min Kang <kang@respice.de>, taken from
// respice - Platform PT.
/**
 * Object autoloading
 *
 * @package core
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: classes.autoload.php 68 2006-06-23 12:10:27Z kang $
 */
/**
 * Autoloading handler
 *
 * @author The myTravelbook Team <http://www.sourceforge.net/projects/mytravelbook>
 * @copyright Copyright (c) 2005-2006, myTravelbook Team
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License (GPL)
 * @version $Id: classes.autoload.php 68 2006-06-23 12:10:27Z kang $
 * @link http://de3.php.net/manual/de/language.oop5.autoload.php
 */
class Classes1 {
	/**
	 * the instance for using the singleton pattern
	 *
	 * @var Classes 
	 */
	private static $_instance;
	/**
	 * an array with all classes and their files
	 *
	 * @var array
	 */
	private $_libs = array ();
	
	/**
	 * The constructor
	 */
	public function __construct () {}
	
	/**
	 * The singleton method
	 *
	 * alias for Classes::singleton()
	 *
	 * @return Classes
	 */
	public static function get () {
		return self::singleton ();
	}
	
	/**
	 * The singleton method
	 *
	 * alias for Classes::get()
	 *
	 * @return Classes
	 */
	public static function singleton () {
		if (!isset (self::$_instance)) {
			$c = __CLASS__;
			self::$_instance = new $c ();
		}
		return self::$_instance;
	}
	
	/**
	 * Adds a class for autoloading
	 *
	 * @param string $className
	 * @param string $file
	 */
	public function addClass ($className, $file) {
		if (!array_key_exists ($className, $this->_libs))
			$this->_libs[$className] = $file;
	}
	
	/**
	 * Loads a class
	 *
	 * @param string $className
	 */
	public function load ($className) {
		if (!array_key_exists ($className, $this->_libs))
			return FALSE;
		if ($this->_libs[$className])
			require_once $this->_libs[$className];
	}
}

/**
 * The autoload function
 * @link http://de3.php.net/manual/de/language.oop5.autoload.php
 * @param string $className
 */
function __autoload ($className) {
	try {
		$Classes = Classes::singleton ();
		$Classes->load ($className);
	} catch (Exception $e) {
        throw $e;
	}
}
?>