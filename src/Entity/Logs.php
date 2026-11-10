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
 * main's log table (`logs`), which main still writes. develop writes and reads Log (`log`), filled from this table
 * by a migration; this entity goes when main is replaced.
 *
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'logs')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Logs
{
    /**
     * @var Member
     */
    #[ORM\JoinColumn(name: 'idMember', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Member::class, fetch: 'EAGER')]
    private $member;

    /**
     * @var string
     */
    #[ORM\Column(name: 'Str', type: 'text', length: 65535, nullable: false)]
    private $logMessage;

    /**
     * @var string
     */
    #[ORM\Column(name: 'Type', type: 'text', length: 255, nullable: false)]
    private $type;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private $created;

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue]
    private $id;

    /**
     * Set member.
     *
     * @param Member $member
     *
     * @return Logs
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member.
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set str.
     *
     * @param string $logMessage
     *
     * @return Logs
     */
    public function setLogMessage($logMessage)
    {
        $this->logMessage = $logMessage;

        return $this;
    }

    /**
     * Get str.
     *
     * @return string
     */
    public function getLogMessage()
    {
        return $this->logMessage;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Logs
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set created.
     *
     * @param DateTime $created
     *
     * @return Logs
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return Carbon
     */
    public function getCreated()
    {
        return Carbon::instance($this->created);
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Triggered on insert.
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
    }
}
