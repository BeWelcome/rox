<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Faq
 *
 * @ORM\Table(name="faq", indexes={@ORM\Index(name="IdCategory", columns={"IdCategory"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FaqRepository")
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
     * @ORM\Column(type="string")
     */
    private $answer;

    /**
     * @ORM\Column(type="string")
     */
    private $question;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false, options={"default" : "CURRENT_TIMESTAMP"})
     */
    private $updated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false, options={"default" : "CURRENT_TIMESTAMP"})
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="Active", type="string", nullable=false)
     */
    private $active = 'Active';

    /**
     * @var integer
     *
     * @ORM\Column(name="SortOrder", type="integer", nullable=false)
     */
    private $sortorder = '0';

    /**
     * @var \AppBundle\Entity\FaqCategory
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\FaqCategory", fetch="EAGER")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdCategory", referencedColumnName="id")
     * })
     */
    private $category = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="PageTitle", type="string", length=100, nullable=false)
     */
    private $pagetitle;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set qanda
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
     * Get qanda
     *
     * @return string
     */
    public function getQAndA()
    {
        return $this->qAndA;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Faq
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
     * @return Faq
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
     * Set active
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
     * Get active
     *
     * @return string
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set sortorder
     *
     * @param integer $sortorder
     *
     * @return Faq
     */
    public function setSortorder($sortorder)
    {
        $this->sortorder = $sortorder;

        return $this;
    }

    /**
     * Get sortorder
     *
     * @return integer
     */
    public function getSortorder()
    {
        return $this->sortorder;
    }

    /**
     * Set category
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
     * Get category
     *
     * @return FaqCategory
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * Set pagetitle
     *
     * @param string $pagetitle
     *
     * @return Faq
     */
    public function setPagetitle($pagetitle)
    {
        $this->pagetitle = $pagetitle;

        return $this;
    }

    /**
     * Get pagetitle
     *
     * @return string
     */
    public function getPagetitle()
    {
        return $this->pagetitle;
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

    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @param string $answer
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
     * @param string $question
     * @return Faq
     */
    public function setQuestion(string $question)
    {
        $this->question = $question;
        return $this;
    }

}
