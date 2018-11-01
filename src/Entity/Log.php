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
 * Logs.
 *
 * @ORM\Table(name="logs")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Log
{
    /**
     * @var \App\Entity\Member
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", fetch="EAGER")
     * @ORM\JoinColumn(name="idMember", referencedColumnName="id")
     */
    private $member;

    /**
     * @var string
     *
     * @ORM\Column(name="Str", type="text", length=65535, nullable=false)
     */
    private $logMessage;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="text", length=255, nullable=false)
     */
    private $type;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * Set member.
     *
     * @param Member $member
     *
     * @return Log
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
     * @return Log
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
     * @return Log
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
     * @param \DateTime $created
     *
     * @return Log
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
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new \DateTime('now');
    }
}
