<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * ForumsPost
 *
 * @ORM\Table(name="forums_posts", indexes={@ORM\Index(name="authorid", columns={"authorid"}), @ORM\Index(name="last_editorid", columns={"last_editorid"}), @ORM\Index(name="threadid", columns={"threadid"}), @ORM\Index(name="IdWriter", columns={"IdWriter"}), @ORM\Index(name="id", columns={"id"}), @ORM\Index(name="IdLocalEvent", columns={"IdLocalEvent"}), @ORM\Index(name="IdPoll", columns={"IdPoll"}), @ORM\Index(name="IdLocalVolMessage", columns={"IdLocalVolMessage"}), @ORM\Index(name="PostVisibility", columns={"PostVisibility"}), @ORM\Index(name="PostDeleted", columns={"PostDeleted"}), @ORM\Index(name="create_time", columns={"create_time"})})
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class ForumPost
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="threadid", type="integer", nullable=true)
     */
    private $threadid;

    /**
     * @var ForumThread
     *
     * @ORM\OneToOne(targetEntity="ForumThread")
     * @ORM\JoinColumn(name="threadid", referencedColumnName="id")
     */
    private $thread;

    /**
     * @var string
     *
     * @ORM\Column(name="PostVisibility", type="string", nullable=false)
     */
    private $postvisibility = 'NoRestriction';

    /**
     * @var Member
     *
     * @ORM\OneToOne(targetEntity="Member")
     * @ORM\JoinColumn(name="authorid", referencedColumnName="id")
     */
    private $author;

    /**
     * @var integer
     *
     * @ORM\Column(name="authorid", type="integer", nullable=false)
     */
    private $authorid;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdWriter", type="integer", nullable=false)
     */
    private $idwriter = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="create_time", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", length=65535, nullable=false)
     */
    private $message;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdContent", type="integer", nullable=false)
     */
    private $idcontent = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="OwnerCanStillEdit", type="string", nullable=false)
     */
    private $ownercanstilledit = 'Yes';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_edittime", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var integer
     *
     * @ORM\Column(name="last_editorid", type="integer", nullable=true)
     */
    private $lastEditorid;

    /**
     * @var boolean
     *
     * @ORM\Column(name="edit_count", type="boolean", nullable=false)
     */
    private $editCount = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdFirstLanguageUsed", type="integer", nullable=false)
     */
    private $idfirstlanguageused = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="HasVotes", type="string", nullable=false)
     */
    private $hasvotes = 'No';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdLocalVolMessage", type="integer", nullable=false)
     */
    private $idlocalvolmessage = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdLocalEvent", type="integer", nullable=false)
     */
    private $idlocalevent = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdPoll", type="integer", nullable=false)
     */
    private $idpoll = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="PostDeleted", type="string", nullable=false)
     */
    private $postdeleted = 'NotDeleted';

    /**
     * Set id
     *
     * @param integer $id
     *
     * @return ForumPost
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
     * Set threadid
     *
     * @param integer $threadid
     *
     * @return ForumPost
     */
    public function setThreadid($threadid)
    {
        $this->threadid = $threadid;

        return $this;
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
     * Set postvisibility
     *
     * @param string $postvisibility
     *
     * @return ForumPost
     */
    public function setPostvisibility($postvisibility)
    {
        $this->postvisibility = $postvisibility;

        return $this;
    }

    /**
     * Get postvisibility
     *
     * @return string
     */
    public function getPostvisibility()
    {
        return $this->postvisibility;
    }

    /**
     * Set authorid
     *
     * @param integer $authorid
     *
     * @return ForumPost
     */
    public function setAuthorid($authorid)
    {
        $this->authorid = $authorid;

        return $this;
    }

    /**
     * Get authorid
     *
     * @return integer
     */
    public function getAuthorid()
    {
        return $this->authorid;
    }

    /**
     * Set idwriter
     *
     * @param integer $idwriter
     *
     * @return ForumPost
     */
    public function setIdwriter($idwriter)
    {
        $this->idwriter = $idwriter;

        return $this;
    }

    /**
     * Get idwriter
     *
     * @return integer
     */
    public function getIdwriter()
    {
        return $this->idwriter;
    }

    /**
     * Set createTime
     *
     * @param \DateTime $createTime
     *
     * @return ForumPost
     */
    public function setCreateTime($createTime)
    {
        $this->createTime = $createTime;

        return $this;
    }

    /**
     * Get createTime
     *
     * @return \DateTime
     */
    public function getCreateTime()
    {
        return $this->createTime;
    }

    /**
     * Set message
     *
     * @param string $message
     *
     * @return ForumPost
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set idcontent
     *
     * @param integer $idcontent
     *
     * @return ForumPost
     */
    public function setIdcontent($idcontent)
    {
        $this->idcontent = $idcontent;

        return $this;
    }

    /**
     * Get idcontent
     *
     * @return integer
     */
    public function getIdcontent()
    {
        return $this->idcontent;
    }

    /**
     * Set ownercanstilledit
     *
     * @param string $ownercanstilledit
     *
     * @return ForumPost
     */
    public function setOwnercanstilledit($ownercanstilledit)
    {
        $this->ownercanstilledit = $ownercanstilledit;

        return $this;
    }

    /**
     * Get ownercanstilledit
     *
     * @return string
     */
    public function getOwnercanstilledit()
    {
        return $this->ownercanstilledit;
    }

    /**
     * Set lastEdittime
     *
     * @param \DateTime $lastEdittime
     *
     * @return ForumPost
     */
    public function setLastEdittime($lastEdittime)
    {
        $this->lastEdittime = $lastEdittime;

        return $this;
    }

    /**
     * Get lastEdittime
     *
     * @return \DateTime
     */
    public function getLastEdittime()
    {
        return $this->lastEdittime;
    }

    /**
     * Set lastEditorid
     *
     * @param integer $lastEditorid
     *
     * @return ForumPost
     */
    public function setLastEditorid($lastEditorid)
    {
        $this->lastEditorid = $lastEditorid;

        return $this;
    }

    /**
     * Get lastEditorid
     *
     * @return integer
     */
    public function getLastEditorid()
    {
        return $this->lastEditorid;
    }

    /**
     * Set editCount
     *
     * @param boolean $editCount
     *
     * @return ForumPost
     */
    public function setEditCount($editCount)
    {
        $this->editCount = $editCount;

        return $this;
    }

    /**
     * Get editCount
     *
     * @return boolean
     */
    public function getEditCount()
    {
        return $this->editCount;
    }

    /**
     * Set idfirstlanguageused
     *
     * @param integer $idfirstlanguageused
     *
     * @return ForumPost
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
     * Set hasvotes
     *
     * @param string $hasvotes
     *
     * @return ForumPost
     */
    public function setHasvotes($hasvotes)
    {
        $this->hasvotes = $hasvotes;

        return $this;
    }

    /**
     * Get hasvotes
     *
     * @return string
     */
    public function getHasvotes()
    {
        return $this->hasvotes;
    }

    /**
     * Set idlocalvolmessage
     *
     * @param integer $idlocalvolmessage
     *
     * @return ForumPost
     */
    public function setIdlocalvolmessage($idlocalvolmessage)
    {
        $this->idlocalvolmessage = $idlocalvolmessage;

        return $this;
    }

    /**
     * Get idlocalvolmessage
     *
     * @return integer
     */
    public function getIdlocalvolmessage()
    {
        return $this->idlocalvolmessage;
    }

    /**
     * Set idlocalevent
     *
     * @param integer $idlocalevent
     *
     * @return ForumPost
     */
    public function setIdlocalevent($idlocalevent)
    {
        $this->idlocalevent = $idlocalevent;

        return $this;
    }

    /**
     * Get idlocalevent
     *
     * @return integer
     */
    public function getIdlocalevent()
    {
        return $this->idlocalevent;
    }

    /**
     * Set idpoll
     *
     * @param integer $idpoll
     *
     * @return ForumPost
     */
    public function setIdpoll($idpoll)
    {
        $this->idpoll = $idpoll;

        return $this;
    }

    /**
     * Get idpoll
     *
     * @return integer
     */
    public function getIdpoll()
    {
        return $this->idpoll;
    }

    /**
     * Set postdeleted
     *
     * @param string $postdeleted
     *
     * @return ForumPost
     */
    public function setPostdeleted($postdeleted)
    {
        $this->postdeleted = $postdeleted;

        return $this;
    }

    /**
     * Get postdeleted
     *
     * @return string
     */
    public function getPostdeleted()
    {
        return $this->postdeleted;
    }

    /**
     * Get postid
     *
     * @return integer
     */
    public function getPostid()
    {
        return $this->postid;
    }

    /**
     * Set thread
     *
     * @param ForumThread $thread
     *
     * @return ForumPost
     */
    public function setThread(ForumThread $thread = null)
    {
        $this->thread = $thread;

        return $this;
    }

    /**
     * Get thread
     *
     * @return ForumThread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * Set author
     *
     * @param Member $author
     *
     * @return ForumPost
     */
    public function setAuthor(Member $author = null)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author
     *
     * @return \AppBundle\Entity\Member
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return ForumPost
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return Carbon
     */
    public function getCreated()
    {
        return Carbon::instance($this->created);
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return ForumPost
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return Carbon
     */
    public function getUpdated()
    {
        if ($this->updated){
            return Carbon::instance($this->updated);
        }
        return Carbon::instance($this->created);
    }
}
