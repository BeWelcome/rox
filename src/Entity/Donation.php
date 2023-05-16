<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Entity\Country as Country;
use App\Entity\Member as Member;
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
    private $statusprivate = 'showamountonly';

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
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country")
     * @ORM\JoinColumn(name="IdCountry", referencedColumnName="country")
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="namegiven", type="text", length=65535, nullable=false)
     */
    private $namegiven;

    /**
     * @var string
     *
     * @ORM\Column(name="referencepaypal", type="text", length=65535, nullable=false)
     */
    private $referencepaypal;

    /**
     * @var string
     *
     * @ORM\Column(name="membercomment", type="text", length=65535, nullable=false)
     */
    private $membercomment;

    /**
     * @var string
     *
     * @ORM\Column(name="SystemComment", type="text", length=65535, nullable=false)
     */
    private $systemcomment;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set donor.
     *
     * @param Member $donor
     *
     * @return Donation
     */
    public function setDonor($donor)
    {
        $this->donor = $donor;

        return $this;
    }

    /**
     * Get donor.
     *
     * @return Member
     */
    public function getDonor()
    {
        return $this->donor;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return Donation
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set statusprivate.
     *
     * @param string $statusprivate
     *
     * @return Donation
     */
    public function setStatusprivate($statusprivate)
    {
        $this->statusprivate = $statusprivate;

        return $this;
    }

    /**
     * Get statusprivate.
     *
     * @return string
     */
    public function getStatusprivate()
    {
        return $this->statusprivate;
    }

    /**
     * Set created.
     *
     * @param DateTime $created
     *
     * @return Donation
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return Carbon
     */
    public function getCreated()
    {
        return Carbon::instance($this->created);
    }

    /**
     * Set amount.
     *
     * @param string $amount
     *
     * @return Donation
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount.
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set money.
     *
     * @param string $money
     *
     * @return Donation
     */
    public function setMoney($money)
    {
        $this->money = $money;

        return $this;
    }

    /**
     * Get money.
     *
     * @return string
     */
    public function getMoney()
    {
        return $this->money;
    }

    /**
     * Set country.
     *
     * @param Country $country
     *
     * @return Donation
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country.
     *
     * @return Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set namegiven.
     *
     * @param string $namegiven
     *
     * @return Donation
     */
    public function setNamegiven($namegiven)
    {
        $this->namegiven = $namegiven;

        return $this;
    }

    /**
     * Get namegiven.
     *
     * @return string
     */
    public function getNamegiven()
    {
        return $this->namegiven;
    }

    /**
     * Set referencepaypal.
     *
     * @param string $referencepaypal
     *
     * @return Donation
     */
    public function setReferencepaypal($referencepaypal)
    {
        $this->referencepaypal = $referencepaypal;

        return $this;
    }

    /**
     * Get referencepaypal.
     *
     * @return string
     */
    public function getReferencepaypal()
    {
        return $this->referencepaypal;
    }

    /**
     * Set membercomment.
     *
     * @param string $membercomment
     *
     * @return Donation
     */
    public function setMembercomment($membercomment)
    {
        $this->membercomment = $membercomment;

        return $this;
    }

    /**
     * Get membercomment.
     *
     * @return string
     */
    public function getMembercomment()
    {
        return $this->membercomment;
    }

    /**
     * Set systemcomment.
     *
     * @param string $systemcomment
     *
     * @return Donation
     */
    public function setSystemcomment($systemcomment)
    {
        $this->systemcomment = $systemcomment;

        return $this;
    }

    /**
     * Get systemcomment.
     *
     * @return string
     */
    public function getSystemcomment()
    {
        return $this->systemcomment;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
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
