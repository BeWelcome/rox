<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CryptedfieldsBeforeDataRetention
 *
 * @ORM\Table(name="cryptedfields_before_data_retention", indexes={@ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\Entity
 */
class CryptedfieldsBeforeDataRetention
{
    /**
     * @var string
     *
     * @ORM\Column(name="AdminCryptedValue", type="text", length=65535, nullable=false)
     */
    private $admincryptedvalue;

    /**
     * @var string
     *
     * @ORM\Column(name="MemberCryptedValue", type="text", length=65535, nullable=false)
     */
    private $membercryptedvalue;

    /**
     * @var string
     *
     * @ORM\Column(name="IsCrypted", type="string", nullable=false)
     */
    private $iscrypted = 'crypted';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var string
     *
     * @ORM\Column(name="ToDo", type="string", nullable=false)
     */
    private $todo = 'nothing';

    /**
     * @var string
     *
     * @ORM\Column(name="temporary_uncrypted_buffer", type="text", length=65535, nullable=true)
     */
    private $temporaryUncryptedBuffer;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdRecord", type="integer", nullable=false)
     */
    private $idrecord = '0';

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
     * Set admincryptedvalue
     *
     * @param string $admincryptedvalue
     *
     * @return CryptedfieldsBeforeDataRetention
     */
    public function setAdmincryptedvalue($admincryptedvalue)
    {
        $this->admincryptedvalue = $admincryptedvalue;

        return $this;
    }

    /**
     * Get admincryptedvalue
     *
     * @return string
     */
    public function getAdmincryptedvalue()
    {
        return $this->admincryptedvalue;
    }

    /**
     * Set membercryptedvalue
     *
     * @param string $membercryptedvalue
     *
     * @return CryptedfieldsBeforeDataRetention
     */
    public function setMembercryptedvalue($membercryptedvalue)
    {
        $this->membercryptedvalue = $membercryptedvalue;

        return $this;
    }

    /**
     * Get membercryptedvalue
     *
     * @return string
     */
    public function getMembercryptedvalue()
    {
        return $this->membercryptedvalue;
    }

    /**
     * Set iscrypted
     *
     * @param string $iscrypted
     *
     * @return CryptedfieldsBeforeDataRetention
     */
    public function setIscrypted($iscrypted)
    {
        $this->iscrypted = $iscrypted;

        return $this;
    }

    /**
     * Get iscrypted
     *
     * @return string
     */
    public function getIscrypted()
    {
        return $this->iscrypted;
    }

    /**
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return CryptedfieldsBeforeDataRetention
     */
    public function setIdmember($idmember)
    {
        $this->idmember = $idmember;

        return $this;
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

    /**
     * Set todo
     *
     * @param string $todo
     *
     * @return CryptedfieldsBeforeDataRetention
     */
    public function setTodo($todo)
    {
        $this->todo = $todo;

        return $this;
    }

    /**
     * Get todo
     *
     * @return string
     */
    public function getTodo()
    {
        return $this->todo;
    }

    /**
     * Set temporaryUncryptedBuffer
     *
     * @param string $temporaryUncryptedBuffer
     *
     * @return CryptedfieldsBeforeDataRetention
     */
    public function setTemporaryUncryptedBuffer($temporaryUncryptedBuffer)
    {
        $this->temporaryUncryptedBuffer = $temporaryUncryptedBuffer;

        return $this;
    }

    /**
     * Get temporaryUncryptedBuffer
     *
     * @return string
     */
    public function getTemporaryUncryptedBuffer()
    {
        return $this->temporaryUncryptedBuffer;
    }

    /**
     * Set idrecord
     *
     * @param integer $idrecord
     *
     * @return CryptedfieldsBeforeDataRetention
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
     * @return CryptedfieldsBeforeDataRetention
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
