<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MembersThreadsSubscribed
 *
 * @ORM\Table(name="members_threads_subscribed", indexes={@ORM\Index(name="IdSubscriber", columns={"IdSubscriber", "IdThread"})})
 * @ORM\Entity
 */
class MembersThreadsSubscribed
{
    /**
     * @var integer
     *
     * @ORM\Column(name="IdSubscriber", type="integer", nullable=false)
     */
    private $idsubscriber;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdThread", type="integer", nullable=false)
     */
    private $idthread;

    /**
     * @var string
     *
     * @ORM\Column(name="ActionToWatch", type="string", nullable=false)
     */
    private $actiontowatch = 'replies';

    /**
     * @var string
     *
     * @ORM\Column(name="UnSubscribeKey", type="string", length=20, nullable=false)
     */
    private $unsubscribekey;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var boolean
     *
     * @ORM\Column(name="notificationsEnabled", type="boolean", nullable=false)
     */
    private $notificationsenabled = '1';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set idsubscriber
     *
     * @param integer $idsubscriber
     *
     * @return MembersThreadsSubscribed
     */
    public function setIdsubscriber($idsubscriber)
    {
        $this->idsubscriber = $idsubscriber;

        return $this;
    }

    /**
     * Get idsubscriber
     *
     * @return integer
     */
    public function getIdsubscriber()
    {
        return $this->idsubscriber;
    }

    /**
     * Set idthread
     *
     * @param integer $idthread
     *
     * @return MembersThreadsSubscribed
     */
    public function setIdthread($idthread)
    {
        $this->idthread = $idthread;

        return $this;
    }

    /**
     * Get idthread
     *
     * @return integer
     */
    public function getIdthread()
    {
        return $this->idthread;
    }

    /**
     * Set actiontowatch
     *
     * @param string $actiontowatch
     *
     * @return MembersThreadsSubscribed
     */
    public function setActiontowatch($actiontowatch)
    {
        $this->actiontowatch = $actiontowatch;

        return $this;
    }

    /**
     * Get actiontowatch
     *
     * @return string
     */
    public function getActiontowatch()
    {
        return $this->actiontowatch;
    }

    /**
     * Set unsubscribekey
     *
     * @param string $unsubscribekey
     *
     * @return MembersThreadsSubscribed
     */
    public function setUnsubscribekey($unsubscribekey)
    {
        $this->unsubscribekey = $unsubscribekey;

        return $this;
    }

    /**
     * Get unsubscribekey
     *
     * @return string
     */
    public function getUnsubscribekey()
    {
        return $this->unsubscribekey;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return MembersThreadsSubscribed
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
     * Set notificationsenabled
     *
     * @param boolean $notificationsenabled
     *
     * @return MembersThreadsSubscribed
     */
    public function setNotificationsenabled($notificationsenabled)
    {
        $this->notificationsenabled = $notificationsenabled;

        return $this;
    }

    /**
     * Get notificationsenabled
     *
     * @return boolean
     */
    public function getNotificationsenabled()
    {
        return $this->notificationsenabled;
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
