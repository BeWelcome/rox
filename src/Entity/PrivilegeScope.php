<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Privilege scopes.
 *
 * @ORM\Table(name="privilegescopes")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class PrivilegeScope
{
    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var Member
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Member")
     * @ORM\JoinColumn(name="IdMember", referencedColumnName="id")
     */
    private $member;

    /**
     * @var Role
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\OneToOne(targetEntity="Role")
     * @ORM\JoinColumn(name="IdRole", referencedColumnName="id")
     */
    private $role;

    /**
     * @var Privilege
     *
     * @ORM\Id
     * @ORM\OneToOne(targetEntity="Privilege")
     * @ORM\JoinColumn(name="IdPrivilege", referencedColumnName="id")
     */
    private $privilege;

    /**
     * @var string
     *
     * @ORM\Column(name="IdType", type="string", length=32)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $type;

    /**
     * Set updated.
     *
     * @param DateTime $updated
     *
     * @return PrivilegeScope
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set idmember.
     *
     * @param int $member
     *
     * @return PrivilegeScope
     */
    public function setMember($member)
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
     * Set role.
     *
     * @param Role $role
     *
     * @return PrivilegeScope
     */
    public function setRole($role)
    {
        $this->role = $role;

        return $this;
    }

    /**
     * Get role.
     *
     * @return Role
     */
    public function getRole()
    {
        return $this->role;
    }

    /**
     * Set privilege.
     *
     * @param Privilege $privilege
     *
     * @return PrivilegeScope
     */
    public function setPrivilege($privilege)
    {
        $this->privilege = $privilege;

        return $this;
    }

    /**
     * Get privilege.
     *
     * @return Privilege
     */
    public function getPrivilege()
    {
        return $this->privilege;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return PrivilegeScope
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Triggered on update.
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new DateTime('now');
    }
}
