<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace AppBundle\Entity;

use Carbon\Carbon;
use Doctrine\ORM\Mapping as ORM;

/**
 * Notes.
 *
 * @ORM\Table(name="notes")
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Notification
{
    /**
     * @var \AppBundle\Entity\Member
     *
     * @ORM\OneToOne(targetEntity="\AppBundle\Entity\Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdMember", referencedColumnName="id")
     * })
     */
    private $member;

    /**
     * @var \AppBundle\Entity\Member
     *
     * @ORM\OneToOne(targetEntity="\AppBundle\Entity\Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdRelMember", referencedColumnName="id")
     * })
     */
    private $relMember;

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="Link", type="string", length=300, nullable=false)
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(name="WordCode", type="string", length=300, nullable=false)
     */
    private $wordcode;

    /**
     * @var bool
     *
     * @ORM\Column(name="Checked", type="boolean", nullable=false)
     */
    private $checked = '0';

    /**
     * @var bool
     *
     * @ORM\Column(name="SendMail", type="boolean", nullable=false)
     */
    private $sendmail = '0';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = 'CURRENT_TIMESTAMP';

    /**
     * @var string
     *
     * @ORM\Column(name="TranslationParams", type="text", length=65535, nullable=true)
     */
    private $translationparams;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Set member.
     *
     * @param Member $member
     *
     * @return Notification
     */
    public function setMember(Member $member)
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
     * Set relMember.
     *
     * @param Member $relMember
     *
     * @return Notification
     */
    public function setRelMember(Member $relMember)
    {
        $this->relMember = $relMember;

        return $this;
    }

    /**
     * Get idrelmember.
     *
     * @return Member
     */
    public function getRelMember()
    {
        return $this->relMember;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Notification
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
     * Set link.
     *
     * @param string $link
     *
     * @return Notification
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link.
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set wordcode.
     *
     * @param string $wordcode
     *
     * @return Notification
     */
    public function setWordcode($wordcode)
    {
        $this->wordcode = $wordcode;

        return $this;
    }

    /**
     * Get wordcode.
     *
     * @return string
     */
    public function getWordcode()
    {
        return $this->wordcode;
    }

    /**
     * Set checked.
     *
     * @param bool $checked
     *
     * @return Notification
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * Get checked.
     *
     * @return bool
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * Set sendmail.
     *
     * @param bool $sendmail
     *
     * @return Notification
     */
    public function setSendmail($sendmail)
    {
        $this->sendmail = $sendmail;

        return $this;
    }

    /**
     * Get sendmail.
     *
     * @return bool
     */
    public function getSendmail()
    {
        return $this->sendmail;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Notification
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
     * Set translationparams.
     *
     * @param string $translationparams
     *
     * @return Notification
     */
    public function setTranslationparams($translationparams)
    {
        $this->translationparams = $translationparams;

        return $this;
    }

    /**
     * Get translationparams.
     *
     * @return string
     */
    public function getTranslationCode()
    {
        if ($this->translationparams) {
            $a = unserialize($this->translationparams);

            return $a[0];
        }

        return $this->wordcode;
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
     * Get translationparams.
     *
     * @return string
     */
    public function getTranslationparams()
    {
        return $this->translationparams;
    }
}
