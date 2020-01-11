<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * PollChoice
 *
 * @ORM\Table(name="polls_choices", indexes={@ORM\Index(name="IdPoll", columns={"IdPoll"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class PollChoice
{
    /**
     * @var Translation
     *
     * @ORM\ManyToMany(targetEntity="Translation", fetch="EAGER")
     * @ORM\JoinTable(name="poll_choices_translations",
     *      joinColumns={@ORM\JoinColumn(name="poll_choice_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="translation_id", referencedColumnName="id")}
     *      )
     *
     * Collects all translated choices of the poll
     */
    private $choiceTexts;

    /**
     * @var integer
     *
     * @ORM\Column(name="Counter", type="integer", nullable=false)
     */
    private $counter = '0';

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
     * @var Poll
     *
     * @ORM\ManyToOne(targetEntity="Poll", inversedBy="choices")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdPoll", referencedColumnName="id")
     * })
     */
    private $poll;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function __construct()
    {
        $this->choiceTexts = new ArrayCollection();
    }

    /**
     * Set choice text
     *
     * @param Translation $choiceText
     *
     * @return PollChoice
     */
    public function setChoiceText($choiceText)
    {
        $this->choiceText = $choiceText;

        return $this;
    }

    /**
     * Get choice texts
     *
     * @return ArrayCollection
     */
    public function getChoiceTexts()
    {
        return $this->choiceTexts;
    }

    /**
     * Set counter
     *
     * @param int $counter
     *
     * @return PollChoice
     */
    public function setCounter($counter)
    {
        $this->counter = $counter;

        return $this;
    }

    /**
     * Get counter
     *
     * @return int
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * Set updated
     *
     * @param DateTime $updated
     *
     * @return PollChoice
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created
     *
     * @param DateTime $created
     *
     * @return PollChoice
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
     * @return PollChoice
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
