<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Notes
 *
 * @ORM\Table(name="notes")
 * @ORM\Entity
 */
class Notes
{
    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdRelMember", type="integer", nullable=false)
     */
    private $idrelmember;

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
     * @var boolean
     *
     * @ORM\Column(name="Checked", type="boolean", nullable=false)
     */
    private $checked = '0';

    /**
     * @var boolean
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
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;



    /**
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return Notes
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
     * Set idrelmember
     *
     * @param integer $idrelmember
     *
     * @return Notes
     */
    public function setIdrelmember($idrelmember)
    {
        $this->idrelmember = $idrelmember;

        return $this;
    }

    /**
     * Get idrelmember
     *
     * @return integer
     */
    public function getIdrelmember()
    {
        return $this->idrelmember;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Notes
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
     * Set link
     *
     * @param string $link
     *
     * @return Notes
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get link
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Set wordcode
     *
     * @param string $wordcode
     *
     * @return Notes
     */
    public function setWordcode($wordcode)
    {
        $this->wordcode = $wordcode;

        return $this;
    }

    /**
     * Get wordcode
     *
     * @return string
     */
    public function getWordcode()
    {
        return $this->wordcode;
    }

    /**
     * Set checked
     *
     * @param boolean $checked
     *
     * @return Notes
     */
    public function setChecked($checked)
    {
        $this->checked = $checked;

        return $this;
    }

    /**
     * Get checked
     *
     * @return boolean
     */
    public function getChecked()
    {
        return $this->checked;
    }

    /**
     * Set sendmail
     *
     * @param boolean $sendmail
     *
     * @return Notes
     */
    public function setSendmail($sendmail)
    {
        $this->sendmail = $sendmail;

        return $this;
    }

    /**
     * Get sendmail
     *
     * @return boolean
     */
    public function getSendmail()
    {
        return $this->sendmail;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Notes
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
     * Set translationparams
     *
     * @param string $translationparams
     *
     * @return Notes
     */
    public function setTranslationparams($translationparams)
    {
        $this->translationparams = $translationparams;

        return $this;
    }

    /**
     * Get translationparams
     *
     * @return string
     */
    public function getTranslationparams()
    {
        return $this->translationparams;
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
