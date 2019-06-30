<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\GroupTypeType;
use App\Doctrine\MemberStatusType;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectManagerAware;
use Doctrine\ORM\Mapping as ORM;

/**
 * Group.
 *
 * @ORM\Table(name="groups")
 * @ORM\Entity(repositoryClass="App\Repository\GroupRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Group implements ObjectManagerAware
{
    const NOT_APPROVED = 0;
    const APPROVED = 1;
    const DISMISSED = 2;
    const IN_DISCUSSION = 3;
    const APPROVED_CLOSED = 4;
    const DISMISSED_CLOSED = 5;
    const IN_DISCUSSION_CLOSED = 6;

    const OPEN = [
        self::NOT_APPROVED, self::IN_DISCUSSION
    ];

    const HANDLED = [
        self::APPROVED, self::DISMISSED
    ];

    const CLOSED = [
        self::APPROVED_CLOSED, self::DISMISSED_CLOSED, self::IN_DISCUSSION_CLOSED
    ];

    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="string", length=40, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="group_type", nullable=false)
     */
    private $type = 'Public';

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="Picture", type="text", length=65535, nullable=false)
     */
    private $picture;

    /**
     * @var string
     *
     * @ORM\Column(name="MoreInfo", type="text", length=65535, nullable=false)
     */
    private $moreInfo = '';

    /**
     * @var int
     *
     * @ORM\Column(name="IdDescription", type="integer", nullable=false)
     */
    private $idDescription = 0;

    /** @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="MemberTranslation", fetch="LAZY")
     * @ORM\JoinTable(name="groups_trads",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="trad_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $descriptions;

    /** @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="GroupMembership", mappedBy="group", cascade={"persist", "remove"}, orphanRemoval=TRUE)
     */
    private $groupMemberships;

    /**
     * @var string
     *
     * @ORM\Column(name="VisiblePosts", type="string", nullable=false)
     */
    private $visibleposts = 'yes';

    /**
     * @var bool
     *
     * @ORM\Column(name="Approved", type="smallint", nullable = true)
     */
    private $approved = self::NOT_APPROVED;

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

    public function __construct()
    {
        $this->descriptions = new ArrayCollection();
        $this->groupMemberships = new ArrayCollection();
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
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
     * Add description.
     *
     * @param MemberTranslation $description
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
     *
     * @param MemberTranslation $description
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

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
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

    public function getMembers()
    {
        return array_map(
            function ($groupMembership) {
                return $groupMembership->getMember();
            },
            $this->groupMemberships->toArray()
        );
    }

    public function isAdmin(Member $admin)
    {
        $admins = $this->getAdmins();

        return \in_array($admin, $admins, true);
    }

    public function isMember(Member $member)
    {
        $members = $this->getCurrentMembers();

        return \in_array($member, $members, true);
    }

    /**
     * @return array Member
     */
    public function getAdmins()
    {
        // Unfortunately we need to replicate old code here
        $roleRepo = $this->objectManager->getRepository(Role::class);

        $role = $roleRepo->findBy(['name' => 'GroupOwner']);
        $privilegeScopesRepo = $this->objectManager->getRepository(PrivilegeScope::class);
        $privilegeScopes = $privilegeScopesRepo->findBy(['role' => $role, 'type' => $this->getId()]);

        if (!$privilegeScopes) {
            return [];
        }

        $admins = [];
        foreach ($privilegeScopes as $privilegeScope) {
            $admin = $privilegeScope->getMember();
            if (false !== strpos(MemberStatusType::ACTIVE_WITH_MESSAGES, $admin->getStatus())) {
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

    public function isPublic()
    {
        return GroupTypeType::PUBLIC === $this->type;
    }

    /**
     * Injects responsible ObjectManager and the ClassMetadata into this persistent object.
     *
     * @param ObjectManager $objectManager
     * @param ClassMetadata $classMetadata
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function injectObjectManager(ObjectManager $objectManager, ClassMetadata $classMetadata)
    {
        $this->objectManager = $objectManager;
    }
}
