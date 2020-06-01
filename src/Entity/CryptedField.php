<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
*/

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Cryptedfield.
 *
 * @ORM\Table(name="cryptedfields", indexes={@ORM\Index(name="IdMember", columns={"IdMember"})})
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class CryptedField
{
    /**
     * @var string
     *
     * @ORM\Column(name="AdminCryptedValue", type="text", length=65535, nullable=false)
     */
    private $adminCryptedValue;

    /**
     * @var string
     *
     * @ORM\Column(name="MemberCryptedValue", type="text", length=65535, nullable=false)
     */
    private $memberCryptedValue;

    /**
     * @var string
     *
     * @ORM\Column(name="IsCrypted", type="string", nullable=false)
     */
    private $iscrypted = 'crypted';

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member", inversedBy="cryptedFields")
     * @ORM\JoinColumn(name="IdMember", referencedColumnName="id")
     */
    private $member;

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
     * @var int
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
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set admincryptedvalue.
     *
     * @param $adminCryptedValue
     *
     * @return CryptedField
     *
     * @internal param string $admincryptedvalue
     */
    public function setAdminCryptedValue($adminCryptedValue)
    {
        $this->adminCryptedValue = $adminCryptedValue;

        return $this;
    }

    /**
     * Get admincryptedvalue.
     *
     * @return string
     */
    public function getAdminCryptedValue()
    {
        return $this->adminCryptedValue;
    }

    /**
     * Set membercryptedvalue.
     *
     * @param mixed $memberCryptedValue
     *
     * @return Cryptedfield
     */
    public function setMemberCryptedValue($memberCryptedValue)
    {
        $this->memberCryptedValue = $memberCryptedValue;

        return $this;
    }

    /**
     * Get membercryptedvalue.
     *
     * @return string
     */
    public function getMemberCryptedValue()
    {
        return $this->memberCryptedValue;
    }

    /**
     * Set iscrypted.
     *
     * @param string $iscrypted
     *
     * @return Cryptedfield
     */
    public function setIsCrypted($iscrypted)
    {
        $this->iscrypted = $iscrypted;

        return $this;
    }

    /**
     * Get iscrypted.
     *
     * @return string
     */
    public function getIsCrypted()
    {
        return $this->iscrypted;
    }

    /**
     * Set todo.
     *
     * @param string $todo
     *
     * @return Cryptedfield
     */
    public function setTodo($todo)
    {
        $this->todo = $todo;

        return $this;
    }

    /**
     * Get todo.
     *
     * @return string
     */
    public function getTodo()
    {
        return $this->todo;
    }

    /**
     * Set temporaryUncryptedBuffer.
     *
     * @param string $temporaryUncryptedBuffer
     *
     * @return Cryptedfield
     */
    public function setTemporaryUncryptedBuffer($temporaryUncryptedBuffer)
    {
        $this->temporaryUncryptedBuffer = $temporaryUncryptedBuffer;

        return $this;
    }

    /**
     * Get temporaryUncryptedBuffer.
     *
     * @return string
     */
    public function getTemporaryUncryptedBuffer()
    {
        return $this->temporaryUncryptedBuffer;
    }

    /**
     * Set idrecord.
     *
     * @param int $idrecord
     *
     * @return Cryptedfield
     */
    public function setIdrecord($idrecord)
    {
        $this->idrecord = $idrecord;

        return $this;
    }

    /**
     * Get idrecord.
     *
     * @return int
     */
    public function getIdrecord()
    {
        return $this->idrecord;
    }

    /**
     * Set tablecolumn.
     *
     * @param string $tablecolumn
     *
     * @return Cryptedfield
     */
    public function setTablecolumn($tablecolumn)
    {
        $this->tablecolumn = $tablecolumn;

        return $this;
    }

    /**
     * Get tablecolumn.
     *
     * @return string
     */
    public function getTablecolumn()
    {
        return $this->tablecolumn;
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

    /**
     * Get member.
     *
     * @return Member
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * Set member.
     *
     * @return Cryptedfield
     */
    public function setMember(Member $member)
    {
        $this->member = $member;

        return $this;
    }
}
