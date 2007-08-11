<?php

/**
 * Swift Mailer Logging Layer
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Log
 * @license GNU Lesser General Public License
 */

require_once dirname(__FILE__) . "/../ClassLoader.php";
Swift_ClassLoader::load("Swift_Log");

/**
 * The Base Logger class
 * @package Swift_Log
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
abstract class Swift_Log_Base implements Swift_Log
{
	/**
	 * A command type entry
	 */
	const COMMAND = ">>";
	/**
	 * A response type entry
	 */
	const RESPONSE = "<<";
	/**
	 * An error type entry
	 */
	const ERROR = "!!";
	/**
	 * A standard entry
	 */
	const NORMAL = "++";
	/**
	 * Failed recipients
	 * @var array
	 */
	protected $failedRecipients = array();
	/**
	 * If the logger is running or not
	 * @var boolean
	 */
	protected $active = false;
	/**
	 * The maximum number of log entries
	 * @var int
	 */
	protected $maxSize = 50;
	
	/**
	 * Enable logging
	 */
	public function enable()
	{
		$this->active = true;
		$this->add("Enabling logging", self::NORMAL);
	}
	/**
	 * Disable logging
	 */
	public function disable()
	{
		$this->add("Disabling logging", self::NORMAL);
		$this->active = false;
	}
	/**
	 * Check if logging is enabled
	 */
	public function isEnabled()
	{
		return $this->active;
	}
	/**
	 * Add a failed recipient to the list
	 * @param string The address of the recipient
	 */
	public function addFailedRecipient($address)
	{
		$this->failedRecipients[$address] = null;
	}
	/**
	 * Get the list of failed recipients
	 * @return array
	 */
	public function getFailedRecipients()
	{
		return array_keys($this->failedRecipients);
	}
	/**
	 * Set the maximum size of this log (zero is no limit)
	 * @param int The maximum entries
	 */
	public function setMaxSize($size)
	{
		$this->maxSize = (int) $size;
	}
	/**
	 * Get the current maximum allowed log size
	 * @return int
	 */
	public function getMaxSize()
	{
		return $this->maxSize;
	}
}
