<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Hosting Eagerness Slider.
 *
 * @ORM\Table(name="hosting_eagerness_slider")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class HostingEagernessSlider
{
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
    private $initialized;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member", inversedBy="groupMemberships")
     * @ORM\JoinColumn(name="member_id", referencedColumnName="id", nullable=FALSE)
     */
    private $member;

    /**
     * @var int
     *
     * @ORM\Column(name="current", type="integer", nullable=false)
     */
    private $step;

    /**
     * @var int
     *
     * @ORM\Column(name="step", type="integer", nullable=false)
     */
    private $current;

    /**
     * @var int
     *
     * @ORM\Column(name="remaining", type="integer", nullable=false)
     */
    private $remaining;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function __construct()
    {
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->initialized = new \DateTime('now');
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

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getInitialized(): ?\DateTimeInterface
    {
        return $this->initialized;
    }

    public function setInitialized(\DateTimeInterface $initialized): self
    {
        $this->initialized = $initialized;

        return $this;
    }

    public function getStep(): ?int
    {
        return $this->step;
    }

    public function setStep(int $step): self
    {
        $this->step = $step;

        return $this;
    }

    public function getCurrent(): ?int
    {
        return $this->current;
    }

    public function setCurrent(int $current): self
    {
        $this->current = $current;

        return $this;
    }

    public function getRemaining(): ?int
    {
        return $this->remaining;
    }

    public function setRemaining(int $remaining): self
    {
        $this->remaining = $remaining;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getMember(): ?Member
    {
        return $this->member;
    }

    public function setMember(?Member $member): self
    {
        $this->member = $member;

        return $this;
    }
}
