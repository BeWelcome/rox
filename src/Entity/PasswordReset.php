<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Entity\NewMember as Member;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'passwordreset')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class PasswordReset
{
    #[ORM\OneToOne(targetEntity: Member::class)]
    private Member $member;

    #[ORM\Column(name: 'generated', type: 'datetime', nullable: false)]
    private DateTime $generated;

    #[ORM\Column(name: 'token', type: 'string', length: 64, nullable: false)]
    private string $token;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function getGenerated(): Carbon
    {
        return Carbon::instance($this->generated);
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->generated = new DateTime('now');
    }
}
