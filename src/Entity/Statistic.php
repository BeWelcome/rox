<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Stats.
 *
 * @ORM\Table(name="stats", indexes={@ORM\Index(name="created", columns={"created"})})
 * @ORM\Entity(repositoryClass="App\Repository\StatisticsRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Statistic
{
    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var int
     *
     * @ORM\Column(name="NbActiveMembers", type="integer", nullable=false)
     */
    private $activeMembers;

    /**
     * @var int
     *
     * @ORM\Column(name="NbMessageSent", type="integer", nullable=false)
     */
    private $messagesSent;

    /**
     * @var int
     *
     * @ORM\Column(name="NbMessageRead", type="integer", nullable=false)
     */
    private $messagesRead;

    /**
     * @var int
     *
     * @ORM\Column(name="NbRequestsSent", type="integer", nullable=false)
     */
    private $requestsSent;

    /**
     * @var int
     *
     * @ORM\Column(name="NbRequestsAccepted", type="integer", nullable=false)
     */
    private $requestsAccepted;

    /**
     * @var int
     *
     * @ORM\Column(name="NbMemberWithOneTrust", type="integer", nullable=false)
     */
    private $membersWithPositiveComment;

    /**
     * @var int
     *
     * @ORM\Column(name="NbMemberWhoLoggedToday", type="integer", nullable=false)
     */
    private $membersWhoLoggedInToday;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set created.
     *
     * @param DateTime $created
     *
     * @return Statistic
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
     * Set number of active members.
     *
     * @param int $activeMembers
     *
     * @return Statistic
     */
    public function setActiveMembers($activeMembers)
    {
        $this->activeMembers = $activeMembers;

        return $this;
    }

    /**
     * Get number of active members.
     *
     * @return int
     */
    public function getActiveMembers()
    {
        return $this->activeMembers;
    }

    /**
     * Set number of messages sent.
     *
     * @param int $messagesSent
     *
     * @return Statistic
     */
    public function setMessagesSent($messagesSent)
    {
        $this->messagesSent = $messagesSent;

        return $this;
    }

    /**
     * Get number of messages sent.
     *
     * @return int
     */
    public function getMessagesSent()
    {
        return $this->messagesSent;
    }

    /**
     * Set number of messages read.
     *
     * @param int $messagesRead
     *
     * @return Statistic
     */
    public function setMessagesRead($messagesRead)
    {
        $this->messagesRead = $messagesRead;

        return $this;
    }

    /**
     * Get number of messages read.
     *
     * @return int
     */
    public function getMessagesRead()
    {
        return $this->messagesRead;
    }

    /**
     * Set number of requests sent.
     *
     * @param int $requestsSent
     *
     * @return Statistic
     */
    public function setRequestsSent($requestsSent)
    {
        $this->requestsSent = $requestsSent;

        return $this;
    }

    /**
     * Get number of requests sent.
     *
     * @return int
     */
    public function getRequestsSent()
    {
        return $this->requestsSent;
    }

    /**
     * Set number of requests which have been accepted.
     *
     * @param int $requestsAccepted
     *
     * @return Statistic
     */
    public function setRequestsAccepted($requestsAccepted)
    {
        $this->requestsAccepted = $requestsAccepted;

        return $this;
    }

    /**
     * Get number of requests which have been accepted.
     *
     * @return int
     */
    public function getRequestsAccepted()
    {
        return $this->requestsAccepted;
    }

    /**
     * Set number of members with at least one positive comment.
     *
     * @param int $membersWithPositiveComment
     *
     * @return Statistic
     */
    public function setMembersWithPositiveComment($membersWithPositiveComment)
    {
        $this->membersWithPositiveComment = $membersWithPositiveComment;

        return $this;
    }

    /**
     * Get number of members with at least one positive comment.
     *
     * @return int
     */
    public function getMembersWithPositiveComment()
    {
        return $this->membersWithPositiveComment;
    }

    /**
     * Set number of members who logged in today.
     *
     * @param int $membersWhoLoggedInToday
     *
     * @return Statistic
     */
    public function setMembersWhoLoggedInToday($membersWhoLoggedInToday)
    {
        $this->membersWhoLoggedInToday = $membersWhoLoggedInToday;

        return $this;
    }

    /**
     * Get number of members who logged in today.
     *
     * @return int
     */
    public function getMembersWhoLoggedInToday()
    {
        return $this->membersWhoLoggedInToday;
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
}
