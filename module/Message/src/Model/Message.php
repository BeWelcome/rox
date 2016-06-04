<?php

namespace Rox\Message\Model;

use Illuminate\Database\Eloquent\Model;
use Rox\Core\Exception\NotFoundException;
use Rox\Member\Model\Member;
use Rox\Message\Repository\MessageRepositoryInterface;

class Message extends Model implements MessageRepositoryInterface
{
    /**
     * Hint to the ORM the name of the created at field, so it will convert it
     * to a carbon object
     */
    const CREATED_AT = 'created';

    /**
     * @var string
     */
    public $table = 'messages';

    /**
     * @var array
     */
    protected $relationships = [
        'sender',
        'receiver',
    ];

    public function sender()
    {
        return $this->hasOne(Member::class, 'id', 'IdSender');
    }

    public function receiver()
    {
        return $this->hasOne(Member::class, 'id', 'IdReceiver');
    }

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

    public function getAttribute($key)
    {
        // The Eloquent implementation of getAttribute will first return the
        // attribute of $key before checking if it has a relationship.
        // We want the opposite of this because we want to define the 'country'
        // key as a relationship to the geoname entity, even though the location
        // table defines a 'country' column.
        if (in_array($key, $this->relationships, true)) {
            return $this->getRelationValue($key);
        }

        return parent::getAttribute($key);
    }

    public function __isset($key)
    {
        return parent::__isset($key) || in_array($key, $this->relationships, true);
    }
}
