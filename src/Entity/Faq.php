<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Faq.
 *
 * @ORM\Table(name="faq", indexes={@ORM\Index(name="IdCategory", columns={"IdCategory"})})
 * @ORM\Entity(repositoryClass="App\Repository\FaqRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Faq
{
    /**
     * @var string
     *
     * @ORM\Column(name="QandA", type="string", nullable=false)
     */
    private $qAndA;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="Active", type="string", nullable=false)
     */
    private $active = 'Active';

    /**
     * @var int
     *
     * @ORM\Column(name="SortOrder", type="integer", nullable=false)
     */
    private $sortorder = '0';

    /**
     * @var FaqCategory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\FaqCategory", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdCategory", referencedColumnName="id")
     * })
     */
    private $category = '0';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set qanda.
     *
     * @param string $qAndA
     *
     * @return Faq
     */
    public function setQAndA($qAndA)
    {
        $this->qAndA = $qAndA;

        return $this;
    }

    /**
     * Get qanda.
     *
     * @return string
     */
    public function getQAndA()
    {
        return $this->qAndA;
    }

    /**
     * Set updated.
     *
     * @param DateTime $updated
     *
     * @return Faq
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created.
     *
     * @param DateTime $created
     *
     * @return Faq
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set active.
     *
     * @param string $active
     *
     * @return Faq
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active.
     *
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set sortorder.
     *
     * @param int $sortorder
     *
     * @return Faq
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
     * Set category.
     *
     * @param FaqCategory $category
     *
     * @return Faq
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Get category.
     *
     * @return FaqCategory
     */
    public function getCategory()
    {
        return $this->category;
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

    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @return Faq
     */
    public function setAnswer(string $answer)
    {
        $this->answer = $answer;

        return $this;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    /**
     * @return Faq
     */
    public function setQuestion(string $question)
    {
        $this->question = $question;

        return $this;
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
    }

    /**
     * Triggered on update.
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new DateTime('now');
    }
}
