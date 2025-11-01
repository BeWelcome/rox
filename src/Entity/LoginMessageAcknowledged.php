<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Entity\NewMember as Member;
use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'login_messages_acknowledged')]
#[ORM\Entity]
class LoginMessageAcknowledged
{
    #[ORM\Column(name: 'acknowledged', type: 'boolean', nullable: false)]
    private bool $acknowledged;

    #[ORM\JoinColumn(name: 'messageid', referencedColumnName: 'id')]
    #[ORM\OneToOne(targetEntity: LoginMessage::class)]
    #[ORM\Id]
    private LoginMessage $message;

    #[ORM\JoinColumn(name: 'memberid', referencedColumnName: 'id')]
    #[ORM\OneToOne(targetEntity: Member::class)]
    #[ORM\Id]
    private Member $member;

    public function setAcknowledged(): self
    {
        $this->acknowledged = true;

        return $this;
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
