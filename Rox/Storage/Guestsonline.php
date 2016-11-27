<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Guestsonline
 *
 * @ORM\Table(name="guestsonline", indexes={@ORM\Index(name="updated", columns={"updated"})})
 * @ORM\Entity
 */
class Guestsonline
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
     * @ORM\Column(name="appearance", type="string", length=32, nullable=false)
     */
    private $appearance;

    /**
     * @var string
     *
     * @ORM\Column(name="lastactivity", type="string", length=255, nullable=false)
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
     * @ORM\Column(name="IpGuest", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $ipguest;



    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Guestsonline
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
     * @return Guestsonline
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
     * @return Guestsonline
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
     * @return Guestsonline
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
     * Get ipguest
     *
     * @return integer
     */
    public function getIpguest()
    {
        return $this->ipguest;
    }
}
