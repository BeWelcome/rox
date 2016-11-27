<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Carbon\Carbon;

/**
 * Message
 *
 * @ORM\Table(name="messages", indexes={@ORM\Index(name="IdParent", columns={"IdParent", "IdReceiver", "IdSender"}), @ORM\Index(name="IdReceiver", columns={"IdReceiver"}), @ORM\Index(name="IdSender", columns={"IdSender"}), @ORM\Index(name="messages_by_spaminfo", columns={"SpamInfo"}), @ORM\Index(name="IdxStatus", columns={"Status"}), @ORM\Index(name="DeleteRequest", columns={"DeleteRequest"}), @ORM\Index(name="WhenFirstRead", columns={"WhenFirstRead"})})
 * @ORM\Entity
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
     * @var Carbon
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var Carbon
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = '0000-00-00 00:00:00';

    /**
     * @var Carbon
     *
     * @ORM\Column(name="DateSent", type="datetime", nullable=false)
     */
    private $datesent = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="DeleteRequest", type="string", nullable=false)
     */
    private $deleterequest;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdParent", type="integer", nullable=false)
     */
    private $idparent = '0';

    /**
     * @var \AppBundle\Entity\Member
     *
     * @ORM\OneToOne(targetEntity="\AppBundle\Entity\Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idReceiver", referencedColumnName="id")
     * })
     */
    private $receiver;

    /**
     * @var \AppBundle\Entity\Member
     *
     * @ORM\OneToOne(targetEntity="\AppBundle\Entity\Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idSender", referencedColumnName="id")
     * })
     */
    private $sender;

    /**
     * @var string
     *
     * @ORM\Column(name="SpamInfo", type="string", nullable=false)
     */
    private $spaminfo = 'NotSpam';

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'ToCheck';

    /**
     * @var string
     *
     * @ORM\Column(name="Message", type="text", length=65535, nullable=false)
     */
    private $message;

    /**
     * @var string
     *
     * @ORM\Column(name="InFolder", type="string", nullable=false)
     */
    private $infolder = 'Normal';

    /**
     * @var Carbon
     *
     * @ORM\Column(name="WhenFirstRead", type="datetime", nullable=false)
     */
    private $whenfirstread = '0000-00-00 00:00:00';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set messagetype
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
     * Get messagetype
     *
     * @return string
     */
    public function getMessagetype()
    {
        return $this->messagetype;
    }

    /**
     * Set updated
     *
     * @param Carbon $updated
     *
     * @return Message
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
        return $this->updated;
    }

    /**
     * Set created
     *
     * @param Carbon $created
     *
     * @return Message
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
        return $this->created;
    }

    /**
     * Set datesent
     *
     * @param Carbon $datesent
     *
     * @return Message
     */
    public function setDatesent($datesent)
    {
        $this->datesent = $datesent;

        return $this;
    }

    /**
     * Get datesent
     *
     * @return Carbon
     */
    public function getDatesent()
    {
        return $this->datesent;
    }

    /**
     * Set deleterequest
     *
     * @param string $deleterequest
     *
     * @return Message
     */
    public function setDeleterequest($deleterequest)
    {
        $this->deleterequest = $deleterequest;

        return $this;
    }

    /**
     * Get deleterequest
     *
     * @return string
     */
    public function getDeleterequest()
    {
        return $this->deleterequest;
    }

    /**
     * Set idparent
     *
     * @param integer $idparent
     *
     * @return Message
     */
    public function setIdparent($idparent)
    {
        $this->idparent = $idparent;

        return $this;
    }

    /**
     * Get idparent
     *
     * @return integer
     */
    public function getIdparent()
    {
        return $this->idparent;
    }

    /**
     * Set Receiver
     *
     * @param \AppBundle\Entity\Member $createdBy
     *
     * @return Message
     */
    public function setReceiver(\AppBundle\Entity\Member $receiver = null)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * Get Receiver
     *
     * @return \AppBundle\Entity\Member
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * Set Sender
     *
     * @param \AppBundle\Entity\Member $createdBy
     *
     * @return Message
     */
    public function setSender(\AppBundle\Entity\Member $sender = null)
    {
        $this->sender = $sender;

        return $this;
    }

    /**
     * Get Sender
     *
     * @return \AppBundle\Entity\Member
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * Set spaminfo
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
     * Get spaminfo
     *
     * @return string
     */
    public function getSpaminfo()
    {
        return $this->spaminfo;
    }

    /**
     * Set status
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
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set message
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
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set infolder
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
     * Get infolder
     *
     * @return string
     */
    public function getInfolder()
    {
        return $this->infolder;
    }

    /**
     * Set whenfirstread
     *
     * @param Carbon $whenfirstread
     *
     * @return Message
     */
    public function setWhenfirstread($whenfirstread)
    {
        $this->whenfirstread = $whenfirstread;

        return $this;
    }

    /**
     * Get whenfirstread
     *
     * @return Carbon
     */
    public function getWhenfirstread()
    {
        return $this->whenfirstread;
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
}
