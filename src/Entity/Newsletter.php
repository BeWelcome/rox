<?php

namespace App\Entity;

use App\Entity\Member as Member;
use Carbon\Carbon;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectManagerAware;

/**
 * Broadcast.
 *
 * @ORM\Table(name="broadcast")
 * @ORM\Entity(repositoryClass="App\Repository\NewsletterRepository")
 */
class Newsletter implements ObjectManagerAware
{
    public const REGULAR_NEWSLETTER = 'Normal';
    public const SPECIFIC_NEWSLETTER = 'Specific';
    public const TERMS_OF_USE = 'TermsOfUse';

    /**
     * @var Member
     *
     * @ORM\OneToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdCreator", referencedColumnName="id")
     * })
     */
    private $createdBy;

    /**
     * @var string
     *
     * @ORM\Column(name="Name", type="text", length=65535, nullable=false)
     */
    private $name;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'Created';

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=true)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="EmailFrom", type="text", length=65535, nullable=true)
     */
    private $emailFrom;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * Set createdBy.
     *
     * @param Member $createdBy
     *
     * @return Newsletter
     */
    public function setCreatedBy($createdBy)
    {
        $this->createdBy = $createdBy;

        return $this;
    }

    /**
     * Get createdBy.
     *
     * @return Member
     */
    public function getCreatedBy()
    {
        return $this->createdBy;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Newsletter
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
     * Set created.
     *
     * @param DateTime $created
     *
     * @return Newsletter
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
     * Set status.
     *
     * @param string $status
     *
     * @return Newsletter
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set type.
     *
     * @param string $type
     *
     * @return Newsletter
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
     * Set emailFrom.
     *
     * @param string $emailFrom
     *
     * @return Newsletter
     */
    public function setEmailFrom($emailFrom)
    {
        $this->emailFrom = $emailFrom;

        return $this;
    }

    /**
     * Get emailFrom.
     *
     * @return string
     */
    public function getEmailFrom()
    {
        return $this->emailFrom;
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

    /*
     * Translated post content is only provided on explicit call to avoid long load times
     */
    public function getTranslations()
    {
        $translationRepository = $this->objectManager->getRepository(Word::class);
        $translatedNews = $translationRepository->findBy([
            'code' => [
                'Broadcast_body_' . $this->name,
                'Broadcast_title_' . $this->name,
            ],
        ]);

        $newsletters = [];
        /** @var Word $item */
        foreach ($translatedNews as $item) {
            if (!isset($newsletters[$item->getLanguage()->getShortCode()])) {
                $newsletter = [];
            } else {
                $newsletter = $newsletters[$item->getLanguage()->getShortCode()];
            }
            // Determine if this is the title or the body of the newsletter (code is broadcast_title|body_$name)
            $part = str_ireplace('Broadcast_', '', str_ireplace('_' . $this->getName(), '', $item->getCode()));
            $newsletter[$part] = $item->getSentence();
            $newsletter['author'] = $item->getAuthor();
            $newsletter['locale'] = $item->getShortCode();
            $newsletters[$item->getLanguage()->getShortCode()] = $newsletter;
        }

        return $newsletters;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function injectObjectManager(ObjectManager $objectManager, ClassMetadata $classMetadata)
    {
        $this->objectManager = $objectManager;
    }
}
