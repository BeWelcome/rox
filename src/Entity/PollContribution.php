<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * PollsContributions
 *
 * @ORM\Table(name="polls_contributions",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="IdMember", columns={"IdMember", "IdPoll"})},
 *     indexes={@ORM\Index(name="idEmail", columns={"Email"}),
 *     @ORM\Index(name="IdPoll", columns={"IdPoll"}),
 *     @ORM\Index(name="IDX_D41FF2B3EA8330B4", columns={"IdMember"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 */
class PollContribution
{
    /**
     * @var string
     *
     * @ORM\Column(name="`Email`", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="EmailIsConfirmed", type="string", length=255, nullable=false)
     */
    private $emailIsConfirmed = 'No';

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
     * @var string
     *
     * @ORM\Column(name="comment", type="text", length=65535, nullable=false)
     */
    private $comment;

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
     * @ORM\ManyToOne(targetEntity="Poll", inversedBy="contributions", fetch="EAGER")
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
     * Set email
     *
     * @param string $email
     *
     * @return PollContribution
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
     * Set emailIsConfirmed
     *
     * @param string $emailIsConfirmed
     *
     * @return PollContribution
     */
    public function setEmailIsConfirmed($emailIsConfirmed)
    {
        $this->emailIsConfirmed = $emailIsConfirmed;

        return $this;
    }

    /**
     * Get emailIsConfirmed
     *
     * @return string
     */
    public function getEmailIsConfirmed()
    {
        return $this->emailIsConfirmed;
    }

    /**
     * Set updated
     *
     * @param DateTime $updated
     *
     * @return PollContribution
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
     * @return PollContribution
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
     * Set comment
     *
     * @param string $comment
     *
     * @return PollContribution
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set idpoll
     *
     * @param Poll $poll
     *
     * @return PollContribution
     */
    public function setPoll(Poll $poll = null)
    {
        $this->poll = $poll;

        return $this;
    }

    /**
     * Get idpoll
     *
     * @return Poll
     */
    public function getPoll()
    {
        return $this->poll;
    }

    /**
     * Set idmember
     *
     * @param Member $member
     *
     * @return PollContribution
     */
    public function setMember(Member $member = null)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get idmember
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
