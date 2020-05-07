<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\LanguageLevelType;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Memberslanguageslevel.
 *
 * @ORM\Table(name="memberslanguageslevel", indexes={@ORM\Index(name="IdMember", columns={"IdMember", "IdLanguage"})})
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class MembersLanguagesLevel
{
    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member", inversedBy="languageLevels")
     * @ORM\JoinColumn(name="IdMember", referencedColumnName="id", nullable=FALSE)
     */
    protected $member;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var Language
     *
     * @ORM\ManyToOne(targetEntity="Language", inversedBy="levels")
     * @ORM\JoinColumn(name="IdLanguage", referencedColumnName="id", nullable=FALSE)
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="Level", type="language_level", nullable=false)
     */
    private $level = LanguageLevelType::BEGINNER;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function __construct()
    {
        $this->created = new DateTime();
    }

    /**
     * Set updated.
     *
     * @param DateTime $updated
     *
     * @return Memberslanguageslevel
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created.
     *
     * @param DateTime $created
     *
     * @return Memberslanguageslevel
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set member.
     *
     * @param Member $member
     *
     * @return Memberslanguageslevel
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
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
     * Set language.
     *
     * @param Language $language
     *
     * @return Memberslanguageslevel
     */
    public function setLanguage(Language $language)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get language.
     *
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Set level.
     *
     * @param string $level
     *
     * @return Memberslanguageslevel
     */
    public function setLevel($level)
    {
        $this->level = $level;

        return $this;
    }

    /**
     * Get level.
     *
     * @return string
     */
    public function getLevel()
    {
        return $this->level;
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
}
