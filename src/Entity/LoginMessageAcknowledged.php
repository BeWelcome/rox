<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * LoginMessageAcknowledged.
 *
 * @ORM\Table(name="login_messages_acknowledged")
 * @ORM\Entity
 */
class LoginMessageAcknowledged
{
    /**
     * @ORM\Column(name="acknowledged", type="boolean", nullable=false)
     */
    private bool $acknowledged;

    /**
     * @ORM\OneToOne(targetEntity="\App\Entity\LoginMessage")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="messageid", referencedColumnName="id")
     * })
     * @ORM\Id
     */
    private LoginMessage $message;

    /**
     * @ORM\OneToOne(targetEntity="\App\Entity\Member")
     * @ORM\Id
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="memberid", referencedColumnName="id")
     * })
     */
    private Member $member;

    public function setAcknowledged(): self
    {
        $this->acknowledged = true;

        return $this;
    }

    public function getAcknowledged(): bool
    {
        return $this->acknowledged;
    }

    public function setMessage(LoginMessage $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): LoginMessage
    {
        return $this->message;
    }

    public function setMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getMember(): Member
    {
        return $this->member;
    }
}
