<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Preferences
 *
 * @ORM\Table(name="preferences", uniqueConstraints={@ORM\UniqueConstraint(name="codeName", columns={"codeName"})})
 * @ORM\Entity
 */
class Preferences
{
    /**
     * @var integer
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="codeName", type="string", length=30, nullable=false)
     */
    private $codename;

    /**
     * @var string
     *
     * @ORM\Column(name="codeDescription", type="string", length=30, nullable=false)
     */
    private $codedescription;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="DefaultValue", type="text", length=255, nullable=false)
     */
    private $defaultvalue;

    /**
     * @var string
     *
     * @ORM\Column(name="PossibleValues", type="text", length=255, nullable=false)
     */
    private $possiblevalues;

    /**
     * @var string
     *
     * @ORM\Column(name="EvalString", type="text", length=65535, nullable=false)
     */
    private $evalstring;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'Inactive';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set position
     *
     * @param integer $position
     *
     * @return Preferences
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Set codename
     *
     * @param string $codename
     *
     * @return Preferences
     */
    public function setCodename($codename)
    {
        $this->codename = $codename;

        return $this;
    }

    /**
     * Get codename
     *
     * @return string
     */
    public function getCodename()
    {
        return $this->codename;
    }

    /**
     * Set codedescription
     *
     * @param string $codedescription
     *
     * @return Preferences
     */
    public function setCodedescription($codedescription)
    {
        $this->codedescription = $codedescription;

        return $this;
    }

    /**
     * Get codedescription
     *
     * @return string
     */
    public function getCodedescription()
    {
        return $this->codedescription;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Preferences
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
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Preferences
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
     * Set defaultvalue
     *
     * @param string $defaultvalue
     *
     * @return Preferences
     */
    public function setDefaultvalue($defaultvalue)
    {
        $this->defaultvalue = $defaultvalue;

        return $this;
    }

    /**
     * Get defaultvalue
     *
     * @return string
     */
    public function getDefaultvalue()
    {
        return $this->defaultvalue;
    }

    /**
     * Set possiblevalues
     *
     * @param string $possiblevalues
     *
     * @return Preferences
     */
    public function setPossiblevalues($possiblevalues)
    {
        $this->possiblevalues = $possiblevalues;

        return $this;
    }

    /**
     * Get possiblevalues
     *
     * @return string
     */
    public function getPossiblevalues()
    {
        return $this->possiblevalues;
    }

    /**
     * Set evalstring
     *
     * @param string $evalstring
     *
     * @return Preferences
     */
    public function setEvalstring($evalstring)
    {
        $this->evalstring = $evalstring;

        return $this;
    }

    /**
     * Get evalstring
     *
     * @return string
     */
    public function getEvalstring()
    {
        return $this->evalstring;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Preferences
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
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
