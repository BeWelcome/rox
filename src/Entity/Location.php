<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
*/

namespace App\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Translatable\Translatable;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'geo__names')]
#[ORM\Index(name: 'geonames_idx_name', columns: ['name'])]
#[ORM\Index(name: 'geonames_idx_latitude', columns: ['latitude'])]
#[ORM\Index(name: 'geonames_idx_longitude', columns: ['longitude'])]
#[ORM\Index(name: 'geonames_idx_fclass', columns: ['feature_class'])]
#[ORM\Index(name: 'geonames_idx_fcode', columns: ['feature_code'])]
#[ORM\Index(name: 'geonames_idx_country', columns: ['country'])]
#[ORM\Index(name: 'geonames_idx_admin1', columns: ['admin1'])]
#[ORM\Index(name: 'geonames_idx_admin2', columns: ['admin2'])]
#[ORM\Index(name: 'geonames_idx_admin3', columns: ['admin3'])]
#[ORM\Index(name: 'geonames_idx_admin4', columns: ['admin4'])]
#[ORM\Index(name: 'geonames_idx_country_id', columns: ['country_id'])]
#[ORM\Index(name: 'geonames_idx_admin1_id', columns: ['admin_1_id'])]
#[ORM\Index(name: 'geonames_idx_admin2_id', columns: ['admin_2_id'])]
#[ORM\Index(name: 'geonames_idx_admin3_id', columns: ['admin_3_id'])]
#[ORM\Index(name: 'geonames_idx_admin4_id', columns: ['admin_4_id'])]
#[ORM\Index(name: 'geonames_idx_country_geoname_id', columns: ['country_id', 'geoname_id'])]
#[ORM\Index(name: 'geonames_idx_country_admin_1_and_2', columns: ['country_id', 'admin_1_id', 'admin_2_id'])]
#[ORM\Entity()]
#[Gedmo\TranslationEntity(class: LocationTranslation::class)]
class Location implements Translatable
{
    /**
     * @var string
     *
     * @Gedmo\Translatable
     */
    #[Gedmo\Translatable]
    #[ORM\Column(name: 'name', type: 'string', length: 200, nullable: true)]
    private $name;

    #[Gedmo\Locale]
    private $locale;

    /**
     * @var float
     */
    #[ORM\Column(name: 'latitude', type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private $latitude;

    /**
     * @var float
     */
    #[ORM\Column(name: 'longitude', type: 'decimal', precision: 10, scale: 7, nullable: true)]
    private $longitude;

    /**
     * @var string
     */
    #[ORM\Column(name: 'feature_class', type: 'string', length: 1, nullable: true)]
    private $featureClass;

    /**
     * @var string
     */
    #[ORM\Column(name: 'feature_code', type: 'string', length: 10, nullable: true)]
    private $featureCode;

    /**
     * @var string
     */
    #[ORM\Column(name: 'country_id', type: 'string', nullable: true)]
    private $countryId;

    /**
     * @var string
     */
    #[ORM\Column(name: 'admin_1_id', type: 'string', nullable: true)]
    private $admin1Id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'admin_2_id', type: 'string', nullable: true)]
    private $admin2Id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'admin_3_id', type: 'string', nullable: true)]
    private $admin3Id;

    /**
     * @var string
     */
    #[ORM\Column(name: 'admin_4_id', type: 'string', nullable: true)]
    private $admin4Id;

    /**
     * @var Location
     */
    #[ORM\JoinColumn(name: 'country', referencedColumnName: 'geoname_id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: self::class, fetch: 'LAZY')]
    private $country;

    /**
     * @var Location
     */
    #[ORM\JoinColumn(name: 'admin1', referencedColumnName: 'geoname_id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: self::class, fetch: 'LAZY')]
    private $admin1;

    /**
     * @var Location
     */
    #[ORM\JoinColumn(name: 'admin2', referencedColumnName: 'geoname_id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: self::class, fetch: 'EXTRA_LAZY')]
    private $admin2;

    /**
     * @var Location
     */
    #[ORM\JoinColumn(name: 'admin3', referencedColumnName: 'geoname_id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: self::class, fetch: 'EXTRA_LAZY')]
    private $admin3;

    /**
     * @var Location
     */
    #[ORM\JoinColumn(name: 'admin4', referencedColumnName: 'geoname_id', nullable: true)]
    #[ORM\ManyToOne(targetEntity: self::class, fetch: 'EXTRA_LAZY')]
    private $admin4;

    /**
     * @var int
     */
    #[ORM\Column(name: 'population', type: Types::BIGINT, nullable: true)]
    private $population;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'moddate', type: 'date', nullable: true)]
    private $modificationDate;

    /**
     * @var int
     */
    #[ORM\Column(name: 'geoname_id', type: 'integer')]
    #[ORM\Id]
    private $geonameId;

    #[ORM\OneToMany(targetEntity: LocationTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['locale' => 'ASC'])]
    private Collection $translations;

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

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLongitude($longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setFeatureClass(string $featureClass): self
    {
        $this->featureClass = $featureClass;

        return $this;
    }

    public function getFeatureClass(): string
    {
        return $this->featureClass;
    }

    public function setFeatureCode(string $featureCode): self
    {
        $this->featureCode = $featureCode;

        return $this;
    }

    public function getFeatureCode(): string
    {
        return $this->featureCode;
    }

    public function setCountry(?self $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCountry(): ?self
    {
        return $this->country;
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

    public function setModificationDate(DateTime $modificationDate): self
    {
        $this->modificationDate = $modificationDate;

        return $this;
    }

    public function getModificationDate(): DateTime
    {
        return $this->modificationDate;
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

    public function getAdmin1(): ?self
    {
        return $this->admin1;
    }

    public function setAdmin1(?self $admin1): self
    {
        $this->admin1 = $admin1;

        return $this;
    }

    public function getAdmin2(): ?self
    {
        return $this->admin2;
    }

    public function setAdmin2(?self $admin2): self
    {
        $this->admin2 = $admin2;

        return $this;
    }

    public function getAdmin3(): ?self
    {
        return $this->admin3;
    }

    public function setAdmin3(?self $admin3): self
    {
        $this->admin3 = $admin3;

        return $this;
    }

    public function getAdmin4(): ?self
    {
        return $this->admin4;
    }

    public function setAdmin4(?self $admin4): self
    {
        $this->admin4 = $admin4;

        return $this;
    }

    public function setAdmin1Id(?string $admin1Id): self
    {
        $this->admin1Id = $admin1Id;

        return $this;
    }

    public function getAdmin1Id(): ?string
    {
        return $this->admin1Id;
    }

    public function setAdmin2Id(?string $admin2Id): self
    {
        $this->admin2Id = $admin2Id;

        return $this;
    }

    public function getAdmin2Id(): ?string
    {
        return $this->admin2Id;
    }

    public function setAdmin3Id(?string $admin3Id): self
    {
        $this->admin3Id = $admin3Id;

        return $this;
    }

    public function getAdmin3Id(): ?string
    {
        return $this->admin3Id;
    }

    public function setAdmin4Id(?string $admin4Id): self
    {
        $this->admin4Id = $admin4Id;

        return $this;
    }

    public function getAdmin4Id(): ?string
    {
        return $this->admin4Id;
    }

    public function getFullname(): string
    {
        $nameOfAdmin1 = (null === $this->admin1) ? '' : ', ' . $this->getAdmin1()->getName();
        $nameOfCountry = (null === $this->country) ? '' : ', ' . $this->getCountry()->getName();

        return $this->getName() . $nameOfAdmin1 . (('' === $nameOfCountry) ? '' : ', ' . $this->getCountry()->getName());
    }

    public function getCountryId(): string
    {
        return $this->countryId;
    }

    public function setCountryId(string $countryId): self
    {
        $this->countryId = $countryId;

        return $this;
    }

    public function setTranslatableLocale($locale)
    {
        $this->locale = $locale;
    }

    public function getTranslations(): array
    {
        return $this->translations->toArray();
    }
}
