<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Donations
 *
 * @ORM\Table(name="donations", indexes={@ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\Entity
 */
class Donations
{
    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember = '0';

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
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = 'CURRENT_TIMESTAMP';

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
     * @var integer
     *
     * @ORM\Column(name="IdCountry", type="integer", nullable=false)
     */
    private $idcountry;

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
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return Donations
     */
    public function setIdmember($idmember)
    {
        $this->idmember = $idmember;

        return $this;
    }

    /**
     * Get idmember
     *
     * @return integer
     */
    public function getIdmember()
    {
        return $this->idmember;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return Donations
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set statusprivate
     *
     * @param string $statusprivate
     *
     * @return Donations
     */
    public function setStatusprivate($statusprivate)
    {
        $this->statusprivate = $statusprivate;

        return $this;
    }

    /**
     * Get statusprivate
     *
     * @return string
     */
    public function getStatusprivate()
    {
        return $this->statusprivate;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Donations
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set amount
     *
     * @param string $amount
     *
     * @return Donations
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set money
     *
     * @param string $money
     *
     * @return Donations
     */
    public function setMoney($money)
    {
        $this->money = $money;

        return $this;
    }

    /**
     * Get money
     *
     * @return string
     */
    public function getMoney()
    {
        return $this->money;
    }

    /**
     * Set idcountry
     *
     * @param integer $idcountry
     *
     * @return Donations
     */
    public function setIdcountry($idcountry)
    {
        $this->idcountry = $idcountry;

        return $this;
    }

    /**
     * Get idcountry
     *
     * @return integer
     */
    public function getIdcountry()
    {
        return $this->idcountry;
    }

    /**
     * Set namegiven
     *
     * @param string $namegiven
     *
     * @return Donations
     */
    public function setNamegiven($namegiven)
    {
        $this->namegiven = $namegiven;

        return $this;
    }

    /**
     * Get namegiven
     *
     * @return string
     */
    public function getNamegiven()
    {
        return $this->namegiven;
    }

    /**
     * Set referencepaypal
     *
     * @param string $referencepaypal
     *
     * @return Donations
     */
    public function setReferencepaypal($referencepaypal)
    {
        $this->referencepaypal = $referencepaypal;

        return $this;
    }

    /**
     * Get referencepaypal
     *
     * @return string
     */
    public function getReferencepaypal()
    {
        return $this->referencepaypal;
    }

    /**
     * Set membercomment
     *
     * @param string $membercomment
     *
     * @return Donations
     */
    public function setMembercomment($membercomment)
    {
        $this->membercomment = $membercomment;

        return $this;
    }

    /**
     * Get membercomment
     *
     * @return string
     */
    public function getMembercomment()
    {
        return $this->membercomment;
    }

    /**
     * Set systemcomment
     *
     * @param string $systemcomment
     *
     * @return Donations
     */
    public function setSystemcomment($systemcomment)
    {
        $this->systemcomment = $systemcomment;

        return $this;
    }

    /**
     * Get systemcomment
     *
     * @return string
     */
    public function getSystemcomment()
    {
        return $this->systemcomment;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
