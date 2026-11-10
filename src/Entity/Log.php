<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * What members and volunteers did on the site (the logs page, Admin\LogController), written by App\Logger\Logger.
 * A migration fills it from main's `logs` (Logs), ids kept.
 *
 * The indexes serve the logs page: newest first, by type and by member.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'log')]
#[ORM\Index(name: 'log_created', columns: ['created'])]
#[ORM\Index(name: 'log_type_created', columns: ['type', 'created'])]
#[ORM\Index(name: 'log_member_created', columns: ['member_id', 'created'])]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Log
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    /** Null for entries without a member (system tasks) and for members that no longer exist. */
    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(name: 'member_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private ?Member $member = null;

    #[ORM\Column(name: 'type', type: 'string', length: 64, nullable: false)]
    private string $type = '';

    /** HTML: the logs page purifies it. */
    #[ORM\Column(name: 'message', type: 'text', length: 16777215, nullable: false)]
    private string $logMessage = '';

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private ?DateTime $created = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function setLogMessage(string $logMessage): self
    {
        $this->logMessage = $logMessage;

        return $this;
    }

    public function getLogMessage(): string
    {
        return $this->logMessage;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
    }
}
