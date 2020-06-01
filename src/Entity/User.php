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
     * @var string
     *
     * @ORM\Column(name="handle", type="string", length=32, nullable=false)
     */
    protected $handle;

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

    public function getHandle(): string
    {
        return $this->handle;
    }

    /**
     * @return User
     */
    public function setHandle(string $handle): self
    {
        $this->handle = $handle;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getLastlogin(): \DateTime
    {
        return $this->lastlogin;
    }

    /**
     * @return User
     */
    public function setLastlogin(\DateTime $lastlogin): self
    {
        $this->lastlogin = $lastlogin;

        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @return User
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return User
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getLocation(): int
    {
        return $this->location;
    }

    public function setLocation(int $location): void
    {
        $this->location = $location;
    }
}
