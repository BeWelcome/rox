<?php

namespace Rox\Message\Model;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder;
use Rox\Core\Exception\NotFoundException;
use Rox\Core\Model\NullableDateFixTrait;
use Rox\Member\Model\Member;
use Rox\Message\Repository\MessageRepositoryInterface;

/**
 * @property integer $id
 * @property-read Member $receiver
 */
class Message extends Model implements MessageRepositoryInterface
{
    use NullableDateFixTrait;

    const FOLDER_INBOX = 'Normal';
    const FOLDER_SPAM = 'Spam';

    const STATE_READ = 'read';
    const STATE_UNREAD = 'unread';

    /**
     * Hint to the ORM the name of the created at field, so it will convert it
     * to a carbon object
     */
    const CREATED_AT = 'created';

    const UPDATED_AT = 'updated';

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

    /**
     * @var array
     */
    protected $dates = [
        'DateSent',
        'WhenFirstRead',
    ];

    public function isUnread()
    {
        // Legacy logic would also check for '0000-00-00 00:00:00', because the
        // field is not nullable. However, we now return null for all dates with
        // that value, so a null check is sufficient.
        return is_null($this->WhenFirstRead);
    }

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
        return parent::__isset($key)
            || in_array($key, $this->dates, true)
            || in_array($key, $this->relationships, true);
    }
}
