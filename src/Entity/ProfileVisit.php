<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Do not check entities with PHPMD
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'profilesvisits')]
#[ORM\Entity(repositoryClass: \App\Repository\ProfileVisitRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ProfileVisit
{
    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private $created;

    /**
     * @var DateTime
     */
    #[ORM\Column(name: 'updated', type: 'datetime', nullable: false)]
    private $updated;

    public function __construct(
        #[ORM\JoinColumn(name: 'IdMember', referencedColumnName: 'id')]
        #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: \Member::class)]
        private Member $member,
        #[ORM\JoinColumn(name: 'IdVisitor', referencedColumnName: 'id')]
        #[ORM\Id]
        #[ORM\ManyToOne(targetEntity: \Member::class)]
        private Member $visitor
    )
    {
    }

    /**
     * Set created.
     *
     * @param DateTime $created
     */
    public function setCreated($created): self
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
     * Set updated.
     *
     * @param DateTime $updated
     *
     * @return ProfileVisit
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return Carbon
     */
    public function getUpdated()
    {
        return Carbon::instance($this->updated);
    }

    /**
     * Get visited.
     */
    public function getVisited(): Carbon
    {
        return Carbon::instance($this->updated);
    }

    /**
     * Set member.
     *
     * @param Member $member
     *
     * @return ProfileVisit
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * Get idmember.
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set visitor.
     *
     * @param Member $visitor
     *
     * @return ProfileVisit
     */
    public function setVisitor($visitor)
    {
        $this->visitor = $visitor;

        return $this;
    }

    /**
     * Get visitor.
     *
     * @return Member
     */
    public function getVisitor()
    {
        return $this->visitor;
    }

    /**
     * Triggered on insert.
     */
    #[ORM\PrePersist]
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
        $this->updated = $this->created;
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
