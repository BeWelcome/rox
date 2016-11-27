<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Polls
 *
 * @ORM\Table(name="polls", indexes={@ORM\Index(name="IdCreator", columns={"IdCreator"})})
 * @ORM\Entity
 */
class Polls
{
    /**
     * @var integer
     *
     * @ORM\Column(name="IdGroupCreator", type="integer", nullable=false)
     */
    private $idgroupcreator = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'Project';

    /**
     * @var string
     *
     * @ORM\Column(name="ResultsVisibility", type="string", nullable=false)
     */
    private $resultsvisibility = 'VisibleAfterVisit';

    /**
     * @var string
     *
     * @ORM\Column(name="Type", type="string", nullable=false)
     */
    private $type = 'MemberPoll';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated = 'CURRENT_TIMESTAMP';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="Started", type="datetime", nullable=false)
     */
    private $started = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="Ended", type="datetime", nullable=false)
     */
    private $ended = '0000-00-00 00:00:00';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created = '0000-00-00 00:00:00';

    /**
     * @var integer
     *
     * @ORM\Column(name="Title", type="integer", nullable=false)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="ForMembersOnly", type="string", nullable=false)
     */
    private $formembersonly = 'Yes';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdLocationsList", type="integer", nullable=false)
     */
    private $idlocationslist = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdGroupsList", type="integer", nullable=false)
     */
    private $idgroupslist = '0';

    /**
     * @var integer
     *
     * @ORM\Column(name="IdCountriesList", type="integer", nullable=false)
     */
    private $idcountrieslist = '0';

    /**
     * @var string
     *
     * @ORM\Column(name="OpenToSubGroups", type="string", nullable=false)
     */
    private $opentosubgroups = 'Yes';

    /**
     * @var string
     *
     * @ORM\Column(name="TypeOfChoice", type="string", nullable=false)
     */
    private $typeofchoice;

    /**
     * @var string
     *
     * @ORM\Column(name="CanChangeVote", type="string", nullable=false)
     */
    private $canchangevote = 'No';

    /**
     * @var string
     *
     * @ORM\Column(name="AllowComment", type="string", nullable=false)
     */
    private $allowcomment = 'No';

    /**
     * @var integer
     *
     * @ORM\Column(name="Description", type="integer", nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="WhereToRestrictMember", type="text", length=65535, nullable=false)
     */
    private $wheretorestrictmember;

    /**
     * @var string
     *
     * @ORM\Column(name="Anonym", type="string", nullable=false)
     */
    private $anonym = 'Yes';

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Members
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Members")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdCreator", referencedColumnName="id")
     * })
     */
    private $idcreator;



    /**
     * Set idgroupcreator
     *
     * @param integer $idgroupcreator
     *
     * @return Polls
     */
    public function setIdgroupcreator($idgroupcreator)
    {
        $this->idgroupcreator = $idgroupcreator;

        return $this;
    }

    /**
     * Get idgroupcreator
     *
     * @return integer
     */
    public function getIdgroupcreator()
    {
        return $this->idgroupcreator;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Polls
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
     * Set resultsvisibility
     *
     * @param string $resultsvisibility
     *
     * @return Polls
     */
    public function setResultsvisibility($resultsvisibility)
    {
        $this->resultsvisibility = $resultsvisibility;

        return $this;
    }

    /**
     * Get resultsvisibility
     *
     * @return string
     */
    public function getResultsvisibility()
    {
        return $this->resultsvisibility;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return Polls
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
     * Set updated
     *
     * @param \DateTime $updated
     *
     * @return Polls
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
     * Set started
     *
     * @param \DateTime $started
     *
     * @return Polls
     */
    public function setStarted($started)
    {
        $this->started = $started;

        return $this;
    }

    /**
     * Get started
     *
     * @return \DateTime
     */
    public function getStarted()
    {
        return $this->started;
    }

    /**
     * Set ended
     *
     * @param \DateTime $ended
     *
     * @return Polls
     */
    public function setEnded($ended)
    {
        $this->ended = $ended;

        return $this;
    }

    /**
     * Get ended
     *
     * @return \DateTime
     */
    public function getEnded()
    {
        return $this->ended;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     *
     * @return Polls
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
     * Set title
     *
     * @param integer $title
     *
     * @return Polls
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return integer
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set formembersonly
     *
     * @param string $formembersonly
     *
     * @return Polls
     */
    public function setFormembersonly($formembersonly)
    {
        $this->formembersonly = $formembersonly;

        return $this;
    }

    /**
     * Get formembersonly
     *
     * @return string
     */
    public function getFormembersonly()
    {
        return $this->formembersonly;
    }

    /**
     * Set idlocationslist
     *
     * @param integer $idlocationslist
     *
     * @return Polls
     */
    public function setIdlocationslist($idlocationslist)
    {
        $this->idlocationslist = $idlocationslist;

        return $this;
    }

    /**
     * Get idlocationslist
     *
     * @return integer
     */
    public function getIdlocationslist()
    {
        return $this->idlocationslist;
    }

    /**
     * Set idgroupslist
     *
     * @param integer $idgroupslist
     *
     * @return Polls
     */
    public function setIdgroupslist($idgroupslist)
    {
        $this->idgroupslist = $idgroupslist;

        return $this;
    }

    /**
     * Get idgroupslist
     *
     * @return integer
     */
    public function getIdgroupslist()
    {
        return $this->idgroupslist;
    }

    /**
     * Set idcountrieslist
     *
     * @param integer $idcountrieslist
     *
     * @return Polls
     */
    public function setIdcountrieslist($idcountrieslist)
    {
        $this->idcountrieslist = $idcountrieslist;

        return $this;
    }

    /**
     * Get idcountrieslist
     *
     * @return integer
     */
    public function getIdcountrieslist()
    {
        return $this->idcountrieslist;
    }

    /**
     * Set opentosubgroups
     *
     * @param string $opentosubgroups
     *
     * @return Polls
     */
    public function setOpentosubgroups($opentosubgroups)
    {
        $this->opentosubgroups = $opentosubgroups;

        return $this;
    }

    /**
     * Get opentosubgroups
     *
     * @return string
     */
    public function getOpentosubgroups()
    {
        return $this->opentosubgroups;
    }

    /**
     * Set typeofchoice
     *
     * @param string $typeofchoice
     *
     * @return Polls
     */
    public function setTypeofchoice($typeofchoice)
    {
        $this->typeofchoice = $typeofchoice;

        return $this;
    }

    /**
     * Get typeofchoice
     *
     * @return string
     */
    public function getTypeofchoice()
    {
        return $this->typeofchoice;
    }

    /**
     * Set canchangevote
     *
     * @param string $canchangevote
     *
     * @return Polls
     */
    public function setCanchangevote($canchangevote)
    {
        $this->canchangevote = $canchangevote;

        return $this;
    }

    /**
     * Get canchangevote
     *
     * @return string
     */
    public function getCanchangevote()
    {
        return $this->canchangevote;
    }

    /**
     * Set allowcomment
     *
     * @param string $allowcomment
     *
     * @return Polls
     */
    public function setAllowcomment($allowcomment)
    {
        $this->allowcomment = $allowcomment;

        return $this;
    }

    /**
     * Get allowcomment
     *
     * @return string
     */
    public function getAllowcomment()
    {
        return $this->allowcomment;
    }

    /**
     * Set description
     *
     * @param integer $description
     *
     * @return Polls
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return integer
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set wheretorestrictmember
     *
     * @param string $wheretorestrictmember
     *
     * @return Polls
     */
    public function setWheretorestrictmember($wheretorestrictmember)
    {
        $this->wheretorestrictmember = $wheretorestrictmember;

        return $this;
    }

    /**
     * Get wheretorestrictmember
     *
     * @return string
     */
    public function getWheretorestrictmember()
    {
        return $this->wheretorestrictmember;
    }

    /**
     * Set anonym
     *
     * @param string $anonym
     *
     * @return Polls
     */
    public function setAnonym($anonym)
    {
        $this->anonym = $anonym;

        return $this;
    }

    /**
     * Get anonym
     *
     * @return string
     */
    public function getAnonym()
    {
        return $this->anonym;
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
     * Set idcreator
     *
     * @param \AppBundle\Entity\Members $idcreator
     *
     * @return Polls
     */
    public function setIdcreator(\AppBundle\Entity\Members $idcreator = null)
    {
        $this->idcreator = $idcreator;

        return $this;
    }

    /**
     * Get idcreator
     *
     * @return \AppBundle\Entity\Members
     */
    public function getIdcreator()
    {
        return $this->idcreator;
    }
}
