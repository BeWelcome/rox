<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Shouts
 *
 * @ORM\Table(name="shouts")
 * @ORM\Entity
 */
class Shouts
{
    /**
     * @var integer
     *
     * @ORM\Column(name="member_id_foreign", type="integer", nullable=false)
     */
    private $memberIdForeign = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="table", type="string", length=75, nullable=false)
     */
    private $table;

    /**
     * @var integer
     *
     * @ORM\Column(name="table_id", type="integer", nullable=false)
     */
    private $tableId = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=75, nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", length=16777215, nullable=false)
     */
    private $text;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set memberIdForeign
     *
     * @param integer $memberIdForeign
     *
     * @return Shouts
     */
    public function setMemberIdForeign($memberIdForeign)
    {
        $this->memberIdForeign = $memberIdForeign;

        return $this;
    }

    /**
     * Get memberIdForeign
     *
     * @return integer
     */
    public function getMemberIdForeign()
    {
        return $this->memberIdForeign;
    }

    /**
     * Set table
     *
     * @param string $table
     *
     * @return Shouts
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Get table
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }

    /**
     * Set tableId
     *
     * @param integer $tableId
     *
     * @return Shouts
     */
    public function setTableId($tableId)
    {
        $this->tableId = $tableId;

        return $this;
    }

    /**
     * Get tableId
     *
     * @return integer
     */
    public function getTableId()
    {
        return $this->tableId;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Shouts
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
     * Set title
     *
     * @param string $title
     *
     * @return Shouts
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return Shouts
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
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
