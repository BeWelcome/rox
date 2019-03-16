<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * User.
 *
 * @ORM\Table(name="user")
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class User
{
    /**
     * @return string
     */
    public function getHandle(): string
    {
        return $this->handle;
    }

    /**
     * @param string $handle
     * @return User
     */
    public function setHandle(string $handle): User
    {
        $this->handle = $handle;
        return $this;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="handle", type="string", length=32, nullable=false)
     */
    protected $handle;

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastlogin(): \DateTime
    {
        return $this->lastlogin;
    }

    /**
     * @param \DateTime $lastlogin
     * @return User
     */
    public function setLastlogin(\DateTime $lastlogin): User
    {
        $this->lastlogin = $lastlogin;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return User
     */
    public function setId(int $id): User
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", nullable=false)
     */
    protected $email;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="LastLogin", type="datetime", nullable=false)
     */
    protected $lastlogin = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="PassWord", type="string", length=100, nullable=true)
     */
    protected $password;

    /**
     * @var int
     *
     * @ORM\Column(name="location", type="integer", nullable=true)
     */
    protected $location;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    protected $id;

    /**
     * @return int
     */
    public function getLocation(): int
    {
        return $this->location;
    }

    /**
     * @param int $location
     */
    public function setLocation(int $location): void
    {
        $this->location = $location;
    }
}
