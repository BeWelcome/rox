<?php

/*
 * @codingStandardsIgnoreFile
 *
 * Auto generated file ignore for Code Sniffer
 */

namespace App\Entity;

use App\Doctrine\GroupMembershipStatusType;
use App\Doctrine\LanguageLevelType;
use App\Doctrine\MemberStatusType;
use App\Repository\MemberRepository;
use Carbon\Carbon;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use Gedmo\Mapping\Annotation as Gedmo;
use Serializable;
use Stringable;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherAwareInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Do not check entities with PHPMD.
 *
 * @SuppressWarnings("PHPMD")
 */
#[ORM\Table(name: 'member')]
#[ORM\Entity(repositoryClass: MemberRepository::class)]
#[Gedmo\TranslationEntity(class: MemberTranslation::class)]
#[ORM\HasLifecycleCallbacks]
class Member implements Stringable, Serializable, UserInterface, PasswordHasherAwareInterface, PasswordAuthenticatedUserInterface
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
    public const ROLE_ADMIN_MemberSBEWELCOME = 'ROLE_ADMIN_MemberSBEWELCOME';
    public const ROLE_ADMIN_POLL = 'ROLE_ADMIN_POLL';
    public const ROLE_ADMIN_PROFILE = 'ROLE_ADMIN_PROFILE';
    public const ROLE_ADMIN_RIGHTS = 'ROLE_ADMIN_RIGHTS';
    public const ROLE_ADMIN_SAFETYTEAM = 'ROLE_ADMIN_SAFETYTEAM';
    public const ROLE_ADMIN_SQLFORVOLUNTEERS = 'ROLE_ADMIN_SQLFORVOLUNTEERS';
    public const ROLE_ADMIN_TREASURER = 'ROLE_ADMIN_TREASURER';
    public const ROLE_ADMIN_WORDS = 'ROLE_ADMIN_WORDS';

    public const int NAME_HIDDEN = 1;
    public const int GENDER_HIDDEN = 2;
    public const int AGE_HIDDEN = 4;
    public const int ADDRESS_HIDDEN = 16;

    public const int DEFAULT_HIDDEN =
        self::NAME_HIDDEN | self::GENDER_HIDDEN | self::AGE_HIDDEN | self::ADDRESS_HIDDEN;

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected int $id;

    #[ORM\Column(name: 'Username', type: 'string', length: 32, nullable: false)]
    protected string $username;

    #[ORM\Column(name: 'Email', type: 'string', nullable: false)]
    protected string $email;

    #[ORM\Column(name: 'NewEmail', type: 'string', nullable: true)]
    protected ?string $newEmail;

    #[ORM\Column(name: 'Locale', type: 'string', length: 8, nullable: false)]
    protected string $locale = 'en';

    #[ORM\Column(name: 'LastActive', type: 'datetime', nullable: true)]
    protected ?DateTime $lastActive = null;

    #[ORM\Column(name: 'PassWord', type: 'string', length: 100, nullable: true)]
    protected ?string $password = null;

    #[ORM\Column(name: 'Status', type: 'member_status', nullable: false)]
    private string $status = 'Incomplete';

    #[ORM\Column(name: 'Reminders', type: 'integer', nullable: false)]
    private int $remindersWithOutLogin = 0;

    /* length set to 780 as original DB design has 255 per name part */
    #[ORM\Column(name: 'Name', type: 'text', length: 780, nullable: true)]
    private ?string $name = null;

    #[ORM\Column(name: 'ShortName', type: 'string', nullable: true)]
    private ?string $shortName = null;

    #[ORM\Column(name: 'HideAttribute', type: 'integer', nullable: false)]
    private int $hideAttribute = self::DEFAULT_HIDDEN;

    #[ORM\Column(name: 'ProfileLanguage', type: 'string', length: 8, nullable: true)]
    #[Gedmo\Translatable]
    private ?string $profileLanguage = null;

    #[ORM\Column(name: 'Accommodation', type: 'accommodation', nullable: true)]
    private ?string $accommodation = null;

    #[ORM\Column(name: 'AdditionalAccommodationInfo', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $additionalAccommodationInfo = null;

    #[ORM\Column(name: 'ILiveWith', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $iLiveWith = null;

    #[ORM\Column(name: 'StandardOffers', type: 'standard_offers', nullable: true)]
    private ?string $standardOffers = null;

    #[ORM\Column(name: 'MaxGuests', type: 'integer', nullable: false)]
    private int $maxGuests = 1;

    #[ORM\Column(name: 'MaxLengthOfStay', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $maxLengthOfStay = null;

    #[ORM\Column(name: 'Organizations', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $organizations = null;

    #[ORM\Column(name: 'Restrictions', type: 'host_restrictions', nullable: true)]
    private ?string $restrictions = null;

    #[ORM\Column(name: 'HouseRules', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $houseRules = null;

    #[ORM\Column(name: 'created', type: 'datetime', nullable: false)]
    private ?DateTime $created = null;

    #[ORM\Column(name: 'updated', type: 'datetime', nullable: true)]
    private ?DateTime $updated = null;

    #[ORM\Column(name: 'AboutMe', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $aboutMe = null;

    #[ORM\Column(name: 'Occupation', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $occupation = null;

    #[ORM\Column(name: 'Gender', type: 'gender_type', nullable: false)]
    private string $gender = 'other';

    #[ORM\Column(name: 'BirthDate', type: 'date', nullable: true)]
    private ?DateTime $birthdate = null;

    #[ORM\Column(name: 'PastTrips', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $pastTrips = null;

    #[ORM\Column(name: 'PlannedTrips', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $plannedTrips = null;

    #[ORM\Column(name: 'Hobbies', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $hobbies = null;

    #[ORM\Column(name: 'Books', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $books = null;

    #[ORM\Column(name: 'Music', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $music = null;

    #[ORM\Column(name: 'Movies', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $movies = null;

    #[ORM\Column(name: 'PleaseBring', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $pleaseBring = null;

    #[ORM\Column(name: 'WhereYouSleep', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $whereYouSleep = null;

    #[ORM\Column(name: 'OfferGuests', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $offerGuests = null;

    #[ORM\Column(name: 'OfferHosts', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $offerHosts = null;

    #[ORM\Column(name: 'GettingThere', type: 'string', nullable: true)]
    #[Gedmo\Translatable]
    private ?string $gettingThere = null;

    #[ORM\Column(name: 'LastSwitchToActive', type: 'datetime', nullable: true)]
    private ?DateTime $lastSwitchToActive = null;

    #[ORM\Column(name: 'bewelcomed', type: 'boolean', nullable: false)]
    private bool $beWelcomed = false;

    #[ORM\Column(name: 'RegistrationKey', type: 'string', nullable: true)]
    private ?string $registrationKey = null;

    #[ORM\Column(name: 'HostingInterest', type: 'integer', nullable: true)]
    private ?int $hostingInterest = null;

    #[ORM\OneToMany(targetEntity: MemberTranslation::class, mappedBy: 'object', cascade: ['persist', 'remove'])]
    #[ORM\OrderBy(['locale' => 'ASC'])]
    private Collection $translations;

    private array $translationsIndexedByLocale = [];
    #[ORM\OneToMany(targetEntity: RightVolunteer::class, mappedBy: 'member', cascade: ['persist', 'remove'], fetch: 'EXTRA_LAZY')]
    private Collection $volunteerRights;

    #[ORM\OneToMany(targetEntity: GroupMembership::class, mappedBy: 'member', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $groupMemberships;

    #[ORM\OneToMany(targetEntity: MemberLanguageLevel::class, mappedBy: 'member', cascade: ['persist', 'remove'])]
    private Collection $languageLevels;

    #[ORM\OneToMany(targetEntity: Relation::class, mappedBy: 'receiver', cascade: ['persist', 'remove'])]
    private Collection $relations;

    #[ORM\OneToMany(targetEntity: MemberPreference::class, mappedBy: 'member', cascade: ['persist', 'remove'])]
    private Collection $preferences;

    #[ORM\OneToMany(targetEntity: Address::class, mappedBy: 'member', cascade: ['persist', 'remove'], fetch: 'EAGER')]
    private Collection $addresses;

    private ?Language $preferredLanguage = null;

    public function __construct()
    {
        $this->addresses = new ArrayCollection();
        $this->volunteerRights = new ArrayCollection();
        $this->translations = new ArrayCollection();
        $this->groupMemberships = new ArrayCollection();
        $this->languageLevels = new ArrayCollection();
        $this->relations = new ArrayCollection();
        $this->preferences = new ArrayCollection();
    }

    public function __serialize(): array
    {
        return [
            'id' => $this->id,
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    public function __unserialize(array $data): void
    {
        $this->id = $data['id'];
        $this->username = $data['username'];
        $this->password = $data['password'];
    }

    public function __toString(): string
    {
        return $this->getId() . ' ' . $this->getUsername() . ' ' . $this->getName();
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

    public function setNewEmail(?string $email): self
    {
        $this->newEmail = $email;

        return $this;
    }

    public function getNewEmail(): ?string
    {
        return $this->newEmail;
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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getVisibleName(): string
    {
        if ($this->isNameVisible()) {
            return $this->name;
        }

        if (null !== $this->shortName) {
            return $this->shortName;
        }

        return '';
    }

    public function getFullName(): ?string
    {
        return $this->name;
    }

    public function setShortName(?string $shortName): self
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getShortName(): ?string
    {
        return $this->shortName;
    }

    public function setProfileLanguage(string $profileLanguage): self
    {
        $this->profileLanguage = $profileLanguage;

        return $this;
    }

    public function getProfileLanguage(): ?string
    {
        return $this->profileLanguage;
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

    public function setAdditionalAccommodationInfo(?string $additionalAccommodationInfo): self
    {
        $this->additionalAccommodationInfo = $additionalAccommodationInfo;

        return $this;
    }

    public function getAdditionalAccommodationInfo(): ?string
    {
        return $this->additionalAccommodationInfo;
    }

    public function setILiveWith(?string $iLiveWith): self
    {
        $this->iLiveWith = $iLiveWith;

        return $this;
    }

    public function getILiveWith(): ?string
    {
        return $this->iLiveWith;
    }

    public function setStandardOffers(array $standardOffers): self
    {
        // \todo implode but check validity
        $this->standardOffers = implode(',', $standardOffers);

        return $this;
    }

    public function getStandardOffers(): array
    {
        return explode(',', $this->standardOffers);
    }

    public function setMaxGuests(int $maxGuests): self
    {
        $this->maxGuests = $maxGuests;

        return $this;
    }

    public function getMaxGuests(): int
    {
        return $this->maxGuests;
    }

    public function setMaxLengthOfStay(?string $maxLengthOfStay): self
    {
        $this->maxLengthOfStay = $maxLengthOfStay;

        return $this;
    }

    public function getMaxLengthOfStay(): ?string
    {
        return $this->maxLengthOfStay;
    }

    public function setOrganizations(?string $organizations): self
    {
        $this->organizations = $organizations;

        return $this;
    }

    public function getOrganizations(): ?string
    {
        return $this->organizations;
    }

    public function setRestrictions(array $restrictions): self
    {
        // \todo implode $restrictions but check if valid.
        $this->restrictions = implode(',', $restrictions);

        return $this;
    }

    public function getRestrictions(): array
    {
        if (empty($this->restrictions)) {
            return [];
        }

        $restrictions = explode(',', (string) $this->restrictions);

        return $restrictions;
    }

    public function setHouseRules(?string $houseRules): self
    {
        $this->houseRules = $houseRules;

        return $this;
    }

    public function getHouseRules(): ?string
    {
        return $this->houseRules;
    }

    public function getUpdated(): ?Carbon
    {
        if (null !== $this->updated) {
            return Carbon::instance($this->updated);
        }

        return null;
    }

    public function getCreated(): Carbon
    {
        return Carbon::instance($this->created);
    }

    public function setLastActive(?DateTime $lastActive): self
    {
        $this->lastActive = $lastActive;

        return $this;
    }

    public function getLastActive(): ?Carbon
    {
        if (null !== $this->lastActive) {
            return Carbon::instance($this->lastActive);
        }

        return null;
    }

    public function setAboutMe(?string $aboutMe): self
    {
        $this->aboutMe = $aboutMe;

        return $this;
    }

    public function getAboutMe(): ?string
    {
        return $this->aboutMe;
    }

    public function setOccupation(?string $occupation): self
    {
        $this->occupation = $occupation;

        return $this;
    }

    public function getOccupation(): ?string
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

    public function showGender(): self
    {
        $this->hideAttribute = ($this->hideAttribute ^ self::GENDER_HIDDEN);

        return $this;
    }

    public function hideGender(): self
    {
        $this->hideAttribute = ($this->hideAttribute | self::GENDER_HIDDEN);

        return $this;
    }

    public function isNameVisible(): bool
    {
        return ($this->hideAttribute & self::NAME_HIDDEN) !== self::NAME_HIDDEN;
    }

    public function isAddressVisible(): bool
    {
        return ($this->hideAttribute & self::ADDRESS_HIDDEN) !== self::ADDRESS_HIDDEN;
    }

    public function isAgeVisible(): bool
    {
        return ($this->hideAttribute & self::AGE_HIDDEN) !== self::AGE_HIDDEN;
    }

    public function isGenderVisible(): bool
    {
        return ($this->hideAttribute & self::GENDER_HIDDEN) !== self::GENDER_HIDDEN;
    }

    public function showAge(): self
    {
        $this->hideAttribute = ($this->hideAttribute ^ self::AGE_HIDDEN);

        return $this;
    }

    public function hideAge(): self
    {
        $this->hideAttribute = ($this->hideAttribute | self::AGE_HIDDEN);

        return $this;
    }

    public function showAddress(): self
    {
        $this->hideAttribute = ($this->hideAttribute ^ self::ADDRESS_HIDDEN);

        return $this;
    }

    public function hideAddress(): self
    {
        $this->hideAttribute = ($this->hideAttribute | self::ADDRESS_HIDDEN);

        return $this;
    }

    public function setBirthdate(?DateTime $birthdate): self
    {
        $this->birthdate = $birthdate;

        return $this;
    }

    public function getBirthdate(): ?Carbon
    {
        if (null === $this->birthdate) {
            return null;
        }

        return Carbon::instance($this->birthdate);
    }

    public function setHobbies(?string $hobbies): self
    {
        $this->hobbies = $hobbies;

        return $this;
    }

    public function getHobbies(): ?int
    {
        return $this->hobbies;
    }

    public function setBooks(?string $books): self
    {
        $this->books = $books;

        return $this;
    }

    public function getBooks(): ?string
    {
        return $this->books;
    }

    public function setMusic(?string $music): self
    {
        $this->music = $music;

        return $this;
    }

    public function getMusic(): ?string
    {
        return $this->music;
    }

    public function setPastTrips(?string $pastTrips): self
    {
        $this->pastTrips = $pastTrips;

        return $this;
    }

    public function getPastTrips(): ?string
    {
        return $this->pastTrips;
    }

    public function setPlannedTrips(?string $plannedTrips): self
    {
        $this->plannedTrips = $plannedTrips;

        return $this;
    }

    public function getPlannedTrips(): ?string
    {
        return $this->plannedTrips;
    }

    public function setPleaseBring(?string $pleaseBring): self
    {
        $this->pleaseBring = $pleaseBring;

        return $this;
    }

    public function getPleaseBring(): ?string
    {
        return $this->pleaseBring;
    }

    public function setWhereYouSleep(?string $whereYouSleep): self
    {
        $this->whereYouSleep = $whereYouSleep;

        return $this;
    }

    public function getWhereYouSleep(): ?string
    {
        return $this->whereYouSleep;
    }

    public function setOfferGuests(?string $offerGuests): self
    {
        $this->offerGuests = $offerGuests;

        return $this;
    }

    public function getOfferGuests(): ?string
    {
        return $this->offerGuests;
    }

    public function setOfferHosts(?string $offerHosts): self
    {
        $this->offerHosts = $offerHosts;

        return $this;
    }

    public function getOfferHosts(): ?string
    {
        return $this->offerHosts;
    }

    public function setGettingThere(?string $gettingThere): self
    {
        $this->gettingThere = $gettingThere;

        return $this;
    }

    public function getGettingThere(): ?string
    {
        return $this->gettingThere;
    }

    public function setMovies(?string $movies): self
    {
        $this->movies = $movies;

        return $this;
    }

    public function getMovies(): ?string
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

    public function unserialize($data): void
    {
        [$this->id, $this->username, $this->password] = unserialize($data);
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
                    $roles[] = 'ROLE_ADMIN_' . strtoupper((string) $volunteerRight->getRight()->getName());
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
                } catch (Exception) {
                }
            },
            $this->groupMemberships->matching($criteria)->toArray()
        );
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
            $address->setMember($this);
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
            $stripped = strip_tags((string) $value);
        }

        return $stripped;
    }

    public function setHideAttribute(int $hideAttribute): self
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
            if ('Words' === $volunteerRight->getRight()->getName()) {
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
            if (strtolower((string) $volunteerRight->getRight()->getName()) === $nameOfRight) {
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
        return !\in_array(
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

    public function isShortNameVisible(): bool
    {
        return empty($this->shortName);
    }

    public function getShortNameOrUsername(): string
    {
        if ($this->isShortNameVisible()) {
            return $this->getShortName();
        }

        return $this->username;
    }

    public function getLanguageLevels(): array
    {
        return array_filter(
            $this->languageLevels->toArray(),
            function (/* @var MemberLanguageLevel */ $k) {
                try {
                    // Make sure language exists in database
                    $language = $k->getLanguage();
                    $language->getName();
                } catch (Exception) {
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

    public function addLanguageLevel(MemberLanguageLevel $languageLevel): self
    {
        if (!$this->languageLevels->contains($languageLevel)) {
            $this->languageLevels->add($languageLevel);
            $languageLevel->setMember($this);
        }

        return $this;
    }

    public function removeLanguageLevel(MemberLanguageLevel $languageLevel): self
    {
        if ($this->languageLevels->contains($languageLevel)) {
            $this->languageLevels->removeElement($languageLevel);
            $languageLevel->setMember(null);
        }

        return $this;
    }

    public function getLanguages(): array
    {
        return array_map(
            function ($languageLevel) {
                return $languageLevel->getLanguage();
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

    public function getPreferredLanguage(): ?Language
    {
        return $this->preferredLanguage;
    }

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

    public function getRegistrationKey(): ?string
    {
        return $this->registrationKey;
    }

    public function setRegistrationKey(?string $registrationKey): self
    {
        $this->registrationKey = $registrationKey;

        return $this;
    }

    public function getAge(): int
    {
        if (null === $this->birthdate) {
            return 0;
        }

        $birthday = $this->getBirthdate();

        return $birthday->diffInYears();
    }

    public function getAvatar(): string
    {
        return '/members/avatar/' . $this->getUsername();
    }

    public function getPasswordHasherName(): ?string
    {
        if (preg_match('/^\*[0-9A-F]{40}$/', (string) $this->getPassWord())) {
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

    public function setGenderOfGuests(?string $genderOfGuests): self
    {
        $this->genderOfGuests = $genderOfGuests;

        return $this;
    }

    public function getGenderOfGuests(): string
    {
        return $this->genderOfGuests;
    }

    public function getRawTranslations(): Collection
    {
        return $this->translations;
    }

    public function getTranslations(): array
    {
        if (empty($this->translationsIndexedByLocale)) {
            $this->translationsIndexedByLocale = [
                'en' => [],
            ];

            /** @var MemberTranslation $translation */
            foreach ($this->translations as $translation) {
                $locale = $translation->getLocale();
                if (!isset($this->translationsIndexedByLocale[$locale])) {
                    $this->translationsIndexedByLocale[$locale] = [];
                }
                $field = $translation->getField();
                $this->translationsIndexedByLocale[$locale][$field] = $translation->getContent();
            }
        }

        return $this->translationsIndexedByLocale;
    }

    public function addTranslation(MemberTranslation $translation): void
    {
        if (!$this->translations->contains($translation)) {
            $this->translations[] = $translation;
            $translation->setObject($this);
        }
    }

    /**
     * Remove after migration.
     */
    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function setCreated(DateTime $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function setUpdated(DateTime $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;

        return $this;
    }

    public function getActiveAddress()
    {
        $expr = new Comparison('active', Comparison::EQ, true);
        $activeOnly = new Criteria();
        $activeOnly->where($expr);

        return $this->addresses->matching($activeOnly)->first();
    }

    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        if (null === $this->created) {
            $this->created = new DateTime('now');
        }
    }
}
