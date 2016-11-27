<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Blog
 *
 * @ORM\Table(name="blog", indexes={@ORM\Index(name="country_id_foreign", columns={"country_id_foreign"}), @ORM\Index(name="trip_id_foreign", columns={"trip_id_foreign"}), @ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\Entity
 */
class Blog
{
    /**
     * @var string
     *
     * @ORM\Column(name="flags", type="blob", length=65535, nullable=false)
     */
    private $flags;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="blog_created", type="datetime", nullable=false)
     */
    private $blogCreated;

    /**
     * @var integer
     *
     * @ORM\Column(name="country_id_foreign", type="integer", nullable=true)
     */
    private $countryIdForeign;

    /**
     * @var integer
     *
     * @ORM\Column(name="trip_id_foreign", type="integer", nullable=true)
     */
    private $tripIdForeign;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=true)
     */
    private $idmember;

    /**
     * @var integer
     *
     * @ORM\Column(name="blog_id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $blogId;



    /**
     * Set flags
     *
     * @param string $flags
     *
     * @return Blog
     */
    public function setFlags($flags)
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * Get flags
     *
     * @return string
     */
    public function getFlags()
    {
        return $this->flags;
    }

    /**
     * Set blogCreated
     *
     * @param \DateTime $blogCreated
     *
     * @return Blog
     */
    public function setBlogCreated($blogCreated)
    {
        $this->blogCreated = $blogCreated;

        return $this;
    }

    /**
     * Get blogCreated
     *
     * @return \DateTime
     */
    public function getBlogCreated()
    {
        return $this->blogCreated;
    }

    /**
     * Set countryIdForeign
     *
     * @param integer $countryIdForeign
     *
     * @return Blog
     */
    public function setCountryIdForeign($countryIdForeign)
    {
        $this->countryIdForeign = $countryIdForeign;

        return $this;
    }

    /**
     * Get countryIdForeign
     *
     * @return integer
     */
    public function getCountryIdForeign()
    {
        return $this->countryIdForeign;
    }

    /**
     * Set tripIdForeign
     *
     * @param integer $tripIdForeign
     *
     * @return Blog
     */
    public function setTripIdForeign($tripIdForeign)
    {
        $this->tripIdForeign = $tripIdForeign;

        return $this;
    }

    /**
     * Get tripIdForeign
     *
     * @return integer
     */
    public function getTripIdForeign()
    {
        return $this->tripIdForeign;
    }

    /**
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return Blog
     */
    public function setIdmember($idmember)
    {
        $this->idmember = $idmember;

        return $this;
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

    /**
     * Get blogId
     *
     * @return integer
     */
    public function getBlogId()
    {
        return $this->blogId;
    }
}
