<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'polls_choices')]
#[ORM\Index(name: 'IdPoll', columns: ['IdPoll'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
class PollChoice
{
    /**
     * @var int
     */
    #[ORM\Column(name: 'IdChoiceText', type: 'integer', nullable: false)]
    private $text;

    /**
     * @var string[]
     *
     * Collects all translated choices of the poll
     */
    private $texts;

    /**
     * @var int
     */
    #[ORM\Column(name: 'Counter', type: 'integer', nullable: false)]
    private $counter = '0';

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
     * @var Poll
     */
    #[ORM\JoinColumn(name: 'IdPoll', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \Poll::class, inversedBy: 'choices')]
    private $poll;

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

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
     * @return PollChoice
     */
    public function setPoll(?Poll $poll = null)
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

    /**
     * Triggered after load from database.
     */
    #[ORM\PostLoad]
    public function onPostLoad(PostLoadEventArgs $eventArgs): void
    {
        $translationRepository = $eventArgs->getObjectManager()->getRepository(Translation::class);
        $translatedTexts = $translationRepository->findBy(['idTrad' => $this->text]);

        $texts = [];
        /** @var Translation $text */
        foreach ($translatedTexts as $text) {
            $texts[$text->getLanguage()->getShortCode()] = $text->getSentence();
        }
        $this->texts = $texts;
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
