<?php

namespace Rox\Message\Model;

use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder;
use Rox\Core\Exception\NotFoundException;
use Rox\Core\Model\AbstractModel;
use Rox\Member\Model\Member;
use Rox\Message\Repository\MessageRepositoryInterface;

/**
 * @property integer $id
 * @property-read Member $receiver
 */
class Message extends AbstractModel implements MessageRepositoryInterface
{
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
    protected $ormRelationships = [
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
}
