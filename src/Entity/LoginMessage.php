<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * LoginMessages.
 *
 * @ORM\Table(name="login_messages")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="App\Repository\LoginMessageRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class LoginMessage
{
    /**
     * @ORM\Column(name="text", type="string", length=255, nullable=false)
     */
    private string $message;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private DateTime $created;

    /**
     * @ORM\Column(name="expires", type="datetime", nullable=false)
     */
    private ?DateTime $expires;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private int $id;

    public function setMessage(string $message): self
    {
        $this->message = $message;

        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function setExpires(?DateTime $expires): self
    {
        $this->expires = $expires;

        return $this;
    }

    public function getExpires(): ?DateTime
    {
        return $this->expires;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
    }
}
