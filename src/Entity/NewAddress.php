<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Entity\NewMember as Member;
use Carbon\Carbon;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'address')]
#[ORM\HasLifecycleCallbacks]
#[ORM\Entity]
class NewAddress
{
    #[ORM\ManyToOne(targetEntity: Member::class, inversedBy: 'addresses')]
    private Member $member;

    #[ORM\Column(name: 'HouseNumber', type: Types::STRING, nullable: true)]
    private ?string $houseNumber = null;

    #[ORM\Column(name: 'StreetName', type: Types::STRING, nullable: true)]
    private ?string $streetName;

    #[ORM\Column(name: 'Zip', type: Types::STRING, nullable: true)]
    private ?string $zip;

    #[ORM\ManyToOne(targetEntity: NewLocation::class)]
    #[ORM\JoinColumn(name: 'City', referencedColumnName: 'geonameId', nullable: false)]
    private NewLocation $city;

    #[ORM\Column(name: 'Latitude', type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $latitude;

    #[ORM\Column(name: 'Longitude', type: Types::DECIMAL, precision: 10, scale: 7, nullable: true)]
    private ?string $longitude;

    #[ORM\Column(name: 'Explanation', type: Types::STRING, nullable: true)]
    #[Gedmo\Translatable]
    private ?string $explanation;

    #[ORM\Column(name: 'active', type: Types::BOOLEAN, nullable: true)]
    private bool $active = true;

    #[ORM\Column(name: 'created', type: Types::DATETIME_MUTABLE, nullable: false)]
    private DateTime $created;

    #[ORM\Column(name: 'updated', type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?DateTime $updated = null;

    #[ORM\Column(name: 'GettingThere', type: Types::STRING, nullable: true)]
    #[Gedmo\Translatable]
    private ?string $gettingThere;

    #[ORM\Id]
    #[ORM\Column(name: 'id')]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private readonly int $id;

    public function setMember(Member $member): self
    {
        $this->member = $member;

        return $this;
    }

    public function getMember(): Member
    {
        return $this->member;
    }

    public function setHouseNumber(?string $houseNumber): self
    {
        $this->houseNumber = $houseNumber;

        return $this;
    }

    public function getHouseNumber(): ?string
    {
        return $this->houseNumber;
    }

    public function setStreetName(?string $streetName): self
    {
        $this->streetName = $streetName;

        return $this;
    }

    public function getStreetName(): ?string
    {
        return $this->streetName;
    }

    public function setZip(?string $zip): self
    {
        $this->zip = $zip;

        return $this;
    }

    public function getZip(): ?string
    {
        return $this->zip;
    }

    public function setCity(NewLocation $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCity(): NewLocation
    {
        return $this->city;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setExplanation(?string $explanation): self
    {
        $this->explanation = $explanation;

        return $this;
    }

    public function getExplanation(): ?string
    {
        return $this->explanation;
    }

    public function setActive(?string $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getActive(): ?string
    {
        return $this->active;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function getUpdated(): Carbon
    {
        return Carbon::instance($this->updated);
    }

    public function setGettingThere(?string $gettingThere): self
    {
        $this->gettingThere = $gettingThere;

        return $this;
    }

    public function getGettingThere(): ?string
    {
        return $this->gettingThere;
    }

    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Triggered on insert.
     */
    #[ORM\PrePersist]
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
    }

    /**
     * Triggered on update.
     */
    #[ORM\PreUpdate]
    public function onPreUpdate()
    {
        $this->updated = new DateTime('now');
    }
}
