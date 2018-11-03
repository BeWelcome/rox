<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LoginMessagesAcknowledged
 *
 * @ORM\Table(name="login_messages_acknowledged")
 * @ORM\Entity
 */
class LoginMessagesAcknowledged
{
    /**
     * @var boolean
     *
     * @ORM\Column(name="acknowledged", type="boolean", nullable=false)
     */
    private $acknowledged;

    /**
     * @var integer
     *
     * @ORM\Column(name="messageId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $messageid;

    /**
     * @var integer
     *
     * @ORM\Column(name="memberId", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $memberid;



    /**
     * Set acknowledged
     *
     * @param boolean $acknowledged
     *
     * @return LoginMessagesAcknowledged
     */
    public function setAcknowledged($acknowledged)
    {
        $this->acknowledged = $acknowledged;

        return $this;
    }

    /**
     * Get acknowledged
     *
     * @return boolean
     */
    public function getAcknowledged()
    {
        return $this->acknowledged;
    }

    /**
     * Set messageid
     *
     * @param integer $messageid
     *
     * @return LoginMessagesAcknowledged
     */
    public function setMessageid($messageid)
    {
        $this->messageid = $messageid;

        return $this;
    }

    /**
     * Get messageid
     *
     * @return integer
     */
    public function getMessageid()
    {
        return $this->messageid;
    }

    /**
     * Set memberid
     *
     * @param integer $memberid
     *
     * @return LoginMessagesAcknowledged
     */
    public function setMemberid($memberid)
    {
        $this->memberid = $memberid;

        return $this;
    }

    /**
     * Get memberid
     *
     * @return integer
     */
    public function getMemberid()
    {
        return $this->memberid;
    }
}
