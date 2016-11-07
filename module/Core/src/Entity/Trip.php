<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */
namespace Rox\Core\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Trip
 *
 * @ORM\Table(name="trips", uniqueConstraints={@ORM\UniqueConstraint(name="id_UNIQUE", columns={"id"})},
 *     indexes={@ORM\Index(name="memberId_idx", columns={"created_by"})})
 * @ORM\Entity(repositoryClass="Rox\Trip\Repository\TripRepository")
 */
class Trip
{
    /**
     * @var string
     *
     * @ORM\Column(name="summary", type="string", length=150, nullable=true)
     */
    private $summary;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=4096, nullable=true)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="countOfTravellers", type="integer", nullable=true)
     */
    private $countoftravellers;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false)
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=false)
     */
    private $updatedAt = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="deleted_at", type="datetime", nullable=true)
     */
    private $deletedAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="additionalInfo", type="integer", nullable=true)
     */
    private $additionalinfo;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="SubTrip", mappedBy="trip")
     */
    private $subtrips;

    public function __construct()
    {
        $this->subtrips= new ArrayCollection();
    }

    /**
     * @var \Rox\Core\Entity\Member
     *
     * @ORM\OneToOne(targetEntity="\Rox\Core\Entity\Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="created_by", referencedColumnName="id")
     * })
     */
    private $createdBy;

    /**
     * Set summary
     *
     * @param string $summary
     *
     * @return Trip
     */
    public function setSummary($summary)
    {
        $this->summary = $summary;

        return $this;
    }

    /**
     * Get summary
     *
     * @return string
     */
    public function getSummary()
    {
        return $this->summary;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Trip
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set countoftravellers
     *
     * @param integer $countoftravellers
     *
     * @return Trip
     */
    public function setCountoftravellers($countoftravellers)
    {
        $this->countoftravellers = $countoftravellers;

        return $this;
    }

    /**
     * Get countoftravellers
     *
     * @return integer
     */
    public function getCountoftravellers()
    {
        return $this->countoftravellers;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Trip
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return Trip
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Set deletedAt
     *
     * @param \DateTime $deletedAt
     *
     * @return Trip
     */
    public function setDeletedAt($deletedAt)
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    /**
     * Get deletedAt
     *
     * @return \DateTime
     */
    public function getDeletedAt()
    {
        return $this->deletedAt;
    }

    /**
     * Set additionalinfo
     *
     * @param integer $additionalinfo
     *
     * @return Trip
     */
    public function setAdditionalinfo($additionalinfo)
    {
        $this->additionalinfo = $additionalinfo;

        return $this;
    }

    /**
     * Get additionalinfo
     *
     * @return integer
     */
    public function getAdditionalinfo()
    {
        return $this->additionalinfo;
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

    /**
     * Set createdBy
     *
     * @param \Rox\Core\Entity\Member $createdBy
     *
     * @return Trip
     */
    public function setCreatedBy(\Rox\Core\Entity\Member $createdBy = null)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy
     *
     * @return \Rox\Core\Entity\Member
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    public function getSubtrips()
    {
        return $this->subtrips;
    }
}
