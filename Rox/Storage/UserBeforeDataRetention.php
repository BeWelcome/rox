<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserBeforeDataRetention
 *
 * @ORM\Table(name="user_before_data_retention", indexes={@ORM\Index(name="user_id", columns={"auth_id"}), @ORM\Index(name="handle", columns={"handle"}), @ORM\Index(name="email", columns={"email"}), @ORM\Index(name="location", columns={"location"})})
 * @ORM\Entity
 */
class UserBeforeDataRetention
{
    /**
     * @var integer
     *
     * @ORM\Column(name="auth_id", type="integer", nullable=true)
     */
    private $authId;

    /**
     * @var string
     *
     * @ORM\Column(name="handle", type="string", length=255, nullable=false)
     */
    private $handle = '';

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=75, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="pw", type="text", length=65535, nullable=false)
     */
    private $pw;

    /**
     * @var integer
     *
     * @ORM\Column(name="active", type="integer", nullable=false)
     */
    private $active = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="lastlogin", type="datetime", nullable=true)
     */
    private $lastlogin;

    /**
     * @var integer
     *
     * @ORM\Column(name="location", type="integer", nullable=true)
     */
    private $location;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set authId
     *
     * @param integer $authId
     *
     * @return UserBeforeDataRetention
     */
    public function setAuthId($authId)
    {
        $this->authId = $authId;

        return $this;
    }

    /**
     * Get authId
     *
     * @return integer
     */
    public function getAuthId()
    {
        return $this->authId;
    }

    /**
     * Set handle
     *
     * @param string $handle
     *
     * @return UserBeforeDataRetention
     */
    public function setHandle($handle)
    {
        $this->handle = $handle;

        return $this;
    }

    /**
     * Get handle
     *
     * @return string
     */
    public function getHandle()
    {
        return $this->handle;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return UserBeforeDataRetention
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set pw
     *
     * @param string $pw
     *
     * @return UserBeforeDataRetention
     */
    public function setPw($pw)
    {
        $this->pw = $pw;

        return $this;
    }

    /**
     * Get pw
     *
     * @return string
     */
    public function getPw()
    {
        return $this->pw;
    }

    /**
     * Set active
     *
     * @param integer $active
     *
     * @return UserBeforeDataRetention
     */
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get active
     *
     * @return integer
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set lastlogin
     *
     * @param \DateTime $lastlogin
     *
     * @return UserBeforeDataRetention
     */
    public function setLastlogin($lastlogin)
    {
        $this->lastlogin = $lastlogin;

        return $this;
    }

    /**
     * Get lastlogin
     *
     * @return \DateTime
     */
    public function getLastlogin()
    {
        return $this->lastlogin;
    }

    /**
     * Set location
     *
     * @param integer $location
     *
     * @return UserBeforeDataRetention
     */
    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }

    /**
     * Get location
     *
     * @return integer
     */
    public function getLocation()
    {
        return $this->location;
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
