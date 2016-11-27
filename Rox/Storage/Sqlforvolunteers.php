<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Sqlforvolunteers
 *
 * @ORM\Table(name="sqlforvolunteers")
 * @ORM\Entity
 */
class Sqlforvolunteers
{
    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="text", length=65535, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="Query", type="text", length=65535, nullable=false)
     */
    private $query;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="param1", type="text", length=65535, nullable=true)
     */
    private $param1;

    /**
     * @var string
     *
     * @ORM\Column(name="param2", type="text", length=65535, nullable=true)
     */
    private $param2;

    /**
     * @var string
     *
     * @ORM\Column(name="LogMe", type="string", nullable=false)
     */
    private $logme = 'False';

    /**
     * @var string
     *
     * @ORM\Column(name="DefValueParam1", type="text", length=65535, nullable=false)
     */
    private $defvalueparam1;

    /**
     * @var string
     *
     * @ORM\Column(name="DefValueParam2", type="text", length=65535, nullable=false)
     */
    private $defvalueparam2;

    /**
     * @var string
     *
     * @ORM\Column(name="Param1Type", type="string", nullable=false)
     */
    private $param1type = 'inputtext';

    /**
     * @var string
     *
     * @ORM\Column(name="Param2Type", type="string", nullable=false)
     */
    private $param2type = 'inputtext';

    /**
     * @var string
     *
     * @ORM\Column(name="param3", type="text", length=65535, nullable=false)
     */
    private $param3;

    /**
     * @var string
     *
     * @ORM\Column(name="DefValueParam3", type="text", length=65535, nullable=false)
     */
    private $defvalueparam3;

    /**
     * @var string
     *
     * @ORM\Column(name="Param3Type", type="string", nullable=false)
     */
    private $param3type = 'inputtext';

    /**
     * @var string
     *
     * @ORM\Column(name="param4", type="text", length=65535, nullable=false)
     */
    private $param4;

    /**
     * @var string
     *
     * @ORM\Column(name="DefValueParam4", type="text", length=65535, nullable=false)
     */
    private $defvalueparam4;

    /**
     * @var string
     *
     * @ORM\Column(name="Param4Type", type="string", nullable=false)
     */
    private $param4type = 'inputtext';

    /**
     * @var string
     *
     * @ORM\Column(name="param5", type="text", length=65535, nullable=false)
     */
    private $param5;

    /**
     * @var string
     *
     * @ORM\Column(name="DefValueParam5", type="text", length=65535, nullable=false)
     */
    private $defvalueparam5;

    /**
     * @var string
     *
     * @ORM\Column(name="Param5Type", type="string", nullable=false)
     */
    private $param5type = 'inputtext';

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
     * @return Sqlforvolunteers
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
     * Set query
     *
     * @param string $query
     *
     * @return Sqlforvolunteers
     */
    public function setQuery($query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * Get query
     *
     * @return string
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Sqlforvolunteers
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
     * Set param1
     *
     * @param string $param1
     *
     * @return Sqlforvolunteers
     */
    public function setParam1($param1)
    {
        $this->param1 = $param1;

        return $this;
    }

    /**
     * Get param1
     *
     * @return string
     */
    public function getParam1()
    {
        return $this->param1;
    }

    /**
     * Set param2
     *
     * @param string $param2
     *
     * @return Sqlforvolunteers
     */
    public function setParam2($param2)
    {
        $this->param2 = $param2;

        return $this;
    }

    /**
     * Get param2
     *
     * @return string
     */
    public function getParam2()
    {
        return $this->param2;
    }

    /**
     * Set logme
     *
     * @param string $logme
     *
     * @return Sqlforvolunteers
     */
    public function setLogme($logme)
    {
        $this->logme = $logme;

        return $this;
    }

    /**
     * Get logme
     *
     * @return string
     */
    public function getLogme()
    {
        return $this->logme;
    }

    /**
     * Set defvalueparam1
     *
     * @param string $defvalueparam1
     *
     * @return Sqlforvolunteers
     */
    public function setDefvalueparam1($defvalueparam1)
    {
        $this->defvalueparam1 = $defvalueparam1;

        return $this;
    }

    /**
     * Get defvalueparam1
     *
     * @return string
     */
    public function getDefvalueparam1()
    {
        return $this->defvalueparam1;
    }

    /**
     * Set defvalueparam2
     *
     * @param string $defvalueparam2
     *
     * @return Sqlforvolunteers
     */
    public function setDefvalueparam2($defvalueparam2)
    {
        $this->defvalueparam2 = $defvalueparam2;

        return $this;
    }

    /**
     * Get defvalueparam2
     *
     * @return string
     */
    public function getDefvalueparam2()
    {
        return $this->defvalueparam2;
    }

    /**
     * Set param1type
     *
     * @param string $param1type
     *
     * @return Sqlforvolunteers
     */
    public function setParam1type($param1type)
    {
        $this->param1type = $param1type;

        return $this;
    }

    /**
     * Get param1type
     *
     * @return string
     */
    public function getParam1type()
    {
        return $this->param1type;
    }

    /**
     * Set param2type
     *
     * @param string $param2type
     *
     * @return Sqlforvolunteers
     */
    public function setParam2type($param2type)
    {
        $this->param2type = $param2type;

        return $this;
    }

    /**
     * Get param2type
     *
     * @return string
     */
    public function getParam2type()
    {
        return $this->param2type;
    }

    /**
     * Set param3
     *
     * @param string $param3
     *
     * @return Sqlforvolunteers
     */
    public function setParam3($param3)
    {
        $this->param3 = $param3;

        return $this;
    }

    /**
     * Get param3
     *
     * @return string
     */
    public function getParam3()
    {
        return $this->param3;
    }

    /**
     * Set defvalueparam3
     *
     * @param string $defvalueparam3
     *
     * @return Sqlforvolunteers
     */
    public function setDefvalueparam3($defvalueparam3)
    {
        $this->defvalueparam3 = $defvalueparam3;

        return $this;
    }

    /**
     * Get defvalueparam3
     *
     * @return string
     */
    public function getDefvalueparam3()
    {
        return $this->defvalueparam3;
    }

    /**
     * Set param3type
     *
     * @param string $param3type
     *
     * @return Sqlforvolunteers
     */
    public function setParam3type($param3type)
    {
        $this->param3type = $param3type;

        return $this;
    }

    /**
     * Get param3type
     *
     * @return string
     */
    public function getParam3type()
    {
        return $this->param3type;
    }

    /**
     * Set param4
     *
     * @param string $param4
     *
     * @return Sqlforvolunteers
     */
    public function setParam4($param4)
    {
        $this->param4 = $param4;

        return $this;
    }

    /**
     * Get param4
     *
     * @return string
     */
    public function getParam4()
    {
        return $this->param4;
    }

    /**
     * Set defvalueparam4
     *
     * @param string $defvalueparam4
     *
     * @return Sqlforvolunteers
     */
    public function setDefvalueparam4($defvalueparam4)
    {
        $this->defvalueparam4 = $defvalueparam4;

        return $this;
    }

    /**
     * Get defvalueparam4
     *
     * @return string
     */
    public function getDefvalueparam4()
    {
        return $this->defvalueparam4;
    }

    /**
     * Set param4type
     *
     * @param string $param4type
     *
     * @return Sqlforvolunteers
     */
    public function setParam4type($param4type)
    {
        $this->param4type = $param4type;

        return $this;
    }

    /**
     * Get param4type
     *
     * @return string
     */
    public function getParam4type()
    {
        return $this->param4type;
    }

    /**
     * Set param5
     *
     * @param string $param5
     *
     * @return Sqlforvolunteers
     */
    public function setParam5($param5)
    {
        $this->param5 = $param5;

        return $this;
    }

    /**
     * Get param5
     *
     * @return string
     */
    public function getParam5()
    {
        return $this->param5;
    }

    /**
     * Set defvalueparam5
     *
     * @param string $defvalueparam5
     *
     * @return Sqlforvolunteers
     */
    public function setDefvalueparam5($defvalueparam5)
    {
        $this->defvalueparam5 = $defvalueparam5;

        return $this;
    }

    /**
     * Get defvalueparam5
     *
     * @return string
     */
    public function getDefvalueparam5()
    {
        return $this->defvalueparam5;
    }

    /**
     * Set param5type
     *
     * @param string $param5type
     *
     * @return Sqlforvolunteers
     */
    public function setParam5type($param5type)
    {
        $this->param5type = $param5type;

        return $this;
    }

    /**
     * Get param5type
     *
     * @return string
     */
    public function getParam5type()
    {
        return $this->param5type;
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
