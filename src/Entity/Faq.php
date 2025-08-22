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
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'faq')]
#[ORM\Index(name: 'IdCategory', columns: ['IdCategory'])]
#[ORM\Entity(repositoryClass: \App\Repository\FaqRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Faq
{
    #[ORM\Column(name: 'QandA', type: 'string', nullable: false)]
    private string $qAndA;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private DateTime $updated;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'Active', type: 'string', nullable: false)]
    private string $active = 'Active';

    #[ORM\Column(name: 'SortOrder', type: 'integer', nullable: false)]
    private int $sortOrder = 0;

    #[ORM\JoinColumn(name: 'IdCategory', referencedColumnName: 'id')]
    #[ORM\ManyToOne(targetEntity: FaqCategory::class, fetch: 'EAGER')]
    private FaqCategory $category;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    public function setQAndA(string $qAndA): self
    {
        $this->qAndA = $qAndA;

        return $this;
    }

    public function getQAndA(): string
    {
        return $this->qAndA;
    }

    public function setUpdated(?DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getUpdated(): ?DateTime
    {
        return $this->updated;
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

    public function setActive(string $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getActive(): string
    {
        return $this->active;
    }

    public function setSortOrder(int $sortOrder): self
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    public function getSortOrder(): int
    {
        return $this->sortOrder;
    }

    public function setCategory(FaqCategory $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory(): FaqCategory
    {
        return $this->category;
    }

    public function getId(): int
    {
        return $this->id;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
    }

    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updated = new DateTime('now');
    }
}
