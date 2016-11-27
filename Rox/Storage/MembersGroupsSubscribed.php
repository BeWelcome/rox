<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MembersGroupsSubscribed
 *
 * @ORM\Table(name="members_groups_subscribed")
 * @ORM\Entity
 */
class MembersGroupsSubscribed
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
     * @ORM\Column(name="IdGroup", type="integer", nullable=false)
     */
    private $idgroup;

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
     * @return MembersGroupsSubscribed
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
     * Set idgroup
     *
     * @param integer $idgroup
     *
     * @return MembersGroupsSubscribed
     */
    public function setIdgroup($idgroup)
    {
        $this->idgroup = $idgroup;

        return $this;
    }

    /**
     * Get idgroup
     *
     * @return integer
     */
    public function getIdgroup()
    {
        return $this->idgroup;
    }

    /**
     * Set actiontowatch
     *
     * @param string $actiontowatch
     *
     * @return MembersGroupsSubscribed
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
     * @return MembersGroupsSubscribed
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
     * @return MembersGroupsSubscribed
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
