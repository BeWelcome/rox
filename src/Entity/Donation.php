<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * DonationEntity.
 *
 * @ORM\Table(name="donations")
 * @ORM\Entity(repositoryClass="App\Repository\DonationRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Donation
{
    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumn(name="IdMember", referencedColumnName="id")
     */
    private $donor;

    /**
     * @var string
     *
     * @ORM\Column(name="Email", type="text", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="StatusPrivate", type="string", nullable=false)
     */
    private $statusPrivate = 'showamountonly';

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="Amount", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $amount;

    /**
     * @var string
     *
     * @ORM\Column(name="Money", type="string", length=10, nullable=false)
     */
    private $money;

    /**
     * @var NewLocation
     *
     * @ORM\ManyToOne(targetEntity="NewLocation")
     * @ORM\JoinColumn(name="IdCountry", referencedColumnName="geonameId")
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="namegiven", type="text", length=65535, nullable=false)
     */
    private $nameGiven;

    /**
     * @var string
     *
     * @ORM\Column(name="referencepaypal", type="text", length=65535, nullable=false)
     */
    private $referencePaypal;

    /**
     * @var string
     *
     * @ORM\Column(name="membercomment", type="text", length=65535, nullable=false)
     */
    private $memberComment;

    /**
     * @var string
     *
     * @ORM\Column(name="SystemComment", type="text", length=65535, nullable=false)
     */
    private $systemComment;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function setDonor($donor): self
    {
        $this->donor = $donor;

        return $this;
    }

    public function getDonor(): ?Member
    {
        return $this->donor;
    }

    public function setEmail($email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setStatusPrivate($statusPrivate): self
    {
        $this->statusPrivate = $statusPrivate;

        return $this;
    }

    public function getStatusPrivate(): string
    {
        return $this->statusPrivate;
    }

    public function setCreated($created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function setMoney(string $money): self
    {
        $this->money = $money;

        return $this;
    }

    public function getMoney(): string
    {
        return $this->money;
    }

    public function setCountry($country): self
    {
        $this->country = $country;

        return $this;
    }

    public function getCountry(): ?NewLocation
    {
        return $this->country;
    }

    public function setNameGiven($nameGiven): self
    {
        $this->nameGiven = $nameGiven;

        return $this;
    }

    public function getNameGiven(): string
    {
        return $this->nameGiven;
    }

    public function setReferencePaypal($referencePaypal): self
    {
        $this->referencePaypal = $referencePaypal;

        return $this;
    }

    public function getReferencePaypal(): string
    {
        return $this->referencePaypal;
    }

    public function setMemberComment($memberComment): self
    {
        $this->memberComment = $memberComment;

        return $this;
    }

    public function getMemberComment(): string
    {
        return $this->memberComment;
    }

    public function setSystemComment($systemComment): self
    {
        $this->systemComment = $systemComment;

        return $this;
    }

    public function getSystemComment(): string
    {
        return $this->systemComment;
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
