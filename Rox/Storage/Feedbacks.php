<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feedbacks
 *
 * @ORM\Table(name="feedbacks", indexes={@ORM\Index(name="IdMember", columns={"IdMember", "IdFeedbackCategory", "IdVolunteer"}), @ORM\Index(name="IdFeedbackCategory", columns={"IdFeedbackCategory"}), @ORM\Index(name="IdVolunteer", columns={"IdVolunteer"})})
 * @ORM\Entity
 */
class Feedbacks
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = '0000-00-00 00:00:00';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdMember", type="integer", nullable=false)
     */
    private $idmember;

    /**
     * @var string
     *
     * @ORM\Column(name="Discussion", type="text", length=65535, nullable=false)
     */
    private $discussion;

    /**
     * @var integer
     *
     * @ORM\Column(name="IdVolunteer", type="integer", nullable=false)
     */
    private $idvolunteer = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'open';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdLanguage", type="integer", nullable=false)
     */
    private $idlanguage = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Feedbackcategories
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Feedbackcategories")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdFeedbackCategory", referencedColumnName="id")
     * })
     */
    private $idfeedbackcategory;



    /**
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Feedbacks
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Feedbacks
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
     * Set idmember
     *
     * @param integer $idmember
     *
     * @return Feedbacks
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
     * Set discussion
     *
     * @param string $discussion
     *
     * @return Feedbacks
     */
    public function setDiscussion($discussion)
    {
        $this->discussion = $discussion;

        return $this;
    }

    /**
     * Get discussion
     *
     * @return string
     */
    public function getDiscussion()
    {
        return $this->discussion;
    }

    /**
     * Set idvolunteer
     *
     * @param integer $idvolunteer
     *
     * @return Feedbacks
     */
    public function setIdvolunteer($idvolunteer)
    {
        $this->idvolunteer = $idvolunteer;

        return $this;
    }

    /**
     * Get idvolunteer
     *
     * @return integer
     */
    public function getIdvolunteer()
    {
        return $this->idvolunteer;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Feedbacks
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set idlanguage
     *
     * @param integer $idlanguage
     *
     * @return Feedbacks
     */
    public function setIdlanguage($idlanguage)
    {
        $this->idlanguage = $idlanguage;

        return $this;
    }

    /**
     * Get idlanguage
     *
     * @return integer
     */
    public function getIdlanguage()
    {
        return $this->idlanguage;
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

    /**
     * Set idfeedbackcategory
     *
     * @param \AppBundle\Entity\Feedbackcategories $idfeedbackcategory
     *
     * @return Feedbacks
     */
    public function setIdfeedbackcategory(\AppBundle\Entity\Feedbackcategories $idfeedbackcategory = null)
    {
        $this->idfeedbackcategory = $idfeedbackcategory;

        return $this;
    }

    /**
     * Get idfeedbackcategory
     *
     * @return \AppBundle\Entity\Feedbackcategories
     */
    public function getIdfeedbackcategory()
    {
        return $this->idfeedbackcategory;
    }
}
