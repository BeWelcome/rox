<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * Feedback.
 *
 * @ORM\Table(name="feedbacks")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Feedback
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var Member
     *
     * @ORM\OneToOne(targetEntity="Member")
     * @ORM\JoinColumn(name="IdMember", referencedColumnName="id")
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="Discussion", type="text", length=65535, nullable=false)
     */
    private $discussion;

    /**
     * @var Language
     *
     * @ORM\OneToOne(targetEntity="Language")
     * @ORM\JoinColumn(name="IdLanguage", referencedColumnName="id")
     */
    private $language;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var FeedbackCategory
     *
     * @ORM\ManyToOne(targetEntity="FeedbackCategory")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdFeedbackCategory", referencedColumnName="id")
     * })
     */
    private $category;

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return Feedback
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
     * @param \DateTime $created
     *
     * @return Feedback
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
     * Set author.
     *
     * @param Member author
     *
     * @return Feedback
     */
    public function setAuthor(Member $author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return Member
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set discussion.
     *
     * @param string $discussion
     *
     * @return Feedback
     */
    public function setDiscussion($discussion)
    {
        $this->discussion = $discussion;

        return $this;
    }

    /**
     * Get discussion.
     *
     * @return string
     */
    public function getDiscussion()
    {
        return $this->discussion;
    }

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return Feedback
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
     * Set language.
     *
     * @param int   $ianguage
     * @param mixed $language
     *
     * @return Feedback
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get idlanguage.
     *
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
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
     * Set category.
     *
     * @param FeedbackCategory $category
     *
     * @return Feedback
     */
    public function setCategory(FeedbackCategory $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return FeedbackCategory
     */
    public function getCategory()
    {
        return $this->category;
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

    /**
     * Triggered on update.
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new \DateTime('now');
    }
}
