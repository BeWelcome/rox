<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Online
 *
 * @ORM\Table(name="online")
 * @ORM\Entity
 */
class Online
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="appearance", type="string", length=256, nullable=false)
     */
    private $appearance;

    /**
     * @var string
     *
     * @ORM\Column(name="lastactivity", type="string", length=256, nullable=false)
     */
    private $lastactivity;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", length=32, nullable=false)
     */
    private $status = 'Active';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idmember;



    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Online
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
     * Set appearance
     *
     * @param string $appearance
     *
     * @return Online
     */
    public function setAppearance($appearance)
    {
        $this->appearance = $appearance;

        return $this;
    }

    /**
     * Get appearance
     *
     * @return string
     */
    public function getAppearance()
    {
        return $this->appearance;
    }

    /**
     * Set lastactivity
     *
     * @param string $lastactivity
     *
     * @return Online
     */
    public function setLastactivity($lastactivity)
    {
        $this->lastactivity = $lastactivity;

        return $this;
    }

    /**
     * Get lastactivity
     *
     * @return string
     */
    public function getLastactivity()
    {
        return $this->lastactivity;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Online
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Get idmember
     *
     * @return integer
     */
    public function getIdmember()
    {
        return $this->idmember;
    }
}
