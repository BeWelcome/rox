<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Right.
 *
 * @ORM\Table(name="rights")
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Right
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="text", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="RightVolunteer", mappedBy="right")
     */
    private $rightVolunteers;

    public function __construct()
    {
        $this->rightVolunteers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @return mixed
     */
    public function getRightVolunteers()
    {
        return $this->rightVolunteers;
    }

    /**
     * @param mixed $rightVolunteers
     */
    public function setRightVolunteers($rightVolunteers)
    {
        $this->rightVolunteers = $rightVolunteers;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Right
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
     * Set name.
     *
     * @param string $name
     *
     * @return Right
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
     * @return Right
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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add rightVolunteer.
     *
     * @param \AppBundle\Entity\RightVolunteer $rightVolunteer
     *
     * @return Right
     */
    public function addRightVolunteer(\AppBundle\Entity\RightVolunteer $rightVolunteer)
    {
        $this->rightVolunteers[] = $rightVolunteer;

        return $this;
    }

    /**
     * Remove rightVolunteer.
     *
     * @param \AppBundle\Entity\RightVolunteer $rightVolunteer
     */
    public function removeRightVolunteer(\AppBundle\Entity\RightVolunteer $rightVolunteer)
    {
        $this->rightVolunteers->removeElement($rightVolunteer);
    }
}
