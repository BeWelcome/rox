<?php

/**
 * Swift Mailer Message Boundary
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Message
 * @license GNU Lesser General Public License
 */

/**
 * Class for generating unique MIME boundaries
 * @package Swift_Message
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_Message_Boundary
{
	/**
	 * Boundaries which have laready been computed for use elsewhere
	 * @var array
	 */
	protected static $used = array();
	
	/**
	 * Compute a unique boundary
	 * @return string
	 */
	public static function Generate()
	{
		do
		{
			$boundary = uniqid(rand(), true);
		} while (in_array($boundary, self::$used));
		self::$used[] = $boundary;
		return "_=_swift-" . $boundary . "_=_";
	}
}
