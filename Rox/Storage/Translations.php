<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Translations
 *
 * @ORM\Table(name="translations", uniqueConstraints={@ORM\UniqueConstraint(name="unique_entry", columns={"IdTrad", "IdOwner", "IdLanguage"})}, indexes={@ORM\Index(name="IdTrad", columns={"IdTrad"})})
 * @ORM\Entity
 */
class Translations
{
    /**
     * @var integer
     *
     * @ORM\Column(name="IdLanguage", type="integer", nullable=false)
     */
    private $idlanguage;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdOwner", type="integer", nullable=false)
     */
    private $idowner;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdTrad", type="integer", nullable=false)
     */
    private $idtrad;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdTranslator", type="integer", nullable=false)
     */
    private $idtranslator;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = '0000-00-00 00:00:00';

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="Sentence", type="text", length=65535, nullable=false)
     */
    private $sentence;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdRecord", type="integer", nullable=false)
     */
    private $idrecord;

    /**
     * @var string
     *
     * @ORM\Column(name="TableColumn", type="string", length=200, nullable=false)
     */
    private $tablecolumn = 'NotSet';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set idlanguage
     *
     * @param integer $idlanguage
     *
     * @return Translations
     */
    public function setIdlanguage($idlanguage)
    {
        $this->idlanguage = $idlanguage;

        return $this;
    }

    /**
     * Get idlanguage
     *
     * @return integer
     */
    public function getIdlanguage()
    {
        return $this->idlanguage;
    }

    /**
     * Set idowner
     *
     * @param integer $idowner
     *
     * @return Translations
     */
    public function setIdowner($idowner)
    {
        $this->idowner = $idowner;

        return $this;
    }

    /**
     * Get idowner
     *
     * @return integer
     */
    public function getIdowner()
    {
        return $this->idowner;
    }

    /**
     * Set idtrad
     *
     * @param integer $idtrad
     *
     * @return Translations
     */
    public function setIdtrad($idtrad)
    {
        $this->idtrad = $idtrad;

        return $this;
    }

    /**
     * Get idtrad
     *
     * @return integer
     */
    public function getIdtrad()
    {
        return $this->idtrad;
    }

    /**
     * Set idtranslator
     *
     * @param integer $idtranslator
     *
     * @return Translations
     */
    public function setIdtranslator($idtranslator)
    {
        $this->idtranslator = $idtranslator;

        return $this;
    }

    /**
     * Get idtranslator
     *
     * @return integer
     */
    public function getIdtranslator()
    {
        return $this->idtranslator;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Translations
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Translations
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
     * Set type
     *
     * @param string $type
     *
     * @return Translations
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set sentence
     *
     * @param string $sentence
     *
     * @return Translations
     */
    public function setSentence($sentence)
    {
        $this->sentence = $sentence;

        return $this;
    }

    /**
     * Get sentence
     *
     * @return string
     */
    public function getSentence()
    {
        return $this->sentence;
    }

    /**
     * Set idrecord
     *
     * @param integer $idrecord
     *
     * @return Translations
     */
    public function setIdrecord($idrecord)
    {
        $this->idrecord = $idrecord;

        return $this;
    }

    /**
     * Get idrecord
     *
     * @return integer
     */
    public function getIdrecord()
    {
        return $this->idrecord;
    }

    /**
     * Set tablecolumn
     *
     * @param string $tablecolumn
     *
     * @return Translations
     */
    public function setTablecolumn($tablecolumn)
    {
        $this->tablecolumn = $tablecolumn;

        return $this;
    }

    /**
     * Get tablecolumn
     *
     * @return string
     */
    public function getTablecolumn()
    {
        return $this->tablecolumn;
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
