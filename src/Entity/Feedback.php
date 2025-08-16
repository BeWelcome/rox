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
 * @SuppressWarnings("PHPMD")
 * Auto generated class do not check mess
 */
#[ORM\Table(name: 'feedbacks')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Feedback
{
    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'updated', type: 'datetime', nullable: false)]
    private $updated;

    /**
     * @var \DateTime
     */
    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private $created;

    /**
     * @var Member
     */
    #[ORM\JoinColumn(name: 'IdMember', referencedColumnName: 'id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: \Member::class)]
    private $author;

    /**
     * @var string
     */
    #[ORM\Column(name: 'Discussion', type: 'text', length: 65535, nullable: false)]
    private $discussion;

    /**
     * @var Language
     */
    #[ORM\JoinColumn(name: 'IdLanguage', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \Language::class)]
    private $language;

    /**
     * @var int
     */
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private $id;

    /**
     * @var FeedbackCategory
     */
    #[ORM\JoinColumn(name: 'IdFeedbackCategory', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: \FeedbackCategory::class)]
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
     * Set language.
     *
     * @param Language $language
     *
     * @return Feedback
     */
    public function setLanguage($language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
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
     */
    #[ORM\PrePersist]
    public function onPrePersist()
    {
        $this->created = new \DateTime('now');
    }

    /**
     * Triggered on update.
     */
    #[ORM\PreUpdate]
    public function onPreUpdate()
    {
        $this->updated = new \DateTime('now');
    }
}
