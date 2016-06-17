<?php

namespace Rox\Member\Model;

use Rox\Core\Model\AbstractModel;

class Trad extends AbstractModel
{
    const CREATED_AT = 'created';
    const UPDATED_AT = 'updated';

    /**
     * @var string
     */
    protected $table = 'memberstrads';

    /**
     * @var array
     */
    protected $ormRelationships = [
        'fromMember',
    ];

    public function member()
    {
        return $this->hasOne(Member::class, 'id', 'IdOwner');
    }
}
