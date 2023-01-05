<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;

/**
 * Specialrelations.
 *
 * @ORM\Table(name="specialrelations", uniqueConstraints={@ORM\UniqueConstraint(name="UniqueRelation", columns={"IdOwner", "IdRelation"})}, indexes={@ORM\Index(name="IdOwner", columns={"IdOwner"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="App\Repository\RelationRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Relation
{
    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private $type;

    /**
     * Contains all comments for these relations (indexed by language).
     */
    private array $comments = [];

    /**
     * @var int
     *
     * @ORM\Column(name="Comment", type="integer", nullable=false)
     */
    private $comment;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumn(name="IdOwner", referencedColumnName="id")
     */
    private $owner;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumn(name="IdRelation", referencedColumnName="id")
     */
    private $relation;

    /**
     * @var string
     *
     * @ORM\Column(name="Confirmed", type="string", nullable=false)
     */
    private $confirmed = 'No';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Relation
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
     * Get comments.
     */
    public function getComments(): array
    {
        return $this->comments;
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
     * Get updated.
     *
     * @return Carbon
     */
    public function getUpdated()
    {
        return Carbon::instance($this->updated);
    }

    /**
     * Set owner.
     *
     * @param Member $owner
     *
     * @return Relation
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return Member
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set relation.
     *
     * @param Member $relation
     *
     * @return Relation
     */
    public function setRelation($relation)
    {
        $this->relation = $relation;

        return $this;
    }

    /**
     * Get relation.
     *
     * @return Member
     */
    public function getRelation()
    {
        return $this->relation;
    }

    /**
     * Set confirmed.
     *
     * @param string $confirmed
     *
     * @return Relation
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Get confirmed.
     *
     * @return string
     */
    public function getConfirmed()
    {
        return $this->confirmed;
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
     * Triggered after load from database.
     *
     * @ORM\PostLoad
     */
    public function onPostLoad(LifecycleEventArgs $args)
    {
        $objectManager = $args->getObjectManager();
        $memberTranslationRepository = $objectManager->getRepository(MemberTranslation::class);
        $translatedComments = $memberTranslationRepository->findBy(['translation' => $this->comment]);

        // Index by language.
        $comments = [];
        foreach ($translatedComments as $comment) {
            $comments[$comment->getLanguage()->getShortCode()] = $comment;
        }

        $this->comments = $comments;
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
        $this->updated = $this->created;
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
