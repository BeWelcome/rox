<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;

/**
 * Specialrelations.
 *
 * @ORM\Table(name="specialrelations",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="UniqueRelation", columns={"IdOwner", "IdRelation"})},
 *     indexes={@ORM\Index(name="IdOwner", columns={"IdOwner"})}
 * )
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="App\Repository\RelationRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Relation
{
    /**
     * @ORM\Column(name="Comment", type="integer", nullable=false)
     */
    private int $comment = 0;

    private ?string $commentText = "";

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private DateTime $created;

    /**
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private DateTime $updated;

    /**
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumn(name="IdOwner", referencedColumnName="id")
     */
    private Member $owner;

    /**
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumn(name="IdRelation", referencedColumnName="id")
     */
    private Member $receiver;

    /**
     * @ORM\Column(name="Confirmed", type="string", nullable=false)
     */
    private string $confirmed = 'No';

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    public function setComment(int $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    public function getComment(): int
    {
        return $this->comment;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function getUpdated(): Carbon
    {
        return Carbon::instance($this->updated);
    }

    public function setUpdated(DateTime $updated): Relation
    {
        $this->updated = $updated;

        return $this;
    }

    public function setOwner(Member $owner): Relation
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return Member
     */
    public function getOwner(): Member
    {
        return $this->owner;
    }

    public function setReceiver(Member $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function getReceiver(): Member
    {
        return $this->receiver;
    }

    public function setConfirmed(string $confirmed): self
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    public function getConfirmed(): string
    {
        return $this->confirmed;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Triggered after load from database.
     *
     * @ORM\PostLoad
     */
    public function onPostLoad(PostLoadEventArgs $args): void
    {
        $objectManager = $args->getObjectManager();
        $memberTranslationRepository = $objectManager->getRepository(MemberTranslation::class);
        $translatedComment = $memberTranslationRepository->findOneBy([
            'translation' => $this->comment,
            'owner' => $this->owner,
        ]);

        if (null !== $translatedComment) {
            $this->commentText = $translatedComment->getSentence();
        }
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist(PrePersistEventArgs $args)
    {
        $this->created = new DateTime('now');
        $this->updated = $this->created;

        if (null !== $this->commentText) {
            $this->createRelationComment($args->getObjectManager());
        }
    }

    /**
     * Triggered on update.
     *
     * @ORM\PostUpdate
     */
    public function onPostUpdate(PostUpdateEventArgs $args)
    {
        if (0 !== $this->comment) {
            $objectManager = $args->getObjectManager();

            $memberTranslationRepository = $objectManager->getRepository(MemberTranslation::class);
            $translatedComment = $memberTranslationRepository->findOneBy([
                'translation' => $this->comment,
                'owner' => $this->getOwner()
            ]);

            $translatedComment->setSentence($this->commentText ?? '');
            $objectManager->persist($translatedComment);
            $objectManager->flush();
        } else {
            $translatedComment = $this->createRelationComment($args->getObjectManager());
            $this->comment = $translatedComment->getId();
        }
    }

    private function createRelationComment(ObjectManager $objectManager): MemberTranslation
    {
        $languageRepository = $objectManager->getRepository(Language::class);
        $language = $languageRepository->findOneBy(['shortCode' => 'en']);

        $translatedComment = new MemberTranslation();
        $translatedComment->setSentence($this->commentText);
        $translatedComment->setOwner($this->getOwner());
        $translatedComment->setTranslator($this->getOwner());
        $translatedComment->setLanguage($language);
        $translatedComment->setTableColumn('specialrelations.comment');
        $objectManager->persist($translatedComment);
        $objectManager->flush();

        $translatedComment->setTranslation($translatedComment->getId());
        $objectManager->persist($translatedComment);
        $objectManager->flush();

        return $translatedComment;
    }

    public function getCommentText(): ?string
    {
        return $this->commentText;
    }

    public function setCommentText(?string $commentText): self
    {
        $this->commentText = $commentText;

        return $this;
    }
}
