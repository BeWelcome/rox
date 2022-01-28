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
    private string $messageType = 'MemberToMember';

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
    private DateTime $created;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="DateSent", type="datetime", nullable=false)
     */
    private DateTime $dateSent;

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
    private $parent = null;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Member", fetch="LAZY")
     */
    private $initiator;

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
    private $spamInfo;

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
     * @var DateTime
     *
     * @ORM\Column(name="WhenFirstRead", type="datetime", nullable=true)
     */
    private $firstRead;

    /**
     * @var Subject
     *
     * @ORM\ManyToOne(targetEntity="Subject", cascade={"persist"}, inversedBy="messages")
     * @ORM\JoinColumn(nullable=true)
     *
     * @Assert\NotBlank()
     */
    private $subject = null;

    /**
     * @var HostingRequest
     *
     * @ORM\ManyToOne(targetEntity="HostingRequest", cascade={"persist"}, fetch="EAGER", inversedBy="messages")
     * @ORM\JoinColumn(nullable=true)
     */
    private $request = null;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function getMessageType(): string
    {
        return $this->messageType;
    }

    public function setUpdated($updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): ?Carbon
    {
        if (null === $this->updated) {
            return null;
        }

        return Carbon::instance($this->updated);
    }

    public function setCreated($created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function setDateSent(DateTime $dateSent): self
    {
        $this->dateSent = $dateSent;

        return $this;
    }

    public function getDateSent(): Carbon
    {
        return Carbon::instance($this->dateSent);
    }

    public function setDeleteRequest($deleteRequest): self
    {
        $this->deleteRequest = $deleteRequest;

        return $this;
    }

    public function getDeleteRequest(): ?string
    {
        return $this->deleteRequest;
    }

    public function setParent(?self $parent): self
    {
        $this->parent = $parent;

        return $this;
    }

    public function getParent(): ?self
    {
        return $this->parent;
    }

    public function setInitiator(Member $initiator): self
    {
        $this->initiator = $initiator;

        return $this;
    }

    public function getInitiator(): Member
    {
        return $this->initiator;
    }

    public function setReceiver(Member $receiver): self
    {
        $this->receiver = $receiver;

        return $this;
    }

    public function getReceiver(): Member
    {
        return $this->receiver;
    }

    public function setSender(Member $sender): self
    {
        $this->sender = $sender;

        return $this;
    }

    public function getSender(): Member
    {
        return $this->sender;
    }

    public function removeFromSpamInfo(string $spamInfo): self
    {
        $info = array_filter(explode(',', $this->spamInfo));
        $key = array_search($spamInfo, $info, true);
        if (false !== $key) {
            unset($info[$key]);
        }
        $this->spamInfo = implode(',', $info);
        if (empty($this->spamInfo)) {
            $this->spamInfo = SpamInfoType::NO_SPAM;
        }

        return $this;
    }

    public function addToSpamInfo(string $spamInfo): self
    {
        if (empty($spamInfo)) {
            return $this;
        }

        $info = array_filter(explode(',', $this->spamInfo));
        $key = array_search($spamInfo, $info, true);
        if (false === $key) {
            $info[] = $spamInfo;
        }
        sort($info);
        if (1 < count($info)) {
            $info = array_diff($info, [SpamInfoType::NO_SPAM]);
        }
        $this->spamInfo = implode(',', $info);

        if (empty($this->spamInfo)) {
            $this->spamInfo = SpamInfoType::NO_SPAM;
        }
        return $this;
    }

    public function getSpamInfo(): string
    {
        return $this->spamInfo;
    }

    public function setSpamInfo(string $spamInfo): self
    {
        $this->spamInfo = $spamInfo;

        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setFolder($folder): self
    {
        $this->folder = $folder;

        return $this;
    }

    public function getFolder(): string
    {
        return $this->folder;
    }

    public function setFirstRead(?DateTime $firstRead): self
    {
        $this->firstRead = $firstRead;

        return $this;
    }

    public function getFirstRead(): ?Carbon
    {
        if (null === $this->firstRead) {
            return null;
        }

        return Carbon::instance($this->firstRead);
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isUnread(): bool
    {
        return null === $this->firstRead;
    }

    public function setSubject(Subject $subject = null): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setRequest(HostingRequest $request = null): self
    {
        $this->request = $request;

        return $this;
    }

    public function getRequest(): ?HostingRequest
    {
        return $this->request;
    }

    public function isDeletedByMember(Member $member): bool
    {
        $deleteRequests = array_filter(explode(',', $this->getDeleteRequest()));

        if ($member === $this->getReceiver()) {
            $key = array_search(DeleteRequestType::RECEIVER_DELETED, $deleteRequests, true);
            if (false !== $key) {
                return true;
            }
            $key = array_search(DeleteRequestType::RECEIVER_PURGED, $deleteRequests, true);
            if (false !== $key) {
                return true;
            }
        }

        if ($member === $this->getSender()) {
            $key = array_search(DeleteRequestType::SENDER_DELETED, $deleteRequests, true);
            if (false !== $key) {
                return true;
            }
            $key = array_search(DeleteRequestType::SENDER_PURGED, $deleteRequests, true);
            if (false !== $key) {
                return true;
            }
        }

        return false;
    }

    public function isPurgedByMember(Member $member): bool
    {
        $deleteRequests = array_filter(explode(',', $this->getDeleteRequest()));
        if ($member === $this->getReceiver()) {
            $key = array_search(DeleteRequestType::RECEIVER_PURGED, $deleteRequests, true);
            if (false !== $key) {
                return true;
            }
        }

        if ($member === $this->getSender()) {
            $key = array_search(DeleteRequestType::SENDER_PURGED, $deleteRequests, true);
            if (false !== $key) {
                return true;
            }
        }

        return false;
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new \DateTime('now');
        if (null === $this->parent) {
            $this->initiator = $this->sender;
        } else {
            $this->initiator = $this->parent->getInitiator();
        }
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
