<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'mycontacts')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: \App\Repository\ProfileNoteRepository::class)]
class ProfileNote
{
    /**
     * @var Member
     */
    #[ORM\JoinColumn(name: 'IdMember', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private $owner;

    /**
     * @var Member
     */
    #[ORM\JoinColumn(name: 'IdContact', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: Member::class)]
    private $member;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private $created;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'updated', type: 'datetime', nullable: false)]
    private $updated;

    /**
     * @var string
     */
    /** main's single category (mycontacts.Category): the first of the note's categories */
    #[ORM\Column(name: 'Category', type: 'string', length: 255, nullable: false)]
    private $category = '';

    /**
     * @var Collection<int, ProfileNoteCategory>
     */
    #[ORM\OneToMany(targetEntity: ProfileNoteCategory::class, mappedBy: 'note', cascade: ['persist', 'remove'], orphanRemoval: true)]
    #[ORM\OrderBy(['name' => 'ASC'])]
    private Collection $categories;

    /**
     * @var string
     */
    #[ORM\Column(name: 'Comment', type: 'text', nullable: false)]
    private $comment;

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    public function __construct()
    {
        $this->categories = new ArrayCollection();
    }

    /**
     * Set owner.
     *
     * @param Member $owner
     *
     * @return ProfileNote
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
     * Set member.
     *
     * @param Member $member
     *
     * @return ProfileNote
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
     * @return list<string> the names of the note's categories, by name
     */
    public function getCategoryNames(): array
    {
        $names = array_map(static fn (ProfileNoteCategory $category): string => $category->getName(), $this->categories->toArray());
        sort($names, \SORT_NATURAL | \SORT_FLAG_CASE);

        return $names;
    }

    /**
     * Gives the note these categories (names, trimmed; empty ones and repeats are left out), keeping those it has.
     *
     * @param iterable<string> $names
     */
    public function setCategoryNames(iterable $names): self
    {
        $wanted = [];
        foreach ($names as $name) {
            $name = trim((string) $name);
            if ('' !== $name) {
                $wanted[mb_strtolower($name)] ??= $name;
            }
        }

        foreach ($this->categories as $category) {
            if (isset($wanted[mb_strtolower($category->getName())])) {
                unset($wanted[mb_strtolower($category->getName())]);
            } else {
                $this->categories->removeElement($category);
            }
        }
        foreach ($wanted as $name) {
            $this->categories->add(new ProfileNoteCategory($this, $name));
        }

        $this->category = $this->getCategoryNames()[0] ?? '';

        return $this;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return ProfileNote
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set created.
     *
     * @param DateTime $created
     *
     * @return ProfileNote
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
     * Set updated.
     *
     * @param DateTime $updated
     *
     * @return ProfileNote
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
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
        $this->updated = $this->created;
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
