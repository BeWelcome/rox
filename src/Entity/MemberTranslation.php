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
 * MemberTranslation.
 *
 * @ORM\Table(name="memberstrads",
 *     uniqueConstraints={@ORM\UniqueConstraint(name="Unique_entry", columns={"IdTrad", "IdOwner", "IdLanguage"})},
 *     indexes={
 *         @ORM\Index(name="memberstrads_trads", columns={"IdTrad"}),
 *         @ORM\Index(name="memberstrads_language", columns={"IdLanguage"}),
 *         @ORM\Index(name="memberstrads_trad_language", columns={"IdLanguage", "IdTrad"})
 *      }
 *     )
 * @ORM\Entity
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class MemberTranslation
{
    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member", fetch="EAGER")
     * @ORM\JoinColumn(name="IdOwner", referencedColumnName="id")
     */
    private $owner;

    /**
     * @var int
     *
     * @ORM\Column(name="IdTrad", type="integer", nullable=false)
     */
    private $translation;

    /**
     * @var Member
     *
     * @ORM\ManyToOne(targetEntity="Member", fetch="LAZY")
     * @ORM\JoinColumn(name="IdTranslator", nullable=false)
     */
    private $translator;

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
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private $type = 'member';

    /**
     * @var string
     *
     * @ORM\Column(name="Sentence", type="text", length=65535, nullable=false)
     */
    private $sentence;

    /**
     * @var int
     *
     * @ORM\Column(name="IdRecord", type="integer", nullable=false)
     */
    private $idrecord = '-1';

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
     * @var Language
     *
     * @ORM\ManyToOne(targetEntity="Language")
     *   @ORM\JoinColumn(name="IdLanguage", referencedColumnName="id")
     */
    private $language;

    /**
     * Set owner.
     *
     * @param Member $owner
     *
     * @return MemberTranslation
     */
    public function setOwner(Member $owner):self
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return Member
     */
    public function getOwner():Member
    {
        return $this->owner;
    }

    /**
     * Set translation.
     *
     * @param int $translation
     *
     * @return MemberTranslation
     */
    public function setTranslation($translation)
    {
        $this->translation = $translation;

        return $this;
    }

    /**
     * Get translation.
     *
     * @return int
     */
    public function getTranslation()
    {
        return $this->translation;
    }

    /**
     * Set translator.
     *
     * @param Member $translator
     *
     * @return MemberTranslation
     */
    public function setTranslator(Member $translator):self
    {
        $this->translator = $translator;

        return $this;
    }

    /**
     * Get translator.
     *
     * @return Member
     */
    public function getTranslator():Member
    {
        return $this->translator;
    }

    /**
     * Set updated.
     *
     * @param DateTime $updated
     *
     * @return MemberTranslation
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return Carbon
     */
    public function getUpdated()
    {
        return Carbon::instance($this->updated);
    }

    /**
     * Set created.
     *
     * @param DateTime $created
     *
     * @return MemberTranslation
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
     * Set type.
     *
     * @param string $type
     *
     * @return MemberTranslation
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set sentence.
     *
     * @param string $sentence
     *
     * @return MemberTranslation
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
     * Set idrecord.
     *
     * @param int $idrecord
     *
     * @return MemberTranslation
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
     * @return MemberTranslation
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
     * Set language.
     *
     * @param Language $language
     *
     * @return MemberTranslation
     */
    public function setLanguage(Language $language = null)
    {
        $this->language = $language;

        return $this;
    }

    /**
     * Get idlanguage.
     *
     * @return Language
     */
    public function getLanguage()
    {
        return $this->language;
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
        $this->updated = $this->created;
        $this->translation = random_int(0, 24500000);
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PostPersist
     */
    public function onPostPersist()
    {
        $this->translation = $this->id;
    }

    /**
     * Triggered on update.
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new DateTime('now');
    }
}
