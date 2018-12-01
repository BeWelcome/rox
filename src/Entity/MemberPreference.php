<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Memberspreferences.
 *
 * @ORM\Table(name="memberspreferences", indexes={@ORM\Index(name="IdMember", columns={"IdMember", "IdPreference"}), @ORM\Index(name="IdPreference", columns={"IdPreference"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class MemberPreference
{
    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member", inversedBy="preferences")
     * @ORM\JoinColumn(name="IdMember", referencedColumnName="id", nullable=false)
     */
    protected $member;

    /**
     * @var Preference
     *
     * @ORM\OneToOne(targetEntity="Preference")
     * @ORM\JoinColumn(name="IdPreference", referencedColumnName="id")
     */
    private $preference;

    /**
     * @var string
     *
     * @ORM\Column(name="Value", type="text", length=65535, nullable=false)
     */
    private $value;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set member.
     *
     * @param Member $member
     *
     * @return MemberPreference
     */
    public function setMember(Member $member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get member.
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set preference.
     *
     * @param Preference $preference
     *
     * @return MemberPreference
     */
    public function setPreference(Preference $preference)
    {
        $this->preference = $preference;

        return $this;
    }

    /**
     * Get preference.
     *
     * @return Preference
     */
    public function getPreference()
    {
        return $this->preference;
    }

    /**
     * Set value.
     *
     * @param string $value
     *
     * @return MemberPreference
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value.
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return MemberPreference
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return MemberPreference
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
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
        $this->created = new \DateTime('now');
    }

    /**
     * Triggered on update.
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new \DateTime('now');
    }
}
