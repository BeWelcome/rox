<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Subject.
 *
 * @ORM\Table(name="subject")
 * @ORM\Entity(repositoryClass="App\Repository\SubjectRepository")
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Subject
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=255)
     */
    private $subject;

    /**
     * @var Message[]
     * @ORM\OneToMany(targetEntity="Message", mappedBy="subject")
     */
    private $messages;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
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
     * Set subject.
     *
     * @param string $subject
     *
     * @return Subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;

        return $this;
    }

    /**
     * Get subject.
     *
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }
}
