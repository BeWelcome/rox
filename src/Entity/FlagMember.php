<?php

namespace App\Entity;

use App\Utilities\LifecycleCallbacksTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Flagsmembers.
 *
 * @ORM\Table(name="flagsmembers", indexes={@ORM\Index(name="IdMember", columns={"IdMember", "IdFlag"})})
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class FlagMember
{
    use LifecycleCallbacksTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var int
     *
     * @ORM\Column(name="IdFlag", type="integer", nullable=false)
     */
    private $idflag;

    /**
     * @var int
     *
     * @ORM\Column(name="Level", type="integer", nullable=false)
     */
    private $level = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="Scope", type="text", length=255, nullable=false)
     */
    private $scope;

    /**
     * @var string
     *
     * @ORM\Column(name="Comment", type="text", length=65535, nullable=false)
     */
    private $comment;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set idmember.
     *
     * @param int $idmember
     *
     * @return FlagMember
     */
    public function setIdmember($idmember)
    {
        $this->idmember = $idmember;

        return $this;
    }

    /**
     * Get idmember.
     *
     * @return int
     */
    public function getIdmember()
    {
        return $this->idmember;
    }

    /**
     * Set idflag.
     *
     * @param int $idflag
     *
     * @return FlagMember
     */
    public function setIdflag($idflag)
    {
        $this->idflag = $idflag;

        return $this;
    }

    /**
     * Get idflag.
     *
     * @return int
     */
    public function getIdflag()
    {
        return $this->idflag;
    }

    /**
     * Set level.
     *
     * @param int $level
     *
     * @return FlagMember
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level.
     *
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * Set scope.
     *
     * @param string $scope
     *
     * @return FlagMember
     */
    public function setScope($scope)
    {
        $this->scope = $scope;

        return $this;
    }

    /**
     * Get scope.
     *
     * @return string
     */
    public function getScope()
    {
        return $this->scope;
    }

    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return FlagMember
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return FlagMember
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
     * @return FlagMember
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
}
