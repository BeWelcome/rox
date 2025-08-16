<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
*/

namespace App\Entity;

use App\Repository\LocationRepository;
use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * Location.
 *
 * @SuppressWarnings("PHPMD")
 * Auto generated class do not check mess
 */
#[ORM\Table(name: 'geonames')]
#[ORM\Index(name: 'geonames_idx_name', columns: ['name'])]
#[ORM\Index(name: 'geonames_idx_latitude', columns: ['latitude'])]
#[ORM\Index(name: 'geonames_idx_longitude', columns: ['longitude'])]
#[ORM\Index(name: 'geonames_idx_fclass', columns: ['fclass'])]
#[ORM\Index(name: 'geonames_idx_fcode', columns: ['fcode'])]
#[ORM\Index(name: 'geonames_idx_country', columns: ['country'])]
#[ORM\Index(name: 'geonames_idx_admin1', columns: ['admin1'])]
#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Column(name: 'name', type: 'string', length: 200, nullable: false)]
    private string $name;

    #[ORM\Column(name: 'latitude', type: 'decimal', precision: 10, scale: 7, nullable: false)]
    private float $latitude;

    #[ORM\Column(name: 'longitude', type: 'decimal', precision: 10, scale: 7, nullable: false)]
    private float $longitude;

    #[ORM\Column(name: 'fclass', type: 'string', length: 1, nullable: false)]
    private string $fclass;

    #[ORM\Column(name: 'fcode', type: 'string', length: 10, nullable: false)]
    private string $fcode;

    #[ORM\JoinColumn(name: 'country', referencedColumnName: 'country')]
    #[ORM\ManyToOne(targetEntity: Country::class)]
    private Country $country;

    #[ORM\Column(name: 'admin1', type: 'string', length: 20, nullable: true)]
    private ?string $admin1;

    #[ORM\Column(name: 'population', type: 'integer', nullable: false)]
    private int $population = 0;

    #[ORM\Column(name: 'moddate', type: 'date', nullable: true)]
    private ?\DateTime $moddate;

    #[ORM\Column(name: 'geonameId', type: 'integer')]
    #[ORM\Id]
    private int $geonameId;

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setLatitude(float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLatitude(): float
    {
        return $this->latitude;
    }

    public function setLongitude(float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLongitude(): float
    {
        return $this->longitude;
    }

    public function setFclass(string $fclass): self
    {
        $this->fclass = $fclass;

        return $this;
    }

    public function getFclass(): string
    {
        return $this->fclass;
    }

    public function setFcode(string $fcode): self
    {
        $this->fcode = $fcode;

        return $this;
    }

    public function getFcode(): string
    {
        return $this->fcode;
    }

    public function setCountry(Country $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCountry(): Country
    {
        return $this->country;
    }

    public function setAdmin1(?string $admin1): self
    {
        $this->admin1 = $admin1;

        return $this;
    }

    public function getAdmin1(): ?string
    {
        return $this->admin1;
    }

    public function setPopulation(int $population): self
    {
        $this->population = $population;

        return $this;
    }

    public function getPopulation(): int
    {
        return $this->population;
    }

    public function setModdate(\DateTime $moddate): self
    {
        $this->moddate = $moddate;

        return $this;
    }

    public function getModdate(): ?Carbon
    {
        return Carbon::make($this->moddate);
    }

    public function getGeonameId(): int
    {
        return $this->geonameId;
    }

    public function setGeonameId(int $geonameId): self
    {
        $this->geonameId = $geonameId;

        return $this;
    }

    public function getFullname(): string
    {
        return $this->getName() . ', ' . $this->getCountry()->getName();
    }
}
