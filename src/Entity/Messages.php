<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * main's table of messages (`messages`), read only: develop keeps the messages in `message` (App\Entity\Message),
 * filled from this table by Version20261029110000. The table stays as long as main runs on the database.
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
#[ORM\Entity(readOnly: true)]
class Messages
{
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'MessageType', type: 'string', nullable: false)]
    private string $messageType = 'MemberToMember';

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private ?DateTime $updated = null;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'DateSent', type: 'datetime', nullable: false)]
    private DateTime $dateSent;

    #[ORM\Column(name: 'DeleteRequest', type: 'delete_request', nullable: true)]
    private ?string $deleteRequest = null;

    #[ORM\Column(name: 'IdParent', type: 'integer', nullable: true)]
    private ?int $parentId = null;

    #[ORM\Column(name: 'initiator_id', type: 'integer', nullable: false)]
    private int $initiatorId;

    #[ORM\Column(name: 'IdReceiver', type: 'integer', nullable: false)]
    private int $receiverId;

    #[ORM\Column(name: 'IdSender', type: 'integer', nullable: false)]
    private int $senderId;

    #[ORM\Column(name: 'SpamInfo', type: 'spam_info', nullable: false)]
    private string $spamInfo;

    #[ORM\Column(name: 'Status', type: 'message_status', nullable: false)]
    private string $status;

    #[ORM\Column(name: 'Message', type: 'text', nullable: false)]
    private string $message;

    #[ORM\Column(name: 'InFolder', type: 'in_folder', nullable: false)]
    private string $folder;

    #[ORM\Column(name: 'WhenFirstRead', type: 'datetime', nullable: true)]
    private ?DateTime $firstRead = null;

    #[ORM\Column(name: 'CheckerComment', type: 'text', nullable: true)]
    private ?string $checkerComment = null;

    #[ORM\Column(name: 'subject_id', type: 'integer', nullable: true)]
    private ?int $subjectId = null;

    #[ORM\Column(name: 'request_id', type: 'integer', nullable: true)]
    private ?int $requestId = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getMessageType(): string
    {
        return $this->messageType;
    }

    public function getUpdated(): ?DateTime
    {
        return $this->updated;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getDateSent(): DateTime
    {
        return $this->dateSent;
    }

    public function getDeleteRequest(): ?string
    {
        return $this->deleteRequest;
    }

    public function getParentId(): ?int
    {
        return $this->parentId;
    }

    public function getInitiatorId(): int
    {
        return $this->initiatorId;
    }

    public function getReceiverId(): int
    {
        return $this->receiverId;
    }

    public function getSenderId(): int
    {
        return $this->senderId;
    }

    public function getSpamInfo(): string
    {
        return $this->spamInfo;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getFolder(): string
    {
        return $this->folder;
    }

    public function getFirstRead(): ?DateTime
    {
        return $this->firstRead;
    }

    public function getCheckerComment(): ?string
    {
        return $this->checkerComment;
    }

    public function getSubjectId(): ?int
    {
        return $this->subjectId;
    }

    public function getRequestId(): ?int
    {
        return $this->requestId;
    }
}
