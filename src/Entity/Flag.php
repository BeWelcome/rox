<?php

namespace App\Entity;

use App\Utilities\LifecycleCallbacksTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * Flags
 *
 * @ORM\Table(name="flags")
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 */
class Flag
{
    use LifecycleCallbacksTrait;

    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="text", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var integer
     *
     * @ORM\Column(name="Relevance", type="integer", nullable=false)
     */
    private $relevance;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Flag
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return Flag
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
     * Set description
     *
     * @param string $description
     *
     * @return Flag
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set relevance
     *
     * @param integer $relevance
     *
     * @return Flag
     */
    public function setRelevance($relevance)
    {
        $this->relevance = $relevance;

        return $this;
    }

    /**
     * Get relevance
     *
     * @return integer
     */
    public function getRelevance()
    {
        return $this->relevance;
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
