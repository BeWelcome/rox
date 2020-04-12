<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Word.
 *
 * @ORM\Table(name="words", uniqueConstraints={@ORM\UniqueConstraint(name="code", columns={"code", "IdLanguage"}), @ORM\UniqueConstraint(name="code_2", columns={"code", "ShortCode"})})
 * @ORM\Entity(repositoryClass="App\Repository\WordRepository")

 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Word
{
    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=128, nullable=false)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="domain", type="domain", length=16, nullable=false)
     */
    private $domain;

    /**
     * @var string
     *
     * @ORM\Column(name="ShortCode", type="string", length=16, nullable=false)
     */
    private $shortCode = 'en';

    /**
     * @var string
     *
     * @ORM\Column(name="Sentence", type="text", length=65535, nullable=false)
     */
    private $sentence;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var string
     *
     * @ORM\Column(name="donottranslate", type="string", nullable=false)
     */
    private $donottranslate = 'no';

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member")
     * @ORM\JoinColumn(name="IdMember", referencedColumnName="id")
     */
    private $author;

    /**
     * @var Language
     *
     * @ORM\ManyToOne(targetEntity="Language")
     * @ORM\JoinColumn(name="IdLanguage", referencedColumnName="id")
     */
    private $language;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var int
     *
     * @ORM\Column(name="TranslationPriority", type="integer", nullable=false)
     */
    private $translationPriority = '5';

    /**
     * @var bool
     *
     * @ORM\Column(name="isarchived", type="boolean", nullable=true)
     */
    private $isarchived = null;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="majorupdate", type="datetime", nullable=false)
     */
    private $majorUpdate;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set code.
     *
     * @param string $code
     *
     * @return Word
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code.
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set shortCode.
     *
     * @param string $shortCode
     *
     * @return Word
     */
    public function setShortCode($shortCode)
    {
        $this->shortCode = $shortCode;

        return $this;
    }

    /**
     * Get shortCode.
     *
     * @return string
     */
    public function getShortCode()
    {
        return $this->shortCode;
    }

    /**
     * Set sentence.
     *
     * @param string $sentence
     *
     * @return Word
     */
    public function setSentence($sentence)
    {
        $this->sentence = $sentence;

        return $this;
    }

    /**
     * Get sentence.
     *
     * @return string
     */
    public function getSentence()
    {
        return $this->sentence;
    }

    /**
     * Set updated.
     *
     * @param DateTime $updated
     *
     * @return Word
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
     * Set donottranslate.
     *
     * @param string $donottranslate
     *
     * @return Word
     */
    public function setDonottranslate($donottranslate)
    {
        $this->donottranslate = $donottranslate;

        return $this;
    }

    /**
     * Get donottranslate.
     *
     * @return string
     */
    public function getDonottranslate()
    {
        return $this->donottranslate;
    }

    /**
     * Sets language and the matching shortcode (\todo remove shortcode or idlanguage when old code is finally replaced).
     *
     * @param Language $language
     *
     * @return Word
     */
    public function setLanguage($language)
    {
        $this->language = $language;
        $this->setShortCode($language->getShortcode());

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
     * Set description.
     *
     * @param string $description
     *
     * @return Word
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set author.
     *
     * @param Member $author
     *
     * @return Word
     */
    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }

    /**
     * Get author.
     *
     * @return Member
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Set created.
     *
     * @param DateTime $created
     *
     * @return Word
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return Carbon
     */
    public function getCreated()
    {
        return Carbon::instance($this->created);
    }

    /**
     * Set translationpriority.
     *
     * @param int   $translationpriority
     *
     * @return Word
     */
    public function setTranslationPriority($translationPriority)
    {
        $this->translationPriority = $translationPriority;

        return $this;
    }

    /**
     * Get translationpriority.
     *
     * @return int
     */
    public function getTranslationPriority()
    {
        return $this->translationPriority;
    }

    /**
     * Set isarchived.
     *
     * @param bool $isarchived
     *
     * @return Word
     */
    public function setIsarchived($isarchived)
    {
        $this->isarchived = $isarchived;

        return $this;
    }

    /**
     * Get isarchived.
     *
     * @return bool
     */
    public function getIsarchived()
    {
        return $this->isarchived;
    }

    /**
     * Set majorupdate.
     *
     * @param DateTime $majorUpdate
     *
     * @return Word
     */
    public function setMajorUpdate($majorUpdate)
    {
        $this->majorUpdate = $majorUpdate;

        return $this;
    }

    /**
     * Get majorUpdate.
     *
     * @return DateTime
     */
    public function getMajorUpdate()
    {
        return $this->majorUpdate;
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
     * @return string
     */
    public function getDomain(): string
    {
        return $this->domain;
    }

    /**
     * @param string $domain
     *
     * @return Word
     */
    public function setDomain(string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }
}
