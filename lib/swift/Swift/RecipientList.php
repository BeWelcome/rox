<?php

/**
 * Swift Mailer Recipient List Container
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift
 * @license GNU Lesser General Public License
 */

require_once dirname(__FILE__) . "/ClassLoader.php";
Swift_ClassLoader::load("Swift_Address");

/**
 * Swift's Recipient List container.  Contains To, Cc, Bcc
 * @package Swift
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_RecipientList extends Swift_AddressContainer
{
	/**
	 * The recipients in the To: header
	 * @var array
	 */
	protected $to = array();
	/**
	 * The recipients in the Cc: header
	 * @var array
	 */
	protected $cc = array();
	/**
	 * The recipients in the Bcc: header
	 * @var array
	 */
	protected $bcc = array();
	
	/**
	 * Add a To: recipient
	 * @param mixed The address to add.  Can be a string or Swift_Address
	 * @param string The personal name, optional
	 */
	public function addTo($address, $name=null)
	{
		if ($address instanceof Swift_Address)
		{
			$this->to[$address->getAddress()] = $address;
		}
		else
		{
			$address = (string) $address;
			$this->to[$address] = new Swift_Address($address, $name);
		}
	}
	/**
	 * Get an array of addresses in the To: field
	 * The array contains Swift_Address objects
	 * @return array
	 */
	public function getTo()
	{
		return $this->to;
	}
	/**
	 * Remove a To: recipient from the list
	 * @param mixed The address to remove.  Can be Swift_Address or a string
	 */
	public function removeTo($address)
	{
		if ($address instanceof Swift_Address)
		{
			$key = $address->getAddress();
		}
		else $key = (string) $address;
		
		if (array_key_exists($key, $this->to)) unset($this->to[$key]);
	}
	/**
	 * Empty all To: addresses
	 */
	public function flushTo()
	{
		$this->to = null;
		$this->to = array();
	}
	/**
	 * Add a Cc: recipient
	 * @param mixed The address to add.  Can be a string or Swift_Address
	 * @param string The personal name, optional
	 */
	public function addCc($address, $name=null)
	{
		if ($address instanceof Swift_Address)
		{
			$this->cc[$address->getAddress()] = $address;
		}
		else
		{
			$address = (string) $address;
			$this->cc[$address] = new Swift_Address($address, $name);
		}
	}
	/**
	 * Get an array of addresses in the Cc: field
	 * The array contains Swift_Address objects
	 * @return array
	 */
	public function getCc()
	{
		return $this->cc;
	}
	/**
	 * Remove a Cc: recipient from the list
	 * @param mixed The address to remove.  Can be Swift_Address or a string
	 */
	public function removeCc($address)
	{
		if ($address instanceof Swift_Address)
		{
			$key = $address->getAddress();
		}
		else $key = (string) $address;
		
		if (array_key_exists($key, $this->cc)) unset($this->cc[$key]);
	}
	/**
	 * Empty all Cc: addresses
	 */
	public function flushCc()
	{
		$this->cc = null;
		$this->cc = array();
	}
	/**
	 * Add a Bcc: recipient
	 * @param mixed The address to add.  Can be a string or Swift_Address
	 * @param string The personal name, optional
	 */
	public function addBcc($address, $name=null)
	{
		if ($address instanceof Swift_Address)
		{
			$this->bcc[$address->getAddress()] = $address;
		}
		else
		{
			$address = (string) $address;
			$this->bcc[$address] = new Swift_Address($address, $name);
		}
	}
	/**
	 * Get an array of addresses in the Bcc: field
	 * The array contains Swift_Address objects
	 * @return array
	 */
	public function getBcc()
	{
		return $this->bcc;
	}
	/**
	 * Remove a Bcc: recipient from the list
	 * @param mixed The address to remove.  Can be Swift_Address or a string
	 */
	public function removeBcc($address)
	{
		if ($address instanceof Swift_Address)
		{
			$key = $address->getAddress();
		}
		else $key = (string) $address;
		
		if (array_key_exists($key, $this->bcc)) unset($this->bcc[$key]);
	}
	/**
	 * Empty all Bcc: addresses
	 */
	public function flushBcc()
	{
		$this->bcc = null;
		$this->bcc = array();
	}
	/**
	 * Empty the entire list
	 */
	public function flush()
	{
		$this->flushTo();
		$this->flushCc();
		$this->flushBcc();
	}
}
