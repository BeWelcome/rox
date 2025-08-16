<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Ewiki.
 *
 * @SuppressWarnings("PHPMD")
 * Auto generated class do not check mess
 */
#[ORM\Table(name: 'ewiki')]
#[ORM\Entity(repositoryClass: \App\Repository\WikiRepository::class)]
class Wiki
{
    #[ORM\Column(name: 'content', type: 'text', length: 16777215, nullable: false)]
    private string $content;

    #[ORM\Column(name: 'author', type: 'string', length: 100, nullable: false)]
    private string $author = 'ewiki';

    #[ORM\Column(name: 'created', type: 'integer', nullable: true)]
    private ?int $created = 1168175948;

    #[ORM\Column(name: 'lastmodified', type: 'integer', nullable: true)]
    private ?int $lastmodified = null;

    #[ORM\Column(name: 'pagename', type: 'string', length: 160)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private string $pagename;

    #[ORM\Column(name: 'version', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    private int $version;

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setCreated(int $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): int
    {
        return $this->created;
    }

    public function setLastmodified(?int $lastmodified): self
    {
        $this->lastmodified = $lastmodified;

        return $this;
    }

    public function getLastmodified(): ?int
    {
        return $this->lastmodified;
    }

    public function setPagename(string $pagename): self
    {
        $this->pagename = $pagename;

        return $this;
    }

    public function getPagename(): string
    {
        return $this->pagename;
    }

    public function setVersion(int $version): self
    {
        $this->version = $version;

        return $this;
    }

    public function getVersion(): int
    {
        return $this->version;
    }
}
