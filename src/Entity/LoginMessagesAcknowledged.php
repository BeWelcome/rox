<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LoginMessagesAcknowledged
 *
 * @ORM\Table(name="login_messages_acknowledged")
 * @ORM\Entity
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class LoginMessagesAcknowledged
{
    /**
     * @var bool
     *
     * @ORM\Column(name="acknowledged", type="boolean", nullable=false)
     */
    private $acknowledged;

    /**
     * @var LoginMessage
     *
     * @ORM\OneToOne(targetEntity="\App\Entity\LoginMessage")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="messageid", referencedColumnName="id")
     * })
     * @ORM\Id
     */
    private $message;

    /**
     * @var Member
     *
     * @ORM\OneToOne(targetEntity="\App\Entity\Member")
     * @ORM\Id
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="memberid", referencedColumnName="id")
     * })
     */
    private $member;

    /**
     * Set acknowledged
     *
     * @param bool $acknowledged
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
     * @return bool
     */
    public function getAcknowledged()
    {
        return $this->acknowledged;
    }

    /**
     * Set message
     *
     * @param LoginMessage
     *
     * @return LoginMessagesAcknowledged
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return LoginMessage
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set memberid
     *
     * @param Member
     *
     * @return LoginMessagesAcknowledged
     */
    public function setMember(Member $member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }
}
