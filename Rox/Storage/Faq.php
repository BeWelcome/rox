<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Faq
 *
 * @ORM\Table(name="faq", indexes={@ORM\Index(name="IdCategory", columns={"IdCategory"})})
 * @ORM\Entity
 */
class Faq
{
    /**
     * @var string
     *
     * @ORM\Column(name="QandA", type="string", length=40, nullable=false)
     */
    private $qanda;

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
     * @var integer
     *
     * @ORM\Column(name="IdCategory", type="integer", nullable=false)
     */
    private $idcategory = '0';

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
     * @param string $qanda
     *
     * @return Faq
     */
    public function setQanda($qanda)
    {
        $this->qanda = $qanda;

        return $this;
    }

    /**
     * Get qanda
     *
     * @return string
     */
    public function getQanda()
    {
        return $this->qanda;
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
     * Set idcategory
     *
     * @param integer $idcategory
     *
     * @return Faq
     */
    public function setIdcategory($idcategory)
    {
        $this->idcategory = $idcategory;

        return $this;
    }

    /**
     * Get idcategory
     *
     * @return integer
     */
    public function getIdcategory()
    {
        return $this->idcategory;
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
}
