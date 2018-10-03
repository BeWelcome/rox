<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Group.
 *
 * @ORM\Table(name="groups")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\GroupRepository")
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

    /**
     * @var string
     *
     * @ORM\Column(name="HasMembers", type="string", nullable=false)
     */
    private $hasmembers = 'HasMember';

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
    private $moreinfo;

    /**
     * @var string
     *
     * @ORM\Column(name="DisplayedOnProfile", type="string", nullable=false)
     */
    private $displayedonprofile = 'Yes';

    /** @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="MembersTrad", fetch="EAGER")
     * @ORM\JoinTable(name="groups_trads",
     *      joinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="trad_id", referencedColumnName="id", unique=true)}
     *      )
     */
    private $descriptions;

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
     * @var arrayCollection
     *
     * @ORM\ManyToMany(targetEntity="Member", mappedBy="groups", fetch="EAGER")
     * @ORM\JoinTable(name="membersgroups",
     *      joinColumns={@ORM\JoinColumn(name="IdMember", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="IdGroup", referencedColumnName="id")}
     *      )
     */
    private $members;

    public function __construct()
    {
        $this->members = new ArrayCollection();
        $this->descriptions = new ArrayCollection();
    }

    /**
     * Set hasmembers.
     *
     * @param string $hasmembers
     *
     * @return Group
     */
    public function setHasmembers($hasmembers)
    {
        $this->hasmembers = $hasmembers;

        return $this;
    }

    /**
     * Get hasmembers.
     *
     * @return string
     */
    public function getHasmembers()
    {
        return $this->hasmembers;
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
     * @param string $moreinfo
     *
     * @return Group
     */
    public function setMoreinfo($moreinfo)
    {
        $this->moreinfo = $moreinfo;

        return $this;
    }

    /**
     * Get moreinfo.
     *
     * @return string
     */
    public function getMoreinfo()
    {
        return $this->moreinfo;
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
        $this->displayedonprofile = $displayedonprofile;

        return $this;
    }

    /**
     * Get displayedonprofile.
     *
     * @return string
     */
    public function getDisplayedonprofile()
    {
        return $this->displayedonprofile;
    }

    /**
     * Set description.
     *
     * @param MembersTrad $description
     *
     * @return Group
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get MembersTrad.
     *
     * @return MembersTrad
     */
    public function getDescription()
    {
        return $this->description;
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
     * Add member.
     *
     * @param Member $member
     *
     * @return Group
     */
    public function addMember(Member $member)
    {
        $this->members[] = $member;

        return $this;
    }

    /**
     * Remove member.
     *
     * @param Member $member
     */
    public function removeMember(Member $member)
    {
        $this->members->removeElement($member);
    }

    /**
     * Get members.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMembers()
    {
        return $this->members;
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
}
