<?php
/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\Doctrine\AccommodationType;
use App\Doctrine\GroupMembershipStatusType;
use App\Doctrine\LanguageLevelType;
use App\Doctrine\MemberStatusType;
use App\Doctrine\TypicalOfferType;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Doctrine\Persistence\ObjectManager;
use Doctrine\Persistence\ObjectManagerAware;
use Exception;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\Security\Core\Exception\RuntimeException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="members")
 * @ORM\Entity(repositoryClass="App\Repository\MemberRepository")
 * @ORM\HasLifecycleCallbacks
 *
 * @SuppressWarnings(PHPMD)
 *
 * @ApiResource(
 *     attributes={"identifiers"="username"},
 *     security="is_granted('ROLE_USER')",
 *     collectionOperations={},
 *     itemOperations={
 *          "get"={"normalization_context"={"groups"={"Member:Read"}}}
 *     }
 * )
 */
class Member
    implements
        \Serializable,
        UserInterface,
        PasswordHasherAwareInterface,
        PasswordAuthenticatedUserInterface
{
    public const ROLE_ADMIN_ACCEPTER = 'ROLE_ADMIN_ACCEPTER';
    public const ROLE_ADMIN_ADMIN = 'ROLE_ADMIN_ADMIN';
    public const ROLE_ADMIN_CHECKER = 'ROLE_ADMIN_CHECKER';
    public const ROLE_ADMIN_COMMENTS = 'ROLE_ADMIN_COMMENTS';
    public const ROLE_ADMIN_COMMUNITYNEWS = 'ROLE_ADMIN_COMMUNITYNEWS';
    public const ROLE_ADMIN_FAQ = 'ROLE_ADMIN_FAQ';
    public const ROLE_ADMIN_FLAGS = 'ROLE_ADMIN_FLAGS';
    public const ROLE_ADMIN_FORUMMODERATOR = 'ROLE_ADMIN_FORUMMODERATOR';
    public const ROLE_ADMIN_GROUP = 'ROLE_ADMIN_GROUP';
    public const ROLE_ADMIN_LOGS = 'ROLE_ADMIN_LOGS';
    public const ROLE_ADMIN_MANAGESUBSCRIPTIONS = 'ROLE_ADMIN_MANAGESUBSCRIPTIONS';
    public const ROLE_ADMIN_MASSMAIL = 'ROLE_ADMIN_MASSMAIL';
    public const ROLE_ADMIN_NEWMEMBERSBEWELCOME = 'ROLE_ADMIN_NEWMEMBERSBEWELCOME';
    public const ROLE_ADMIN_POLL = 'ROLE_ADMIN_POLL';
    public const ROLE_ADMIN_PROFILE = 'ROLE_ADMIN_PROFILE';
    public const ROLE_ADMIN_RIGHTS = 'ROLE_ADMIN_RIGHTS';
    public const ROLE_ADMIN_SAFETYTEAM = 'ROLE_ADMIN_SAFETYTEAM';
    public const ROLE_ADMIN_SQLFORVOLUNTEERS = 'ROLE_ADMIN_SQLFORVOLUNTEERS';
    public const ROLE_ADMIN_TREASURER = 'ROLE_ADMIN_TREASURER';
    public const ROLE_ADMIN_WORDS = 'ROLE_ADMIN_WORDS';

    public const MEMBER_FIRSTNAME_HIDDEN = 1;
    public const MEMBER_SECONDNAME_HIDDEN = 2;
    public const MEMBER_LASTNAME_HIDDEN = 4;

    /**
     * @ORM\Column(name="Username", type="string", length=32, nullable=false)
     *
     * @Groups({"Member:Read"})
     *
     * @ApiProperty(identifier=true)
     *
     * @ApiFilter(SearchFilter::class, strategy="exact")
     */
    protected string $username;

    /**
     * @ORM\Column(name="Email", type="string", nullable=false)
     *
     * @Groups({"Member:Read:Owner"})
     */
    protected string $email;

    /**
     * @ORM\Column(name="LastLogin", type="datetime", nullable=true)
     *
     * @Groups({"Member:Read"})
     */
    protected ?DateTime $lastLogin = null;

    /**
     * @ORM\Column(name="PassWord", type="string", length=100, nullable=true)
     */
    protected ?string $password = null;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ApiProperty(identifier=false)
     */
    protected int $id;

    /**
     * @ORM\Column(name="Status", type="member_status", nullable=false)
     */
    private string $status = "";

    /**
     * @ORM\ManyToOne(targetEntity="NewLocation")
     * @ORM\JoinColumn(name="IdCity", referencedColumnName="geonameId", nullable=true)
     *
     * @Groups({"Member:Read"})
     *
     * @ApiFilter(SearchFilter::class, strategy="ipartial", properties={"city.name", "city.country.name"})
     * @ApiFilter(SearchFilter::class, strategy="exact", properties={"city.latitude", "city.longitude"})
     */
    private ?NewLocation $city = null;

    /**
     * @ORM\Column(name="Latitude", type="decimal", precision=10, scale=7, nullable=true)
     */
    private ?string $latitude;

    /**
     * @ORM\Column(name="Longitude", type="decimal", precision=10, scale=7, nullable=true)
     */
    private ?string $longitude;

    /**
     * @ORM\Column(name="NbRemindWithoutLogingIn", type="integer", nullable=false)
     */
    private int $remindersWithOutLogin = 0;

    /**
     * @ORM\Column(name="FirstName", type="string", nullable=false)
     *
     * @Groups({"Member:Read"})
     */
    private string $firstName = '';

    /**
     * @ORM\Column(name="SecondName", type="string", nullable=true)
     *
     * @Groups({"Member:Read"})
     */
    private ?string $secondName = null;

    /**
     * @ORM\Column(name="LastName", type="string", nullable=false)
     *
     * @Groups({"Member:Read"})
     */
    private string $lastName = '';

    /**
     * @ORM\Column(name="HideAttribute", type="integer", nullable=false)
     */
    private int $hideAttribute = self::MEMBER_FIRSTNAME_HIDDEN | self::MEMBER_SECONDNAME_HIDDEN | self::MEMBER_LASTNAME_HIDDEN;

    /**
     * @ORM\Column(name="Accomodation", type="accommodation", nullable=true)
     *
     * @Groups({"Member:Read"})
     *
     * @ApiFilter(SearchFilter::class, strategy="exact")
     */
    private ?string $accommodation = null;

    /**
     * @ORM\Column(name="AdditionalAccomodationInfo", type="integer", nullable=false)
     */
    private int $additionalAccommodationInfo = 0;

    /**
     * @ORM\Column(name="ILiveWith", type="integer", nullable=false)
     */
    private int $iLiveWith = 0;

    /**
     * @ORM\Column(name="InformationToGuest", type="integer", nullable=false)
     */
    private int $informationForGuest = 0;

    /**
     * @ORM\Column(name="TypicOffer", type="typical_offer", nullable=false)
     *
     * @Groups({"Member:Read"})
     */
    private string $typicalOffer = '';

    /**
     * @ORM\Column(name="Offer", type="integer", nullable=false)
     */
    private int $offer = 0;

    /**
     * @ORM\Column(name="MaxGuest", type="integer", nullable=false)
     *
     * @Groups({"Member:Read"})
     *
     * @ApiFilter(SearchFilter::class, strategy="exact")
     */
    private int $maxGuest = 1;

    /**
     * @ORM\Column(name="MaxLenghtOfStay", type="integer", nullable=false)
     */
    private int $maxLengthOfStay = 0;

    /**
     * @ORM\Column(name="Organizations", type="integer", nullable=false)
     */
    private int $organizations = 0;

    /**
     * @ORM\Column(name="Restrictions", type="string", nullable=false)
     *
     * @Groups({"Member:Read"})
     */
    private string $restrictions = '';

    /**
     * @ORM\Column(name="OtherRestrictions", type="integer", nullable=false)
     */
    private int $otherRestrictions = 0;

    /**
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private DateTime $updated;

    /**
     * @ORM\Column(name="created", type="datetime", nullable=false)
     *
     * @Groups({"Member:Read"})
     */
    private DateTime $created;

    /**
     * @ORM\Column(name="ProfileSummary", type="integer", nullable=false)
     */
    private int $profileSummary = 0;

    /**
     * @ORM\Column(name="Occupation", type="integer", nullable=false)
     */
    private int $occupation = 0;

    /**
     * @ORM\Column(name="Gender", type="string", nullable=false)
     */
    private string $gender = 'IDontTell';

    /**
     * @ORM\Column(name="HideGender", type="string", nullable=false)
     */
    private string $hideGender = 'No';

    /**
     * @ORM\Column(name="GenderOfGuest", type="string", nullable=false)
     */
    private string $genderOfGuest = 'any';

    /**
     * @ORM\Column(name="HideBirthDate", type="string", nullable=false)
     */
    private string $hideAge = 'No';

    /**
     * @ORM\Column(name="BirthDate", type="date", nullable=true)
     */
    private ?DateTime $birthdate = null;

    /**
     * @ORM\Column(name="AdressHidden", type="string", nullable=false)
     */
    private string $adressHidden = 'Yes';

    /**
     * @ORM\Column(name="WebSite", type="text", length=255, nullable=true)
     */
    private ?string $website = null;

    /**
     * @ORM\Column(name="chat_SKYPE", type="text", length=255, nullable=true)
     */
    private ?string $chatSkype = null;

    /**
     * @ORM\Column(name="chat_ICQ", type="text", length=255, nullable=true)
     */
    private ?string $chatIcq = null;

    /**
     * @ORM\Column(name="chat_AOL", type="text", length=255, nullable=true)
     */
    private ?string $chatAol = null;

    /**
     * @ORM\Column(name="chat_MSN", type="text", length=255, nullable=true)
     */
    private ?string $chatMsn = null;

    /**
     * @ORM\Column(name="chat_YAHOO", type="text", length=255, nullable=true)
     */
    private ?string $chatYahoo = null;

    /**
     * @ORM\Column(name="chat_Others", type="text", length=255, nullable=true)
     */
    private ?string $chatOthers = null;

    /**
     * @ORM\Column(name="FutureTrips", type="integer", nullable=false)
     */
    private int $futureTrips = 0;

    /**
     * @ORM\Column(name="OldTrips", type="integer", nullable=false)
     */
    private int $oldTrips = 0;

    /**
     * @ORM\Column(name="LogCount", type="integer", nullable=false)
     */
    private int $logcount = 0;

    /**
     * @ORM\Column(name="Hobbies", type="integer", nullable=false)
     */
    private int $hobbies = 0;

    /**
     * @ORM\Column(name="Books", type="integer", nullable=false)
     */
    private int $books = 0;

    /**
     * @ORM\Column(name="Music", type="integer", nullable=false)
     */
    private int $music = 0;

    /**
     * @ORM\Column(name="PastTrips", type="integer", nullable=false)
     */
    private int $pastTrips = 0;

    /**
     * @ORM\Column(name="PlannedTrips", type="integer", nullable=false)
     */
    private int $plannedTrips = 0;

    /**
     * @ORM\Column(name="PleaseBring", type="integer", nullable=false)
     */
    private int $pleaseBring = 0;

    /**
     * @ORM\Column(name="OfferGuests", type="integer", nullable=false)
     */
    private int $offerGuests = 0;

    /**
     * @ORM\Column(name="OfferHosts", type="integer", nullable=false)
     */
    private int $offerHosts = 0;

    /**
     * @ORM\Column(name="PublicTransport", type="integer", nullable=false)
     */
    private int $publicTransport = 0;

    /**
     * @ORM\Column(name="Movies", type="integer", nullable=false)
     */
    private int $movies = 0;

    /**
     * @ORM\Column(name="chat_GOOGLE", type="integer", nullable=false)
     */
    private int $chatGoogle = 0;

    /**
     * @ORM\Column(name="LastSwitchToActive", type="datetime", nullable=true)
     */
    private ?DateTime $lastSwitchToActive = null;

    /**
     * @ORM\Column(name="bewelcomed", type="boolean", nullable=false)
     */
    private bool $beWelcomed = false;

    /**
     * @ORM\Column(name="registration_key", type="string", nullable=true)
     */
    private ?string $registrationKey = null;

    /**
     * @ORM\Column(name="hosting_interest", type="integer", nullable=true)
     */
    private ?int $hostingInterest = null;

    /**
     * @ORM\OneToMany(targetEntity="CryptedField", mappedBy="member")
     */
    private Collection $fields;

    /**
     * @ORM\OneToMany(targetEntity="RightVolunteer", mappedBy="member", fetch="EXTRA_LAZY")
     */
    private Collection $volunteerRights;

    /**
     * @ORM\OneToMany(targetEntity="GroupMembership", mappedBy="member", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private Collection $groupMemberships;

    /**
     * @ORM\OneToMany(targetEntity="MembersLanguagesLevel", mappedBy="member")
     *
     * @Groups({"Member:Read"})
     *
     * @ApiFilter(SearchFilter::class, strategy="exact", properties={"languageLevels.level", "languageLevels.language.name", "languageLevels.language.englishname", "languageLevels.language.shortCode"})
     */
    private Collection $languageLevels;

    private array $memberFields;

    private Collection $comments;

    /**
     * @ORM\OneToMany(targetEntity="Relation", mappedBy="receiver", fetch="EXTRA_LAZY")
     */
    private Collection $relations;

    /**
     * @ORM\OneToMany(targetEntity="MemberPreference", mappedBy="member")
     */
    private Collection $preferences;

    /**
     * @ORM\OneToMany(targetEntity="Address", mappedBy="member")
     */
    private Collection $addresses;

    private ?Language $preferredLanguage = null;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->volunteerRights = new ArrayCollection();
        $this->cryptedFields = new ArrayCollection();
        $this->groupMemberships = new ArrayCollection();
        $this->languageLevels = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->relationships = new ArrayCollection();
        $this->preferences = new ArrayCollection();
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setCity(NewLocation $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getCity(): ?NewLocation
    {
        return $this->city;
    }

    public function setLatitude(?float $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLongitude(?float $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setRemindersWithOutLogin(int $remindersWithOutLogin): self
    {
        $this->remindersWithOutLogin = $remindersWithOutLogin;

        return $this;
    }

    public function getRemindersWithOutLogin(): int
    {
        return $this->remindersWithOutLogin;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setSecondName(?string $secondName): self
    {
        $this->secondName = $secondName;

        return $this;
    }

    public function getSecondName(): ?string
    {
        return $this->secondName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setAccommodation(?string $accommodation): self
    {
        $this->accommodation = $accommodation;

        return $this;
    }

    public function getAccommodation(): ?string
    {
        return $this->accommodation;
    }

    public function setAdditionalAccommodationinfo(int $additionalAccommodationInfo): self
    {
        $this->additionalAccommodationInfo = $additionalAccommodationInfo;

        return $this;
    }

    public function getAdditionalAccommodationinfo(): int
    {
        return $this->additionalAccommodationInfo;
    }

    public function setILiveWith(string $iLiveWith): self
    {
        $this->iLiveWith = $iLiveWith;

        return $this;
    }

    public function getILiveWith(): string
    {
        return $this->iLiveWith;
    }

    public function setInformationForGuest(int $informationForGuest): self
    {
        $this->informationForGuest = $informationForGuest;

        return $this;
    }

    public function getInformationForGuest(): int
    {
        return $this->informationForGuest;
    }

    public function setTypicalOffer(string $typicalOffer): self
    {
        $this->typicalOffer = $typicalOffer;

        return $this;
    }

    public function getTypicalOffer(): string
    {
        return $this->typicalOffer;
    }

    public function setOffer(int $offer): self
    {
        $this->offer = $offer;

        return $this;
    }

    public function getOffer(): int
    {
        return $this->offer;
    }

    public function setMaxGuest(int $maxGuest): self
    {
        $this->maxGuest = $maxGuest;

        return $this;
    }

    public function getMaxGuest(): int
    {
        return $this->maxGuest;
    }

    public function setMaxLengthOfStay(int $maxLengthOfStay): self
    {
        $this->maxLengthOfStay = $maxLengthOfStay;

        return $this;
    }

    public function getMaxLengthOfStay(): int
    {
        return $this->maxLengthOfStay;
    }

    public function setOrganizations(int $organizations): self
    {
        $this->organizations = $organizations;

        return $this;
    }

    public function getOrganizations(): int
    {
        return $this->organizations;
    }

    public function setRestrictions(string $restrictions): self
    {
        $this->restrictions = $restrictions;

        return $this;
    }

    public function getRestrictions(): string
    {
        return $this->restrictions;
    }

    public function setOtherRestrictions(int $otherRestrictions): self
    {
        $this->otherRestrictions = $otherRestrictions;

        return $this;
    }

    public function getOtherRestrictions(): int
    {
        return $this->otherRestrictions;
    }

    public function getUpdated(): ?Carbon
    {
        if (null !== $this->updated) {
            return Carbon::instance($this->updated);
        }

        return null;
    }

    public function getCreated(): ?Carbon
    {
        if (null !== $this->created) {
            return Carbon::instance($this->created);
        }

        return null;
    }

    public function setLastLogin(?DateTime $lastLogin): self
    {
        $this->lastLogin = $lastLogin;

        return $this;
    }

    public function getLastLogin(): ?Carbon
    {
        if (null !== $this->lastLogin) {
            return Carbon::instance($this->lastLogin);
        }

        return null;
    }

    public function setProfileSummary(int $profileSummary): self
    {
        $this->profileSummary = $profileSummary;

        return $this;
    }

    public function getProfileSummary(): int
    {
        return $this->profileSummary;
    }

    public function setOccupation(int $occupation): self
    {
        $this->occupation = $occupation;

        return $this;
    }

    public function getOccupation(): int
    {
        return $this->occupation;
    }

    public function setPassword(?string $hashedPassword): self
    {
        $this->password = $hashedPassword;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setGender(string $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getGender(): string
    {
        return $this->gender;
    }

    public function setShowGender(bool $show): self
    {
        $this->hideGender = $show ? 'No' : 'Yes';

        return $this;
    }

    public function getShowGender(): bool
    {
        return $this->hideGender === 'No';
    }

    public function setShowAge(bool $show): self
    {
        $this->hideAge = $show ? 'No' : 'Yes';

        return $this;
    }

    public function getShowAge(): string
    {
        return $this->hideAge === 'No';
    }

    public function setBirthdate(DateTime $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getBirthdate(): Carbon
    {
        return Carbon::instance($this->birthdate);
    }

    public function setAdressHidden(string $adressHidden): self
    {
        $this->adressHidden = $adressHidden;

        return $this;
    }

    public function getAdressHidden(): string
    {
        return $this->adressHidden;
    }

    public function setHobbies(int $hobbies): self
    {
        $this->hobbies = $hobbies;

        return $this;
    }

    public function getHobbies(): int
    {
        return $this->hobbies;
    }

    public function setBooks(int $books): self
    {
        $this->books = $books;

        return $this;
    }

    public function getBooks(): int
    {
        return $this->books;
    }

    public function setMusic(int $music): self
    {
        $this->music = $music;

        return $this;
    }

    public function getMusic(): int
    {
        return $this->music;
    }

    public function setPastTrips(int $pastTrips): self
    {
        $this->pastTrips = $pastTrips;

        return $this;
    }

    public function getPastTrips(): int
    {
        return $this->pastTrips;
    }

    public function setPlannedTrips(int $plannedTrips): self
    {
        $this->plannedTrips = $plannedTrips;

        return $this;
    }

    public function getPlannedTrips(): int
    {
        return $this->plannedTrips;
    }

    public function setPleaseBring(int $pleaseBring): self
    {
        $this->pleaseBring = $pleaseBring;

        return $this;
    }

    public function getPleaseBring(): int
    {
        return $this->pleaseBring;
    }

    public function setOfferGuests(int $offerGuests): self
    {
        $this->offerGuests = $offerGuests;

        return $this;
    }

    public function getOfferGuests(): int
    {
        return $this->offerGuests;
    }

    public function setOfferHosts(int $offerHosts): self
    {
        $this->offerHosts = $offerHosts;

        return $this;
    }

    public function getOfferHosts(): int
    {
        return $this->offerHosts;
    }

    public function setPublicTransport(int $publicTransport): self
    {
        $this->publicTransport = $publicTransport;

        return $this;
    }

    public function getPublicTransport(): int
    {
        return $this->publicTransport;
    }

    public function setMovies(int $movies): self
    {
        $this->movies = $movies;

        return $this;
    }

    public function getMovies(): int
    {
        return $this->movies;
    }

    public function setLastSwitchToActive(?DateTime $lastSwitchToActive): self
    {
        $this->lastSwitchToActive = $lastSwitchToActive;

        return $this;
    }

    public function getLastSwitchToActive(): ?DateTime
    {
        return $this->lastSwitchToActive;
    }

    public function setBeWelcomed(bool $beWelcomed): self
    {
        $this->beWelcomed = $beWelcomed;

        return $this;
    }

    public function getBeWelcomed(): bool
    {
        return $this->beWelcomed;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function serialize(): string
    {
        return serialize([
            $this->id,
            $this->username,
            $this->password,
            // see section on salt below
            // $this->salt,
        ]);
    }

    public function unserialize($serialized): void
    {
        list(
            $this->id,
            $this->username,
            $this->password,
            ) = unserialize($serialized);
    }

    public function getRoles(): array
    {
        // Grant user role to everyone
        $roles = [
            'ROLE_USER',
        ];

        $volunteerRights = $this->getVolunteerRights();
        if (null !== $volunteerRights) {
            foreach ($volunteerRights->getIterator() as $volunteerRight) {
                if (0 !== $volunteerRight->getLevel()) {
                    $roles[] = 'ROLE_ADMIN_' . strtoupper($volunteerRight->getRight()->getName());
                }
            }

            // If additional roles are found add ROLE_ADMIN as well to get past the /admin firewall
            if (\count($roles) > 1) {
                $roles[] = 'ROLE_ADMIN';
            }
        }

        return $roles;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }

    public function isPrivileged(): bool
    {
        if (\in_array('ROLE_ADMIN', $this->getRoles(), true)) {
            return true;
        }

        return false;
    }

    public function getLocale(): string
    {
        return 'en';
    }

    public function getVolunteerRights(): Collection
    {
        return $this->volunteerRights;
    }

    public function getGroups()
    {
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('status', GroupMembershipStatusType::CURRENT_MEMBER));

        // get all groups, work around problem with database
        return array_map(
            function ($groupMembership) {
                try {
                    return $groupMembership->getGroup();
                } catch (\Exception $e) {
                }
            },
            $this->groupMemberships->matching($criteria)->toArray()
        );
    }

    public function getComments(): Collection
    {
        return $this->comments;
    }

    public function addField(CryptedField $field): self
    {
        $this->fields[] = $field;

        return $this;
    }

    public function removeField(CryptedField $field): void
    {
        $this->fields->removeElement($field);
    }

    public function getFields(): Collection
    {
        return $this->fields;
    }

    public function getMemberFields(): array
    {
        return $this->memberFields;
    }

    public function addVolunteerRight(RightVolunteer $volunteerRight): self
    {
        if (!$this->volunteerRights->contains($volunteerRight)) {
            $this->volunteerRights->add($volunteerRight);
        }

        return $this;
    }

    public function removeVolunteerRight(RightVolunteer $volunteerRight): void
    {
        $this->volunteerRights->removeElement($volunteerRight);
    }

    public function getAddresses(): Collection
    {
        return $this->addresses;
    }

    public function addAddress(Address $address): self
    {
        if (!$this->addresses->contains($address)) {
            $this->addresses[] = $address;
        }

        return $this;
    }

    public function removeAddress(Address $address): void
    {
        $this->addresses->removeElement($address);
    }

    public function getGroupMemberships(): Collection
    {
        return $this->groupMemberships;
    }

    public function addGroupMembership(GroupMembership $groupMembership): self
    {
        if (!$this->groupMemberships->contains($groupMembership)) {
            $this->groupMemberships->add($groupMembership);
        }

        return $this;
    }

    public function removeGroupMembership(GroupMembership $groupMembership): void
    {
        $this->groupMemberships->removeElement($groupMembership);
    }

    public function getField($fieldName, bool $decrypt = true, string $prefix = 'members')
    {
        $stripped = '';
        $criteria = Criteria::create()
            ->where(Criteria::expr()->eq('tablecolumn', $prefix . '.' . $fieldName));
        $field = $this->fields->matching($criteria)->first();
        if (false !== $field && true === $decrypt) {
            $value = $field->getMemberCryptedValue();
            $stripped = strip_tags($value);
        }

        return $stripped;
    }

    public function setHideAttribute($hideAttribute): self
    {
        $this->hideAttribute = $hideAttribute;

        return $this;
    }

    public function getHideAttribute(): int
    {
        return $this->hideAttribute;
    }

    public function setHostingInterest(?int $hostingInterest): self
    {
        $this->hostingInterest = $hostingInterest;

        return $this;
    }

    public function getHostingInterest(): ?int
    {
        return $this->hostingInterest;
    }

    public function hasRight(string $nameOfRight): bool
    {
        $hasRight = false;
        $volunteerRights = $this->getVolunteerRights();

        /** @var RightVolunteer $volunteerRight */
        foreach ($volunteerRights->getIterator() as $volunteerRight) {
            if ($volunteerRight->getRight()->getName() === $nameOfRight) {
               $hasRight = true;
               break;
            }
        }

        return $hasRight;
    }

    public function hasRightsForLocale($locale): bool
    {
        $hasRight = false;
        $volunteerRights = $this->getVolunteerRights();

        /* \todo find way to define rights name */
        /** @var RightVolunteer $volunteerRight */
        foreach ($volunteerRights->getIterator() as $volunteerRight) {
            if ($volunteerRight->getRight()->getName() === 'Words') {
                $strScope = str_replace('"', '', str_replace(',', ';', $volunteerRight->getScope()));
                $scope = explode(';', $strScope);
                if (\in_array($locale, $scope, true)) {
                    $hasRight = true;
                }
                if (\in_array('All', $scope, true)) {
                    $hasRight = true;
                }
            }
        }

        return $hasRight;
    }

    public function getLevelForRight($nameOfRight): int
    {
        $nameOfRight = strtolower(str_replace('ROLE_ADMIN_', '', $nameOfRight));
        $level = false;
        $volunteerRights = $this->getVolunteerRights();

        /** @var RightVolunteer $volunteerRight */
        foreach ($volunteerRights->getIterator() as $volunteerRight) {
            if ($volunteerRight->getRight()->getName() === $nameOfRight) {
                $level = $volunteerRight->getLevel();
            }
        }

        return $level;
    }

    public function getScopeForRight($nameOfRight): array
    {
        $nameOfRight = strtolower(str_replace('ROLE_ADMIN_', '', $nameOfRight));
        $scope = [];
        $volunteerRights = $this->getVolunteerRights();

        /** @var RightVolunteer $volunteerRight */
        foreach ($volunteerRights->getIterator() as $volunteerRight) {
            if ($volunteerRight->getRight()->getName() === $nameOfRight) {
                $scopes = str_replace(';', ',', str_replace('"', '', $volunteerRight->getScope()));
                $scope = explode(',', $scopes);
            }
        }

        return $scope;
    }

    public function isBrowsable(): bool
    {
        if (\in_array(
            $this->status,
            [
                MemberStatusType::TAKEN_OUT,
                MemberStatusType::SUSPENDED,
                MemberStatusType::ASKED_TO_LEAVE,
                MemberStatusType::BUGGY,
                MemberStatusType::BANNED,
                MemberStatusType::REJECTED,
                MemberStatusType::PASSED_AWAY,
                MemberStatusType::DUPLICATE_SIGNED,
            ],
            true
        )) {
            return false;
        }

        return true;
    }

    public function isSuspended(): bool
    {
        $suspended = (MemberStatusType::SUSPENDED === $this->status) ? true : false;

        return $suspended;
    }

    public function isExpired(): bool
    {
        $askedToLeave = (MemberStatusType::ASKED_TO_LEAVE === $this->status) ? true : false;

        return $askedToLeave;
    }

    public function isBanned(): bool
    {
        return MemberStatusType::BANNED === $this->status;
    }

    public function isDeniedAccess(): bool
    {
        return !in_array(
            $this->status,
            [
                MemberStatusType::ACTIVE,
                MemberStatusType::OUT_OF_REMIND,
                MemberStatusType::ACCOUNT_ACTIVATED,
                MemberStatusType::MAIL_CONFIRMED,
                MemberStatusType::AWAITING_MAIL_CONFIRMATION,
            ],
            true
        );
    }

    public function isNotConfirmedYet(): bool
    {
        return MemberStatusType::AWAITING_MAIL_CONFIRMATION === $this->status;
    }

    public function didConfirmMailAddress(): bool
    {
        return MemberStatusType::MAIL_CONFIRMED === $this->status;
    }

    public function isFirstnameShown(): bool
    {
        return ($this->hideAttribute & self::MEMBER_FIRSTNAME_HIDDEN) !== self::MEMBER_FIRSTNAME_HIDDEN;
    }

    public function getFirstnameOrUsername(): string
    {
        if ($this->isFirstnameShown()) {
            return $this->getFirstname();
        }

        return $this->username;
    }

    public function getLanguageLevels(): array
    {
        return array_filter(
            $this->languageLevels->toArray(),
            function (/** @var MembersLanguagesLevel */ $k) {
                try {
                    // Make sure language exists in database
                    $language = $k->getLanguage();
                    $language->getName();
                } catch(Exception $e) {
                    return false;
                }
                return true;
            }
        );
    }

    public function getSkilledLanguageLevels(): array
    {
        $criteria = Criteria::create()->where(Criteria::expr()->orX(
            Criteria::expr()->neq('level', LanguageLevelType::BEGINNER),
            Criteria::expr()->eq('level', LanguageLevelType::HELLO_ONLY)
        ));

        return $this->languageLevels->matching($criteria)
            ->toArray();
    }

    public function addLanguageLevel(MembersLanguagesLevel $level): self
    {
        if (!$this->languageLevels->contains($level)) {
            $this->languageLevels->add($level);
            $level->setMember($this);
        }

        return $this;
    }

    public function removeLanguageLevel(MembersLanguagesLevel $level): self
    {
        if ($this->languageLevels->contains($level)) {
            $this->languageLevels->removeElement($level);
            $level->setMember(null);
        }

        return $this;
    }

    public function getLanguages(): array
    {
        return array_map(
            function ($level) {
                return $level->getLanguage();
            },
            $this->languageLevels->toArray()
        );
    }

    public function getMemberPreference(Preference $preference): MemberPreference
    {
        // Check if member has preference
        $criteria = Criteria::create()->where(Criteria::expr()->eq('preference', $preference));

        $memberPreference = $this->preferences->matching($criteria)->first();
        if (false === $memberPreference) {
            $memberPreference = new MemberPreference();
            $memberPreference->setMember($this);
            $memberPreference->setPreference($preference);
            $memberPreference->setValue($preference->getDefaultValue());
        }

        return $memberPreference;
    }

    public function getMemberPreferenceValue(Preference $preference): string
    {
        $value = $preference->getDefaultValue();

        // Check if member has preference
        $criteria = Criteria::create()->where(Criteria::expr()->eq('preference', $preference));

        $match = $this->preferences->matching($criteria)->first();
        if ($match) {
            $value = $match->getValue();
        }

        return $value;
    }

    /**
     * Triggered on insert.
     *
     * @ORM\PrePersist
     */
    public function onPrePersist(): void
    {
        $this->created = new DateTime('now');
        $this->updated = $this->created;
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

    public function getPreferredLanguage(): ?Language
    {
        return $this->preferredLanguage;
    }

    public function initializePreferredLanguage(EntityManagerInterface $entityManager): void
    {
        // Get preference for locale
        $preferenceRepository = $entityManager->getRepository(Preference::class);
        /** @var Preference $preference */
        $preference = $preferenceRepository->findOneBy([
            'codename' => Preference::LOCALE,
        ]);
        $languageId = $this->getMemberPreferenceValue($preference);

        $languageRepository = $entityManager->getRepository(Language::class);
        /** @var Language $language */
        $language = $languageRepository->findOneBy([
            'id' => $languageId,
        ]);
        if (null === $language) {
            // Language doesn't exist but should!
            // Return English in this case
            $language = $languageRepository->findOneBy([
                'shortCode' => 'en',
            ]);
        }

        $this->preferredLanguage = $language;
    }

    /**
     * @return Collection|MemberPreference[]
     */
    public function getPreferences(): Collection
    {
        return $this->preferences;
    }

    public function addPreference(MemberPreference $preference): self
    {
        if (!$this->preferences->contains($preference)) {
            $this->preferences[] = $preference;
            $preference->setMember($this);
        }

        return $this;
    }

    public function removePreference(MemberPreference $preference): self
    {
        if ($this->preferences->contains($preference)) {
            $this->preferences->removeElement($preference);
            // set the owning side to null (unless already changed)
            if ($preference->getMember() === $this) {
                $preference->setMember(null);
            }
        }

        return $this;
    }

    /**
     * @ORM\PostLoad
     */
    public function postLoad(PostLoadEventArgs $args)
    {
        $entityManager = $args->getObjectManager();

        $this->initializeMemberFields($entityManager);

        $this->initializePreferredLanguage($entityManager);
    }

    /**
     * Provides an array of all translated items of a profile.
     *
     * Is called when a member is loaded from the database (adds a penalty)
     */
    public function initializeMemberFields(EntityManagerInterface $entityManager): array
    {
        $memberTranslationRepository = $entityManager->getRepository(MemberTranslation::class);
        /** @var MemberTranslation[] $memberTranslations */
        $memberTranslations = $memberTranslationRepository->findBy(['owner' => $this]);

        $memberFields = [];
        foreach ($memberTranslations as $memberTranslation) {
            $tableColumn = $memberTranslation->getTableColumn();
            if ('members.' !== substr($tableColumn, 0, 8)) {
                continue;
            }
            $tableColumn = str_ireplace('members.', '', $tableColumn);

            $memberFields[$memberTranslation->getLanguage()->getShortCode()][$tableColumn] = $memberTranslation->getSentence();
        }

        // Normalize array: make sure for all locales all fields are set, use first locale as fallback for the other
        $fallback = array_key_first($memberFields);
        $fields = [
            'Occupation',
            'ILiveWith',
            'MaxLenghtOfStay',
            'MotivationForHospitality',
            'Offer',
            'Organizations',
            'AdditionalAccomodationInfo',
            'OtherRestrictions',
            'InformationToGuest',
            'Hobbies',
            'Books',
            'Music',
            'Movies',
            'PleaseBring',
            'OfferGuests',
            'OfferHosts',
            'PublicTransport',
            'PastTrips',
            'PlannedTrips',
            'ProfileSummary',
        ];

        foreach (array_keys($memberFields) as $locale) {
            foreach ($fields as $field) {
                if (!isset($memberFields[$locale][$field])) {
                    // Check if field exists in fallback locale
                    if (isset($memberFields[$fallback][$field])) {
                        $memberFields[$locale][$field] = $memberFields[$fallback][$field];
                    } else {
                        // Hack. Set field to empty value and make sure it is also set for next next locale
                        $memberFields[$fallback][$field] = '';
                        $memberFields[$locale][$field] = '';
                    }
                }
            }
        }
        $this->memberFields = $memberFields;

        return $this->memberFields;
    }

    public function getRegistrationKey(): string
    {
        return $this->registrationKey;
    }

    public function setRegistrationKey(?string $registrationKey): self
    {
        $this->registrationKey = $registrationKey;

        return $this;
    }

    public function getRegion(): ?NewLocation
    {
        return $this->city->getAdmin1();
    }

    public function getCountry(): ?NewLocation
    {
        return $this->city->getCountry();
    }

    /**
     * @Groups({"Member:Read"})
     */
    public function getAge(): int
    {
        if (null === $this->birthdate) {
            return 0;
        }

        $birthday = $this->getBirthdate();

        return $birthday->diffInYears();
    }

    /**
     * @Groups({"Member:Read"})
     */
    public function getAvatar(): string
    {
        return '/members/avatar/' . $this->getUsername();
    }

    /**
     * @Groups({"Member:Read"})
     */
    public function getName(): string
    {
        $name = '';
        if (!($this->hideAttribute & self::MEMBER_FIRSTNAME_HIDDEN)) {
            $name .= $this->firstName . ' ';
        }
        if (!($this->hideAttribute & self::MEMBER_SECONDNAME_HIDDEN)) {
            $name .= $this->secondName. ' ';
        }
        if (!($this->hideAttribute & self::MEMBER_LASTNAME_HIDDEN)) {
            $name .= $this->lastName;
        }

        return $name;
    }

    public function getPasswordHasherName(): ?string
    {
        if (preg_match('/^\*[0-9A-F]{40}$/', $this->getPassWord())) {
            // Use migrating password hasher in case of legacy password
            return null;
        }

        if ($this->isPrivileged()) {
            return 'harsh';
        }

        return null;
    }

    public function getRelations(): Collection
    {
        return $this->relations;
    }

    public function addRelation(Relation $relation): self
    {
        if (!$this->relations->contains($relation)) {
            $this->relations[] = $relation;
            $relation->setOwner($this);
        }

        return $this;
    }

    public function removeRelation(Relation $relation): self
    {
        if ($this->relations->contains($relation)) {
            $this->relations->removeElement($relation);

            if ($relation->getOwner() === $this) {
                $relation->setOwner(null);
            }
        }

        return $this;
    }
}
