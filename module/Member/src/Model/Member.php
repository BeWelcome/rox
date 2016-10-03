<?php

namespace Rox\Member\Model;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Rox\Auth\Encoder\LegacyPasswordEncoder;
use Rox\Auth\Model\VolunteerRight;
use Rox\Core\Exception\NotFoundException;
use Rox\Core\Exception\RuntimeException;
use Rox\Core\Model\AbstractModel;
use Rox\Geo\Model\Location;
use Rox\I18n\Model\Language;
use Rox\Member\Repository\MemberRepositoryInterface;
use Symfony\Component\Security\Core\Encoder\EncoderAwareInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class Member.
 *
 * @property Collection $comments
 * @property Collection $groups
 * @property Collection $trads
 * @property Collection $preferences
 * @property Collection|MemberRight[] $rights
 * @property int $id
 *
 * @method Builder|HasMany hasMany($related, $foreignKey = null, $localKey = null)
 *
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @todo Maybe use a decorated to implement UserInterface?
 */
class Member extends AbstractModel implements MemberRepositoryInterface, UserInterface, EncoderAwareInterface
{
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * @var string
     */
    protected $table = 'members';

    /**
     * @var array
     */
    protected $dates = [
        'BirthDate',
        'LastLogin',
    ];

    /**
     * @var array
     */
    protected $casts = [
        'Latitude' => 'float',
        'Longitude' => 'float',
    ];

    /**
     * @var array
     */
    protected $ormRelationships = [
        'city',
        'comments',
        'cryptedFields',
        'groups',
        'languages',
        'relationships',
        'trads',
        'preferences',
        'rights',
    ];

    /**
     * @var array
     */
    protected $tradFields = [
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

    public function groups()
    {
        return $this->belongsToMany(Group::class, 'membersgroups', 'IdMember', 'IdGroup')->withPivot('Status');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class, 'IdToMember', 'id');
    }

    public function languages()
    {
        return $this->belongsToMany(Language::class, 'memberslanguageslevel', 'IdMember', 'IdLanguage')
            ->withPivot('Level');
    }

    public function relationships()
    {
        return $this->belongsToMany(self::class, 'specialrelations', 'IdOwner', 'IdRelation')->withPivot([
            'Type',
            'Confirmed',
        ]);
    }

    public function cryptedFields()
    {
        return $this->hasMany(CryptedField::class, 'IdMember', 'id');
    }

    public function city()
    {
        return $this->hasOne(Location::class, 'geonameid', 'IdCity');
    }

    public function trads()
    {
        $model = new \Member();

        $tradFields = $model->get_trads_fields();

        $ids = array_filter($this->attributes, function ($value, $key) use ($tradFields) {
            return (int) $value !== 0 && in_array($key, $tradFields, true);
        }, ARRAY_FILTER_USE_BOTH);

        return HasMany::noConstraints(function () use ($ids) {
            $instance = new Trad();

            /** @var Builder|\Illuminate\Database\Eloquent\Builder $query */
            $query = $instance->newQuery();

            return new HasMany($query->whereIn('IdTrad', $ids), $this, $instance->getTable() . '.' . 'IdOwner', 'id');
        });
    }

    /**
     * @return BelongsToMany
     */
    public function preferences()
    {
        $pivot = $this->belongsToMany(
            Preference::class,
            'memberspreferences',
            'IdMember',
            'IdPreference'
        )->withPivot([
            'Value',
        ]);

        $pivot->withTimestamps('created', 'updated');

        return $pivot;
    }

    /**
     * Sets eager loading for the Right definition of the MemberRight, because
     * a MemberRight by itself doesn't tell us what the right actually is.
     *
     * Alternative way to do this is like preferences, which a Member would
     * belong to many Rights, and the Level, Scope, Comment, etc would become
     * pivot values.
     *
     * @return HasMany
     */
    public function volunteerRights()
    {
        return $this->hasMany(VolunteerRight::class, 'IdMember');

//         $relation->getQuery()->with('right');

//        return $relation;
    }

    /**
     * @param $username
     *
     * @return $this
     *
     * @throws NotFoundException
     */
    public function getByUsername($username)
    {
        $q = $this->newQuery();

        $q->where([
            'Username' => $username,
        ]);

        $member = $q->get()->first();

        if (!$member) {
            throw new NotFoundException();
        }

        return $member;
    }

    /**
     * @param $id
     *
     * @return $this
     *
     * @throws NotFoundException
     */
    public function getById($id)
    {
        $q = $this->newQuery();

        $q->where([
            'Id' => $id,
        ]);

        $member = $q->get()->first();

        if (!$member) {
            throw new NotFoundException();
        }

        return $member;
    }

    public function __get($key)
    {
        $key = $this->normalizeKey($key);

        $value = parent::__get($key);

        if (in_array($key, $this->tradFields, true)) {
            $trad = $this->getTradByLanguage(0, $value);

            return $trad ? $trad->Sentence : '';
        }

        return $value;
    }

    /**
     * Custom query modification can be applied to all queries here.
     *
     * @return Builder|EloquentBuilder
     */
    public function newQuery()
    {
        /** @var Builder $q */
        $q = parent::newQuery();

        return $q;
    }

    /**
     * Convenience method to fetch a crypted row by its respective Member field name.
     *
     * @param $fieldName
     */
    public function getCryptedField($fieldName)
    {
        return $this->cryptedFields->keyBy('TableColumn')->get('members.' . $fieldName);
    }

    public function getTradByLanguage($language, $tradId)
    {
        $allLang = $this->trads->where('IdTrad', $tradId, false);

        $byLang = $allLang->keyBy('IdLanguage');

        if ($exact = $byLang->get($language)) {
            return $exact;
        }

        if ($english = $byLang->get(0)) {
            return $english;
        }

        $allLang = $allLang->sort(function ($a, $b) {
            return ($a['IdLanguage'] < $b['IdLanguage']) ? -1 : 1;
        });

        if ($first = $allLang->first()) {
            return $first;
        }

        return '';
    }

    /**
     * Returns the roles granted to the user.
     *
     * Symfony only calls this once when logging in. The results are stored
     * in the session. So the queries behind checking the admin role are not
     * repeating for each request.
     *
     * @todo There are a few rows for admin rights where level = 0
     *
     * @return string[]
     */
    public function getRoles()
    {
        // Grant user role to everyone
        $roles = [
            'ROLE_USER',
        ];

        $volunteerRights = $this->volunteerRights()->with('right')->get()->all();
        foreach ($volunteerRights as $volunteerRight) {
            if ($volunteerRight->Level !== 0) {
                $roles[] = 'ROLE_ADMIN_' . strtoupper($volunteerRight->right->Name);
            }
        }

        // If additional roles are found add ROLE_ADMIN as well to get past the /admin firewall
        if (count($roles) > 1) {
            $roles[] = 'ROLE_ADMIN';
        }

        return $roles;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->PassWord;
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string|null The salt
     */
    public function getSalt()
    {
        return;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->Username;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getPotentialGuests()
    {
        $tripModel = new \TripsModel();
        $potentialGuests = $tripModel->getTripsNearMe($this, 1, 2);

        return $potentialGuests;
    }

    /**
     * Returns an array with all rights assigned to the user
     * (Level at least 1)
     *
     * return array string
     */
    public function getRights()
    {
        $rights = [];
        $volunteerRights = $this->volunteerRights()->get();
        foreach ($volunteerRights as $volunteerRight) {
            if ($volunteerRight->Level > 0) {
                $rights[] = $volunteerRight->right->Name;
            }
        }
        return $rights;
    }

    /**
     * $rightName is case sensitive.
     *
     * Port of MOD_right_flag::hasRight
     *
     * @param $rightName
     * @param $scope
     *
     * @return int
     */
    public function getRightLevel($rightName, $scope = null)
    {
        $rights = $this->rights;

        $right = $rights->where('right.Name', $rightName)->first();

        // Right doesn't exist
        if (!$right) {
            return 0;
        }

        // If the right level is 0, no need to check the scope.
        if ((int) $right->Level === 0) {
            return 0;
        }

        // If no scope was requested, just return the level
        if (!$scope) {
            return (int) $right->Level;
        }

        // Break the available scopes into an array and clean them
        $scopes = explode(',', $right->Scope);

        // Trim quotes and spaces (\x20)
        $scopes = array_map(function ($value) {
            return trim($value, "\"\x20");
        }, $scopes);

        $scopes = array_map('strtolower', $scopes);

        // If one of the available scopes is 'all', then allow any requested
        // scope.
        if (in_array('all', $scopes, true)) {
            return (int) $right->Level;
        }

        // Requested scope isn't available
        if (!in_array(strtolower($scope), $scopes, true)) {
            return 0;
        }

        return (int) $right->Level;
    }

    public function isBrowseable()
    {
        if (in_array(
            $this->Status,
            [
            'TakenOut',
            'SuspendedBeta',
            'AskToLeave',
            'Buggy',
            'Banned',
            'Rejected',
            'DuplicateSigned', ],
            true
        )) {
            return false;
        }
        return true;
    }

    /**
     * Gets the name of the encoder used to encode the password.
     *
     * If the method returns null, the standard way to retrieve the encoder
     * will be used instead.
     *
     * @return string|null
     *
     * @throws RuntimeException Password not supported.
     */
    public function getEncoderName()
    {
        if (preg_match('/^\*[0-9A-F]{40}$/', $this->PassWord)) {
            return LegacyPasswordEncoder::class;
        }

        if (!preg_match('/^\$2y\$[0-9]{2}\$.{53}$/', $this->PassWord)) {
            throw new RuntimeException('Password is neither bcrypt or legacy sha1.');
        }
    }
}
