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
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'messages', options: ['collate' => 'utf8mb4_general_ci', 'charset' => 'utf8mb4'])]
#[ORM\Index(name: 'IdParent', columns: ['IdParent', 'IdReceiver', 'IdSender'])]
#[ORM\Index(name: 'IdReceiver', columns: ['IdReceiver'])]
#[ORM\Index(name: 'IdSender', columns: ['IdSender'])]
#[ORM\Index(name: 'messages_by_spaminfo', columns: ['SpamInfo'])]
#[ORM\Index(name: 'IdxStatus', columns: ['Status'])]
#[ORM\Index(name: 'DeleteRequest', columns: ['DeleteRequest'])]
#[ORM\Index(name: 'WhenFirstRead', columns: ['WhenFirstRead'])]
#[ORM\Entity(repositoryClass: \App\Repository\MessageRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Message
{
    #[ORM\Column(name: 'MessageType', type: 'string', nullable: false)]
    private string $messageType = 'MemberToMember';

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private ?DateTime $updated = null;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'DateSent', type: 'datetime', nullable: false)]
    private DateTime $dateSent;

    #[ORM\Column(name: 'DeleteRequest', type: 'delete_request', nullable: true)]
    private string $deleteRequest;

    #[ORM\JoinColumn(name: 'IdParent', referencedColumnName: 'id', nullable: true)]
    #[ORM\OneToOne(targetEntity: self::class, fetch: 'LAZY')]
    private ?Message $parent = null;

    #[ORM\JoinColumn(name: 'initiator_id', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class, fetch: 'LAZY')]
    private Member $initiator;

    #[ORM\JoinColumn(name: 'IdReceiver', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class, fetch: 'EAGER')]
    private Member $receiver;

    #[ORM\JoinColumn(name: 'IdSender', referencedColumnName: 'id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Member::class, fetch: 'EAGER')]
    private Member $sender;

    #[ORM\Column(name: 'SpamInfo', type: 'spam_info', nullable: false)]
    private string $spamInfo = SpamInfoType::NO_SPAM;

    #[ORM\Column(name: 'Status', type: 'message_status', nullable: false)]
    private string $status = 'ToSend';

    #[ORM\Column(name: 'Message', type: 'text', length: 65535, nullable: false)]
    private string $message;

    #[ORM\Column(name: 'InFolder', type: 'in_folder', nullable: false)]
    private string $folder = InFolderType::NORMAL;

    #[ORM\Column(name: 'WhenFirstRead', type: 'datetime', nullable: true)]
    private ?DateTime $firstRead;

    #[ORM\Column(name: 'CheckerComment', type: 'text', nullable: true)]
    private ?string $checkerComment;

    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToOne(targetEntity: Subject::class, cascade: ['persist'])]
    #[Assert\NotBlank]
    private ?Subject $subject;

    #[ORM\JoinColumn(nullable: true)]
    #[ORM\ManyToOne(targetEntity: HostingRequest::class, cascade: ['persist'], fetch: 'EAGER', inversedBy: 'messages')]
    private ?HostingRequest $request = null;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

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
        if (1 < \count($info)) {
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

    public function getCheckerComment(): ?string
    {
        return $this->checkerComment;
    }

    public function setCheckerComment(?string $checkerComment): self
    {
        $this->checkerComment = $checkerComment;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function isUnread(): bool
    {
        return null === $this->firstRead;
    }

    public function setSubject(?Subject $subject = null): self
    {
        $this->subject = $subject;

        return $this;
    }

    public function getSubject(): ?Subject
    {
        return $this->subject;
    }

    public function setRequest(?HostingRequest $request = null): self
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
        $deleteRequests = array_filter(explode(',', (string) $this->getDeleteRequest()));

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
        $deleteRequests = array_filter(explode(',', (string) $this->getDeleteRequest()));
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
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
        if (null === $this->parent) {
            $this->initiator = $this->sender;
        } else {
            $this->initiator = $this->parent->getInitiator();
        }
        $this->dateSent = $this->created;
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated = new DateTime('now');
    }

    public function setMessageType(string $messageType): self
    {
        $this->messageType = $messageType;

        return $this;
    }

    public function isMessage(): bool
    {
        return null === $this->request;
    }

    public function isHostingRequest(): bool
    {
        return null !== $this->request && null === $this->request->getInviteForLeg();
    }

    public function isInvitation(): bool
    {
        return null !== $this->request && null !== $this->request->getInviteForLeg();
    }
}
