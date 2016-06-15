<?php

namespace Rox\Member\Model;

use Rox\Core\Model\AbstractModel;

class Comment extends AbstractModel
{
    const CREATED_AT = 'created';

    /**
     * @var array
     */
    protected $ormRelationships = [
        'fromMember',
        'toMember',
    ];

    public function fromMember()
    {
        return $this->hasOne(Member::class, 'id', 'IdFromMember');
    }

    public function toMember()
    {
        return $this->hasOne(Member::class, 'id', 'IdToMember');
    }
}
