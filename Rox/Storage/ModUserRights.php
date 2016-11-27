<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModUserRights
 *
 * @ORM\Table(name="mod_user_rights", indexes={@ORM\Index(name="app_id", columns={"app_id"}), @ORM\Index(name="name", columns={"name"})})
 * @ORM\Entity
 */
class ModUserRights
{
    /**
     * @var integer
     *
     * @ORM\Column(name="app_id", type="integer", nullable=true)
     */
    private $appId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=75, nullable=false)
     */
    private $name;

    /**
     * @var integer
     *
     * @ORM\Column(name="has_implied", type="integer", nullable=false)
     */
    private $hasImplied = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="level", type="integer", nullable=false)
     */
    private $level = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set appId
     *
     * @param integer $appId
     *
     * @return ModUserRights
     */
    public function setAppId($appId)
    {
        $this->appId = $appId;

        return $this;
    }

    /**
     * Get appId
     *
     * @return integer
     */
    public function getAppId()
    {
        return $this->appId;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return ModUserRights
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set hasImplied
     *
     * @param integer $hasImplied
     *
     * @return ModUserRights
     */
    public function setHasImplied($hasImplied)
    {
        $this->hasImplied = $hasImplied;

        return $this;
    }

    /**
     * Get hasImplied
     *
     * @return integer
     */
    public function getHasImplied()
    {
        return $this->hasImplied;
    }

    /**
     * Set level
     *
     * @param integer $level
     *
     * @return ModUserRights
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level
     *
     * @return integer
     */
    public function getLevel()
    {
        return $this->level;
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
