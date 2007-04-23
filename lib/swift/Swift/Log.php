<?php

/**
 * Swift Mailer Logging Layer Interface
 * Please read the LICENSE file
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Log
 * @license GNU Lesser General Public License
 */

/**
 * The Logger Interface
 * @package Swift_Log
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
interface Swift_Log
{
	/**
	 * Enable logging
	 */
	public function enable();
	/**
	 * Disable logging
	 */
	public function disable();
	/**
	 * Check if logging is enabled
	 */
	public function isEnabled();
	/**
	 * Add a failed recipient to the list
	 * @param string The address of the recipient
	 */
	public function addFailedRecipient($address);
	/**
	 * Get the list of failed recipients
	 * @return array
	 */
	public function getFailedRecipients();
	/**
	 * Set the maximum size of this log (zero is no limit)
	 * @param int The maximum entries
	 */
	public function setMaxSize($size);
	/**
	 * Get the current maximum allowed log size
	 * @return int
	 */
	public function getMaxSize();
	/**
	 * Add a new entry to the log
	 * @param string The information to log
	 * @param string The type of entry (see the constants: COMMAND, RESPONSE, ERROR, NORMAL)
	 */
	public function add($text, $type);
	/**
	 * Dump the contents of the log to the browser
	 */
	public function dump();
	/**
	 * Empty the log contents
	 */
	public function clear();
}
