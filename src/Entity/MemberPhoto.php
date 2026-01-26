<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'member_photo')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class MemberPhoto
{
    #[ORM\Column(name: 'FilePath', type: 'text', length: 255, nullable: false)]
    private string $filepath = '';

    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id')]
    #[ORM\OneToOne(targetEntity: Member::class)]
    private Member $member;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function setFilepath(string $filepath): self
    {
        $this->filepath = $filepath;

        return $this;
    }

    public function getFilepath(): string
    {
        return $this->filepath;
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

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function getId(): int
    {
        return $this->id;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
    }
}
