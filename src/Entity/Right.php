<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Right.
 *
 * @SuppressWarnings("PHPMD")
 * Auto generated class do not check mess
 */
#[ORM\Table(name: 'rights')]
#[ORM\Entity]
#[ORM\HasLifecycleCallbacks]
class Right
{
    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private \DateTime $created;

    #[ORM\Column(name: 'Name', type: 'text', length: 255, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'Description', type: 'text', length: 65535, nullable: false)]
    private string $description;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\OneToMany(targetEntity: RightVolunteer::class, mappedBy: 'right')]
    private Collection $rightVolunteers;

    public function __construct()
    {
        $this->rightVolunteers = new ArrayCollection();
    }

    public function getRightVolunteers(): Collection
    {
        return $this->rightVolunteers;
    }

    public function setRightVolunteers(Collection $rightVolunteers): self
    {
        $this->rightVolunteers = $rightVolunteers;

        return $this;
    }

    public function setCreated(\DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): \DateTime
    {
        return $this->created;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function addRightVolunteer(RightVolunteer $rightVolunteer): self
    {
        $this->rightVolunteers[] = $rightVolunteer;

        return $this;
    }

    public function removeRightVolunteer(RightVolunteer $rightVolunteer): self
    {
        $this->rightVolunteers->removeElement($rightVolunteer);

        return $this;
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created = new \DateTime('now');
    }
}
