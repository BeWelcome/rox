<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectManagerAware;

/**
 * PollChoice.
 *
 * @ORM\Table(name="polls_choices", indexes={@ORM\Index(name="IdPoll", columns={"IdPoll"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class PollChoice implements ObjectManagerAware
{
    /**
     * @var int
     *
     * @ORM\Column(name="IdChoiceText", type="integer", nullable=false)
     */
    private $text;

    /**
     * @var string[]
     *
     * Collects all translated choices of the poll
     */
    private $texts;

    /**
     * @var int
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
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Set choice text.
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
     * Get choice texts.
     *
     * @return string[]
     */
    public function getTexts()
    {
        return $this->texts;
    }

    /**
     * Set counter.
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
     * Get counter.
     *
     * @return int
     */
    public function getCounter()
    {
        return $this->counter;
    }

    /**
     * Set updated.
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
     * Get updated.
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created.
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
     * Set poll.
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
     * Get poll.
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

    /**
     * Triggered after load from database.
     *
     * @ORM\PostLoad
     */
    public function onPostLoad()
    {
        $translationRepository = $this->objectManager->getRepository(Translation::class);
        $translatedTexts = $translationRepository->findBy(['idTrad' => $this->text]);

        $texts = [];
        /** @var Translation $text */
        foreach ($translatedTexts as $text) {
            $texts[$text->getLanguage()->getShortCode()] = $text->getSentence();
        }
        $this->texts = $texts;
    }

    public function injectObjectManager(ObjectManager $objectManager, ClassMetadata $classMetadata)
    {
        $this->objectManager = $objectManager;
    }

    public function getText(): int
    {
        return $this->text;
    }

    public function setText(int $text): void
    {
        $this->text = $text;
    }
}
