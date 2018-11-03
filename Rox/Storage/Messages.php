<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Messages
 *
 * @ORM\Table(name="messages", indexes={@ORM\Index(name="IdParent", columns={"IdParent", "IdReceiver", "IdSender"}), @ORM\Index(name="IdReceiver", columns={"IdReceiver"}), @ORM\Index(name="IdSender", columns={"IdSender"}), @ORM\Index(name="messages_by_spaminfo", columns={"SpamInfo"}), @ORM\Index(name="IdxStatus", columns={"Status"}), @ORM\Index(name="DeleteRequest", columns={"DeleteRequest"}), @ORM\Index(name="WhenFirstRead", columns={"WhenFirstRead"})})
 * @ORM\Entity
 */
class Messages
{
    /**
     * @var string
     *
     * @ORM\Column(name="MessageType", type="string", nullable=false)
     */
    private $messagetype = 'MemberToMember';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdMessageFromLocalVol", type="integer", nullable=false)
     */
    private $idmessagefromlocalvol = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
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
     * @var integer
     *
     * @ORM\Column(name="IdReceiver", type="integer", nullable=false)
     */
    private $idreceiver;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdSender", type="integer", nullable=false)
     */
    private $idsender;

    /**
     * @var string
     *
     * @ORM\Column(name="IdentityInformation", type="text", length=65535, nullable=false)
     */
    private $identityinformation;

    /**
     * @var string
     *
     * @ORM\Column(name="SendConfirmation", type="string", nullable=false)
     */
    private $sendconfirmation;

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
     * @var \DateTime
     *
     * @ORM\Column(name="WhenFirstRead", type="datetime", nullable=false)
     */
    private $whenfirstread = '0000-00-00 00:00:00';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdChecker", type="integer", nullable=false)
     */
    private $idchecker = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdTriggerer", type="integer", nullable=false)
     */
    private $idtriggerer = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="JoinMemberPict", type="string", nullable=false)
     */
    private $joinmemberpict = 'no';

    /**
     * @var string
     *
     * @ORM\Column(name="CheckerComment", type="text", length=65535, nullable=false)
     */
    private $checkercomment;

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
     * @return Messages
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
     * Set idmessagefromlocalvol
     *
     * @param integer $idmessagefromlocalvol
     *
     * @return Messages
     */
    public function setIdmessagefromlocalvol($idmessagefromlocalvol)
    {
        $this->idmessagefromlocalvol = $idmessagefromlocalvol;

        return $this;
    }

    /**
     * Get idmessagefromlocalvol
     *
     * @return integer
     */
    public function getIdmessagefromlocalvol()
    {
        return $this->idmessagefromlocalvol;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Messages
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Messages
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set datesent
     *
     * @param \DateTime $datesent
     *
     * @return Messages
     */
    public function setDatesent($datesent)
    {
        $this->datesent = $datesent;

        return $this;
    }

    /**
     * Get datesent
     *
     * @return \DateTime
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
     * @return Messages
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
     * @return Messages
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
     * Set idreceiver
     *
     * @param integer $idreceiver
     *
     * @return Messages
     */
    public function setIdreceiver($idreceiver)
    {
        $this->idreceiver = $idreceiver;

        return $this;
    }

    /**
     * Get idreceiver
     *
     * @return integer
     */
    public function getIdreceiver()
    {
        return $this->idreceiver;
    }

    /**
     * Set idsender
     *
     * @param integer $idsender
     *
     * @return Messages
     */
    public function setIdsender($idsender)
    {
        $this->idsender = $idsender;

        return $this;
    }

    /**
     * Get idsender
     *
     * @return integer
     */
    public function getIdsender()
    {
        return $this->idsender;
    }

    /**
     * Set identityinformation
     *
     * @param string $identityinformation
     *
     * @return Messages
     */
    public function setIdentityinformation($identityinformation)
    {
        $this->identityinformation = $identityinformation;

        return $this;
    }

    /**
     * Get identityinformation
     *
     * @return string
     */
    public function getIdentityinformation()
    {
        return $this->identityinformation;
    }

    /**
     * Set sendconfirmation
     *
     * @param string $sendconfirmation
     *
     * @return Messages
     */
    public function setSendconfirmation($sendconfirmation)
    {
        $this->sendconfirmation = $sendconfirmation;

        return $this;
    }

    /**
     * Get sendconfirmation
     *
     * @return string
     */
    public function getSendconfirmation()
    {
        return $this->sendconfirmation;
    }

    /**
     * Set spaminfo
     *
     * @param string $spaminfo
     *
     * @return Messages
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
     * @return Messages
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
     * @return Messages
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
     * @return Messages
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
     * @param \DateTime $whenfirstread
     *
     * @return Messages
     */
    public function setWhenfirstread($whenfirstread)
    {
        $this->whenfirstread = $whenfirstread;

        return $this;
    }

    /**
     * Get whenfirstread
     *
     * @return \DateTime
     */
    public function getWhenfirstread()
    {
        return $this->whenfirstread;
    }

    /**
     * Set idchecker
     *
     * @param integer $idchecker
     *
     * @return Messages
     */
    public function setIdchecker($idchecker)
    {
        $this->idchecker = $idchecker;

        return $this;
    }

    /**
     * Get idchecker
     *
     * @return integer
     */
    public function getIdchecker()
    {
        return $this->idchecker;
    }

    /**
     * Set idtriggerer
     *
     * @param integer $idtriggerer
     *
     * @return Messages
     */
    public function setIdtriggerer($idtriggerer)
    {
        $this->idtriggerer = $idtriggerer;

        return $this;
    }

    /**
     * Get idtriggerer
     *
     * @return integer
     */
    public function getIdtriggerer()
    {
        return $this->idtriggerer;
    }

    /**
     * Set joinmemberpict
     *
     * @param string $joinmemberpict
     *
     * @return Messages
     */
    public function setJoinmemberpict($joinmemberpict)
    {
        $this->joinmemberpict = $joinmemberpict;

        return $this;
    }

    /**
     * Get joinmemberpict
     *
     * @return string
     */
    public function getJoinmemberpict()
    {
        return $this->joinmemberpict;
    }

    /**
     * Set checkercomment
     *
     * @param string $checkercomment
     *
     * @return Messages
     */
    public function setCheckercomment($checkercomment)
    {
        $this->checkercomment = $checkercomment;

        return $this;
    }

    /**
     * Get checkercomment
     *
     * @return string
     */
    public function getCheckercomment()
    {
        return $this->checkercomment;
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
