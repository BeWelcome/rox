<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Legacy: the poll data as the old polls pages stored it. The polls pages use App\Entity\Poll;
 * Version20261026100000 copies this data there. Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'polls_record_of_choices')]
#[ORM\Index(name: 'polls_record_of_choices_members', columns: ['IdMember'])]
#[ORM\Index(name: 'polls_record_of_choices_email', columns: ['Email'])]
#[ORM\Index(name: 'polls_record_of_choices_poll', columns: ['IdPoll'])]
#[ORM\Index(name: 'polls_record_of_choices_poll_choice', columns: ['IdPollChoice'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
class PollsRecordOfChoice
{
    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'updated', type: 'datetime', nullable: false)]
    private $updated;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private $created;

    /**
     * @var PollsChoice
     */
    #[ORM\JoinColumn(name: 'IdPollChoice', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: PollsChoice::class)]
    private $pollChoice;

    /**
     * @var string
     */
    #[ORM\Column(name: 'Email', type: 'string', length: 255, nullable: false)]
    private $email;

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var Polls
     */
    #[ORM\JoinColumn(name: 'IdPoll', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Polls::class)]
    private $poll;

    /**
     * @var Member
     */
    #[ORM\JoinColumn(name: 'IdMember', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private $member;

    /**
     * Set updated.
     *
     * @param DateTime $updated
     *
     * @return PollsRecordOfChoice
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return Carbon
     */
    public function getUpdated()
    {
        return Carbon::instance($this->updated);
    }

    /**
     * Set created.
     *
     * @param DateTime $created
     *
     * @return PollsRecordOfChoice
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
     * Set poll choice.
     *
     * @param PollsChoice $pollChoice
     *
     * @return PollsRecordOfChoice
     */
    public function setPollChoice($pollChoice)
    {
        $this->pollChoice = $pollChoice;

        return $this;
    }

    /**
     * Get poll choice.
     *
     * @return PollsChoice
     */
    public function getPollChoice()
    {
        return $this->pollChoice;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return PollsRecordOfChoice
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
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
     * Set poll.
     *
     * @return PollsRecordOfChoice
     */
    public function setPoll(?Polls $poll = null)
    {
        $this->poll = $poll;

        return $this;
    }

    /**
     * Get poll.
     *
     * @return Polls
     */
    public function getPoll()
    {
        return $this->poll;
    }

    /**
     * Set member.
     *
     * @return PollsRecordOfChoice
     */
    public function setMember(?Member $member = null)
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
     * Triggered on insert.
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
        $this->updated = new DateTime('now');
    }

    /**
     * Triggered on update.
     */
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated = new DateTime('now');
    }
}
