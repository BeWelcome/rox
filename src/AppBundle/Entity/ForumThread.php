<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\JoinTable;
use Doctrine\ORM\Mapping\OneToMany;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping as ORM;

/**
 * ForumThread
 *
 * @ORM\Table(name="forums_threads", indexes={@ORM\Index(name="first_postid", columns={"first_postid"}), @ORM\Index(name="last_postid", columns={"last_postid"}), @ORM\Index(name="geonameid", columns={"geonameid"}), @ORM\Index(name="admincode", columns={"admincode"}), @ORM\Index(name="countrycode", columns={"countrycode"}), @ORM\Index(name="continent", columns={"continent"}), @ORM\Index(name="IdGroup", columns={"IdGroup"}), @ORM\Index(name="ThreadVisibility", columns={"ThreadVisibility"}), @ORM\Index(name="ThreadDeleted", columns={"ThreadDeleted"})})
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class ForumThread
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="expiredate", type="datetime", nullable=true)
     */
    private $expiredate = '0000-00-00 00:00:00';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdTitle", type="integer", nullable=false)
     */
    private $idtitle = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;

    /**
     * @var ForumPost
     *
     * @ORM\OneToOne(targetEntity="ForumPost")
     * @ORM\JoinColumn(name="first_postid", referencedColumnName="id")
     */
    private $firstPost;

    /**
     * @var integer
     *
     * @ORM\Column(name="first_postid", type="integer", nullable=true)
     */
    private $firstPostid;

    /**
     * @var ForumPost
     *
     * @ORM\OneToOne(targetEntity="ForumPost")
     * @ORM\JoinColumn(name="last_postid", referencedColumnName="id")
     */
    private $lastPost;

    /**
     * @var integer
     *
     * @ORM\Column(name="last_postid", type="integer", nullable=true)
     */
    private $lastPostid;


    /**
     * @var integer
     *
     * @ORM\Column(name="replies", type="smallint", nullable=false)
     */
    private $replies = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="views", type="integer", nullable=false)
     */
    private $views = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="geonameid", type="integer", nullable=true)
     */
    private $geonameid;

    /**
     * @var string
     *
     * @ORM\Column(name="admincode", type="string", length=2, nullable=true)
     */
    private $admincode;

    /**
     * @var string
     *
     * @ORM\Column(name="countrycode", type="string", length=2, nullable=true)
     */
    private $countrycode;

    /**
     * @var string
     *
     * @ORM\Column(name="continent", type="string", nullable=true)
     */
    private $continent;

    /**
     * @var integer
     *
     * @ORM\Column(name="tag1", type="integer", nullable=true)
     */
    private $tag1;

    /**
     * @var integer
     *
     * @ORM\Column(name="tag2", type="integer", nullable=true)
     */
    private $tag2;

    /**
     * @var integer
     *
     * @ORM\Column(name="tag3", type="integer", nullable=true)
     */
    private $tag3;

    /**
     * @var integer
     *
     * @ORM\Column(name="tag4", type="integer", nullable=true)
     */
    private $tag4;

    /**
     * @var integer
     *
     * @ORM\Column(name="tag5", type="integer", nullable=true)
     */
    private $tag5;

    /**
     * @var integer
     *
     * @ORM\Column(name="stickyvalue", type="integer", nullable=false)
     */
    private $stickyvalue = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdFirstLanguageUsed", type="integer", nullable=false)
     */
    private $idfirstlanguageused = '0';

    /**
     * @var Group
     *
     * @ORM\OneToOne(targetEntity="Group")
     * @ORM\JoinColumn(name="IdGroup", referencedColumnName="id")
     */
    private $group;

    /**
     * @var string
     *
     * @ORM\Column(name="ThreadVisibility", type="string", nullable=false)
     */
    private $threadvisibility = 'NoRestriction';

    /**
     * @var string
     *
     * @ORM\Column(name="WhoCanReply", type="string", nullable=false)
     */
    private $whocanreply = 'MembersOnly';

    /**
     * @var string
     *
     * @ORM\Column(name="ThreadDeleted", type="string", nullable=false)
     */
    private $threadDeleted = 'NotDeleted';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=false)
     */
    private $deletedAt = '0000-00-00 00:00:00';

    /**
     * @var integer
     *
     * @ORM\Column(name="threadid", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $threadid;

    /**
     * @ORM\OneToMany(targetEntity="ForumPost", mappedBy="threadId")
     */
    private $posts;


    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return ForumThread
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set expiredate
     *
     * @param \DateTime $expiredate
     *
     * @return ForumThread
     */
    public function setExpiredate($expiredate)
    {
        $this->expiredate = $expiredate;

        return $this;
    }

    /**
     * Get expiredate
     *
     * @return \DateTime
     */
    public function getExpiredate()
    {
        return $this->expiredate;
    }

    /**
     * Set idtitle
     *
     * @param integer $idtitle
     *
     * @return ForumThread
     */
    public function setIdtitle($idtitle)
    {
        $this->idtitle = $idtitle;

        return $this;
    }

    /**
     * Get idtitle
     *
     * @return integer
     */
    public function getIdtitle()
    {
        return $this->idtitle;
    }

    /**
     * Set title
     *
     * @param string $title
     *
     * @return ForumThread
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set firstPostid
     *
     * @param integer $firstPostid
     *
     * @return ForumThread
     */
    public function setFirstPostid($firstPostid)
    {
        $this->firstPostid = $firstPostid;

        return $this;
    }

    /**
     * Get firstPostid
     *
     * @return integer
     */
    public function getFirstPostid()
    {
        return $this->firstPostid;
    }

    /**
     * Set lastPostid
     *
     * @param integer $lastPostid
     *
     * @return ForumThread
     */
    public function setLastPostid($lastPostid)
    {
        $this->lastPostid = $lastPostid;

        return $this;
    }

    /**
     * Get lastPostid
     *
     * @return integer
     */
    public function getLastPostid()
    {
        return $this->lastPostid;
    }

    /**
     * Set replies
     *
     * @param integer $replies
     *
     * @return ForumThread
     */
    public function setReplies($replies)
    {
        $this->replies = $replies;

        return $this;
    }

    /**
     * Get replies
     *
     * @return integer
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * Set views
     *
     * @param integer $views
     *
     * @return ForumThread
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Get views
     *
     * @return integer
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Set geonameid
     *
     * @param integer $geonameid
     *
     * @return ForumThread
     */
    public function setGeonameid($geonameid)
    {
        $this->geonameid = $geonameid;

        return $this;
    }

    /**
     * Get geonameid
     *
     * @return integer
     */
    public function getGeonameid()
    {
        return $this->geonameid;
    }

    /**
     * Set admincode
     *
     * @param string $admincode
     *
     * @return ForumThread
     */
    public function setAdmincode($admincode)
    {
        $this->admincode = $admincode;

        return $this;
    }

    /**
     * Get admincode
     *
     * @return string
     */
    public function getAdmincode()
    {
        return $this->admincode;
    }

    /**
     * Set countrycode
     *
     * @param string $countrycode
     *
     * @return ForumThread
     */
    public function setCountrycode($countrycode)
    {
        $this->countrycode = $countrycode;

        return $this;
    }

    /**
     * Get countrycode
     *
     * @return string
     */
    public function getCountrycode()
    {
        return $this->countrycode;
    }

    /**
     * Set continent
     *
     * @param string $continent
     *
     * @return ForumThread
     */
    public function setContinent($continent)
    {
        $this->continent = $continent;

        return $this;
    }

    /**
     * Get continent
     *
     * @return string
     */
    public function getContinent()
    {
        return $this->continent;
    }

    /**
     * Set tag1
     *
     * @param integer $tag1
     *
     * @return ForumThread
     */
    public function setTag1($tag1)
    {
        $this->tag1 = $tag1;

        return $this;
    }

    /**
     * Get tag1
     *
     * @return integer
     */
    public function getTag1()
    {
        return $this->tag1;
    }

    /**
     * Set tag2
     *
     * @param integer $tag2
     *
     * @return ForumThread
     */
    public function setTag2($tag2)
    {
        $this->tag2 = $tag2;

        return $this;
    }

    /**
     * Get tag2
     *
     * @return integer
     */
    public function getTag2()
    {
        return $this->tag2;
    }

    /**
     * Set tag3
     *
     * @param integer $tag3
     *
     * @return ForumThread
     */
    public function setTag3($tag3)
    {
        $this->tag3 = $tag3;

        return $this;
    }

    /**
     * Get tag3
     *
     * @return integer
     */
    public function getTag3()
    {
        return $this->tag3;
    }

    /**
     * Set tag4
     *
     * @param integer $tag4
     *
     * @return ForumThread
     */
    public function setTag4($tag4)
    {
        $this->tag4 = $tag4;

        return $this;
    }

    /**
     * Get tag4
     *
     * @return integer
     */
    public function getTag4()
    {
        return $this->tag4;
    }

    /**
     * Set tag5
     *
     * @param integer $tag5
     *
     * @return ForumThread
     */
    public function setTag5($tag5)
    {
        $this->tag5 = $tag5;

        return $this;
    }

    /**
     * Get tag5
     *
     * @return integer
     */
    public function getTag5()
    {
        return $this->tag5;
    }

    /**
     * Set stickyvalue
     *
     * @param integer $stickyvalue
     *
     * @return ForumThread
     */
    public function setStickyvalue($stickyvalue)
    {
        $this->stickyvalue = $stickyvalue;

        return $this;
    }

    /**
     * Get stickyvalue
     *
     * @return integer
     */
    public function getStickyvalue()
    {
        return $this->stickyvalue;
    }

    /**
     * Set idfirstlanguageused
     *
     * @param integer $idfirstlanguageused
     *
     * @return ForumThread
     */
    public function setIdfirstlanguageused($idfirstlanguageused)
    {
        $this->idfirstlanguageused = $idfirstlanguageused;

        return $this;
    }

    /**
     * Get idfirstlanguageused
     *
     * @return integer
     */
    public function getIdfirstlanguageused()
    {
        return $this->idfirstlanguageused;
    }

    /**
     * Set idgroup
     *
     * @param integer $idgroup
     *
     * @return ForumThread
     */
    public function setIdgroup($idgroup)
    {
        $this->idgroup = $idgroup;

        return $this;
    }

    /**
     * Get idgroup
     *
     * @return integer
     */
    public function getIdgroup()
    {
        return $this->idgroup;
    }

    /**
     * Set threadvisibility
     *
     * @param string $threadvisibility
     *
     * @return ForumThread
     */
    public function setThreadvisibility($threadvisibility)
    {
        $this->threadvisibility = $threadvisibility;

        return $this;
    }

    /**
     * Get threadvisibility
     *
     * @return string
     */
    public function getThreadvisibility()
    {
        return $this->threadvisibility;
    }

    /**
     * Set whocanreply
     *
     * @param string $whocanreply
     *
     * @return ForumThread
     */
    public function setWhocanreply($whocanreply)
    {
        $this->whocanreply = $whocanreply;

        return $this;
    }

    /**
     * Get whocanreply
     *
     * @return string
     */
    public function getWhocanreply()
    {
        return $this->whocanreply;
    }

    /**
     * Set threaddeleted
     *
     * @param string $threadDeleted
     *
     * @return ForumThread
     */
    public function setThreadDeleted($threadDeleted)
    {
        $this->threadDeleted = $threadDeleted;

        return $this;
    }

    /**
     * Get threaddeleted
     *
     * @return string
     */
    public function getThreadDeleted()
    {
        return $this->threadDeleted;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return ForumThread
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return ForumThread
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return ForumThread
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Get threadid
     *
     * @return integer
     */
    public function getThreadid()
    {
        return $this->threadid;
    }

    /**
     * Set group
     *
     * @param \AppBundle\Entity\Group $group
     *
     * @return ForumThread
     */
    public function setGroup(\AppBundle\Entity\Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group
     *
     * @return \AppBundle\Entity\Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Add post
     *
     * @param \AppBundle\Entity\ForumPost $post
     *
     * @return ForumThread
     */
    public function addPost(\AppBundle\Entity\ForumPost $post)
    {
        $this->posts[] = $post;

        return $this;
    }

    /**
     * Remove post
     *
     * @param \AppBundle\Entity\ForumPost $post
     */
    public function removePost(\AppBundle\Entity\ForumPost $post)
    {
        $this->posts->removeElement($post);
    }

    /**
     * Get posts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPosts()
    {
        return $this->posts;
    }

    /**
     * Set firstPost
     *
     * @param \AppBundle\Entity\ForumPost $firstPost
     *
     * @return ForumThread
     */
    public function setFirstPost(\AppBundle\Entity\ForumPost $firstPost = null)
    {
        $this->firstPost = $firstPost;

        return $this;
    }

    /**
     * Get firstPost
     *
     * @return \AppBundle\Entity\ForumPost
     */
    public function getFirstPost()
    {
        return $this->firstPost;
    }

    /**
     * Set lastPost
     *
     * @param \AppBundle\Entity\ForumPost $lastPost
     *
     * @return ForumThread
     */
    public function setLastPost(\AppBundle\Entity\ForumPost $lastPost = null)
    {
        $this->lastPost = $lastPost;

        return $this;
    }

    /**
     * Get lastPost
     *
     * @return \AppBundle\Entity\ForumPost
     */
    public function getLastPost()
    {
        return $this->lastPost;
    }
}
