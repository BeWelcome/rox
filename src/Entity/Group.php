<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
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
class Group
{
    const NOT_APPROVED = 0;
    const APPROVED = 1;
    const DISMISSED = 2;
    const IN_DISCUSSION = 3;

    /**
     * @var string
     *
     * @ORM\Column(name="HasMembers", type="string", nullable=false)
     */
    private $hasMembers = 'HasMember';

    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="string", length=40, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private $type = 'Public';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var int
     *
     * @ORM\Column(name="NbChilds", type="integer", nullable=false)
     */
    private $nbchilds = '0';

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
    private $moreInfo;

    /**
     * @var string
     *
     * @ORM\Column(name="DisplayedOnProfile", type="string", nullable=false)
     */
    private $displayedOnProfile = 'Yes';

    /** @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="MembersTrad", fetch="LAZY")
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

    public function __construct()
    {
        $this->descriptions = new ArrayCollection();
        $this->groupMemberships = new ArrayCollection();
    }

    /**
     * Set hasmembers.
     *
     * @param string $hasMembers
     *
     * @return Group
     */
    public function setHasMembers($hasMembers)
    {
        $this->hasMembers = $hasMembers;

        return $this;
    }

    /**
     * Get hasmembers.
     *
     * @return string
     */
    public function getHasMembers()
    {
        return $this->hasMembers;
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
     * Set nbchilds.
     *
     * @param int $nbchilds
     *
     * @return Group
     */
    public function setNbchilds($nbchilds)
    {
        $this->nbchilds = $nbchilds;

        return $this;
    }

    /**
     * Get nbchilds.
     *
     * @return int
     */
    public function getNbchilds()
    {
        return $this->nbchilds;
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
     * Set moreinfo.
     *
     * @param string $moreInfo
     *
     * @return Group
     */
    public function setMoreInfo($moreInfo)
    {
        $this->moreInfo = $moreInfo;

        return $this;
    }

    /**
     * Get moreinfo.
     *
     * @return string
     */
    public function getMoreInfo()
    {
        return $this->moreInfo;
    }

    /**
     * Set displayedonprofile.
     *
     * @param string $displayedonprofile
     *
     * @return Group
     */
    public function setDisplayedonprofile($displayedonprofile)
    {
        $this->displayedOnProfile = $displayedonprofile;

        return $this;
    }

    /**
     * Get displayedonprofile.
     *
     * @return string
     */
    public function getDisplayedonprofile()
    {
        return $this->displayedOnProfile;
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
     * Set visiblecomments.
     *
     * @param string $visiblecomments
     *
     * @return Group
     */
    public function setVisiblecomments($visiblecomments)
    {
        $this->visiblecomments = $visiblecomments;

        return $this;
    }

    /**
     * Get visiblecomments.
     *
     * @return string
     */
    public function getVisiblecomments()
    {
        return $this->visiblecomments;
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
     * @param MembersTrad $description
     *
     * @return Group
     */
    public function addDescription(MembersTrad $description)
    {
        $this->descriptions[] = $description;

        return $this;
    }

    /**
     * Remove description.
     *
     * @param MembersTrad $description
     */
    public function removeDescription(MembersTrad $description)
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
        $this->created = new \DateTime('now');
    }

    public function getGroupMemberships()
    {
        return $this->groupMemberships->toArray();
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
}
