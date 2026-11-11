<?php

namespace App\Entity;

use App\Repository\DonationRepository;
use Carbon\Carbon;
use DateTime;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

/**
 * A donation: made through PayPal on the donation page, or added by the treasurer (bank transfers, HelloAsso, ...).
 * The donor is a member, a name or unknown. Version20261111100000 filled the table from main's `donations`, the ids
 * kept.
 */
#[ORM\Table(name: 'donation')]
#[ORM\Index(name: 'donation_created_idx', columns: ['created'])]
#[ORM\Entity(repositoryClass: DonationRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Donation
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Member::class)]
    #[ORM\JoinColumn(onDelete: 'SET NULL')]
    private ?Member $donor = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $email = '';

    /** showamountonly: the public list shows the amount, not the donor */
    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $statusPrivate = 'showamountonly';

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?DateTimeInterface $created = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $amount = null;

    /** The currency as given: EUR, € or $ (older donations), or empty */
    #[ORM\Column(type: Types::STRING, length: 10)]
    private string $money = 'EUR';

    #[ORM\ManyToOne(targetEntity: Location::class)]
    #[ORM\JoinColumn(referencedColumnName: 'geoname_id')]
    private ?Location $country = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $nameGiven = '';

    #[ORM\Column(type: Types::STRING, length: 255)]
    private string $referencePaypal = '';

    #[ORM\Column(type: Types::TEXT)]
    private string $memberComment = '';

    /** Shown in the public list of donations */
    #[ORM\Column(type: Types::TEXT)]
    private string $systemComment = '';

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDonor(): ?Member
    {
        return $this->donor;
    }

    public function setDonor(?Member $donor): self
    {
        $this->donor = $donor;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getStatusPrivate(): string
    {
        return $this->statusPrivate;
    }

    public function setStatusPrivate(string $statusPrivate): self
    {
        $this->statusPrivate = $statusPrivate;

        return $this;
    }

    public function getCreated(): ?Carbon
    {
        return null === $this->created ? null : Carbon::instance($this->created);
    }

    public function setCreated(?DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getMoney(): string
    {
        return $this->money;
    }

    public function setMoney(string $money): self
    {
        $this->money = $money;

        return $this;
    }

    public function getCountry(): ?Location
    {
        return $this->country;
    }

    public function setCountry(?Location $country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getNameGiven(): string
    {
        return $this->nameGiven;
    }

    public function setNameGiven(string $nameGiven): self
    {
        $this->nameGiven = $nameGiven;

        return $this;
    }

    public function getReferencePaypal(): string
    {
        return $this->referencePaypal;
    }

    public function setReferencePaypal(string $referencePaypal): self
    {
        $this->referencePaypal = $referencePaypal;

        return $this;
    }

    public function getMemberComment(): string
    {
        return $this->memberComment;
    }

    public function setMemberComment(string $memberComment): self
    {
        $this->memberComment = $memberComment;

        return $this;
    }

    public function getSystemComment(): string
    {
        return $this->systemComment;
    }

    public function setSystemComment(string $systemComment): self
    {
        $this->systemComment = $systemComment;

        return $this;
    }

    /**
     * A donation added by the treasurer keeps the day they entered; one through PayPal gets the time it came in.
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->created ??= new DateTime();
    }
}
