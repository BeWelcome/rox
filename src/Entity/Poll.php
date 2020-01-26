<?php

namespace App\Entity;

use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Polls
 *
 * @ORM\Table(name="polls", indexes={@ORM\Index(name="IdCreator", columns={"IdCreator"})})
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD)
 * Auto generated class do not check mess
 */
class Poll
{
    /**
     * @var Member
     *
     * @ORM\OneToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdGroupCreator", referencedColumnName="id")
     * })
     */
    private $groupCreator;

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
     * @var DateTime
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="Started", type="datetime", nullable=false)
     */
    private $started;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="Ended", type="datetime", nullable=false)
     */
    private $ended;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var PollChoice
     *
     * @ORM\OneToMany(targetEntity="PollChoice", mappedBy="poll" )
     * @ORM\JoinTable(name="polls_choice",
     *      joinColumns={@ORM\JoinColumn(name="IdPoll", referencedColumnName="id")},
     *      )
     *
     * Collects all translated titles of the poll
     */
    private $choices;

    /**
     * @var PollContribution
     *
     * @ORM\OneToMany(targetEntity="PollContribution", mappedBy="poll" )
     * @ORM\JoinTable(name="polls_contributions",
     *      joinColumns={@ORM\JoinColumn(name="IdPoll", referencedColumnName="id")},
     *      )
     *
     * Collects all translated titles of the poll
     */
    private $contributions;

    /**
     * @var Translation
     *
     * @ORM\ManyToMany(targetEntity="Translation", fetch="EAGER")
     * @ORM\JoinTable(name="polls_translations",
     *      joinColumns={@ORM\JoinColumn(name="poll_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="translation_id", referencedColumnName="id")}
     *      )
     *
     * Collects all translated titles of the poll
     */
    private $titles;

    /**
     * @var Group
     *
     * @ORM\ManyToMany(targetEntity="Group", fetch="EAGER")
     * @ORM\JoinTable(name="polls_list_allowed_groups",
     *      joinColumns={@ORM\JoinColumn(name="IdPoll", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="IdGroup", referencedColumnName="id")}
     *      )
     *
     * Collects all groups associated with the poll
     */
    private $groups;

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
     * @var Member
     *
     * @ORM\OneToOne(targetEntity="Member")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdCreator", referencedColumnName="id")
     * })
     */
    private $creator;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function __construct()
    {
        $this->titles = new ArrayCollection();
        $this->groups = new ArrayCollection();
        $this->choices = new ArrayCollection();
        $this->contributions = new ArrayCollection();
    }

    /**
     * Set group creator
     *
     * @param Member $groupCreator
     *
     * @return Poll
     */
    public function setGroupCreator($groupCreator)
    {
        $this->groupCreator = $groupCreator;

        return $this;
    }

    /**
     * Get group creator
     *
     * @return Member
     */
    public function getGroupCreator()
    {
        return $this->groupCreator;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Poll
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
     * @return Poll
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
     * @return Poll
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
     * @param DateTime $updated
     *
     * @return Poll
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return Carbon
     */
    public function getUpdated()
    {
        return Carbon::instance($this->updated);
    }

    /**
     * Set started
     *
     * @param DateTime $started
     *
     * @return Poll
     */
    public function setStarted($started)
    {
        $this->started = $started;

        return $this;
    }

    /**
     * Get started
     *
     * @return Carbon
     */
    public function getStarted()
    {
        return Carbon::instance($this->started);
    }

    /**
     * Set ended
     *
     * @param DateTime $ended
     *
     * @return Poll
     */
    public function setEnded($ended)
    {
        $this->ended = $ended;

        return $this;
    }

    /**
     * Get ended
     *
     * @return Carbon
     */
    public function getEnded()
    {
        return Carbon::instance($this->ended);
    }

    /**
     * Set created
     *
     * @param DateTime $created
     *
     * @return Poll
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return Carbon
     */
    public function getCreated()
    {
        return Carbon::instance($this->created);
    }

    /**
     * Get Choices
     *
     * @return ArrayCollection
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * Get Contributions
     *
     * @return ArrayCollection
     */
    public function getContributions()
    {
        return $this->contributions;
    }

    /**
     * Get titles
     *
     * @return ArrayCollection
     */
    public function getTitles()
    {
        return $this->titles;
    }

    /**
     * Get groups
     *
     * @return ArrayCollection
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set formembersonly
     *
     * @param string $formembersonly
     *
     * @return Poll
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
     * @param int $idlocationslist
     *
     * @return Poll
     */
    public function setIdlocationslist($idlocationslist)
    {
        $this->idlocationslist = $idlocationslist;

        return $this;
    }

    /**
     * Get idlocationslist
     *
     * @return int
     */
    public function getIdlocationslist()
    {
        return $this->idlocationslist;
    }

    /**
     * Set idgroupslist
     *
     * @param int $idgroupslist
     *
     * @return Poll
     */
    public function setIdgroupslist($idgroupslist)
    {
        $this->idgroupslist = $idgroupslist;

        return $this;
    }

    /**
     * Get idgroupslist
     *
     * @return int
     */
    public function getIdgroupslist()
    {
        return $this->idgroupslist;
    }

    /**
     * Set idcountrieslist
     *
     * @param int $idcountrieslist
     *
     * @return Poll
     */
    public function setIdcountrieslist($idcountrieslist)
    {
        $this->idcountrieslist = $idcountrieslist;

        return $this;
    }

    /**
     * Get idcountrieslist
     *
     * @return int
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
     * @return Poll
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
     * @return Poll
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
     * @return Poll
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
     * @return Poll
     */
    public function setAllowComment($allowcomment)
    {
        $this->allowcomment = $allowcomment;

        return $this;
    }

    /**
     * Get comments allowed?
     *
     * @return string
     */
    public function getAllowComment()
    {
        return $this->allowcomment;
    }

    /**
     * Set description
     *
     * @param int $description
     *
     * @return Poll
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return int
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
     * @return Poll
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
     * @return Poll
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set creator
     *
     * @param Member $creator
     *
     * @return Poll
     */
    public function setCreator(Member $creator)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return Member
     */
    public function getCreator()
    {
        return $this->creator;
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new DateTime('now');
        $this->updated = new DateTime('now');
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

    public function addChoice(PollChoice $choice): self
    {
        if (!$this->choices->contains($choice)) {
            $this->choices[] = $choice;
            $choice->setPoll($this);
        }

        return $this;
    }

    public function removeChoice(PollChoice $choice): self
    {
        if ($this->choices->contains($choice)) {
            $this->choices->removeElement($choice);
            // set the owning side to null (unless already changed)
            if ($choice->getPoll() === $this) {
                $choice->setPoll(null);
            }
        }

        return $this;
    }

    public function addContribution(PollContribution $contribution): self
    {
        if (!$this->contributions->contains($contribution)) {
            $this->contributions[] = $contribution;
            $contribution->setPoll($this);
        }

        return $this;
    }

    public function removeContribution(PollContribution $contribution): self
    {
        if ($this->contributions->contains($contribution)) {
            $this->contributions->removeElement($contribution);
            // set the owning side to null (unless already changed)
            if ($contribution->getPoll() === $this) {
                $contribution->setPoll(null);
            }
        }

        return $this;
    }

    public function addTitle(Translation $title): self
    {
        if (!$this->titles->contains($title)) {
            $this->titles[] = $title;
        }

        return $this;
    }

    public function removeTitle(Translation $title): self
    {
        if ($this->titles->contains($title)) {
            $this->titles->removeElement($title);
        }

        return $this;
    }

    public function addGroup(Group $group): self
    {
        if (!$this->groups->contains($group)) {
            $this->groups[] = $group;
        }

        return $this;
    }

    public function removeGroup(Group $group): self
    {
        if ($this->groups->contains($group)) {
            $this->groups->removeElement($group);
        }

        return $this;
    }
}
