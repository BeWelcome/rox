<?php

namespace Rox\Member\Model;

use Countable;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder;
use Rox\Core\Exception\NotFoundException;
use Rox\Geo\Model\Location;
use Rox\I18n\Model\Language;
use Rox\Member\Repository\MemberRepositoryInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Traversable;

/**
 * Class Member
 *
 * @property Traversable|Countable $comments
 * @property Traversable|Countable $groups
 * @property Collection $trads
 * @property integer $id
 * @method Builder|HasMany hasMany($a, $b, $c)
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 *
 * @todo Maybe use a decorated to implement UserInterface?
 */
class Member extends Model implements MemberRepositoryInterface, UserInterface
{
    /**
     * @var string
     */
    protected $table = 'members';

    /**
     * @var boolean
     */
    public $timestamps = false;

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
    protected $modelRelationships = [
        'city',
        'comments',
        'cryptedFields',
        'groups',
        'languages',
        'relationships',
        'trads',
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

    public function __isset($key)
    {
        $key = $this->normalizeKey($key);

        return parent::__isset($key) || in_array($key, $this->modelRelationships, true);
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

    protected function normalizeKey($key)
    {
        $keys = array_keys($this->attributes);

        $lcKeys = array_map('strtolower', $keys);

        $position = array_search(strtolower($key), $lcKeys, true);

        if (!$position) {
            return $key;
        }

        return $keys[$position];
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
     * Convenience method to fetch a crypted row by its respective Member field name
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
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return (Role|string)[] The user roles
     */
    public function getRoles()
    {
        // TODO: Implement getRoles() method.
        return ['ROLE_USER'];
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
}
