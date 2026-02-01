<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
*/

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'geo__countries')]
#[ORM\Entity]
class Country
{
    #[ORM\JoinColumn(name: 'country', referencedColumnName: 'geoname_id', nullable: false)]
    #[ORM\ManyToOne(targetEntity: Location::class, fetch: 'EAGER')]
    private Location $country;

    #[ORM\Column(name: 'continent', type: 'string', length: 2, nullable: false)]
    private string $continent;

    #[ORM\Column(name: 'country_id', type: 'string', length: 2)]
    #[ORM\Id]
    private string $countryId;

    public function setCountry(Location $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCountry(): Location
    {
        return $this->country;
    }

    public function setContinent($continent): self
    {
        $this->continent = $continent;

        return $this;
    }

    public function getContinent(): string
    {
        return $this->continent;
    }

    public function getCountryId(): string
    {
        return $this->country;
    }

    public function setCountryId($countryId): self
    {
        $this->countryId = $countryId;

        return $this;
    }
}
