<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * MembersSessions
 *
 * @ORM\Table(name="members_sessions")
 * @ORM\Entity
 */
class MembersSessions
{
    /**
     * @var string
     *
     * @ORM\Column(name="SeriesToken", type="string", length=32, nullable=true)
     */
    private $seriestoken;

    /**
     * @var string
     *
     * @ORM\Column(name="AuthToken", type="string", length=32, nullable=true)
     */
    private $authtoken;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="modified", type="datetime", nullable=false)
     */
    private $modified = 'CURRENT_TIMESTAMP';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idmember;



    /**
     * Set seriestoken
     *
     * @param string $seriestoken
     *
     * @return MembersSessions
     */
    public function setSeriestoken($seriestoken)
    {
        $this->seriestoken = $seriestoken;

        return $this;
    }

    /**
     * Get seriestoken
     *
     * @return string
     */
    public function getSeriestoken()
    {
        return $this->seriestoken;
    }

    /**
     * Set authtoken
     *
     * @param string $authtoken
     *
     * @return MembersSessions
     */
    public function setAuthtoken($authtoken)
    {
        $this->authtoken = $authtoken;

        return $this;
    }

    /**
     * Get authtoken
     *
     * @return string
     */
    public function getAuthtoken()
    {
        return $this->authtoken;
    }

    /**
     * Set modified
     *
     * @param \DateTime $modified
     *
     * @return MembersSessions
     */
    public function setModified($modified)
    {
        $this->modified = $modified;

        return $this;
    }

    /**
     * Get modified
     *
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
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
}
