<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * PollsRecordOfChoices
 *
 * @ORM\Table(name="polls_record_of_choices", indexes={@ORM\Index(name="IdMember", columns={"IdMember"}), @ORM\Index(name="idEmail", columns={"Email"}), @ORM\Index(name="IdPoll", columns={"IdPoll"}), @ORM\Index(name="IdPollChoice", columns={"IdPollChoice"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 */
class PollRecordOfChoice
{
    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var PollChoice
     *
     * @ORM\ManyToOne(targetEntity="PollChoice")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdPollChoice", referencedColumnName="id")
     * })
     */
    private $pollChoice;

    /**
     * @var string
     *
     * @ORM\Column(name="Email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var Poll
     *
     * @ORM\ManyToOne(targetEntity="Poll")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdPoll", referencedColumnName="id")
     * })
     */
    private $poll;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdMember", referencedColumnName="id")
     * })
     */
    private $member;

    /**
     * Set updated
     *
     * @param DateTime $updated
     *
     * @return PollRecordOfChoice
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return Carbon
     */
    public function getUpdated()
    {
        return Carbon::instance($this->updated);
    }

    /**
     * Set created
     *
     * @param DateTime $created
     *
     * @return PollRecordOfChoice
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return Carbon
     */
    public function getCreated()
    {
        return Carbon::instance($this->created);
    }

    /**
     * Set poll choice
     *
     * @param PollChoice $pollChoice
     *
     * @return PollRecordOfChoice
     */
    public function setPollChoice($pollChoice)
    {
        $this->pollChoice = $pollChoice;

        return $this;
    }

    /**
     * Get poll choice
     *
     * @return PollChoice
     */
    public function getPollChoice()
    {
        return $this->pollChoice;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return PollRecordOfChoice
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set poll
     *
     * @param Poll $poll
     *
     * @return PollRecordOfChoice
     */
    public function setPoll(Poll $poll = null)
    {
        $this->poll = $poll;

        return $this;
    }

    /**
     * Get poll
     *
     * @return Poll
     */
    public function getPoll()
    {
        return $this->poll;
    }

    /**
     * Set member
     *
     * @param Member $member
     *
     * @return PollRecordOfChoice
     */
    public function setMember(Member $member = null)
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

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
        $this->updated = new DateTime('now');
    }

    /**
     * Triggered on update.
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new DateTime('now');
    }
}
