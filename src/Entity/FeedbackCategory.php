<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feedback category.
 *
 * @ORM\Table(name="feedbackcategories")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class FeedbackCategory
{
    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="text", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="CategoryDescription", type="text", length=255, nullable=false)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="EmailToNotify", type="text", length=65535, nullable=false)
     */
    private $emailtonotify;

    /**
     * @var int
     *
     * @ORM\Column(name="IdVolunteer", type="integer", nullable=false)
     */
    private $idvolunteer = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="sortOrder", type="integer", nullable=false)
     */
    private $sortorder = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="visible", type="integer", nullable=false)
     */
    private $visible = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return FeedbackCategory
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return FeedbackCategory
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return FeedbackCategory
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set emailtonotify.
     *
     * @param string $emailtonotify
     *
     * @return FeedbackCategory
     */
    public function setEmailtonotify($emailtonotify)
    {
        $this->emailtonotify = $emailtonotify;

        return $this;
    }

    /**
     * Get emailtonotify.
     *
     * @return string
     */
    public function getEmailtonotify()
    {
        return $this->emailtonotify;
    }

    /**
     * Set idvolunteer.
     *
     * @param int $idvolunteer
     *
     * @return FeedbackCategory
     */
    public function setIdvolunteer($idvolunteer)
    {
        $this->idvolunteer = $idvolunteer;

        return $this;
    }

    /**
     * Get idvolunteer.
     *
     * @return int
     */
    public function getIdvolunteer()
    {
        return $this->idvolunteer;
    }

    /**
     * Set sortorder.
     *
     * @param int $sortorder
     *
     * @return FeedbackCategory
     */
    public function setSortorder($sortorder)
    {
        $this->sortorder = $sortorder;

        return $this;
    }

    /**
     * Get sortorder.
     *
     * @return int
     */
    public function getSortorder()
    {
        return $this->sortorder;
    }

    /**
     * Set visible.
     *
     * @param int $visible
     *
     * @return FeedbackCategory
     */
    public function setVisible($visible)
    {
        $this->visible = $visible;

        return $this;
    }

    /**
     * Get visible.
     *
     * @return int
     */
    public function getVisible()
    {
        return $this->visible;
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

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new \DateTime('now');
    }
}
