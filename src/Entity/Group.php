<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\GroupType;
use App\Doctrine\MemberStatusType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\ObjectManager;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'groups')]
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

    #[ORM\Column(name: 'Name', type: 'string', length: 40, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'Type', type: 'group_type', nullable: false)]
    private string $type = GroupType::PUBLIC;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private \DateTime $created;

    #[ORM\Column(name: 'Picture', type: 'text', length: 65535, nullable: false)]
    private string $picture;

    #[ORM\Column(name: 'MoreInfo', type: 'text', length: 65535, nullable: false)]
    private string $moreInfo = '';

    #[ORM\Column(name: 'IdDescription', type: 'integer', nullable: false)]
    private int $idDescription = 0;

    #[ORM\JoinTable(name: 'groups_trads')]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id')]
    #[ORM\InverseJoinColumn(name: 'trad_id', referencedColumnName: 'id', unique: true)]
    #[ORM\ManyToMany(targetEntity: MemberTranslation::class, fetch: 'LAZY')]
    private Collection $descriptions;

    #[ORM\OneToMany(targetEntity: GroupMembership::class, mappedBy: 'group', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $groupMemberships;

    #[ORM\Column(name: 'VisiblePosts', type: 'string', nullable: false)]
    private string $visibleposts = 'yes';

    #[ORM\Column(name: 'approved', type: 'smallint', nullable: true)]
    private int $approved = self::NOT_APPROVED;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    private ObjectManager $objectManager;

    public function __construct()
    {
        $this->descriptions = new ArrayCollection();
        $this->groupMemberships = new ArrayCollection();
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
     * @param \DateTime $created
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
     * @return \DateTime
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
     * Add description.
     *
     * @return Group
     */
    public function addDescription(MemberTranslation $description)
    {
        if (!$this->descriptions->contains($description)) {
            $this->descriptions[] = $description;
            $this->idDescription = $description->getId();
        }

        return $this;
    }

    /**
     * Remove description.
     */
    public function removeDescription(MemberTranslation $description)
    {
        $this->descriptions->removeElement($description);
    }

    /**
     * @return array
     */
    public function getDescriptions()
    {
        $descriptions = [];
        // return array based on locale
        foreach ($this->descriptions as $description) {
            $descriptions[$description->getLanguage()->getShortCode()] = $description;
        }

        return $descriptions;
    }

    /**
     * Set visibleposts.
     *
     * @param string $visibleposts
     *
     * @return Group
     */
    public function setVisibleposts($visibleposts)
    {
        $this->visibleposts = $visibleposts;

        return $this;
    }

    /**
     * Get visibleposts.
     *
     * @return string
     */
    public function getVisibleposts()
    {
        return $this->visibleposts;
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
            function ($groupMembership) {
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
            function ($groupMembership) {
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
        $this->created = new \DateTime('now');
    }

    #[ORM\PostLoad]
    public function onPostLoad(PostLoadEventArgs $eventArgs): void
    {
        $this->objectManager = $eventArgs->getObjectManager();
    }

    public function getMoreInfo(): ?string
    {
        return $this->moreInfo;
    }

    public function setMoreInfo(string $moreInfo): self
    {
        $this->moreInfo = $moreInfo;

        return $this;
    }

    public function getIdDescription(): ?int
    {
        return $this->idDescription;
    }

    public function setIdDescription(int $idDescription): self
    {
        $this->idDescription = $idDescription;

        return $this;
    }
}
