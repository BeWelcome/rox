<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * AdminCode.
 *
 * @ORM\Table(name="geonames_admincodes", indexes={@ORM\Index(name="country_code", columns={"country_code"}), @ORM\Index(name="admin_code", columns={"admin_code"})})
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class AdminCode
{
    /**
     * @var string
     *
     * @ORM\Column(name="country_code", type="string", length=2, nullable=false)
     */
    private $countryCode;

    /**
     * @var string
     *
     * @ORM\Column(name="admin_code", type="string", length=2, nullable=false)
     */
    private $adminCode;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=64, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=5)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $code;

    /**
     * Set countryCode.
     *
     * @param string $countryCode
     *
     * @return AdminCode
     */
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;

        return $this;
    }

    /**
     * Get countryCode.
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    /**
     * Set adminCode.
     *
     * @param string $adminCode
     *
     * @return AdminCode
     */
    public function setAdminCode($adminCode)
    {
        $this->adminCode = $adminCode;

        return $this;
    }

    /**
     * Get adminCode.
     *
     * @return string
     */
    public function getAdminCode()
    {
        return $this->adminCode;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return AdminCode
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
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }
}
