<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Preference.
 *
 * @ORM\Table(name="preferences", uniqueConstraints={@ORM\UniqueConstraint(name="codeName", columns={"codeName"})})
 * @ORM\Entity(readOnly=true)
 */
class Preference
{
    public const MESSAGE_AND_REQUEST_FILTER = 'PreferenceMessageFilter';
    public const FORUM_FILTER = 'PreferenceForumFilter';
    public const SHOW_MAP = 'PreferenceShowMap';
    public const SHOW_SEARCH_OPTIONS = 'PreferenceShowSearchOptions';
    public const SHOW_ONLINE_ACTIVITIES = 'PreferenceOnlineActivities';
    public const NEWSLETTERS_VIA_EMAIL = 'PreferenceAcceptNewsByMail';
    public const LOCAL_EVENT_NOTIFICATIONS = 'PreferenceLocalEvent';
    public const LOCALE = 'PreferenceLanguage';
    public const HTML_MAILS = 'PreferenceHtmlMails';
    public const NUMBER_FORUM_POSTS = 'ForumThreadsOnLandingPage';
    public const NUMBER_GROUPS_POSTS = 'GroupsThreadsOnLandingPage';
    public const SHOW_MY_GROUP_POSTS_ONLY = 'ShowMyGroupsTopicsOnly';
    public const ACTIVITIES_NEAR_ME_RADIUS = 'ActivitiesNearMeRadius';
    public const READ_COMMENT_GUIDELINES = 'ReadCommentGuidelines';
    public const FORUM_ORDER_LIST_ASC = 'PreferenceForumOrderListAsc';
    public const TRIPS_VICINITY_RADIUS = 'TripLegsVicinityRadius';
    public const SEARCH_OPTIONS = 'SearchOptions';

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="codeName", type="string", length=30, nullable=false)
     */
    private $codename;

    /**
     * @var string
     *
     * @ORM\Column(name="codeDescription", type="string", length=30, nullable=false)
     */
    private $codedescription;

    /**
     * @var string
     *
     * @ORM\Column(name="Description", type="text", length=65535, nullable=false)
     */
    private $description;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var string
     *
     * @ORM\Column(name="DefaultValue", type="text", length=255, nullable=false)
     */
    private $defaultValue;

    /**
     * @var string
     *
     * @ORM\Column(name="PossibleValues", type="text", length=255, nullable=false)
     */
    private $possibleValues;

    /**
     * @var string
     *
     * @ORM\Column(name="Status", type="string", nullable=false)
     */
    private $status = 'Inactive';

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Get position.
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Get codename.
     *
     * @return string
     */
    public function getCodename()
    {
        return $this->codename;
    }

    /**
     * Get codedescription.
     *
     * @return string
     */
    public function getCodedescription()
    {
        return $this->codedescription;
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
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Get default value.
     *
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * Get possiblevalues.
     *
     * @return string
     */
    public function getPossibleValues()
    {
        return $this->possibleValues;
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
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function setCodename(string $codename): self
    {
        $this->codename = $codename;

        return $this;
    }

    public function setCodedescription(string $codedescription): self
    {
        $this->codedescription = $codedescription;

        return $this;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function setDefaultValue(string $defaultValue): self
    {
        $this->defaultValue = $defaultValue;

        return $this;
    }

    public function setPossibleValues(string $possibleValues): self
    {
        $this->possibleValues = $possibleValues;

        return $this;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
