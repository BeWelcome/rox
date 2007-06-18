<?php

/**
 * Swift Mailer Command Event Listener Interface
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Events
 * @license GNU Lesser General Public License
 */


/**
 * Contains the list of methods a plugin requiring the use of a CommandEvent must implement
 * @package Swift_Events
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
interface Swift_Events_CommandListener extends Swift_Events_Listener
{
	/**
	 * Executes when Swift sends a command
	 * @param Swift_Events_CommandEvent Information about the command sent
	 */
	public function commandSent(Swift_Events_CommandEvent $e);
}
