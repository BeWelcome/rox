<?php

/**
 * Swift Mailer Message Component
 * Composes MIME 1.0 messages meeting various RFC standards
 * Deals with attachments, embedded images, multipart bodies, forwarded messages...
 * Please read the LICENSE file
 * @copyright Chris Corbyn <chris@w3style.co.uk>
 * @author Chris Corbyn <chris@w3style.co.uk>
 * @package Swift_Message
 * @license GNU Lesser General Public License
 */

require_once dirname(__FILE__) . "/ClassLoader.php";
Swift_ClassLoader::load("Swift_Address");
Swift_ClassLoader::load("Swift_Message_Mime");
Swift_ClassLoader::load("Swift_Message_Image");
Swift_ClassLoader::load("Swift_Message_Part");


/**
 * Swift Message class
 * @package Swift_Message
 * @author Chris Corbyn <chris@w3style.co.uk>
 */
class Swift_Message extends Swift_Message_Mime
{
	/**
	 * The version of this library
	 */
	const VERSION = "3.1.2";
	/**
	 * Constant from a high priority message (pretty meaningless)
	 */
	const PRIORITY_HIGH = 1;
	/**
	 * Constant for a low priority message
	 */
	const PRIORITY_LOW = 5;
	/**
	 * Constant for a normal priority message
	 */
	const PRIORITY_NORMAL = 3;
	/**
	 * The MIME warning for client not supporting multipart content
	 * @var string
	 */
	protected $mimeWarning = null;
	/**
	 * References which point to the parent MIME documents of the various parts
	 * @var array
	 */
	protected $parentRefs = array("alternative" => null, "mixed" => null, "related" => null);
	/**
	 * Lists of references for all alternative parts
	 * @var array
	 */
	protected $alternativeRefs = array();
	/**
	 * List of references for all mixed parts
	 * @var array
	 */
	protected $mixedRefs = array();
	/**
	 * List of references for all related parts
	 * @var array
	 */
	protected $relatedRefs = array();
	
	public function __construct($subject="", $body=null, $type="text/plain", $encoding=null, $charset=null)
	{
		parent::__construct();
		if (function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get"))
		{
			date_default_timezone_set(@date_default_timezone_get());
		}
		$this->setReturnPath(null);
		$this->setTo("");
		$this->setFrom("");
		$this->setCc(null);
		$this->setBcc(null);
		$this->setReplyTo(null);
		$this->setSubject($subject);
		$this->setDate(time());
		$this->headers->set("X-Mailer", "Swift " . self::VERSION);
		$this->headers->set("MIME-Version", "1.0");
		$this->setContentType($type);	
		$this->setCharset($charset);
		$this->setFlowed(true);
		$this->setEncoding($encoding);
		
		foreach (array_keys($this->parentRefs) as $key)
		{
			$this->parentRefs[$key] = $this;
		}
		
		$this->setMimeWarning(
		"This is a message in multipart MIME format.  Your mail client should not be displaying this. " .
		"Consider upgrading your mail client to view this message correctly."
		);
		
		if ($body !== null)
		{
			$this->setData($body);
			if ($charset === null)
			{
				Swift_ClassLoader::load("Swift_Message_Encoder");
				if (Swift_Message_Encoder::instance()->isUTF8($body)) $this->setCharset("utf-8");
				else $this->setCharset("iso-8859-1");
			}
		}
	}
	/**
	 * Set the address in the Return-Path: header
	 * @param string The bounce-detect address
	 */
	public function setReturnPath($address)
	{
		if ($address instanceof Swift_Address) $address = $address->build(true);
		$this->headers->set("Return-Path", $address);
	}
	/**
	 * Return the address used in the Return-Path: header
	 * @return string
	 * @param boolean Return the address for SMTP command
	 */
	public function getReturnPath($smtp=false)
	{
		if ($this->headers->has("Return-Path"))
		{
			if (!$smtp) return $this->headers->get("Return-Path");
			else
			{
				$path = $this->headers->get("Return-Path");
				if (strpos($path, ">") > strpos($path, "<")) return substr($path, ($start = strpos($path, "<")), ($start + strrpos($path, ">") + 1));
				else return "<" . $path . ">";
			}
		}
	}
	/**
	 * Set the address in the From: header
	 * @param string The address to set as From
	 */
	public function setFrom($from)
	{
		if ($from instanceof Swift_Address) $from = $from->build();
		$this->headers->set("From", $from);
	}
	/**
	 * Get the address used in the From: header
	 * @return string
	 */
	public function getFrom()
	{
		if ($this->headers->has("From")) return $this->headers->get("From");
	}
	/**
	 * Set the list of recipients in the To: header
	 * @param mixed An array or a string
	 */
	public function setTo($to)
	{
		if ($to)
		{
			if (!is_array($to)) $to = array($to);
			foreach ($to as $key => $value)
			{
				if ($value instanceof Swift_Address) $to[$key] = $value->build();
			}
		}
		$this->headers->set("To", $to);
	}
	/**
	 * Return the list of recipients in the To: header
	 * @return array
	 */
	public function getTo()
	{
		if ($this->headers->has("To"))
		{
			$to = $this->headers->get("To");
			if ($to == "") return array();
			else return (array) $to;
		}
	}
	/**
	 * Set the list of recipients in the Reply-To: header
	 * @param mixed An array or a string
	 */
	public function setReplyTo($replyto)
	{
		if ($replyto)
		{
			if (!is_array($replyto)) $replyto = array($replyto);
			foreach ($replyto as $key => $value)
			{
				if ($value instanceof Swift_Address) $replyto[$key] = $value->build();
			}
		}
		$this->headers->set("Reply-To", $replyto);
	}
	/**
	 * Return the list of recipients in the Reply-To: header
	 * @return array
	 */
	public function getReplyTo()
	{
		if ($this->headers->has("Reply-To"))
		{
			$reply_to = $this->headers->get("Reply-To");
			if ($reply_to == "") return array();
			else return (array) $reply_to;
		}
	}
	/**
	 * Set the list of recipients in the Cc: header
	 * @param mixed An array or a string
	 */
	public function setCc($cc)
	{
		if ($cc)
		{
			if (!is_array($cc)) $cc = array($cc);
			foreach ($cc as $key => $value)
			{
				if ($value instanceof Swift_Address) $cc[$key] = $value->build();
			}
		}
		$this->headers->set("Cc", $cc);
	}
	/**
	 * Return the list of recipients in the Cc: header
	 * @return array
	 */
	public function getCc()
	{
		if ($this->headers->has("Cc"))
		{
			$cc = $this->headers->get("Cc");
			if ($cc == "") return array();
			else return (array) $cc;
		}
	}
	/**
	 * Set the list of recipients in the Bcc: header
	 * @param mixed An array or a string
	 */
	public function setBcc($bcc)
	{
		if ($bcc)
		{
			if (!is_array($bcc)) $bcc = array($bcc);
			foreach ($bcc as $key => $value)
			{
				if ($value instanceof Swift_Address) $bcc[$key] = $value->build();
			}
		}
		$this->headers->set("Bcc", $bcc);
	}
	/**
	 * Return the list of recipients in the Bcc: header
	 * @return array
	 */
	public function getBcc()
	{
		if ($this->headers->has("Bcc"))
		{
			$bcc = $this->headers->get("Bcc");
			if ($bcc == "") return array();
			else return (array) $bcc;
		}
	}
	/**
	 * Set the subject in the headers
	 * @param string The subject of the email
	 */
	public function setSubject($subject)
	{
		$this->headers->set("Subject", $subject);
	}
	/**
	 * Get the current subject used in the headers
	 * @return string
	 */
	public function getSubject()
	{
		return $this->headers->get("Subject");
	}
	/**
	 * Set the date in the headers in RFC 2822 format
	 * @param int The time as a UNIX timestamp
	 */
	public function setDate($date)
	{
		$this->headers->set("Date", date("r", $date));
	}
	/**
	 * Get the date as it looks in the headers
	 * @return string
	 */
	public function getDate()
	{
		return strtotime($this->headers->get("Date"));
	}
	/**
	 * Set the charset of the document
	 * @param string The charset used
	 */
	public function setCharset($charset)
	{
		$this->headers->setAttribute("Content-Type", "charset", $charset);
		if (($this->getEncoding() == "7bit") && (strtolower($charset) == "utf-8" || strtolower($charset) == "utf8")) $this->setEncoding("8bit");
	}
	/**
	 * Get the charset used in the document
	 * Returns null if none is set
	 * @return string
	 */
	public function getCharset()
	{
		if ($this->headers->hasAttribute("Content-Type", "charset"))
		{
			return $this->headers->getAttribute("Content-Type", "charset");
		}
		else
		{
			return null;
		}
	}
	/**
	 * Set the "format" attribute to flowed
	 * @param boolean On or Off
	 */
	public function setFlowed($flowed=true)
	{
		$value = null;
		if ($flowed) $value = "flowed";
		$this->headers->setAttribute("Content-Type", "format", $value);
	}
	/**
	 * Check if the message format is set as flowed
	 * @return boolean
	 */
	public function isFlowed()
	{
		if ($this->headers->hasAttribute("Content-Type", "format")
			&& $this->headers->getAttribute("Content-Type", "format") == "flowed")
		{
			return true;
		}
		else return false;
	}
	/**
	 * Set the message prioirty in the mail client (don't rely on this)
	 * @param int The priority as a value between 1 (high) and 5 (low)
	 */
	public function setPriority($priority)
	{
		$priority = (int) $priority;
		if ($priority > self::PRIORITY_LOW) $priority = self::PRIORITY_LOW;
		if ($priority < self::PRIORITY_HIGH) $priority = self::PRIORITY_HIGH;
		$label = array(1 => "High", 2 => "High", 3 => "Normal", 4 => "Low", 5 => "Low");
		$this->headers->set("X-Priority", $priority);
		$this->headers->set("X-MSMail-Priority", $label[$priority]);
		$this->headers->set("X-MimeOLE", "Produced by SwiftMailer " . self::VERSION);
	}
	/**
	 * Request that the client send back a read-receipt (don't rely on this!)
	 * @param string Request address
	 */
	public function requestReadReceipt($request)
	{
		if ($request instanceof Swift_Address) $request = $request->build();
		if (!$request)
		{
			$this->headers->set("Disposition-Notification-To", null);
			$this->headers->set("X-Confirm-Reading-To", null);
			$this->headers->set("Return-Receipt-To", null);
		}
		else
		{
			$this->headers->set("Disposition-Notification-To", $request);
			$this->headers->set("X-Confirm-Reading-To", $request);
			$this->headers->set("Return-Receipt-To", $request);
		}
	}
	/**
	 * Check if a read receipt has been requested for this message
	 * @return boolean
	 */
	public function wantsReadReceipt()
	{
		return $this->headers->has("Disposition-Notification-To");
	}
	/**
	 * Get the current message priority
	 * Returns NULL if none set
	 * @return int
	 */
	public function getPriority()
	{
		if ($this->headers->has("X-Priority")) return $this->headers->get("X-Priority");
		else return null;
	}
	/**
	 * Alias for setData()
	 * @param mixed Body
	 */
	public function setBody($body)
	{
		$this->setData($body);
	}
	/**
	 * Alias for getData()
	 * @return mixed The document body
	 */
	public function getBody()
	{
		return $this->getData();
	}
	/**
	 * Set the MIME warning message which is displayed to old clients
	 * @var string The full warning message (in 7bit ascii)
	 */
	public function setMimeWarning($text)
	{
		$this->mimeWarning = (string) $text;
	}
	/**
	 * Get the MIME warning which is displayed to old clients
	 * @return string
	 */
	public function getMimeWarning()
	{
		return $this->mimeWarning;
	}
	/**
	 * Attach a mime part or an attachment of some sort
	 * Any descendant of Swift_Message_Mime can be added safely (including other Swift_Message objects for mail forwarding!!)
	 * @param Swift_Message_Mime The document to attach
	 * @param string An identifier to use (one is returned otherwise)
	 * @return string The identifier for the part
	 */
	public function attach(Swift_Message_Mime $child, $id=null)
	{
		try {
			switch (true)
			{
				case (($child->getLevel() == "alternative") || $child instanceof Swift_Message_Part):
					$sign = (strtolower($child->getContentType()) == "text/plain") ? -1 : 1;
					$id = $this->parentRefs["alternative"]->addChild($child, $id, $sign);
					$this->alternativeRefs[$id] = $child;
					break;
				case (($child->getLevel() == "related") || $child instanceof Swift_Message_EmbeddedFile):
					$id = "cid:" . $child->getContentId();
					$id = $this->parentRefs["related"]->addChild($child, $id, 1);
					$this->relatedRefs[$id] = $child;
					break;
				case (($child->getLevel() == "alternative") || $child instanceof Swift_Message_Attachment): default:
					$id = $this->parentRefs["mixed"]->addChild($child, $id, 1);
					$this->mixedRefs[$id] = $child;
					break;
			}
			$this->postAttachFixStructure();
			$this->fixContentType();
			return $id;
		} catch (Swift_Message_MimeException $e) {
			throw new Swift_Message_MimeException("Something went wrong whilst trying to move some MIME parts during an attach(). " .
				"The MIME component threw an exception:<br />" . $e->getMessage());
		}
	}
	/**
	 * Remove a nested MIME part
	 * @param string The ID of the attached part
	 * @throws Swift_Message_MimeException If no such part exists
	 */
	public function detach($id)
	{
		try {
			switch (true)
			{
				case array_key_exists($id, $this->alternativeRefs):
					$this->parentRefs["alternative"]->removeChild($id);
					unset($this->alternativeRefs[$id]);
					break;
				case array_key_exists($id, $this->relatedRefs):
					$this->parentRefs["related"]->removeChild($id);
					unset($this->relatedRefs[$id]);
					break;
				case array_key_exists($id, $this->mixedRefs):
					$this->parentRefs["mixed"]->removeChild($id);
					unset($this->mixedRefs[$id]);
					break;
				default:
					throw new Swift_Message_MimeException("Unable to detach part identified by ID '" . $id . "' since it's not registered.");
					break;
			}
			$this->postDetachFixStructure();
			$this->fixContentType();
		} catch (Swift_Message_MimeException $e) {
			throw new Swift_Message_MimeException("Something went wrong whilst trying to move some MIME parts during a detach(). " .
				"The MIME component threw an exception:<br />" . $e->getMessage());
		}
	}
	/**
	 * Sets the correct content type header by looking at what types of data we have set
	 */
	protected function fixContentType()
	{
		if (!empty($this->mixedRefs)) $this->setContentType("multipart/mixed");
		elseif (!empty($this->relatedRefs)) $this->setContentType("multipart/related");
		elseif (!empty($this->alternativeRefs)) $this->setContentType("multipart/alternative");
	}
	/**
	 * Move a branch of the tree, containing all it's MIME parts onto another branch
	 * @param string The content type on the branch itself
	 * @param string The content type which may exist in the branch's parent
	 * @param array The array containing all the nodes presently
	 * @param string The location of the branch now
	 * @param string The location of the branch after moving
	 * @param string The key to identify the branch by in it's new location
	 */
	protected function moveBranchIn($type, $nested_type, $from, $old_branch, $new_branch, $tag)
	{
		$new = new Swift_Message_Part();
		$new->setContentType($type);
		$this->parentRefs[$new_branch]->addChild($new, $tag, -1);
		
		switch ($new_branch)
		{
			case "related": $this->relatedRefs[$tag] = $new;
				break;
			case "mixed": $this->mixedRefs[$tag] = $new;
				break;
		}
		
		foreach ($from as $id => $ref)
		{
			$sign = (strtolower($ref->getContentType()) == "text/html"
				|| strtolower($ref->getContentType()) == $nested_type) ? -1 : 1;
			switch ($new_branch)
			{
				case "related": $this->relatedRefs[$tag]->addChild($ref, $id, $sign);
					break;
				case "mixed": $this->mixedRefs[$tag]->addChild($ref, $id, $sign);
					break;
			}
			$this->parentRefs[$old_branch]->removeChild($id);
		}
		$this->parentRefs[$old_branch] = $new;
	}
	/**
	 * Analyzes the mixing of MIME types in a mulitpart message an re-arranges if needed
	 * It looks complicated and long winded but the concept is pretty simple, even if putting it
	 * in code does me make want to cry!
	 */
	protected function postAttachFixStructure()
	{
		switch (true)
		{
			case (!empty($this->mixedRefs) && !empty($this->relatedRefs) && !empty($this->alternativeRefs)):
				if (!isset($this->relatedRefs["_alternative"]))
				{
					$this->moveBranchIn(
						"multipart/alternative", "multipart/alternative", $this->alternativeRefs, "alternative", "related", "_alternative");
				}
				if (!isset($this->mixedRefs["_related"]))
				{
					$this->moveBranchIn(
						"multipart/related", "multipart/alternative", $this->relatedRefs, "related", "mixed", "_related");
				}
				break;
			case (!empty($this->mixedRefs) && !empty($this->relatedRefs)):
				if (!isset($this->mixedRefs["_related"]))
				{
					$this->moveBranchIn(
						"multipart/related", "multipart/related", $this->relatedRefs, "related", "mixed", "_related");
				}
				break;
			case (!empty($this->mixedRefs) && !empty($this->alternativeRefs)):
				if (!isset($this->mixedRefs["_alternative"]))
				{
					$this->moveBranchIn(
						"multipart/alternative", null, $this->alternativeRefs, "alternative", "mixed", "_alternative");
				}
				break;
			case (!empty($this->relatedRefs) && !empty($this->alternativeRefs)):
				if (!isset($this->relatedRefs["_alternative"]))
				{
					$this->moveBranchIn(
						"multipart/alternative", "multipart/alternative", $this->alternativeRefs, "alternative", "related", "_alternative");
				}
				break;
		}
	}
	/**
	 * Move a branch further toward the top of the tree
	 * @param array The array containing MIME parts from the old branch
	 * @param string The name of the old branch
	 * @param string The name of the new branch
	 * @param string The key of the branch being moved
	 */
	protected function moveBranchOut($from, $old_branch, $new_branch, $tag)
	{
		foreach ($from as $id => $ref)
		{
			$sign = (strtolower($ref->getContentType()) == "text/html"
				|| strtolower($ref->getContentType()) == "multipart/alternative") ? -1 : 1;
			$this->parentRefs[$new_branch]->addChild($ref, $id, $sign);
			switch ($new_branch)
			{
				case "related": $this->relatedRefs[$tag]->removeChild($id);
					break;
				case "mixed": $this->parentRefs[$old_branch]->removeChild($id);
					break;
			}
		}
		$this->parentRefs[$new_branch]->removeChild($tag);
		$mixed = $this->parentRefs[$new_branch];
		$this->parentRefs[$old_branch] = $mixed;
		switch ($new_branch)
		{
			case "related": unset($this->relatedRefs[$tag]);
				break;
			case "mixed": unset($this->mixedRefs[$tag]);
				break;
		}
	}
	/**
	 * Analyzes the mixing of MIME types in a mulitpart message an re-arranges if needed
	 * It looks complicated and long winded but the concept is pretty simple, even if putting it
	 * in code does me make want to cry!
	 */
	protected function postDetachFixStructure()
	{
		switch (true)
		{
			case (!empty($this->mixedRefs) && !empty($this->relatedRefs) && !empty($this->alternativeRefs)):
				if (array_keys($this->relatedRefs) == array("_alternative"))
				{
					$alt = $this->parentRefs["related"]->getChild("_alternative");
					$this->parentRefs["mixed"]->addChild($alt, "_alternative", -1);
					$this->mixedRefs["_alternative"] = $alt;
					$this->parentRefs["related"]->removeChild("_alternative");
					unset($this->relatedRefs["_alternative"]);
					$this->parentRefs["mixed"]->removeChild("_related");
					unset($this->mixedRefs["_related"]);
				}
				if (array_keys($this->mixedRefs) == array("_related"))
				{
					$this->moveBranchOut($this->relatedRefs, "related", "mixed", "_related");
				}
				break;
			case (!empty($this->mixedRefs) && !empty($this->relatedRefs)):
				if (array_keys($this->mixedRefs) == array("_related"))
				{
					$this->moveBranchOut($this->relatedRefs, "related", "mixed", "_related");
				}
				if (isset($this->relatedRefs["_alternative"]))
				{
					$this->detach("_alternative");
				}
				break;
			case (!empty($this->mixedRefs) && !empty($this->alternativeRefs)):
				if (array_keys($this->mixedRefs) == array("_alternative"))
				{
					$this->moveBranchOut($this->alternativeRefs, "alternative", "mixed", "_alternative");
				}
				break;
			case (!empty($this->relatedRefs) && !empty($this->alternativeRefs)):
				if (array_keys($this->relatedRefs) == array("_alternative"))
				{
					$this->moveBranchOut($this->alternativeRefs, "alternative", "related", "_alternative");
				}
				break;
			case (!empty($this->mixedRefs)):
				if (isset($this->mixedRefs["_related"])) $this->detach("_related");
			case (!empty($this->relatedRefs)):
				if (isset($this->relatedRefs["_alternative"]) || isset($this->mixedRefs["_alternative"]))
					$this->detach("_alternative");
				break;
		}
	}
	/**
	 * Execute needed logic prior to compilation
	 */
	public function preBuild()
	{
		$data = $this->getData();
		if (!($enc = $this->getEncoding()))
		{
			$this->setEncoding("8bit");
		}
		if ($this->getCharset() === null && !$this->numChildren())
		{
			Swift_ClassLoader::load("Swift_Message_Encoder");
			if (is_string($data) && Swift_Message_Encoder::instance()->isUTF8($data))
			{
				$this->setCharset("utf-8");
			}
			elseif(is_string($data) && Swift_Message_Encoder::instance()->is7BitAscii($data))
			{
				$this->setCharset("us-ascii");
				if (!$enc) $this->setEncoding("7bit");
			}
			else $this->setCharset("iso-8859-1");
		}
		elseif ($this->numChildren())
		{
			if (!$this->getData())
			{
				$this->setData($this->getMimeWarning());
				$this->setLineWrap(76);
			}
			
			if ($this->getCharset() !== null) $this->setCharset(null);
			if ($this->isFlowed()) $this->setFlowed(false);
			$this->setEncoding("7bit");
		}
	}
}
