<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Utilities\LifecycleCallbacksTrait;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * ForumThread.
 *
 * @ORM\Table(name="forums_threads", indexes={
 *     @ORM\Index(name="first_postid", columns={"first_postid"}),
 *     @ORM\Index(name="last_postid", columns={"last_postid"}),
 *     @ORM\Index(name="IdGroup", columns={"IdGroup"}),
 *     @ORM\Index(name="ThreadVisibility", columns={"ThreadVisibility"}),
 *     @ORM\Index(name="ThreadDeleted", columns={"ThreadDeleted"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class ForumThread
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue
     */
    private $id;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="expiredate", type="datetime", nullable=true)
     */
    private $expiredate = null;

    /**
     * @var int
     *
     * @ORM\Column(name="IdTitle", type="integer", nullable=false)
     */
    private $idTitle = '0';

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
     * @ORM\JoinColumn(name="first_postid", referencedColumnName="id", nullable=false)
     */
    private $firstPost;

    /**
     * @var ForumPost
     *
     * @ORM\OneToOne(targetEntity="ForumPost")
     * @ORM\JoinColumn(name="last_postid", referencedColumnName="id", nullable=false)
     */
    private $lastPost;

    /**
     * @var int
     *
     * @ORM\Column(name="replies", type="integer", nullable=false)
     */
    private $replies = 0;

    /**
     * @var int
     *
     * @ORM\Column(name="views", type="integer", nullable=false)
     */
    private $views = 0;

    /**
     * Not used (historic)
     *
     * @var int
     *
     * @ORM\Column(name="geonameid", type="integer", nullable=true)
     */
    private $geonameId = null;

    /**
     * Not used (historic)
     *
     * @var string
     *
     * @ORM\Column(name="admincode", type="string", length=2, nullable=true)
     */
    private $adminCode = null;

    /**
     * Not used (historic)
     *
     * @var string
     *
     * @ORM\Column(name="countrycode", type="string", length=2, nullable=true)
     */
    private $countrycode = null;

    /**
     * Not used (historic)
     *
     * @var string
     *
     * @ORM\Column(name="continent", type="string", nullable=true)
     */
    private $continent = null;

    /**
     * @var int
     *
     * @ORM\Column(name="stickyvalue", type="integer", nullable=false)
     */
    private $stickyValue = 0;

    /**
     * @var Language
     *
     * Default English
     *
     * @ORM\ManyToOne(targetEntity="Language")
     * @ORM\JoinColumn(name="IdFirstLanguageUsed", referencedColumnName="id", nullable=false)
     */
    private $language = null;

    /**
     * @var Group
     *
     * @ORM\ManyToOne(targetEntity="Group")
     * @ORM\JoinColumn(name="IdGroup", referencedColumnName="id", nullable=true)
     */
    private $group;

    /**
     * @var string
     *
     * @ORM\Column(name="ThreadVisibility", type="string", nullable=false)
     */
    private $visibility = 'NoRestriction';

    /**
     * @var string
     *
     * @ORM\Column(name="WhoCanReply", type="string", nullable=false)
     */
    private $whoCanReply = 'MembersOnly';

    /**
     * @var string
     *
     * @ORM\Column(name="ThreadDeleted", type="string", nullable=false)
     */
    private $deleted = 'NotDeleted';

    /**
     * @ORM\OneToMany(targetEntity="ForumPost", mappedBy="thread")
     */
    private $posts;

    public function __construct()
    {
        $this->posts = new ArrayCollection();
    }

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return ForumThread
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
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
     * Set expiredate.
     *
     * @param DateTime $expiredate
     *
     * @return ForumThread
     */
    public function setExpiredate($expiredate)
    {
        $this->expiredate = $expiredate;

        return $this;
    }

    /**
     * Get expiredate.
     *
     * @return DateTime
     */
    public function getExpiredate()
    {
        return $this->expiredate;
    }

    /**
     * Set idtitle.
     *
     * @param int $idTitle
     *
     * @return ForumThread
     */
    public function setIdTitle($idTitle)
    {
        $this->idTitle = $idTitle;

        return $this;
    }

    /**
     * Get idtitle.
     *
     * @return int
     */
    public function getIdTitle()
    {
        return $this->idTitle;
    }

    /**
     * Set title.
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
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set first post.
     *
     * @param ForumPost $firstPost
     *
     * @return ForumThread
     */
    public function setFirstPost($firstPost)
    {
        $this->firstPost = $firstPost;

        return $this;
    }

    /**
     * Get first post.
     *
     * @return ForumPost
     */
    public function getFirstPost()
    {
        return $this->firstPost;
    }

    /**
     * Set last post.
     *
     * @param ForumPost $lastPost
     *
     * @return ForumThread
     */
    public function setLastPost($lastPost)
    {
        $this->lastPost = $lastPost;

        return $this;
    }

    /**
     * Get last post.
     *
     * @return ForumPost
     */
    public function getLastPost()
    {
        return $this->lastPost;
    }

    /**
     * Set replies.
     *
     * @param int $replies
     *
     * @return ForumThread
     */
    public function setReplies($replies)
    {
        $this->replies = $replies;

        return $this;
    }

    /**
     * Get replies.
     *
     * @return int
     */
    public function getReplies()
    {
        return $this->replies;
    }

    /**
     * Set views.
     *
     * @param int $views
     *
     * @return ForumThread
     */
    public function setViews($views)
    {
        $this->views = $views;

        return $this;
    }

    /**
     * Get views.
     *
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Set geonameid.
     *
     * @param int $geonameId
     *
     * @return ForumThread
     */
    public function setGeonameId($geonameId)
    {
        $this->geonameId = $geonameId;

        return $this;
    }

    /**
     * Get geonameid.
     *
     * @return int
     */
    public function getGeonameId()
    {
        return $this->geonameId;
    }

    /**
     * Set admincode.
     *
     * @param string $adminCode
     *
     * @return ForumThread
     */
    public function setAdminCode($adminCode)
    {
        $this->adminCode = $adminCode;

        return $this;
    }

    /**
     * Get admincode.
     *
     * @return string
     */
    public function getAdminCode()
    {
        return $this->adminCode;
    }

    /**
     * Set countrycode.
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
     * Get countrycode.
     *
     * @return string
     */
    public function getCountrycode()
    {
        return $this->countrycode;
    }

    /**
     * Set continent.
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
     * Get continent.
     *
     * @return string
     */
    public function getContinent()
    {
        return $this->continent;
    }

    /**
     * Set tag1.
     *
     * @param int $tag1
     *
     * @return ForumThread
     */
    public function setTag1($tag1)
    {
        $this->tag1 = $tag1;

        return $this;
    }

    /**
     * Get tag1.
     *
     * @return int
     */
    public function getTag1()
    {
        return $this->tag1;
    }

    /**
     * Set tag2.
     *
     * @param int $tag2
     *
     * @return ForumThread
     */
    public function setTag2($tag2)
    {
        $this->tag2 = $tag2;

        return $this;
    }

    /**
     * Get tag2.
     *
     * @return int
     */
    public function getTag2()
    {
        return $this->tag2;
    }

    /**
     * Set tag3.
     *
     * @param int $tag3
     *
     * @return ForumThread
     */
    public function setTag3($tag3)
    {
        $this->tag3 = $tag3;

        return $this;
    }

    /**
     * Get tag3.
     *
     * @return int
     */
    public function getTag3()
    {
        return $this->tag3;
    }

    /**
     * Set tag4.
     *
     * @param int $tag4
     *
     * @return ForumThread
     */
    public function setTag4($tag4)
    {
        $this->tag4 = $tag4;

        return $this;
    }

    /**
     * Get tag4.
     *
     * @return int
     */
    public function getTag4()
    {
        return $this->tag4;
    }

    /**
     * Set tag5.
     *
     * @param int $tag5
     *
     * @return ForumThread
     */
    public function setTag5($tag5)
    {
        $this->tag5 = $tag5;

        return $this;
    }

    /**
     * Get tag5.
     *
     * @return int
     */
    public function getTag5()
    {
        return $this->tag5;
    }

    /**
     * Set stickyvalue.
     *
     * @param int $stickyValue
     *
     * @return ForumThread
     */
    public function setStickyValue($stickyValue)
    {
        $this->stickyValue = $stickyValue;

        return $this;
    }

    /**
     * Get stickyvalue.
     *
     * @return int
     */
    public function getStickyValue()
    {
        return $this->stickyValue;
    }

    /**
     * Set idfirstlanguageused.
     *
     * @param int $language
     *
     * @return ForumThread
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get idfirstlanguageused.
     *
     * @return int
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set idgroup.
     *
     * @param int $idgroup
     *
     * @return ForumThread
     */
    public function setIdgroup($idgroup)
    {
        $this->idgroup = $idgroup;

        return $this;
    }

    /**
     * Get idgroup.
     *
     * @return int
     */
    public function getIdgroup()
    {
        return $this->idgroup;
    }

    /**
     * Set threadvisibility.
     *
     * @param string $visibility
     *
     * @return ForumThread
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }

    /**
     * Get threadvisibility.
     *
     * @return string
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * Set whocanreply.
     *
     * @param string $whoCanReply
     *
     * @return ForumThread
     */
    public function setWhoCanReply($whoCanReply)
    {
        $this->whoCanReply = $whoCanReply;

        return $this;
    }

    /**
     * Get whocanreply.
     *
     * @return string
     */
    public function getWhoCanReply()
    {
        return $this->whoCanReply;
    }

    /**
     * Set threaddeleted.
     *
     * @param string $deleted
     *
     * @return ForumThread
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get threaddeleted.
     *
     * @return string
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set group.
     *
     * @param Group $group
     *
     * @return ForumThread
     */
    public function setGroup(Group $group = null)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * Get group.
     *
     * @return Group
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Add post.
     *
     * @param ForumPost $post
     *
     * @return ForumThread
     */
    public function addPost(ForumPost $post)
    {
        $this->posts[] = $post;

        return $this;
    }

    /**
     * Remove post.
     *
     * @param ForumPost $post
     */
    public function removePost(ForumPost $post)
    {
        $this->posts->removeElement($post);
    }

    /**
     * Get posts.
     *
     * @return Collection
     */
    public function getPosts()
    {
        return $this->posts;
    }
}
