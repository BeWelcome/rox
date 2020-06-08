<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\DeleteRequestType;
use App\Doctrine\InFolderType;
use App\Doctrine\SpamInfoType;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Message.
 *
 * @ORM\Table(name="messages",
 *     options={"collate":"utf8mb4_general_ci", "charset":"utf8mb4"},
 *     indexes={@ORM\Index(name="IdParent",
 *         columns={"IdParent", "IdReceiver", "IdSender"}),
 *         @ORM\Index(name="IdReceiver", columns={"IdReceiver"}),
 *         @ORM\Index(name="IdSender", columns={"IdSender"}),
 *         @ORM\Index(name="messages_by_spaminfo", columns={"SpamInfo"}),
 *         @ORM\Index(name="IdxStatus", columns={"Status"}),
 *         @ORM\Index(name="DeleteRequest", columns={"DeleteRequest"}),
 *         @ORM\Index(name="WhenFirstRead", columns={"WhenFirstRead"})})
 * @ORM\Entity(repositoryClass="App\Repository\MessageRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Message
{
    /**
     * @var string
     *
     * @ORM\Column(name="MessageType", type="string", nullable=false)
     */
    private $messageType = 'MemberToMember';

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="DateSent", type="datetime", nullable=false)
     */
    private $dateSent;

    /**
     * @var string
     *
     * @ORM\Column(name="DeleteRequest", type="delete_request", nullable=true)
     */
    private $deleteRequest;

    /**
     * @var Message
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Message", fetch="LAZY")
     * @ORM\JoinColumn(name="idParent", referencedColumnName="id", nullable=true)
     */
    private $parent;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", fetch="EAGER")
     * @ORM\JoinColumn(name="idReceiver", referencedColumnName="id")
     */
    private $receiver;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", fetch="EAGER")
     * @ORM\JoinColumn(name="idSender", referencedColumnName="id")
     */
    private $sender;

    /**
     * @var string
     *
     * @ORM\Column(name="SpamInfo", type="spam_info", nullable=false)
     */
    private $spaminfo = SpamInfoType::NO_SPAM;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="message_status", nullable=false)
     */
    private $status = 'ToSend';

    /**
     * @var string
     *
     * @ORM\Column(name="Message", type="text", length=65535, nullable=false)
     *
     * @Assert\NotBlank()
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="InFolder", type="in_folder", nullable=false)
     */
    private $folder = InFolderType::NORMAL;

    /**
     * @var Carbon
     *
     * @ORM\Column(name="WhenFirstRead", type="datetime", nullable=true)
     */
    private $firstRead;

    /**
     * @var Subject
     *
     * @ORM\ManyToOne(targetEntity="Subject", cascade={"persist"}, inversedBy="messages")
     *
     * @Assert\NotBlank()
     */
    private $subject;

    /**
     * @var HostingRequest
     *
     * @ORM\ManyToOne(targetEntity="HostingRequest", cascade={"persist"}, fetch="EAGER", inversedBy="messages")
     * @ORM\JoinColumn(nullable=true)
     */
    private $request;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Get messagetype.
     *
     * @return string
     */
    public function getMessageType()
    {
        return $this->messageType;
    }

    /**
     * Set updated.
     *
     * @param DateTime $updated
     *
     * @return Message
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
     * Set created.
     *
     * @param DateTime $created
     *
     * @return Message
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
     * Set datesent.
     *
     * @return Message
     */
    public function setDateSent(DateTime $dateSent)
    {
        $this->dateSent = $dateSent;

        return $this;
    }

    /**
     * Get dateSent.
     *
     * @return Carbon
     */
    public function getDateSent()
    {
        return Carbon::instance($this->dateSent);
    }

    /**
     * Set deleteRequest.
     *
     * @param string $deleteRequest
     *
     * @return Message
     */
    public function setDeleteRequest($deleteRequest)
    {
        $this->deleteRequest = $deleteRequest;

        return $this;
    }

    /**
     * Get deleterequest.
     *
     * @return string
     */
    public function getDeleteRequest()
    {
        return $this->deleteRequest;
    }

    /**
     * Set parent.
     *
     * @param Message $parent
     *
     * @return Message
     */
    public function setParent(?self $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * Get parent.
     *
     * @return Message|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * Set Receiver.
     *
     * @return Message
     */
    public function setReceiver(Member $receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get Receiver.
     *
     * @return Member
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set Sender.
     *
     * @return Message
     */
    public function setSender(Member $sender)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get Sender.
     *
     * @return Member
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set spaminfo.
     *
     * @param string $spaminfo
     *
     * @return Message
     */
    public function setSpaminfo($spaminfo)
    {
        $this->spaminfo = $spaminfo;

        return $this;
    }

    /**
     * Update spaminfo.
     *
     * @param string $spaminfo
     *
     * @return Message
     */
    public function removeFromSpaminfo($spaminfo)
    {
        $info = array_filter(explode(',', $this->spaminfo));
        $key = array_search($spaminfo, $info, true);
        if (false !== $key) {
            unset($info[$key]);
        }
        $this->spaminfo = implode(',', $info);
        if (empty($this->spaminfo)) {
            $this->spaminfo = SpamInfoType::NO_SPAM;
        }

        return $this;
    }

    /**
     * Update spaminfo.
     *
     * @param string $spaminfo
     *
     * @return Message
     */
    public function addToSpamInfo($spaminfo)
    {
        if (SpamInfoType::NO_SPAM === $this->spaminfo) {
            $this->spaminfo = '';
        }
        $info = array_filter(explode(',', $this->spaminfo));
        $key = array_search($spaminfo, $info, true);
        if (false === $key) {
            $info[] = $spaminfo;
        }
        $this->spaminfo = implode(',', $info);

        return $this;
    }

    /**
     * Get spaminfo.
     *
     * @return string
     */
    public function getSpaminfo()
    {
        return $this->spaminfo;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return Message
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set message.
     *
     * @param string $message
     *
     * @return Message
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
     * Set infolder.
     *
     * @param string $folder
     *
     * @return Message
     */
    public function setFolder($folder)
    {
        $this->folder = $folder;

        return $this;
    }

    /**
     * Get infolder.
     *
     * @return string
     */
    public function getFolder()
    {
        return $this->folder;
    }

    /**
     * Set firstRead.
     *
     * @param DateTime $firstRead
     *
     * @return Message
     */
    public function setFirstRead($firstRead)
    {
        $this->firstRead = $firstRead;

        return $this;
    }

    /**
     * Get firstRead.
     *
     * @throws Exception
     *
     * @return Carbon
     */
    public function getFirstRead()
    {
        if ($this->firstRead === new DateTime('0000-00-00 00:00:00')) {
            return null;
        }

        return $this->firstRead;
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

    public function isUnread()
    {
        return null === $this->firstRead;
    }

    /**
     * Set subject.
     *
     * @param Subject $subject
     *
     * @return Message
     */
    public function setSubject(Subject $subject = null)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject.
     *
     * @return Subject
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * Set request.
     *
     * @param HostingRequest $request
     *
     * @return Message
     */
    public function setRequest(HostingRequest $request = null)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @return bool
     */
    public function isReceiverDeleted(Member $member)
    {
        if ($member === $this->getReceiver()) {
            $deleteRequest = $this->getDeleteRequest();
            $requests = array_filter(explode(',', $deleteRequest));
            $key = array_search(DeleteRequestType::RECEIVER_DELETED, $requests, true);
            if (false !== $key) {
                return true;
            }
            $key = array_search(DeleteRequestType::RECEIVER_PURGED, $requests, true);
            if (false !== $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isSenderDeleted(Member $member)
    {
        if ($member === $this->getSender()) {
            $deleteRequest = $this->getDeleteRequest();
            $requests = array_filter(explode(',', $deleteRequest));
            $key = array_search(DeleteRequestType::SENDER_DELETED, $requests, true);
            if (false !== $key) {
                return true;
            }
            $key = array_search(DeleteRequestType::SENDER_PURGED, $requests, true);
            if (false !== $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get request.
     *
     * @return HostingRequest
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new \DateTime('now');
        $this->dateSent = $this->created;
    }

    /**
     * Triggered on update.
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new \DateTime('now');
    }

    public function setMessageType(string $messageType): self
    {
        $this->messageType = $messageType;

        return $this;
    }
}
