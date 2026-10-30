<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\GroupType;
use App\Doctrine\MemberStatusType;
use DateTime;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\ObjectManager;

/**
 * A group (`group`). main's table `groups` is App\Entity\Groups; Version20261029100000 copied it into this one.
 *
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: '`group`')]
#[ORM\Entity(repositoryClass: \App\Repository\GroupRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Group
{
    public const int NOT_APPROVED = 0;
    public const int APPROVED = 1;
    public const int DISMISSED = 2;
    public const int IN_DISCUSSION = 3;
    public const int APPROVED_CLOSED = 4;
    public const int DISMISSED_CLOSED = 5;
    public const int IN_DISCUSSION_CLOSED = 6;

    public const array OPEN = [
        self::NOT_APPROVED, self::IN_DISCUSSION,
    ];

    public const array HANDLED = [
        self::APPROVED, self::DISMISSED,
    ];

    public const array CLOSED = [
        self::APPROVED_CLOSED, self::DISMISSED_CLOSED, self::IN_DISCUSSION_CLOSED,
    ];

    /** The group of all members: its forum is the agora. */
    public const int BEWELCOME = 0;

    #[ORM\Column(name: 'name', type: 'string', length: 100, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'type', type: 'group_type', nullable: false)]
    private string $type = GroupType::PUBLIC;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    /** The file name of the group's picture in the group image directory; empty for none. */
    #[ORM\Column(name: 'picture', type: 'string', length: 255, nullable: false, options: ['default' => ''])]
    private string $picture = '';

    /** The description in every language it was written or translated in. */
    #[ORM\OneToMany(targetEntity: GroupTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $translations;

    #[ORM\OneToMany(targetEntity: GroupMembership::class, mappedBy: 'group', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $groupMemberships;

    /** The group's forum posts are visible to its members only (else to all members). */
    #[ORM\Column(name: 'members_only', type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $membersOnly = false;

    #[ORM\Column(name: 'approved', type: 'smallint', nullable: false, options: ['default' => 0])]
    private int $approved = self::NOT_APPROVED;

    /** Where the group is about, if anywhere: a place, region or country (geonames). */
    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(name: 'location_id', referencedColumnName: 'geoname_id', nullable: true, onDelete: 'SET NULL')]
    private ?Location $location = null;

    /** @var Collection<int, GroupCategory> */
    #[ORM\ManyToMany(targetEntity: GroupCategory::class)]
    #[ORM\JoinTable(name: 'group_category_group')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'group_category_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Collection $categories;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    private ObjectManager $objectManager;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->groupMemberships = new ArrayCollection();
        $this->categories = new ArrayCollection();
    }

    public function isBeWelcome(): bool
    {
        return isset($this->id) && self::BEWELCOME === $this->id;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): self
    {
        $this->location = $location;

        return $this;
    }

    /**
     * @return list<GroupCategory>
     */
    public function getCategories(): array
    {
        return array_values($this->categories->toArray());
    }

    public function addCategory(GroupCategory $category): self
    {
        if (!$this->categories->contains($category)) {
            $this->categories->add($category);
        }

        return $this;
    }

    public function removeCategory(GroupCategory $category): self
    {
        $this->categories->removeElement($category);

        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Group
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
     * Set created.
     *
     * @param DateTime $created
     *
     * @return Group
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set picture.
     *
     * @param string $picture
     *
     * @return Group
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * Get picture.
     *
     * @return string
     */
    public function getPicture()
    {
        return $this->picture;
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
     * Sets the description in a language: a new translation, or a change of the one there is.
     */
    public function setDescription(string $locale, string $text, ?Member $translator): self
    {
        $description = $this->getDescriptions()[$locale] ?? null;
        if (null === $description) {
            $description = new GroupTranslation($locale, GroupTranslation::DESCRIPTION, $text);
            $description->setObject($this);
            $this->translations->add($description);
        } elseif ($description->getContent() !== $text) {
            $description->setContent($text);
            $description->setUpdatedAt(new DateTimeImmutable());
        }
        $description->setTranslator($translator);

        return $this;
    }

    /**
     * @return array<string, GroupTranslation> the description in every language, by locale
     */
    public function getDescriptions(): array
    {
        $descriptions = [];
        /** @var GroupTranslation $translation */
        foreach ($this->translations as $translation) {
            if (GroupTranslation::DESCRIPTION === $translation->getField()) {
                $descriptions[$translation->getLocale()] = $translation;
            }
        }

        return $descriptions;
    }

    /**
     * The description in a language; else in English, else in the language it was written in.
     */
    public function getDescription(string $locale = 'en'): string
    {
        $descriptions = $this->getDescriptions();
        $description = $descriptions[$locale] ?? $descriptions['en'] ?? reset($descriptions);

        return false === $description ? '' : (string) $description->getContent();
    }

    public function setMembersOnly(bool $membersOnly): self
    {
        $this->membersOnly = $membersOnly;

        return $this;
    }

    public function isMembersOnly(): bool
    {
        return $this->membersOnly;
    }

    /**
     * Set approved.
     *
     * @param bool $approved
     *
     * @return Group
     */
    public function setApproved($approved)
    {
        $this->approved = $approved;

        return $this;
    }

    /**
     * Get approved.
     *
     * @return bool
     */
    public function getApproved()
    {
        return $this->approved;
    }

    public function getGroupMemberships()
    {
        return $this->groupMemberships->toArray();
    }

    public function getGroupMembership($member)
    {
        $expr = new Comparison('member', '=', $member);
        $criteria = new Criteria();
        $criteria->where($expr);

        return $this->groupMemberships->matching($criteria)->first();
    }

    public function addGroupMembership(GroupMembership $groupMembership)
    {
        if (!$this->groupMemberships->contains($groupMembership)) {
            $this->groupMemberships->add($groupMembership);
            $groupMembership->setGroup($this);
        }

        return $this;
    }

    public function removeGroupMembership(GroupMembership $groupMembership)
    {
        if ($this->groupMemberships->contains($groupMembership)) {
            $this->groupMemberships->removeElement($groupMembership);
            $groupMembership->setGroup(null);
        }

        return $this;
    }

    /**
     * @return Member[]
     */
    public function getMembers(): array
    {
        return array_map(
            static function ($groupMembership) {
                return $groupMembership->getMember();
            },
            $this->groupMemberships->toArray()
        );
    }

    public function isAdmin(Member $admin): bool
    {
        $admins = $this->getAdministrators();

        $isAdmin = \in_array($admin, $admins, true);

        return $isAdmin;
    }

    public function isMember(Member $member): bool
    {
        $members = $this->getCurrentMembers();

        return \in_array($member, $members, true);
    }

    public function getMembership(Member $member): ?GroupMembership
    {
        $criterion = new Criteria();
        $criterion->where(Criteria::expr()->eq('member', $member));

        return $this->groupMemberships->matching($criterion)->first();
    }

    /**
     * This function returns the actual admins of the group.
     *
     * @return Member[]
     */
    public function getAdministrators(): array
    {
        // Unfortunately we need to replicate old code here
        $privilegeRepository = $this->objectManager->getRepository(Privilege::class);
        $privilege = $privilegeRepository->findOneBy(['controller' => Privilege::GROUP_CONTROLLER]);

        $roleRepo = $this->objectManager->getRepository(Role::class);
        $role = $roleRepo->findBy(['name' => Role::GROUP_OWNER]);

        $privilegeScopesRepo = $this->objectManager->getRepository(PrivilegeScope::class);
        $privilegeScopes = $privilegeScopesRepo->findBy([
            'privilege' => $privilege,
            'role' => $role,
            'type' => $this->getId(),
        ]);

        $admins = [];
        foreach ($privilegeScopes as $privilegeScope) {
            $admin = $privilegeScope->getMember();
            if (str_contains(MemberStatusType::ACTIVE_WITH_MESSAGES, $admin->getStatus())) {
                $admins[] = $admin;
            }
        }

        return $admins;
    }

    public function getCurrentMembers()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', 'In'));

        return array_map(
            static function ($groupMembership) {
                return $groupMembership->getMember();
            },
            $this->groupMemberships->matching($criteria)->toArray()
        );
    }

    public function isPublic(): bool
    {
        return GroupType::PUBLIC === $this->type;
    }

    /**
     * Triggered on insert.
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
    }

    #[ORM\PostLoad]
    public function onPostLoad(PostLoadEventArgs $eventArgs): void
    {
        $this->objectManager = $eventArgs->getObjectManager();
    }
}
