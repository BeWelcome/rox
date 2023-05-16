<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\ForumDeleteStatusType;
use App\Doctrine\ForumVisibilityType;
use App\Doctrine\PostCanStillEditType;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectManagerAware;

/**
 * ForumsPost.
 *
 * @ORM\Table(name="forums_posts", indexes={
 *     @ORM\Index(name="last_editorid", columns={"last_editorid"}),
 *     @ORM\Index(name="threadid", columns={"threadid"}),
 *     @ORM\Index(name="IdWriter", columns={"IdWriter"}),
 *     @ORM\Index(name="PostVisibility", columns={"PostVisibility"}),
 *     @ORM\Index(name="PostDeleted", columns={"PostDeleted"}),
 *     @ORM\Index(name="create_time", columns={"create_time"})})
 * @ORM\Entity(repositoryClass="App\Repository\ForumPostRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class ForumPost implements ObjectManagerAware
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
     * @var ForumThread
     *
     * @ORM\ManyToOne(targetEntity="ForumThread", inversedBy="posts")
     * @ORM\JoinColumn(name="threadid", referencedColumnName="id")
     */
    private $thread;

    /**
     * @var string
     *
     * @ORM\Column(name="PostVisibility", type="forum_visibility", nullable=false)
     */
    private $postvisibility = ForumVisibilityType::MEMBERS_ONLY;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumn(name="IdWriter", referencedColumnName="id")
     */
    private $author;

    /**
     * @var DateTime
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
     * @var int
     *
     * @ORM\Column(name="IdContent", type="integer", nullable=false)
     */
    private $idcontent = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="OwnerCanStillEdit", type="can_still_edit", nullable=false)
     */
    private $ownerCanStillEdit = PostCanStillEditType::CAN_STILL_EDIT;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="last_edittime", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var int
     *
     * @ORM\Column(name="last_editorid", type="integer", nullable=true)
     */
    private $lastEditorid;

    /**
     * @var bool
     *
     * @ORM\Column(name="edit_count", type="boolean", nullable=false)
     */
    private $editCount = '0';

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
     * @var string
     *
     * @ORM\Column(name="PostDeleted", type="forum_delete_status", nullable=false)
     */
    private $deleted = ForumDeleteStatusType::NOT_DELETED;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Set id.
     *
     * @param int $id
     *
     * @return ForumPost
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
     * Set postvisibility.
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
     * Get postvisibility.
     *
     * @return string
     */
    public function getPostvisibility()
    {
        return $this->postvisibility;
    }

    /**
     * Set authorid.
     *
     * @param int $authorid
     *
     * @return ForumPost
     */
    public function setAuthorid($authorid)
    {
        $this->authorid = $authorid;

        return $this;
    }

    /**
     * Get authorid.
     *
     * @return int
     */
    public function getAuthorid()
    {
        return $this->authorid;
    }

    /**
     * Set idwriter.
     *
     * @param int $idwriter
     *
     * @return ForumPost
     */
    public function setIdwriter($idwriter)
    {
        $this->idwriter = $idwriter;

        return $this;
    }

    /**
     * Get idwriter.
     *
     * @return int
     */
    public function getIdwriter()
    {
        return $this->idwriter;
    }

    /**
     * Set message.
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
     * Get message.
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set idcontent.
     *
     * @param int $idcontent
     *
     * @return ForumPost
     */
    public function setIdcontent($idcontent)
    {
        $this->idcontent = $idcontent;

        return $this;
    }

    /**
     * Get idcontent.
     *
     * @return int
     */
    public function getIdcontent()
    {
        return $this->idcontent;
    }

    /**
     * Set ownercanstilledit.
     *
     * @param string $ownerCanStillEdit
     *
     * @return ForumPost
     */
    public function setOwnerCanStillEdit($ownerCanStillEdit)
    {
        $this->ownerCanStillEdit = $ownerCanStillEdit;

        return $this;
    }

    /**
     * Get ownercanstilledit.
     *
     * @return string
     */
    public function getOwnerCanStillEdit()
    {
        return $this->ownerCanStillEdit;
    }

    /**
     * Set lastEdittime.
     *
     * @param DateTime $lastEdittime
     *
     * @return ForumPost
     */
    public function setLastEdittime($lastEdittime)
    {
        $this->lastEdittime = $lastEdittime;

        return $this;
    }

    /**
     * Get lastEdittime.
     *
     * @return DateTime
     */
    public function getLastEdittime()
    {
        return $this->lastEdittime;
    }

    /**
     * Set lastEditorid.
     *
     * @param int $lastEditorid
     *
     * @return ForumPost
     */
    public function setLastEditorid($lastEditorid)
    {
        $this->lastEditorid = $lastEditorid;

        return $this;
    }

    /**
     * Get lastEditorid.
     *
     * @return int
     */
    public function getLastEditorid()
    {
        return $this->lastEditorid;
    }

    /**
     * Set editCount.
     *
     * @param bool $editCount
     *
     * @return ForumPost
     */
    public function setEditCount($editCount)
    {
        $this->editCount = $editCount;

        return $this;
    }

    /**
     * Get editCount.
     *
     * @return bool
     */
    public function getEditCount()
    {
        return $this->editCount;
    }

    /**
     * Set language.
     *
     * @param Language $language
     *
     * @return ForumPost
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set hasvotes.
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
     * Get hasvotes.
     *
     * @return string
     */
    public function getHasvotes()
    {
        return $this->hasvotes;
    }

    /**
     * Set idlocalvolmessage.
     *
     * @param int $idlocalvolmessage
     *
     * @return ForumPost
     */
    public function setIdlocalvolmessage($idlocalvolmessage)
    {
        $this->idlocalvolmessage = $idlocalvolmessage;

        return $this;
    }

    /**
     * Get idlocalvolmessage.
     *
     * @return int
     */
    public function getIdlocalvolmessage()
    {
        return $this->idlocalvolmessage;
    }

    /**
     * Set idlocalevent.
     *
     * @param int $idlocalevent
     *
     * @return ForumPost
     */
    public function setIdlocalevent($idlocalevent)
    {
        $this->idlocalevent = $idlocalevent;

        return $this;
    }

    /**
     * Get idlocalevent.
     *
     * @return int
     */
    public function getIdlocalevent()
    {
        return $this->idlocalevent;
    }

    /**
     * Set idpoll.
     *
     * @param int $idpoll
     *
     * @return ForumPost
     */
    public function setIdpoll($idpoll)
    {
        $this->idpoll = $idpoll;

        return $this;
    }

    /**
     * Get idpoll.
     *
     * @return int
     */
    public function getIdpoll()
    {
        return $this->idpoll;
    }

    /**
     * Set deleted.
     *
     * @param string $deleted
     *
     * @return ForumPost
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    /**
     * Get deleted.
     *
     * @return string
     */
    public function getDeleted()
    {
        return $this->deleted;
    }

    /**
     * Set thread.
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
     * Get thread.
     *
     * @return ForumThread
     */
    public function getThread()
    {
        return $this->thread;
    }

    /**
     * Set author.
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
     * Get author.
     *
     * @return Member
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set created.
     *
     * @param DateTime $created
     *
     * @return ForumPost
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
     * @return ForumPost
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
        if ($this->updated && -62169987208 !== $this->updated->getTimestamp()) {
            return Carbon::instance($this->updated);
        }

        return Carbon::instance($this->created);
    }

    /*
     * Translated post content is only provided on explicit call to avoid long load times
     */
    public function getMessageTranslations()
    {
        $translationRepository = $this->objectManager->getRepository(Translation::class);
        $translatedMessages = $translationRepository->findBy(['idTrad' => $this->idcontent]);

        $messages = [];
        /** @var Translation $message */
        foreach ($translatedMessages as $message) {
            $messages[$message->getLanguage()->getShortCode()] = [
                'language' => $message->getLanguage(),
                'message' => $message->getSentence(),
            ];
        }

        return $messages;
    }

    public function injectObjectManager(ObjectManager $objectManager, ClassMetadata $classMetadata)
    {
        $this->objectManager = $objectManager;
    }
}
