<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * GroupsRelated
 *
 * @ORM\Table(name="groups_related")
 * @ORM\Entity
 */
class GroupsRelated
{
    /**
     * @var integer
     *
     * @ORM\Column(name="group_id", type="integer", nullable=true)
     */
    private $groupId;

    /**
     * @var integer
     *
     * @ORM\Column(name="related_id", type="integer", nullable=true)
     */
    private $relatedId;

    /**
     * @var integer
     *
     * @ORM\Column(name="addedby", type="integer", nullable=true)
     */
    private $addedby;

    /**
     * @var integer
     *
     * @ORM\Column(name="deletedby", type="integer", nullable=true)
     */
    private $deletedby;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="ts", type="datetime", nullable=false)
     */
    private $ts = 'CURRENT_TIMESTAMP';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set groupId
     *
     * @param integer $groupId
     *
     * @return GroupsRelated
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }

    /**
     * Get groupId
     *
     * @return integer
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Set relatedId
     *
     * @param integer $relatedId
     *
     * @return GroupsRelated
     */
    public function setRelatedId($relatedId)
    {
        $this->relatedId = $relatedId;

        return $this;
    }

    /**
     * Get relatedId
     *
     * @return integer
     */
    public function getRelatedId()
    {
        return $this->relatedId;
    }

    /**
     * Set addedby
     *
     * @param integer $addedby
     *
     * @return GroupsRelated
     */
    public function setAddedby($addedby)
    {
        $this->addedby = $addedby;

        return $this;
    }

    /**
     * Get addedby
     *
     * @return integer
     */
    public function getAddedby()
    {
        return $this->addedby;
    }

    /**
     * Set deletedby
     *
     * @param integer $deletedby
     *
     * @return GroupsRelated
     */
    public function setDeletedby($deletedby)
    {
        $this->deletedby = $deletedby;

        return $this;
    }

    /**
     * Get deletedby
     *
     * @return integer
     */
    public function getDeletedby()
    {
        return $this->deletedby;
    }

    /**
     * Set ts
     *
     * @param \DateTime $ts
     *
     * @return GroupsRelated
     */
    public function setTs($ts)
    {
        $this->ts = $ts;

        return $this;
    }

    /**
     * Get ts
     *
     * @return \DateTime
     */
    public function getTs()
    {
        return $this->ts;
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
