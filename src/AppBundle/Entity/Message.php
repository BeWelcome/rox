<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

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
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MessageRepository")
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
    private $messagetype = 'MemberToMember';

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false, options={"default" : "CURRENT_TIMESTAMP"})
     */
    private $created;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="DateSent", type="datetime", nullable=false)
     */
    private $datesent;

    /**
     * @var string
     *
     * @ORM\Column(name="DeleteRequest", type="delete_request", nullable=true)
     */
    private $deleteRequest;

    /**
     * @var Message
     *
     * @ORM\OneToOne(targetEntity="AppBundle\Entity\Message", fetch="LAZY")
     * @ORM\JoinColumn(name="idParent", referencedColumnName="id", nullable=true)
     */
    private $parent;

    /**
     * @var \AppBundle\Entity\Member
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Member", fetch="EAGER")
     * @ORM\JoinColumn(name="idReceiver", referencedColumnName="id")
     */
    private $receiver;

    /**
     * @var \AppBundle\Entity\Member
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Member", fetch="EAGER")
     * @ORM\JoinColumn(name="idSender", referencedColumnName="id")
     */
    private $sender;

    /**
     * @var string
     *
     * @ORM\Column(name="SpamInfo", type="spam_info", nullable=false)
     */
    private $spaminfo = 'NotSpam';

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
    private $infolder = 'Normal';

    /**
     * @var Carbon
     *
     * @ORM\Column(name="WhenFirstRead", type="datetime", nullable=true)
     */
    private $whenfirstread;

    /**
     * @var Subject
     *
     * @ORM\OneToOne(targetEntity="Subject", cascade={"persist"}, fetch="EAGER")
     *
     * @Assert\NotBlank()
     */
    private $subject;

    /**
     * @var HostingRequest
     *
     * @ORM\OneToOne(targetEntity="HostingRequest", cascade={"persist"}, fetch="EAGER")
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
     * Set messagetype.
     *
     * @param string $messagetype
     *
     * @return Message
     */
    public function setMessagetype($messagetype)
    {
        $this->messagetype = $messagetype;

        return $this;
    }

    /**
     * Get messagetype.
     *
     * @return string
     */
    public function getMessagetype()
    {
        return $this->messagetype;
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
     * @param DateTime $datesent
     * @param mixed    $dateSent
     *
     * @return Message
     */
    public function setDateSent($dateSent)
    {
        $this->datesent = $dateSent;

        return $this;
    }

    /**
     * Get datesent.
     *
     * @return Carbon
     */
    public function getDateSent()
    {
        return Carbon::instance($this->datesent);
    }

    /**
     * Set deleterequest.
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
    public function setParent(self $parent)
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
     * Set child.
     *
     * @param Message $child
     *
     * @return Message
     */
    public function setChild(self $child)
    {
        $this->child = $child;

        return $this;
    }

    /**
     * Get child.
     *
     * @return Message|null
     */
    public function getChild()
    {
        return $this->child;
    }

    /**
     * Set Receiver.
     *
     * @param Member $receiver
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
     * @param Member $sender
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
     * @param string $infolder
     *
     * @return Message
     */
    public function setInfolder($infolder)
    {
        $this->infolder = $infolder;

        return $this;
    }

    /**
     * Get infolder.
     *
     * @return string
     */
    public function getInfolder()
    {
        return $this->infolder;
    }

    /**
     * Set whenfirstread.
     *
     * @param DateTime $whenFirstRead
     *
     * @return Message
     */
    public function setWhenFirstRead($whenFirstRead)
    {
        $this->whenfirstread = $whenFirstRead;

        return $this;
    }

    /**
     * Get whenFirstRead.
     *
     * @return Carbon
     */
    public function getWhenFirstRead()
    {
        if ($this->whenfirstread === new DateTime('0000-00-00 00:00:00')) {
            return null;
        }

        return $this->whenfirstread;
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
        return null === $this->whenfirstread;
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
     * Get request.
     *
     * @return HostingRequest
     */
    public function getRequest()
    {
        return $this->request;
    }
}
