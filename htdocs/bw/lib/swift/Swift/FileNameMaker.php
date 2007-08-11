<?php

/**
 * Swift Mailer File name making component (to avoid clashes)
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Message
 * @license GNU Lesser General Public License
 */

/**
 * File name maker (makes filenames in sequence)
 * @package Swift_Message
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_FileNameMaker
{
	/**
	 * A singleton instance
	 * @var swift_FileNameMaker
	 */
	protected static $instance = null;
	/**
	 * Just a number to increment
	 * @var int
	 */
	protected $id = 1;
	
	/**
	 * Singleton Factory
	 * @return Swift_FileNameMaker
	 */
	public static function instance()
	{
		if (self::$instance === null) self::$instance = new Swift_FileNameMaker();
		
		return self::$instance;
	}
	/**
	 * Get a unique filename (just a sequence)
	 * @param string the prefix for the filename
	 * @return string
	 */
	public function Generate($prefix="file")
	{
		return $prefix . ($this->id++);
	}
}