<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * Language.
 *
 * @ORM\Table(name="languages", uniqueConstraints={@ORM\UniqueConstraint(name="ShortCode", columns={"ShortCode"})})
 * @ORM\Entity(repositoryClass="App\Repository\LanguageRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Language
{
    /**
     * @var string
     *
     * @ORM\Column(name="EnglishName", type="text", length=255, nullable=false)
     *
     * @Groups({"Member:Read"})
     */
    private $englishname;

    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="text", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     */
    private $translatedname;

    /**
     * @var string
     *
     * @ORM\Column(name="ShortCode", type="string", length=16, nullable=false)
     *
     * @Groups({"Member:Read"})
     */
    private $shortCode;

    /**
     * @var string
     *
     * @ORM\Column(name="WordCode", type="text", length=255, nullable=false)
     */
    private $wordCode;

    /**
     * @var int
     *
     * @ORM\Column(name="FlagSortCriteria", type="integer", nullable=false)
     */
    private $flagsortcriteria = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="IsWrittenLanguage", type="boolean", nullable=false)
     */
    private $iswrittenlanguage = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="IsSpokenLanguage", type="boolean", nullable=false)
     */
    private $isspokenlanguage = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="IsSignLanguage", type="boolean", nullable=false)
     */
    private $issignlanguage = '0';

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="MembersLanguagesLevel", mappedBy="language")
     */
    private $levels;

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
        $this->levels = new ArrayCollection();
    }

    /**
     * Set englishname.
     *
     * @param string $englishname
     *
     * @return Language
     */
    public function setEnglishname($englishname)
    {
        $this->englishname = $englishname;

        return $this;
    }

    /**
     * Get englishname.
     *
     * @return string
     */
    public function getEnglishname()
    {
        return $this->englishname;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Language
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Language
     */
    public function setTranslatedName($name)
    {
        $this->translatedname = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getTranslatedName()
    {
        return $this->translatedname;
    }

    /**
     * Set shortcode.
     *
     * @param string $shortCode
     *
     * @return Language
     */
    public function setShortCode($shortCode)
    {
        $this->shortCode = $shortCode;

        return $this;
    }

    /**
     * Get shortcode.
     *
     * @return string
     */
    public function getShortCode()
    {
        return $this->shortCode;
    }

    /**
     * Set word code.
     *
     * @param string $wordCode
     *
     * @return Language
     */
    public function setWordCode($wordCode)
    {
        $this->wordCode = $wordCode;

        return $this;
    }

    /**
     * Get word code.
     *
     * @return string
     */
    public function getWordCode()
    {
        return $this->wordCode;
    }

    /**
     * Set flagsortcriteria.
     *
     * @param int $flagsortcriteria
     *
     * @return Language
     */
    public function setFlagsortcriteria($flagsortcriteria)
    {
        $this->flagsortcriteria = $flagsortcriteria;

        return $this;
    }

    /**
     * Get flagsortcriteria.
     *
     * @return int
     */
    public function getFlagsortcriteria()
    {
        return $this->flagsortcriteria;
    }

    /**
     * Set iswrittenlanguage.
     *
     * @param bool $iswrittenlanguage
     *
     * @return Language
     */
    public function setIsWrittenlanguage($iswrittenlanguage)
    {
        $this->iswrittenlanguage = $iswrittenlanguage;

        return $this;
    }

    /**
     * Get iswrittenlanguage.
     *
     * @return bool
     */
    public function getIsWrittenlanguage()
    {
        return $this->iswrittenlanguage;
    }

    /**
     * Set isspokenlanguage.
     *
     * @param bool $isspokenlanguage
     *
     * @return Language
     */
    public function setIsSpokenlanguage($isspokenlanguage)
    {
        $this->isspokenlanguage = $isspokenlanguage;

        return $this;
    }

    /**
     * Get isspokenlanguage.
     *
     * @return bool
     */
    public function getIsSpokenlanguage()
    {
        return $this->isspokenlanguage;
    }

    /**
     * Set issignlanguage.
     *
     * @param bool $issignlanguage
     *
     * @return Language
     */
    public function setIsSignlanguage($issignlanguage)
    {
        $this->issignlanguage = $issignlanguage;

        return $this;
    }

    /**
     * Get issignlanguage.
     *
     * @return bool
     */
    public function getIsSignlanguage()
    {
        return $this->issignlanguage;
    }

    /**
     * Get levels.
     *
     * @return array
     */
    public function getLevels()
    {
        return $this->levels->toArray();
    }

    /**
     * Add level.
     *
     * @return Language
     */
    public function addLevel(MembersLanguagesLevel $level)
    {
        if (!$this->levels->contains($level)) {
            $this->levels->add($level);
            $level->setLanguage($this);
        }

        return $this;
    }

    /**
     * Remove level.
     *
     * @return $this
     */
    public function removeJob(MembersLanguagesLevel $level)
    {
        if ($this->levels->contains($level)) {
            $this->levels->removeElement($level);
            $level->setLanguage(null);
        }

        return $this;
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

    public function removeLevel(MembersLanguagesLevel $level): self
    {
        if ($this->levels->contains($level)) {
            $this->levels->removeElement($level);
            // set the owning side to null (unless already changed)
            if ($level->getLanguage() === $this) {
                $level->setLanguage(null);
            }
        }

        return $this;
    }
}
