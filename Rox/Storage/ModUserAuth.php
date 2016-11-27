<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ModUserAuth
 *
 * @ORM\Table(name="mod_user_auth", indexes={@ORM\Index(name="name", columns={"name"})})
 * @ORM\Entity
 */
class ModUserAuth
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=75, nullable=false)
     */
    private $name = '';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set name
     *
     * @param string $name
     *
     * @return ModUserAuth
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
}
