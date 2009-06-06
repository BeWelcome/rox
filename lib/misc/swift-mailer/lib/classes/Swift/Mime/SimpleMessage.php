<?php

/*
 The default Message class Swift Mailer.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

//@require 'Swift/Mime/Message.php';
//@require 'Swift/Mime/MimePart.php';
//@require 'Swift/Mime/MimeEntity.php';
//@require 'Swift/Mime/HeaderSet.php';
//@require 'Swift/Mime/ContentEncoder.php';

/**
 * The default email message class.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_SimpleMessage extends Swift_Mime_MimePart
  implements Swift_Mime_Message
{
  
  /**
   * Create a new SimpleMessage with $headers, $encoder and $cache.
   * @param Swift_Mime_HeaderSet $headers
   * @param Swift_Mime_ContentEncoder $encoder
   * @param Swift_KeyCache $cache
   * @param string $charset
   */
  public function __construct(Swift_Mime_HeaderSet $headers,
    Swift_Mime_ContentEncoder $encoder, Swift_KeyCache $cache, $charset = null)
  {
    parent::__construct($headers, $encoder, $cache, $charset);
    $this->getHeaders()->defineOrdering(array(
      'Return-Path',
      'Sender',
      'Message-ID',
      'Date',
      'Subject',
      'From',
      'Reply-To',
      'To',
      'Cc',
      'Bcc',
      'MIME-Version',
      'Content-Type',
      'Content-Transfer-Encoding'
      ));
    $this->getHeaders()->setAlwaysDisplayed(
      array('Date', 'Message-ID', 'From')
      );
    $this->getHeaders()->addTextHeader('MIME-Version', '1.0');
    $this->setDate(time());
    $this->setId($this->getId());
    $this->getHeaders()->addMailboxHeader('From');
  }
  
  /**
   * Always returns {@link LEVEL_TOP} for a message instance.
   * @return int
   */
  public function getNestingLevel()
  {
    return self::LEVEL_TOP;
  }
  
  /**
   * Set the subject of this message.
   * @param string $subject
   */
  public function setSubject($subject)
  {
    if (!$this->_setHeaderFieldModel('Subject', $subject))
    {
      $this->getHeaders()->addTextHeader('Subject', $subject);
    }
    return $this;
  }
  
  /**
   * Get the subject of this message.
   * @return string
   */
  public function getSubject()
  {
    return $this->_getHeaderFieldModel('Subject');
  }
  
  /**
   * Set the date at which this message was created.
   * @param int $date
   */
  public function setDate($date)
  {
    if (!$this->_setHeaderFieldModel('Date', $date))
    {
      $this->getHeaders()->addDateHeader('Date', $date);
    }
    return $this;
  }
  
  /**
   * Get the date at which this message was created.
   * @return int
   */
  public function getDate()
  {
    return $this->_getHeaderFieldModel('Date');
  }
  
  /**
   * Set the return-path (the bounce address) of this message.
   * @param string $address
   */
  public function setReturnPath($address)
  {
    if (!$this->_setHeaderFieldModel('Return-Path', $address))
    {
      $this->getHeaders()->addPathHeader('Return-Path', $address);
    }
    return $this;
  }
  
  /**
   * Get the return-path (bounce address) of this message.
   * @return string
   */
  public function getReturnPath()
  {
    return $this->_getHeaderFieldModel('Return-Path');
  }
  
  /**
   * Set the sender of this message.
   * This does not override the From field, but it has a higher significance.
   * @param string $sender
   * @param string $name optional
   */
  public function setSender($address, $name = null)
  {
    if (!is_array($address) && isset($name))
    {
      $address = array($address => $name);
    }
    
    if (!$this->_setHeaderFieldModel('Sender', (array) $address))
    {
      $this->getHeaders()->addMailboxHeader('Sender', (array) $address);
    }
    return $this;
  }
  
  /**
   * Get the sender of this message.
   * @return string
   */
  public function getSender()
  {
    return $this->_getHeaderFieldModel('Sender');
  }
  
  /**
   * Add a From: address to this message.
   * 
   * If $name is passed this name will be associated with the address.
   * 
   * @param string $address
   * @param string $name optional
   */
  public function addFrom($address, $name = null)
  {
    return $this->setFrom(array_merge(
      (array) $this->getFrom(), array($address => $name)
    ));
  }
  
  /**
   * Set the from address of this message.
   * 
   * You may pass an array of addresses if this message is from multiple people.
   * 
   * If $name is passed and the first parameter is a string, this name will be
   * associated with the address.
   * 
   * @param string $addresses
   * @param string $name optional
   */
  public function setFrom($addresses, $name = null)
  {
    if (!is_array($addresses) && isset($name))
    {
      $addresses = array($addresses => $name);
    }
    
    if (!$this->_setHeaderFieldModel('From', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('From', (array) $addresses);
    }
    return $this;
  }
  
  /**
   * Get the from address of this message.
   * 
   * @return string
   */
  public function getFrom()
  {
    return $this->_getHeaderFieldModel('From');
  }
  
  /**
   * Add a Reply-To: address to this message.
   * 
   * If $name is passed this name will be associated with the address.
   * 
   * @param string $address
   * @param string $name optional
   */
  public function addReplyTo($address, $name = null)
  {
    return $this->setReplyTo(array_merge(
      (array) $this->getReplyTo(), array($address => $name)
    ));
  }
  
  /**
   * Set the reply-to address of this message.
   * 
   * You may pass an array of addresses if replies will go to multiple people.
   * 
   * If $name is passed and the first parameter is a string, this name will be
   * associated with the address.
   *
   * @param string $addresses
   * @param string $name optional
   */
  public function setReplyTo($addresses, $name = null)
  {
    if (!is_array($addresses) && isset($name))
    {
      $addresses = array($addresses => $name);
    }
    
    if (!$this->_setHeaderFieldModel('Reply-To', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('Reply-To', (array) $addresses);
    }
    return $this;
  }
  
  /**
   * Get the reply-to address of this message.
   * 
   * @return string
   */
  public function getReplyTo()
  {
    return $this->_getHeaderFieldModel('Reply-To');
  }
  
  /**
   * Add a To: address to this message.
   * 
   * If $name is passed this name will be associated with the address.
   * 
   * @param string $address
   * @param string $name optional
   */
  public function addTo($address, $name = null)
  {
    return $this->setTo(array_merge(
      (array) $this->getTo(), array($address => $name)
    ));
  }
  
  /**
   * Set the to addresses of this message.
   * 
   * If multiple recipients will receive the message and array should be used.
   * 
   * If $name is passed and the first parameter is a string, this name will be
   * associated with the address.
   * 
   * @param array $addresses
   * @param string $name optional
   */
  public function setTo($addresses, $name = null)
  {
    if (!is_array($addresses) && isset($name))
    {
      $addresses = array($addresses => $name);
    }
    
    if (!$this->_setHeaderFieldModel('To', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('To', (array) $addresses);
    }
    return $this;
  }
  
  /**
   * Get the To addresses of this message.
   * 
   * @return array
   */
  public function getTo()
  {
    return $this->_getHeaderFieldModel('To');
  }
  
  /**
   * Add a Cc: address to this message.
   * 
   * If $name is passed this name will be associated with the address.
   * 
   * @param string $address
   * @param string $name optional
   */
  public function addCc($address, $name = null)
  {
    return $this->setCc(array_merge(
      (array) $this->getCc(), array($address => $name)
    ));
  }
  
  /**
   * Set the Cc addresses of this message.
   * 
   * If $name is passed and the first parameter is a string, this name will be
   * associated with the address.
   *
   * @param array $addresses
   * @param string $name optional
   */
  public function setCc($addresses, $name = null)
  {
    if (!is_array($addresses) && isset($name))
    {
      $addresses = array($addresses => $name);
    }
    
    if (!$this->_setHeaderFieldModel('Cc', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('Cc', (array) $addresses);
    }
    return $this;
  }
  
  /**
   * Get the Cc address of this message.
   * 
   * @return array
   */
  public function getCc()
  {
    return $this->_getHeaderFieldModel('Cc');
  }
  
  /**
   * Add a Bcc: address to this message.
   * 
   * If $name is passed this name will be associated with the address.
   * 
   * @param string $address
   * @param string $name optional
   */
  public function addBcc($address, $name = null)
  {
    return $this->setBcc(array_merge(
      (array) $this->getBcc(), array($address => $name)
    ));
  }
  
  /**
   * Set the Bcc addresses of this message.
   * 
   * If $name is passed and the first parameter is a string, this name will be
   * associated with the address.
   * 
   * @param array $addresses
   * @param string $name optional
   */
  public function setBcc($addresses, $name = null)
  {
    if (!is_array($addresses) && isset($name))
    {
      $addresses = array($addresses => $name);
    }
    
    if (!$this->_setHeaderFieldModel('Bcc', (array) $addresses))
    {
      $this->getHeaders()->addMailboxHeader('Bcc', (array) $addresses);
    }
    return $this;
  }
  
  /**
   * Get the Bcc addresses of this message.
   * 
   * @return array
   */
  public function getBcc()
  {
    return $this->_getHeaderFieldModel('Bcc');
  }
  
  /**
   * Set the priority of this message.
   * The value is an integer where 1 is the highest priority and 5 is the lowest.
   * @param int $priority
   */
  public function setPriority($priority)
  {
    $priorityMap = array(
      1 => 'Highest',
      2 => 'High',
      3 => 'Normal',
      4 => 'Low',
      5 => 'Lowest'
      );
    $pMapKeys = array_keys($priorityMap);
    if ($priority > max($pMapKeys))
    {
      $priority = max($pMapKeys);
    }
    elseif ($priority < min($pMapKeys))
    {
      $priority = min($pMapKeys);
    }
    if (!$this->_setHeaderFieldModel('X-Priority',
      sprintf('%d (%s)', $priority, $priorityMap[$priority])))
    {
      $this->getHeaders()->addTextHeader('X-Priority',
        sprintf('%d (%s)', $priority, $priorityMap[$priority]));
    }
    return $this;
  }
  
  /**
   * Get the priority of this message.
   * The returned value is an integer where 1 is the highest priority and 5
   * is the lowest.
   * @return int
   */
  public function getPriority()
  {
    list($priority) = sscanf($this->_getHeaderFieldModel('X-Priority'),
      '%[1-5]'
      );
    return isset($priority) ? $priority : 3;
  }
  
  /**
   * Ask for a delivery receipt from the recipient to be sent to $addresses
   * @param array $addresses
   */
  public function setReadReceiptTo($addresses)
  {
    if (!$this->_setHeaderFieldModel('Disposition-Notification-To', $addresses))
    {
      $this->getHeaders()
        ->addMailboxHeader('Disposition-Notification-To', $addresses);
    }
    return $this;
  }
  
  /**
   * Get the addresses to which a read-receipt will be sent.
   * @return string
   */
  public function getReadReceiptTo()
  {
    return $this->_getHeaderFieldModel('Disposition-Notification-To');
  }
  
  /**
   * Attach a {@link Swift_Mime_MimeEntity} such as an Attachment or MimePart.
   * @param Swift_Mime_MimeEntity $entity
   */
  public function attach(Swift_Mime_MimeEntity $entity)
  {
    $this->setChildren(array_merge($this->getChildren(), array($entity)));
    return $this;
  }
  
  /**
   * Remove an already attached entity.
   * @param Swift_Mime_MimeEntity $entity
   */
  public function detach(Swift_Mime_MimeEntity $entity)
  {
    $newChildren = array();
    foreach ($this->getChildren() as $child)
    {
      if ($entity !== $child)
      {
        $newChildren[] = $child;
      }
    }
    $this->setChildren($newChildren);
    return $this;
  }
  
  /**
   * Attach a {@link Swift_Mime_MimeEntity} and return it's CID source.
   * This method should be used when embedding images or other data in a message.
   * @param Swift_Mime_MimeEntity $entity
   * @return string
   */
  public function embed(Swift_Mime_MimeEntity $entity)
  {
    $this->attach($entity);
    return 'cid:' . $entity->getId();
  }
  
  /**
   * Get this message as a complete string.
   * @return string
   */
  public function toString()
  {
    if (count($children = $this->getChildren()) > 0 && $this->getBody() != '')
    {
      $this->setChildren(array_merge(array($this->_becomeMimePart()), $children));
      $string = parent::toString();
      $this->setChildren($children);
    }
    else
    {
      $string = parent::toString();
    }
    return $string;
  }
  
  /**
   * Write this message to a {@link Swift_InputByteStream}.
   * @param Swift_InputByteStream $is
   */
  public function toByteStream(Swift_InputByteStream $is)
  {
    if (count($children = $this->getChildren()) > 0 && $this->getBody() != '')
    {
      $this->setChildren(array_merge(array($this->_becomeMimePart()), $children));
      parent::toByteStream($is);
      $this->setChildren($children);
    }
    else
    {
      parent::toByteStream($is);
    }
  }
  
  // -- Protected methods
  
  /** @see Swift_Mime_SimpleMimeEntity::_getIdField() */
  protected function _getIdField()
  {
    return 'Message-ID';
  }
  
  // -- Private methods
  
  /** Turn the body of this message into a child of itself if needed */
  private function _becomeMimePart()
  {
    $part = new parent($this->getHeaders()->newInstance(), $this->getEncoder(),
      $this->_getCache(), $this->_userCharset
      );
    $part->setContentType($this->_userContentType);
    $part->setBody($this->getBody());
    $part->setFormat($this->_userFormat);
    $part->setDelSp($this->_userDelSp);
    $part->_setNestingLevel($this->_getTopNestingLevel());
    return $part;
  }
  
  /** Get the highest nesting level nested inside this message */
  private function _getTopNestingLevel()
  {
    $highestLevel = $this->getNestingLevel();
    foreach ($this->getChildren() as $child)
    {
      $childLevel = $child->getNestingLevel();
      if ($highestLevel < $childLevel)
      {
        $highestLevel = $childLevel;
      }
    }
    return $highestLevel;
  }
  
}
